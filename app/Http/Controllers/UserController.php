<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Logs the user in
     */
    

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
      $this->authorize("viewAny", User::class);

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

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

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
