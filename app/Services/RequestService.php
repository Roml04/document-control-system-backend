<?php

namespace App\Services;

use App\Enums\ManagersApprovalDecision;
use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Models\Comment;
use App\Models\File;
use App\Models\ManagersApproval;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

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
          $requests = RequestModel::with('version')->where("status", "coordinator_approval")->get();
        }

        if($role === 'superior') {
          $requests = RequestModel::with('version')->where("status", "superior_approval")->get();
        }

        if($role === 'manager') {
          $requests = RequestModel::with('version')->where("status", "managers_approval")->whereHas("managersApproval", function ($query) use($user) {
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

    public function createRequest(Request $request) {
      $validated = $request->validate([
        "type" => ["required", "in:upl,rev,resub"],
        "title" => ["required", "string"],
        "reason" => ["required", "string"],

        "originator" => ["required", "string"],
        "department" => ["required", "string"],
        "revisionNumber" => ["required", "string"],
        "revisionDetails" => ["required", "string"],
        "approver" => ["required", "string"],
        "fileId" => ["nullable", "exists:files,id"],
        "fileTitle" => ["required","string"],
        "fileType" => ["in:document,checklist,form"],
        "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
      ]);

      $user = $request->user();
      $requestingUserId = $user->id;

      $uploadedFile = $request->file("file");
      $uploadedFile->getClientOriginalExtension();

      $fileName = strtolower("$user->first_name$user->last_name") . "-" . now()->format('YmdHsu') . "." . $uploadedFile->getClientOriginalExtension();
      $filePath = $uploadedFile->storeAs('versions', $fileName);

      $validatedWithFile = [
        ...$validated,
        "userId" => $requestingUserId,
        "fileName" => $fileName,
        "filePath" => $filePath,
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

    public function updateStatus(bool $isApproved, int $requestId, int $userId, ?string $comment) {
      DB::transaction(function () use($isApproved, $requestId, $userId, $comment) {
        $requestItem = RequestModel::where("id", $requestId)->firstOrFail();
        
        $requestItem->update([
          "status" => $isApproved ? getNextStatus($requestItem->type, $requestItem->status) : "denied",
        ]);

        if($requestItem->status === "denied") {
          $requestItem->version->update([
            "status" => "rejected"
          ]);
        }

        if($requestItem->status === "managers_approval") {
          $this->createManagerDecisions($requestItem->id);
        }

        if($comment) {
          Comment::create([
            "content" => $comment,
            "user_id" => $userId,
            "request_id" => $requestId
          ]);
        }

        return $requestItem;
      });
    }

    public function createManagerDecisions(int $requestId) {

      $managerIds = User::where('role', UserRole::Manager)->pluck('id');

      DB::transaction(function() use($managerIds, $requestId) {
        foreach($managerIds as $managerId) {
          ManagersApproval::create([
            "manager_id" => $managerId,
            "request_id" => $requestId,
            "decision" => ManagersApprovalDecision::Pending,
            "decided_at" => null
          ]);
        }
      });
    }

    public function updateManagerDecision(int $requestId, int $managerId, bool $isApproved, ?string $comment) {  
      $authManagerDecision = ManagersApproval::with('request')->where(['request_id' => $requestId, 'manager_id' => $managerId])->firstOrFail();
      
      $authManagerDecision->update(['decision' => $isApproved ? "approved" : "denied", "decided_at" => now()]);
      
      $allDecisions = ManagersApproval::where(['request_id' => $requestId])->get();

      /**
       * Checks if there are no pending decisions and if there is at least one decision that was denied
       */
      if($allDecisions->where('decision', 'pending')->count() === 0 && $allDecisions->where('decision', 'denied')->count() > 0) {
        $relatedRequest = $authManagerDecision->request;
        $this->denyRequest($relatedRequest);
      }

      /**
       * Checks if the amount of approved decisions with this particular request is the same with the total amount of created decisions. 
       */
      if($allDecisions->count() === $allDecisions->where('decision', 'approved')->count()) {
        $this->approveRequest($authManagerDecision->request);
      }

      if($comment) {
        Comment::create([
          "content" => $comment,
          "user_id" => $managerId,
          "request_id" => $requestId
        ]);
      }

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => 'IDK'
      ]);
    }

    public function approveRequest(RequestModel $requestItem) {

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

      return $requestItem;
    }

    public function denyRequest(RequestModel $requestItem) {
      $relatedVersion = $requestItem->load('version')->version;
      
      $requestItem->update([
        "status" => "denied"
      ]);

      $relatedVersion->update([
        "status" => "rejected"
      ]);

      return $requestItem;
    }
}
