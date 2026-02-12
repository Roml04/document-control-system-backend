<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

Route::get('/', function () {
    return "Laravel 12";
});

Route::post('/login', function (Request $request) {
  abort(403);
});