<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

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
      return response()->json(["ok" => true, "data" => [
        "id" => $version->id,
        "fileTitle" => $version->file_title,
        "fileType" => $version->file_type,
        "originator" => $version->originator,
        "department" => $version->department,
        "revisionNumber" => $version->revision_number,
        "revisionDetails" => $version->revision_details,
        "uploadDate" => $version->upload_date,
        "revisionDate" => $version->revision_date,
        "approver" => $version->approver,
        "approvedDate" => $version->approved_date,
        // "status" => $version->status,
        "fileName" => $version->file_name,
        "filePath" => $version->file_path,
        // "fileId" => $version->file_id,
        // "requesetId" => $version->request_id
      ], "message" => "Retrieved version id [" . $version->id . "]"]);

      // Version::where("id", )
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
