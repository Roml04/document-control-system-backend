<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {

      try {
        $validated = $request->validate([
          'first_name' => ['required', 'string'],
          'last_name' => ['required', 'string'],
          'email' => ['required', 'email', 'unique:users,email'],
          'password' => ['required', 'string', Password::min(8)],
          'role' => ['required', new Enum(UserRole::class)]
        ]);

        $user = User::create([
          'first_name' => $validated['first_name'],
          'last_name' => $validated['last_name'],
          'email' => $validated['email'],
          'password' => Hash::make($validated['password']),
          'role' => $validated['role']
        ]);

        // $token_name = strtolower("$user->first_name-token");

        // $token = $user->createToken($token_name)->plainTextToken;

        return response()->json(['message' => 'User successfully created'], 201);

      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
      try {
        $validated = $request->validate([
          'email' => ['email', 'required'],
          'password' => ['string', Password::min(8)]
        ]);
        
        $user = User::where('email', $validated['email'])->firstOrFail();

        if (!$user) {
          return response()->json(['message' => "No user found"], 404);
        }
        
        if(!Hash::check($validated['password'], $user->password)) {
          return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        $token_name = strtolower("$user->first_name-token");

        $token = $user->createToken($token_name)->plainTextToken;

        return response()->json(['message' => "Login successful", 'data' => [
          'user_id' => $user['id'],
          'first_name' => $user['first_name'],
          'last_name' => $user['last_name'],
          'role' => $user['role'],
          'token' => $token,
        ]]);
      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
