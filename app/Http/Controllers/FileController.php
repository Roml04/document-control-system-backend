<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileResource;
use App\Models\File;

class FileController extends Controller
{
    public function index() {
      $this->authorize("viewAny", File::class);

      $files = File::with("version:file_id,originator,revision_number,revision_date")->get();

      return response()->json([
        "ok" => true,
        "data" => FileResource::collection($files),
        "message" => "Successfully indexed files"
      ]);
    }

    public function view(File $file) {
      $this->authorize("view", File::class);

      $file->load("version");

      return response()->json([
        "ok" => true,
        "data" => new FileResource($file),
        "message" => "Successfully retrieved file"
      ]);
    }
}