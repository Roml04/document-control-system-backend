<?php

namespace App\Services;

use App\Enums\ManagersApprovalDecision;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\File;
use App\Models\ManagersApproval;
use App\Models\Request as RequestModel;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;

class RequestService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
