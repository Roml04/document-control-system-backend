<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
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
    public function view(User $user): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
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
    public function update(User $user, File $file): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, File $file): bool
    {
      return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, File $file): bool
    {
      return false;
    }
}
