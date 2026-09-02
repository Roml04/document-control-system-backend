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
          "type" => $validatedWithFile["type"],
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
      RequestModel::create([
        "type" => 'rev',
        "title" => $validated['title'],
        "reason" => $validated['reason'],
        "status" => "coordinator_approval",
        "user_id" => $user->id
      ]);
    }

    public function createResubRequest() {}

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
      
      if($decision) {
        $relatedVersion = $requestItem->load('version')->version;

        $file = File::create([
          "title" => $relatedVersion->file_title,
          "type" => $relatedVersion->file_type
        ]);

        $requestItem->update([
          "status" => "approved"
        ]);

        $relatedVersion->update([
          "approved_date" => now(),
          "file_id" => $file->id,
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
      }
    }
}
