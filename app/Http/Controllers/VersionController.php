<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Models\Request as RequestModel;
use App\Models\Version;
use Carbon\Carbon;
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

        $parentFileName = $version->file_name;

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
          "status" => getNextStatus("rev", $version->request->status, $version->request->was_edited),
        ]);

        /**
         * Moves the edited file from /draft to /versions
         * and renames the file
         */
        if(Storage::missing("/draft/$parentFileName")) {
          Storage::copy("/versions/$parentFileName", $filePath);
        } else {
          Storage::move("/draft/$parentFileName", $filePath);
        }
      });

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
