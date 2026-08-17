<?php

use App\Models\File;
use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'The backend is running...';
});

Route::get('/login', function () {
    // $users = User::all(["first_name", "last_name"]);

    // $files = File::all(["title", "type"]);
    // $file = File::where('id', 1)->first();

    // $request = Request::where('id', 1)->first();

    // return $file->version;
    abort(403);
});
