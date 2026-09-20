<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FileController;
use App\Http\Controllers\OnlyOfficeController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->group(function() {

  Route::get('/logout', [AuthController::class, 'logout']);

  Route::prefix('user')->controller(UserController::class)->group(function() {
    Route::get('/', 'index');
  });

  Route::prefix('request')->controller(RequestController::class)->group(function() {
    Route::get("/", 'index');
    Route::post('/', 'store');
    Route::get("/{request}", "view");
    Route::patch("/{request}", 'update');
  });

  Route::prefix('version')->controller(VersionController::class)->group(function() {
    Route::post("/", "index");
    Route::get("/{version}", "view");
    Route::patch("/{version}", "edit");
    Route::get("/{version}/file", "download");
    Route::get("/{version}/status", 'getStatus');
  });

  Route::prefix('comment')->controller(CommentController::class)->group(function() {
    Route::get('/', 'index');
  });

  Route::prefix('file')->controller(FileController::class)->group(function() {
    Route::get('/', 'index');
    Route::get('/{file}', 'view');
  });
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
  return response()->json($request->user());
});

Route::controller(OnlyOfficeController::class)->group(function() {
  /**
   * file
   */
  Route::get('/onlyoffice/file/{version}/view', "viewFile")->middleware("signed")->name('onlyoffice.view');
  Route::get('/onlyoffice/file/{version}/edit', "editFile")->middleware("signed")->name('onlyoffice.edit');
  
  /**
   * config
   */
  Route::get('/onlyoffice/view/{version}', 'view');
  Route::get('/onlyoffice/edit/{version}', 'edit');

  /**
   * callback handler
   */
  Route::post('/onlyoffice/callback/{version}', 'callback');
});
