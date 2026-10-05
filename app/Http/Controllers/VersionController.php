<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Models\Version;
use App\Services\VersionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VersionController extends Controller
{
    public function __construct(public VersionService $versionService) {}
    
    public function index() {
      return response()->json([
        "message" => "files"
      ]);
    }

    public function store() {
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

    public function edit(Request $request, Version $version) {

      $validated = $request->validate([
        "id" => ["required", "exists:versions,id"],
        "fileTitle" => ["required", "string"],
        "fileType" => ["required", "in:document,checklist,form"],
        "originator" => ["required", "string"],
        "department" => ["required", "string"],
        "revisionNumber" => ["required", "string"],
        "revisionDetails" => ["required", "string"],
        "approver" => ["required", "string"],
        "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"]
      ]);

      $this->versionService->editVersion($validated, $request, $version);

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Successfully patched version"
      ]);
    }

    public function getStatus(Version $version) {
      if(!$version->edit_session_started_at || !$version->draft_saved_at) {
        return response()->json([
          "saved" => false,
          "start" => $version->edit_session_started_at,
          "end" => $version->draft_saved_at,
          "difference" => null
        ]);
      }

      $savedStatuses = [2, 3, 6, 7];

      return response()->json([
        "saved" => in_array($version->last_save_status, $savedStatuses),
        "start" => Carbon::parse($version->edit_session_started_at),
        "end" => Carbon::parse($version->draft_saved_at),
        "lastSaveStatus" => $version->last_save_status,
      ]);
    }
}
