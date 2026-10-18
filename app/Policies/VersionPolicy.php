<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Version;
use Illuminate\Auth\Access\Response;

class VersionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Version $version): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
    }

    /**
     * Determine whether the user can download the document attached with the model
     */
    public function download(User $user): bool
    {
      return in_array($user->role, ["originator", "coordinator", "superior", "manager", "sysadmin"]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Version $version): bool
    {
      return $user->role === "originator" && $user->id === $version->request->user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function adminUpdate(User $user, Version $version): bool
    {
      return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Version $version): bool
    {
        return $user->role === "sysadmin";
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Version $version): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Version $version): bool
    {
        return false;
    }
}
