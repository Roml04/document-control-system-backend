<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminFileController extends Controller
{
    public function index() {
      $files = File::all();

      return response()->json($files);
    }

    public function view(File $file) {
      return response()->json($file);
    }

    public function store(Request $request) {
      return DB::transaction(function() use($request) {
        $validated = $request->validate([
          "fileTitle" => ["required", "string"],
          "fileType" => ["required", "in:document,checklist,form"],
          "originator" => ["required", "string"],
          "department" => ["required", "string"],
          "revisionNumber" => ["required", "string"],
          "revisionDetails" => ["required", "string"],
          "uploadDate" => ["nullable", "date"],
          "revisionDate" => ["nullable", "date"],
          "approverId" => ["required", "exists:users,id"],
          "approvedDate" => ["nullable", "date"],
          "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
        ]);

        $user = $request->user();

        $uploadedFile = $request->file("file");
        $fileName = formatFileName($user->first_name, $user->last_name, $uploadedFile->getClientOriginalExtension());
        $filePath = $uploadedFile->storeAs("versions", $fileName);

        $file = File::create([
          "title" => $validated["fileTitle"],
          "type" => $validated["fileType"],
        ]);

        Version::create([
          "file_title" => $validated["fileTitle"],
          "file_type" => $validated["fileType"],
          "originator" => $validated["originator"],
          "department" => $validated["department"],
          "revision_number" => $validated["revisionNumber"],
          "revision_details" => $validated["revisionDetails"],
          "upload_date" => $validated["uploadDate"] ?? now(),
          "revision_date" => $validated["revisionDate"] ?? now(),
          "approver_id" => $validated["approverId"],
          "approved_date" => $validated["approvedDate"] ?? now(),
          "status" => "published",
          "file_name" => $fileName,
          "file_path" => $filePath,
          "file_id" => $file->id,
          "request_id" => null,
        ]);

        return response()->json([
          "ok" => true,
          "message" => "File created successfully"
        ]);
      });
    }

    public function edit(Request $request, File $file) {
      return DB::transaction(function() use($request, $file) {
        $validated = $request->validate([
          "fileTitle" => ["required", "string"],
          "fileType" => ["required", "in:document,checklist,form"],
          "originator" => ["required", "string"],
          "department" => ["required", "string"],
          "revisionNumber" => ["required", "string"],
          "revisionDetails" => ["required", "string"],
          "approver" => ["required", "string"],
          "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"]
        ]);

        /**
         * Prepares the necessary resources (version and current user)
         */
        $version = $file->version()->latest()->first();
        $user = $request->user();

        $uploadedFile = $request->file("file");
        $prevFileName = $version->file_name;

        $fileName = null;
        $filePath = null;

        if(!$uploadedFile) {
          $fileName = formatFileName($user->first_name, $user->last_name, pathinfo($version->file_path, PATHINFO_EXTENSION));
          $filePath = "versions/" . $fileName;

          /**
           * Moves the edited file from /draft to /versions
           * and renames the file
           */
          if(Storage::exists("/draft/$prevFileName")) {
            Storage::move("/draft/$prevFileName", $filePath);
          } else {
            Storage::copy("/versions/$prevFileName", $filePath);
          }
        }

        if($uploadedFile) {
          $fileName = formatFileName($user->first_name, $user->last_name, pathinfo($version->file_name, PATHINFO_EXTENSION));
          $filePath = $uploadedFile->storeAs("versions", $fileName);
        }

        $file->update([
          "title" => $validated["fileTitle"],
          "type" => $validated["fileType"],
        ]);

        $version->update([
          "file_title" => $validated["fileTitle"],
          "file_type" => $validated["fileType"],
          "originator" => $validated["originator"],
          "department" => $validated["department"],
          "revision_number" => $validated["revisionNumber"],
          "revision_details" => $validated["revisionDetails"],
          "revision_date" => now(),
          "approver" => $validated["approver"],
          "approved_date" => now(),
          "status" => "published",
          ...($fileName ? ["file_name" => $fileName] : []),
          ...($filePath ? ["file_path" => $filePath] : []),
        ]);

        return response()->json([
          "ok" => true,
          "message" => "Successfully edited file $file->id"
        ]);
      });
    }

    public function delete(File $file) {
      $file->delete();

      return response()->json([
        "ok" => true,
        "message" => "Successfully deleted file"
      ]);
    }
}
