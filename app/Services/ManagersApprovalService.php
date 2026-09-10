<?php

namespace App\Services;

use App\Enums\ManagersApprovalDecision;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\ManagersApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ManagersApprovalService
{
    public function __construct() {}

    /**
     * Creates entries of pending decisions on the managersapprovals table
     * for each manager.
     */
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

    /**
     * Updates the decision of the current manager on a certain request.
     */
    public function updateManagerDecision(array $validated, int $userId) {  
      $comment = $validated['comment'];
      $requestId = $validated['requestId'];

      $authManagerDecision = ManagersApproval::with('request')
        ->where([
            'request_id' => $validated['requestId'],
            'manager_id' => $userId
          ])
        ->firstOrFail();

      $authManagerDecision->update(['decision' => $validated['isApproved'] ? "approved" : "denied", "decided_at" => now()]);

      if($comment) {
        Comment::create([
          "content" => $comment,
          "user_id" => $userId,
          "request_id" => $requestId
        ]);
      }

      return $this->areAllApproved($requestId);
    }

    /**
     * Checks all the decisions related to a certain request and returns a bool value.
     * The returned value is TRUE if all decisions are approved
     * The returned value is FALSE if at least one decision is denied. 
     * The returned value is NULL if not all managers have decided yet.
     */
    public function areAllApproved(int $requestId) {
      $allDecisions = ManagersApproval::where(['request_id' => $requestId])->get();
      /**
       * Checks if there are no pending decisions and if there is at least one decision that was denied
       */
      if($allDecisions->where('decision', 'pending')->count() === 0 && $allDecisions->where('decision', 'denied')->count() > 0) {
        return false;
      }

      /**
       * Checks if the amount of approved decisions with this particular request is the same with the total amount of created decisions. 
       */
      if($allDecisions->count() === $allDecisions->where('decision', 'approved')->count()) {
        return true;
      }

      return null;
    }
}
