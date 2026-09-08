<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index() {
      $files = File::with("version:file_id,originator,revision_number,revision_date")->get();

      return response()->json([
        "ok" => true,
        "data" => FileResource::collection($files),
        "message" => "Successfully retrieved files"
      ]);
    }

    public function view(File $file) {
      $file->load("version");

      return response()->json([
        "ok" => true,
        "data" => new FileResource($file),
        "message" => "Successfully retrieved the file"
      ]);
    }
}