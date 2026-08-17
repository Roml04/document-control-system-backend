<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{
  public function register(Request $request) {
    $validated = $request->validate([
      'firstName' => ['required', 'string'],
      'lastName' => ['required', 'string'],
      'email' => ['required', 'email', 'unique:users,email'],
      'password' => ['required', 'string', 'min:8'],
    ]);

    User::create([
      'first_name' => $validated['firstName'],
      'last_name' => $validated['lastName'],
      'email' => $validated['email'],
      'password' => $validated['password'],
      'role' => 'guest',
    ]);

    return response()->json([
      'ok' => true,
      'data' => null,
      'message' => 'Registration successful',
    ], 200);
  }

  public function login(Request $request) {

    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
      return response()->json([
        'ok' => false,
        'message' => 'Invalid credentials'
      ], 401);
    }

    /** @var User $user */
    $user = Auth::user();

    if(!$user) {
      return response()->json([
        "ok" => false,
        "message" => "Unauthenticated"
      ]);
    }

    $now = now()->format('Y-m-d-H-i-s');

    $token = $user->createToken("user-$user->id-$now")->plainTextToken;
    $firstName = $user->first_name;
    $lastName = $user->last_name;
    $role = $user->role;

    return response()->json([
      'ok' => true,
      'data' => [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'role' => $role,
        'token' => $token
      ],
      'message' => 'Login successful',
    ]);
  }

  public function logout(Request $request) {
    $request->user()->currentAccessToken()->delete();
 
    return response()->json([
      'ok' => true,
      'message' => 'Logged out successfully'
    ]);
  }
}
