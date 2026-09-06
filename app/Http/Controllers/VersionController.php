<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Models\Request as RequestModel;
use App\Models\Version;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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

    public function download(Version $version) {
      $filePath = $version->file_path;

      if(Storage::missing($filePath)) {
        return response()->json([
          "message" => "The file does not exist in the system"
        ], 404);
      }

      return Storage::response($filePath);
    }

    public function edit(Request $request) {
      $validated = $request->validate([
        "id" => ["required", "exists:versions,id"],
        "fileTitle" => ["required", "string"],
        "fileType" => ["required", "in:document,checklist,form"],
        "originator" => ["required", "string"],
        "department" => ["required", "string"],
        "revisionNumber" => ["required", "string"],
        "revisionDetails" => ["required", "string"],
        "approver" => ["required", "string"],
      ]);

      DB::transaction(function () use($validated) {
        $version = Version::with('request')->findOrFail($validated["id"]);

        $user = $version->request->load('user')->user;

        $prevFilePath = $version->file_path;

        $fileName = strtolower("$user->first_name$user->last_name") . "-" . now()->format('YmdHsu') . "." . pathinfo($version->file_path, PATHINFO_EXTENSION);
        $filePath = "versions/" . $fileName;

        $version->update([
          "file_title" => $validated["fileTitle"],
          "file_type" => $validated["fileType"],
          "originator" => $validated["originator"],
          "department" => $validated["department"],
          "revision_number" => $validated["revisionNumber"],
          "revision_details" => $validated["revisionDetails"],
          "revision_date" => now(),
          "approver" => $validated["approver"],
          "file_name" => $fileName,
          "file_path" => $filePath,
        ]);

        $version->request->update([
          "status" => getNextStatus("rev", $version->request->status)
        ]);

        Storage::move("/draft/$prevFilePath", $filePath);
      });

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Successfully patched version"
      ]);
    }
}
