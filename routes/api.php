<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(UserController::class)->group(function() {
  Route::post('/register', [UserController::class, 'register']);
  Route::post('/login', [UserController::class, 'login']);
});

Route::prefix('document')->controller(DocumentController::class)->group(function() {
  Route::post('/', 'index');
  Route::post('/create', 'store');
  Route::post('/{document}', 'show');
  Route::put('/{document}', 'update');
  Route::delete('/{document}', 'destroy');
  
  Route::prefix('{document}/version')->controller(VersionController::class)->group(function() {
    //routes
  });
});

// Route::middleware(['auth:sanctum'])->group(function() {
  Route::controller(RevisionController::class)->group(function() {
    Route::post('/revision', 'create');
    Route::get('/revision', 'index');
    Route::patch('/revision/{revision}', 'update');
  });
// });

Route::controller(VersionController::class)->group(function() {
  Route::post('/version', 'store');
  Route::post('/version/latest', 'showLatest');
  Route::post('/version/pending/{document}', 'showPending');
  Route::patch('/version/{version}', 'update');
});

Route::middleware('auth:sanctum')->get('/me', function(Request $request) {
  return response()->json($request->user());
});
