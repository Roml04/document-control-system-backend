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
          "approved_date" => $version->approved_date,
          "status" => "pending",
          "file_name" => $version->file_name,
          "file_path" => $version->file_path,
          "file_id" => $version->file_id,
          "request_id" => $requestItem->id,
        ]);
      });
    }

    public function createResubRequest() {}

    public function createDelRequest(array $validated, User $user) {
      $userId = $user->id;

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
         * avoid conflicting with the file's latest version  
         */
        $relatedVersion = Version::findOrFail($validated["latestVersionId"]);

        $newVersion = $relatedVersion->replicate()->fill([
          "request_id" => $requestItem->id,
          "status" => "pending"
        ]);

        $newVersion->save();
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

    public function updateDelRequest(array $validated, User $user) {
      $requestItem = RequestModel::findOrFail($validated["requestId"]);

      $reqStatus = $requestItem->status;

      $requestItem->update([
        "status" => $validated['isApproved'] ? getNextStatus($requestItem->type, $requestItem->status) : "denied",
      ]);

      $reqStatus = $requestItem->status;

      $comment = $validated["comment"];

      if($comment) {
        Comment::create([
          "content" => $comment,
          "user_id" => $user->id,
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
    }

    public function finalizeRequest(RequestModel $requestItem, bool $decision) { 
      DB::transaction(function() use($requestItem, $decision) {
        if(!$decision) {
          $relatedVersion = $requestItem->version;

          $relatedVersion->update([
            "status" => "rejected"
          ]);

          Storage::move($relatedVersion->file_path, "/rejected/$relatedVersion->file_path");

          return;
        }

        /**
         * Deletes the related file if reques type is "del"
         */
        if($requestItem->type === "del") {
          $requestItem->update([
            "status" => "approved",
          ]);

          $relatedVersion = $requestItem->version;
          
          File::destroy($requestItem->file_id);

          $relatedVersion->delete();

          return;
        }
      
        $relatedVersion = $requestItem->load('version')->version;
        
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
          ]);
        }

        /**
         * Updates the related file
         */
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
          "status" => "approved",
        ]);

        $relatedVersion->update([
          "approved_date" => now(),
          "status" => "published"
        ]);
      });
    }

    public function approvalProcess(string $reqStatus, array $validated, RequestModel $requestItem, int $userId) {

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

        return;
      }

      $requestItem->update([
        "status" => $validated['isApproved'] ? getNextStatus($requestItem->type, $requestItem->status) : "denied",
      ]);

      if($comment) {
        Comment::create([
          "content" => $comment,
          "user_id" => $userId,
          "request_id" => $validated['requestId']
        ]);
      }

      $reqStatus = $requestItem->status;

      if($reqStatus === "managers_approval") {
        $this->managersApprovalService->createManagerDecisions($requestItem->id);

        return;
      }

      if($reqStatus === "denied") {
        $this->finalizeRequest($requestItem, false);

        return;
      }

      if($reqStatus === "approved") {
        $this->finalizeRequest($requestItem, true);

        return;
      }
    }
}

