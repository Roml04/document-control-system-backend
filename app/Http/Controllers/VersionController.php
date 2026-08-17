<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class VersionController extends Controller
{
    public function index() {
      return response()->json([
        "message" => "files"
      ]);
    }

    public function store(Request $request) {
      return response()->json([
        "ok" => true,
        "data" => [],
        "message" => "File Saved"
      ]);
    }
}
