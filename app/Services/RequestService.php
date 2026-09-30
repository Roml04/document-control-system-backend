<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Mail\DelRequestApproved;
use App\Mail\FileDeleted;
use App\Mail\ManagerDecided;
use App\Mail\NotifyCoordinator;
use App\Mail\NotifyManagers;
use App\Mail\NotifySuperior;
use App\Mail\RequestDenied;
use App\Mail\RequestUpdated;
use App\Mail\RevRequestApproved;
use App\Mail\UplRequestApproved;
use App\Models\Comment;
use App\Models\File;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class RequestService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
      protected ManagersApprovalService $managersApprovalService, 
      protected VersionService $versionService
    ) {}

    public function showRequests(Request $request) {

      $user = $request->user();

      $forApprovals = DB::transaction(function () use($user) {
        $requests = [];

        $role = $user->role;

        /**
         * NOTE: use enum here
         */
        if($role === 'originator') {
          return $requests;
        }

        if($role === 'coordinator') {
          $requests = RequestModel::with(['version', 'user:id,first_name,last_name,role'])->where("status", "coordinator_approval")->get();
        }

        if($role === 'superior') {
          $requests = RequestModel::with(['version', 'user:id,first_name,last_name,role'])->where("status", "superior_approval")->get();
        }

        if($role === 'manager') {
          $requests = RequestModel::with(['version', 'user:id,first_name,last_name,role'])->where("status", "managers_approval")->whereHas("managersApproval", function ($query) use($user) {
            $query->where(['manager_id' => $user->id, 'decision' => 'pending']);
          })->get();
        }

        if($role === 'sysadmin') {
          $requests = RequestModel::all();
        }

        return $requests->sortByDesc("created_at")->values();
      });

      $user->request->load(['version:id,request_id,upload_date', 'user:id,first_name,last_name,role']);

      return [
        "myRequests" => RequestResource::collection($user->request->sortByDesc('created_at')->values()),
        "forApprovals" => RequestResource::collection($forApprovals)
      ];
    }

    public function createUplRequest(array $validated, User $user, UploadedFile $uploadedFile) {
      $fileName = formatFileName($user->first_name, $user->last_name, $uploadedFile->getClientOriginalExtension());

      $validatedWithFile = [
        ...$validated,
        "userId" => $user->id,
        "fileName" => $fileName,
        "filePath" => $uploadedFile->storeAs('versions', $fileName),
      ];
      
      DB::transaction(function() use($validatedWithFile) {
        $requestItem = RequestModel::create([
          "type" => 'upl',
          "title" => $validatedWithFile["title"],
          "reason" => $validatedWithFile["reason"],
          "status" => "coordinator_approval",
          "user_id" => $validatedWithFile["userId"],
        ]);
        
        Version::create([
          "file_title" => $validatedWithFile["fileTitle"],
          "file_type" => $validatedWithFile["fileType"],
          "originator" => $validatedWithFile["originator"],
          "department" => $validatedWithFile["department"],
          "revision_number" => $validatedWithFile["revisionNumber"],
          "revision_details" => $validatedWithFile["revisionDetails"],
          "upload_date" => now(),
          "revision_date" => now(),
          "approver" => $validatedWithFile["approver"],
          "status" => "pending",
          "file_name" => $validatedWithFile["fileName"],
          "file_path" => $validatedWithFile["filePath"], 
          "file_id" => $validatedWithFile["fileId"] ?? null,
          "request_id" => $requestItem->id,
        ]);

        $this->notifyApprover(UserRole::Coordinator, $requestItem);
      });
    }

    public function createRevRequest(array $validated, User $user) {
      $version = Version::findOrFail($validated["latestVersionId"]);
      $userId = $user->id;

      DB::transaction(function () use($validated, $version, $userId) {
        $requestItem = RequestModel::create([
          "type" => 'rev',
          "title" => $validated['title'],
          "reason" => $validated['reason'],
          "status" => "coordinator_approval",
          "user_id" => $userId,
          "file_id" => $version->file->id
        ]);

        /**
         * NOTE: Use replicate here
         */
        Version::create([
          "file_title" => $version->file_title,
          "file_type" => $version->file_type,
          "originator" => $version->originator,
          "department" => $version->department,
          "revision_number" => $version->revision_number,
          "revision_details" => $version->revision_details,
          "upload_date" => $version->upload_date,
          "revision_date" => $version->revision_date,
          "approver" => $version->approver,
          "status" => "pending",
          "file_name" => $version->file_name,
          "file_path" => $version->file_path,
          "file_id" => $version->file_id,
          "request_id" => $requestItem->id,
        ]);
        
        $this->notifyApprover(UserRole::Coordinator, $requestItem);
      });
    }

    public function createResubRequest(array $validated, User $user, UploadedFile | null $uploadedFile) {
      DB::transaction(function () use($validated, $user, $uploadedFile) {
        $fileName = null;
        $filePath = null;

        $requestItem = RequestModel::with(["version"])->findOrFail($validated["requestId"]);
        $version = $requestItem->version()->latest()->firstOrFail();

        if(!$uploadedFile) {

          $fileName = formatFileName($user->first_name, $user->last_name, pathinfo($version->file_name, PATHINFO_EXTENSION));
          $filePath = "/versions/$fileName";

          if(Storage::exists("/draft/$version->file_name")) {
            /**
             * Moves the edited file from /draft to 
             * /versions and renames it
             */
            Storage::move("/draft/$version->file_name", $filePath);
          } elseif(Storage::exists("/versions/$version->file_name")) {
            /**
             * Creates a copy of the published version 
             * on the same file directory
             */
            Storage::copy("/versions/$version->file_name", $filePath);
          } else {
            /**
             * Copies the file from /rejected if the 
             * does not exist on both /draft and /versions 
             */
            Storage::copy("/rejected/$version->file_name", $filePath);
          }
        }

        if($uploadedFile) {
          /**
           * Saves the uploaded file in /versions if existing
           * then changes the $fileName and $filePath
           */
          $fileName = formatFileName($user->first_name, $user->last_name, $uploadedFile->getClientOriginalExtension());
          $filePath = $uploadedFile->storeAs("versions", $fileName);
        }

        $requestItem->update([
          "title" => $validated["title"],
          "reason" => $validated["reason"],
          "status" => "coordinator_approval"
        ]);

        $newVersion = $version->replicate()->fill([
          ...($validated["fileTitle"] ? ["file_title" => $validated["fileTitle"]] : []),
          ...($validated["fileType"] ? ["file_type" => $validated["fileType"]] : []),
          ...($validated["originator"] ? ["originator" => $validated["originator"]] : []),
          ...($validated["department"] ? ["department" => $validated["department"]] : []),
          ...($validated["revisionNumber"] ? ["revision_number" => $validated["revisionNumber"]] : []),
          ...($validated["revisionDetails"] ? ["revision_details" => $validated["revisionDetails"]] : []),
          ...($validated["approver"] ? ["approver" => $validated["approver"]] : []),
          "approved_date" => null,
          "status" => "pending",
          ...($fileName ? ["file_name" => $fileName] : []),
          ...($filePath ? ["file_path" => $filePath] : []),
        ]);

        $newVersion->save();

        /**
         * Deletes all comments
         */
        $requestItem->comment()->delete();

        /**
         * Deletes all previous manager decisions
         */
        $requestItem->managersApproval()->delete();

        /**
         * DEV-NOTE: Delete residual file in /draft
         */

        $this->notifyApprover(UserRole::Coordinator, $requestItem);
      });
    }

    public function createDelRequest(array $validated, int $userId) {
      DB::transaction(function() use($validated, $userId) {
        $requestItem = RequestModel::create([
          "type" => "del",
          "title" => $validated["title"],
          "reason" => $validated["reason"],
          "status" => "coordinator_approval",
          "user_id" => $userId,
          "file_id" => $validated["fileId"],
        ]);

        /**
         * Creates a duplicate version with the id of the 
         * delete request and set the status to pending to
         * avoid conflicti  ng with the file's latest version  
         */
        $relatedVersion = Version::findOrFail($validated["latestVersionId"]);

        $newVersion = $relatedVersion->replicate()->fill([
          "request_id" => $requestItem->id,
          "status" => "pending"
        ]);

        $newVersion->save();
        
        $this->notifyApprover(UserRole::Coordinator, $requestItem);
      });
    }

    public function updateUplRequest(array $validated, User $user) {
      $requestItem = RequestModel::findOrFail($validated["requestId"]);

      $this->approvalProcess($requestItem->status, $validated, $requestItem, $user->id);
    }

    public function updateRevRequest(array $validated, User $user) {
      $requestItem = RequestModel::findOrFail($validated["requestId"]);

      $this->approvalProcess($requestItem->status, $validated, $requestItem, $user->id);
    }

    public function updateDelRequest(array $validated, int $userId) {
      DB::transaction(function() use($validated, $userId) {
        $requestItem = RequestModel::findOrFail($validated["requestId"]);

        $reqStatus = $requestItem->status;

        $requestItem->update([
          "status" => $validated['isApproved'] ? getNextStatus($requestItem->type, $requestItem->status, $requestItem->was_edited) : "denied",
        ]);

        $reqStatus = $requestItem->status;

        $comment = $validated["comment"];

        if($comment) {
          Comment::create([
            "content" => $comment,
            "user_id" => $userId,
            "request_id" => $validated['requestId']
          ]);
        }

        if($reqStatus === "denied") {
          $this->finalizeRequest($requestItem, false);
          
          return;
        }

        if($reqStatus === "approved") {
          $this->finalizeRequest($requestItem, true);
          
          return;
        }

        Mail::to($requestItem->user)->send(new RequestUpdated($requestItem, $userId));

        $this->notifyApprover(UserRole::Superior, $requestItem);
      });
    }

    public function finalizeRequest(RequestModel $requestItem, bool $decision) { 
      DB::transaction(function() use($requestItem, $decision) {
        $relatedVersion = $requestItem->version()->latest()->firstOrFail();

        if(!$decision) {
          $requestItem->update([
            "status" => "denied"
          ]);
          
          $relatedVersion->update([
            "status" => "rejected"
          ]);

          $currentPublished = Version::where(['file_id' => $relatedVersion->file_id, 'status' => 'published'])
            ->first();

          $isSameFile = $currentPublished && $currentPublished->file_path === $relatedVersion->file_path;

          $requestItem->update([
            "was_edited" => !$isSameFile
          ]);
          /**
           * Move the published file to /rejected if
           * the published file and the current file
           * is not the same and update the file_path
           */
          if(!$isSameFile) {
            $rejectedVersionFilePath = "rejected/$relatedVersion->file_name";

            Storage::move($relatedVersion->file_path, $rejectedVersionFilePath);

            $relatedVersion->update([
              "file_path" => $rejectedVersionFilePath
            ]);
          }    

          return;
        }

        /**
         * Deletes the related file if request type is "del"
         */
        if($requestItem->type === "del") {
          $requestItem->update([
            "status" => "approved",
          ]);
          
          $file = File::findOrFail($requestItem->file_id);

          Storage::move($relatedVersion->file_path, "/rejected/$relatedVersion->file_name");

          $relatedVersion->update([
            "status" => "rejected"
          ]);

          $file->version()->where(["status" => "published"])->latest()->firstOrFail()->update([
            "status" => "rejected"
          ]);

          $file->delete();

          Mail::to($requestItem->user)->send(new DelRequestApproved($requestItem));

          $managers = User::where(['role' => UserRole::Manager])->get();

          foreach($managers as $manager) {
            Mail::to($manager)->send(new FileDeleted($requestItem));
          }
          return;
        }
      
        /**
         * Creates a file and updates the file_id of both the version and request 
         */
        if($requestItem->type === "upl") {
          $file = File::create([
            "title" => $relatedVersion->file_title,
            "type" => $relatedVersion->file_type
          ]);

          $relatedVersion->update([
            "file_id" => $file->id,
          ]);

          $requestItem->update([
            "file_id" => $file->id,
            "status" => "approved",
          ]);

          Mail::to($requestItem->user)->send(new UplRequestApproved($requestItem));
        }

        /**
         * Updates the related file
         */
        if($requestItem->type === "rev") {
          $relatedFile = $relatedVersion->file;

          $relatedFile->update([
            'title' => $relatedVersion->file_title,
            'type' => $relatedVersion->file_type
          ]);

          $requestItem->update([
            "status" => "approved",
          ]);
          
          Mail::to($requestItem->user)->send(new RevRequestApproved($requestItem));
        }

        $relatedVersion->update([
          "approved_date" => now(),
          "status" => "published"
          ]);
          
      });
    }

    public function approvalProcess(string $reqStatus, array $validated, RequestModel $requestItem, int $userId) {

      DB::transaction(function () use($reqStatus, $validated, $requestItem, $userId) {
        $comment = $validated["comment"];

        if($reqStatus === "managers_approval") {
          $decision = $this->managersApprovalService
            ->updateManagerDecision($validated, $userId);

          if($decision !== null) {
            $this->finalizeRequest($requestItem, $decision);
          }

          if($comment) {
            Comment::create([
              "content" => $comment,
              "user_id" => $userId,
              "request_id" => $validated['requestId']
            ]);
          }

          Mail::to($requestItem->user)->send(new ManagerDecided($requestItem, $userId));

          return;
        }

        $requestItem->update([
          "status" => $validated['isApproved'] ? getNextStatus($requestItem->type, $requestItem->status, $requestItem->was_edited) : "denied",
        ]);

        if($comment) {
          Comment::create([
            "content" => $comment,
            "user_id" => $userId,
            "request_id" => $validated['requestId']
          ]);
        }

        $newRequestStatus = $requestItem->status;

        if($newRequestStatus !== "denied" && $newRequestStatus !== "approved") {
          Mail::to($requestItem->user)->send(new RequestUpdated($requestItem, $userId));
        }

        if($newRequestStatus === "superior_approval") {
          $this->notifyApprover(UserRole::Superior, $requestItem);
        }

        if($newRequestStatus === "managers_approval") {
          $this->managersApprovalService->createManagerDecisions($requestItem->id);

          $this->notifyApprover(UserRole::Manager, $requestItem);
          return;
        }

        if($newRequestStatus === "denied") {
          $this->finalizeRequest($requestItem, false);
          Mail::to($requestItem->user)->send(new RequestDenied($requestItem));
          
          return; 
        }

        if($newRequestStatus === "approved") {
          $this->finalizeRequest($requestItem, true);

          return;
        }
      });

    }

    public function notifyApprover(UserRole $approver, RequestModel $requestItem) {
      $approvers = User::where(['role' => $approver])->get();

      switch($approver) {
        case UserRole::Coordinator:
          foreach($approvers as $coordinator) {
            Mail::to($coordinator)->send(new NotifyCoordinator($requestItem));
          }
          return;

        case UserRole::Superior:
          foreach($approvers as $superior) {
            Mail::to($superior)->send(new NotifySuperior($requestItem));
          }
          return;

        case UserRole::Manager:
          foreach($approvers as $manager) {
            Mail::to($manager)->send(new NotifyManagers($requestItem));
          }
          return;

        default:
          throw new InvalidArgumentException("Invalid approver: $approver");
      }
    }
}

