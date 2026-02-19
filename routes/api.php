<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'store']);

Route::controller(UserController::class)->group(function() {
  Route::post('/user', 'show');
});

Route::controller(DocumentController::class)->group(function() {
  Route::get('/document/{id}', 'show');
});


// Route::middleware(['auth:sanctum'])->group(function() {
  Route::controller(RevisionController::class)->group(function() {
    Route::post('/revision', 'create');
    Route::get('/revision', 'index');
  });
// });

Route::controller(VersionController::class)->group(function() {
  Route::post('/version/latest', 'showLatest');
});

Route::middleware('auth:sanctum')->get('/me', function(Request $request) {
  return response()->json($request->user());
});
