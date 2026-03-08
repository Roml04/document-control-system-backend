<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    /**
     * Logs the user in
     */
    public function login(Request $request) 
    {
        $validated = $request->validate([
          'email' => ['required', 'email'],
          'password' => ['required']
        ]);

        $user = User::where('email', $validated['email'])->first();

        if(!$user || !Hash::check($validated['password'], $user['password'])) {
          return response()->json([
            'ok' => false,
            'data' => null,
            'message' => 'Invalid user credentials'
          ], 401);
        }

        $responseData = [
          'id' => $user['id'],
          'firstName' => $user['first_name'],
          'lastName' => $user['last_name'],
          'role' => $user['role'],
        ];

        return response()->json([
          'ok' => true,
          'data' => $responseData,
          'message' => 'Login successful'
        ]);
    }

    public function register(Request $request) 
    {
        $validated = $request->validate([
          'firstName' => ['required', 'string'],
          'lastName' => ['required', 'string'],
          'email' => ['required', 'email', 'unique:users,email'],
          'password' => ['required', 'string', 'min:8'],
          'role' => ['nullable', new Enum(UserRole::class)]
        ]);

        User::create([
          'first_name' => $validated['firstName'],
          'last_name' => $validated['lastName'],
          'email' => $validated['email'],
          'password' => $validated['password'],
          'role' => $validated['role']
        ]);

        return response()->json([
          'ok' => true,
          'data' => null,
          'message' => 'Registration successful'
        ], 200);
    }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
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
