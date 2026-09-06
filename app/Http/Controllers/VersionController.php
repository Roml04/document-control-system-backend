<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Models\Request as RequestModel;
use App\Models\Version;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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

    public function view(Version $version) {
      return response()->json([
        "ok" => true,
        "data" => new VersionResource($version),
        "message" => "Successfully retrieved version"
      ]);
    }

    public function viewFile(Version $version) {
      $filePath = $version->file_path;

      if(Storage::missing($filePath)) {
        return response()->json([
          "message" => "The file does not exist in the system"
        ], 404);
      }

      return Storage::response($filePath);
    }
}
