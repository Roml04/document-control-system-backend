<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
    public function view(User $user, User $model): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
      return $user->role === "sysadmin" && $model->role !== "sysadmin";
    }

    /**
     * Determine whether the user can reset their password.
     */
    public function resetPassword(User $user): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
