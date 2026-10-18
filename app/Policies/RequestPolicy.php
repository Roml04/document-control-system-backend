<?php

namespace App\Policies;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
    }

    public function adminViewAny(User $user): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Request $request): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
      return in_array($user->role, ["originator", "sysadmin"]);
    }

    public function adminCreate(User $user): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can review the model.
     */
    public function review(User $user, Request $request): bool
    {
      $requiredRole = match ($request->status) {
        "coordinator_approval" => "coordinator",
        "originator_edit" => "originator",
        "superior_approval" => "superior",
        "managers_approval" => "manager",
        "approved" => null,
        "denied" => null,
        default => null
      };
      
      return $requiredRole !== null && $user->role === $requiredRole;
    }

    public function adminReview(User $user): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
      return in_array($user->role, ["sysadmin"]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Request $request): bool
    {
        return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        return false;
    }
}
