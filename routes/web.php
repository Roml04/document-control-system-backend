<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

Route::get('/', function () {
    return "hello world";
});

Route::post('/login', function (Request $request) {
  abort(403);
});