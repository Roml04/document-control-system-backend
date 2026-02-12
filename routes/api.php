<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'store']);

Route::controller(UserController::class)->group(function() {
  Route::post('/user', 'show');
});

// Route::middleware(['auth:sanctum'])->group(function() {});

Route::middleware('auth:sanctum')->get('/me', function(Request $request) {
  return response()->json($request->user());
});
