<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request) {
      $this->authorize("adminViewAny", User::class);

      $users = User::query();
      
      if($request->has('role')) {
        $users->where(['role' => $request->role]);
      }

      return response()->json([
        "ok" => true,
        "data" => UserResource::collection($users->get()),
        "message" => "Successfully indexed users"
      ]);
    }

    public function view(User $user) {
      $this->authorize("view", $user);

      return response()->json([
        "ok" => true,
        "data" => new UserResource($user),
        "message" => "Successfully retrieved user"
      ]);
    }

    public function store(Request $request) {
      $this->authorize("create", User::class);

      $validated = $request->validate([
        'firstName' => ['required', 'string'],
        'lastName' => ['required', 'string'],
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:8'],
        'role' => ['required', 'in:guest,originator,coordinator,superior,manager'],
      ]);

      User::create([
        'first_name' => $validated["firstName"],
        'last_name' => $validated["lastName"],
        'email' => $validated["email"],
        'password' => Hash::make($validated["password"]),
        'role' => $validated['role'],
      ]);

      return response()->json([
        'ok' => true,
        'data' => null,
        'message' => 'Successfully created user'
      ]);
    }

    public function edit(User $user, Request $request) {
      $this->authorize("update", $user);

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
        'data' => null,
        'message' => 'Successfully updated user'
      ]);
    }

    public function delete(User $user) {
      $this->authorize("delete", $user);

      $user->delete();

      return response()->json([
        "ok" => true,
        'data' => null,
        "message" => "Successfully deleted user"
      ]);
    }

    public function resetPassword(User $user, Request $request) {
      $this->authorize("resetPassword", User::class);

      $validated = $request->validate([
        'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);

      $user->update([
        'password' => Hash::make($validated["password"])
      ]);

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Successfully reset password",
      ]);
    }
}
