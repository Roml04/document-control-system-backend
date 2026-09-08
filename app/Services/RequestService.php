<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Models\Comment;
use App\Models\File;
use App\Models\ManagersApproval;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
      $requestingUserId = $user->id;

      $fileName = strtolower("$user->first_name$user->last_name") . "-" . now()->format('YmdHsu') . "." . $uploadedFile->getClientOriginalExtension();

      $validatedWithFile = [
        ...$validated,
        "userId" => $requestingUserId,
        "fileName" => $fileName,
        "filePath" => $uploadedFile->storeAs('versions', $fileName),
      ];

      DB::transaction(function() use($validatedWithFile) {
        $requestModel = RequestModel::create([
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
          "request_id" => $requestModel->id,
        ]);
      });
    }

    public function createRevRequest(array $validated, User $user) {
      $versionWithFile = Version::findOrFail($validated["latestVersionId"]);
      $userId = $user->id;

      DB::transaction(function () use($validated, $versionWithFile, $userId) {
        $requestItem = RequestModel::create([
          "type" => 'rev',
          "title" => $validated['title'],
          "reason" => $validated['reason'],
          "status" => "coordinator_approval",
          "user_id" => $userId
        ]);

        Version::create([
          "file_title" => $versionWithFile->file_title,
          "file_type" => $versionWithFile->file_type,
          "originator" => $versionWithFile->originator,
          "department" => $versionWithFile->department,
          "revision_number" => $versionWithFile->revision_number,
          "revision_details" => $versionWithFile->revision_details,
          "upload_date" => $versionWithFile->upload_date,
          "revision_date" => $versionWithFile->revision_date,
          "approver" => $versionWithFile->approver,
          "approved_date" => $versionWithFile->approved_date,
          "status" => "pending",
          "file_name" => $versionWithFile->file_name,
          "file_path" => $versionWithFile->file_path,
          "file_id" => $versionWithFile->file_id,
          "request_id" => $requestItem->id,
        ]);
      });
    }

    public function createResubRequest() {}

    public function createDelRequest() {}

    public function updateUplRequest(array $validated, User $user) {
      $userId = $user->id;
      $decision = null;

      /**
       * Checks if the current user is manager or not
       */
      if($user->role === UserRole::Manager->value) {
        $this->managersApprovalService
          ->updateManagerDecision($validated, $userId);

        $decision = $this->managersApprovalService
          ->checkAllDecisions($validated['requestId']);
      } else {
        /**
         * updates all requests without the managers_approval status
         */
        $this->updateNonManagerRequest($validated, $userId);
      }
      
      if($decision !== null) {
        $request = ManagersApproval::with('request')
          ->where(['request_id' => $validated['requestId'], 'manager_id' => $userId])
          ->firstOrFail()->request;

        $this->finalizeRequest($request, $decision);
      }
    }

    public function updateNonManagerRequest(array $validated, int $userId) {
      DB::transaction(function () use($validated, $userId) {
        $comment = $validated['comment'];
        $requestItem = RequestModel::where("id", $validated['requestId'])->firstOrFail();
        
        $requestItem->update([
          "status" => $validated['isApproved'] ? getNextStatus($requestItem->type, $requestItem->status) : "denied",
        ]);
        
        if($requestItem->status === "denied") {
          $requestItem->version->update([
            "status" => "rejected"
          ]);

          Storage::move($requestItem->version->file_path, "/rejected/" . $requestItem->version->file_path);
        }

        if($requestItem->status === "managers_approval") {
          $this->managersApprovalService->createManagerDecisions($requestItem->id);
        }

        if($comment) {
          Comment::create([
            "content" => $comment,
            "user_id" => $userId,
            "request_id" => $validated['requestId']
          ]);
        }
      });
    }

    public function finalizeRequest(RequestModel $requestItem, bool $decision) {
      DB::transaction(function() use($requestItem, $decision) {
        if($decision) {
          $relatedVersion = $requestItem->load('version')->version;

          if($requestItem->type === "upl") {
            $file = File::create([
              "title" => $relatedVersion->file_title,
              "type" => $relatedVersion->file_type
            ]);

            $relatedVersion->update([
              "file_id" => $file->id,
            ]);
          }

          if($requestItem->type === "rev") {
            $relatedFile = $relatedVersion->load('file')->file;

            $relatedFile->update([
              'title' => $relatedVersion->file_title,
              'type' => $relatedVersion->file_type
            ]);
          }

          if($requestItem->type === "resub") {
            /**
             * Upsert
             */
          }

          $requestItem->update([
            "status" => "approved"
          ]);

          $relatedVersion->update([
            "approved_date" => now(),
            "status" => "published"
          ]);

        } else {

          $relatedVersion = $requestItem->load('version')->version;
          
          $requestItem->update([
            "status" => "denied"
          ]);

          $relatedVersion->update([
            "status" => "rejected"
          ]);

          Storage::move($relatedVersion->file_path, "/rejected/$relatedVersion->file_path");
        }
      });
    }
}
