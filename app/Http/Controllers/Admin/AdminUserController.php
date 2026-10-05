<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function view(User $user) {
      return new UserResource($user);
    }

    public function edit(User $user, Request $request) {
      $validated = $request->validate([
        'email' => ['required', 'email'],
        'firstName' => ['required', 'string'],
        'lastName' => ['required', 'string'],
        'role' => ['required', 'in:guest,originator,coordinator,superior,manager'],
      ]);

      $user->update([
        'email' => $validated['email'],
        'first_name' => $validated['firstName'],
        'last_name' => $validated['lastName'],
        'role' => $validated['role'],
      ]);

      return response()->json([
        'ok' => true,
        'message' => 'User updated'
      ]);
    }

    public function delete(User $user) {
      $user->delete();

      return response()->json([
        "ok" => true,
        "message" => "User deleted"
      ]);
    }

    public function resetPassword(User $user, Request $request) {
      $validated = $request->validate([
        'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);


      $user->update([
        'password' => Hash::make($validated["password"])
      ]);

      return response()->json([
        "ok" => true,
        "data" => [],
        "message" => "Password reset successfully",
      ]);
    }
}
