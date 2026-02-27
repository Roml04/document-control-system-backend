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
  Route::get('/document', 'index');
  Route::get('/document/{document}', 'show');
  Route::get('/document/{document}/version', 'updateVersion');
});

// Route::middleware(['auth:sanctum'])->group(function() {
  Route::controller(RevisionController::class)->group(function() {
    Route::post('/revision', 'create');
    Route::get('/revision', 'index');
    Route::patch('/revision/{revision}', 'update');
  });
// });

Route::controller(VersionController::class)->group(function() {
  Route::post('/version/latest', 'showLatest');
  Route::patch('/version/{version}', 'update');
});

Route::middleware('auth:sanctum')->get('/me', function(Request $request) {
  return response()->json($request->user());
});
