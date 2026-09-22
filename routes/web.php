<?php

use App\Models\File;
use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'The backend is running...';
});

Route::get('/login', function () {
    abort(403);
});
