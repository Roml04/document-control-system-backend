<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function() {
  Route::controller(UserController::class)->group(function() {
    Route::get('/user/{id}', 'show');
    Route::post('/user', 'create');
  });
});
