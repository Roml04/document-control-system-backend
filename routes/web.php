<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

Route::get('/', function () {
    return "The backend is running...";
});

Route::post('/login', function (Request $request) {
  abort(403);
});