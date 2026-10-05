<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Mail\NotifySuperior;
use App\Models\User;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class VersionService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function createVersion() {
      
    }

    public function editVersion(array $validated, Request $request, Version $version) {
      DB::transaction(function () use($validated, $request, $version) {
        $fileName = null;
        $filePath = null;

        $version->load(["request"]);
        $requestItem = $version->request;
        $user = $requestItem->user;
        // $user = $version->request->load('user')->user;

        $uploadedFile = $request->file("file");
        $parentFileName = $version->file_name;

        if(!$uploadedFile) {
          $fileName = formatFileName($user->first_name, $user->last_name, pathinfo($version->file_path, PATHINFO_EXTENSION));
          $filePath = "versions/" . $fileName;

          /**
           * Moves the edited file from /draft to /versions
           * and renames the file
           */
          if(Storage::exists("/draft/$parentFileName")) {
            Storage::move("/draft/$parentFileName", $filePath);
          } else {
            Storage::copy("/versions/$parentFileName", $filePath);
          }
        }

        if($uploadedFile) {
          $fileName = formatFileName($user->first_name, $user->last_name, pathinfo($version->file_name, PATHINFO_EXTENSION));
          $filePath = $uploadedFile->storeAs("versions", $fileName);
        }

        $version->update([
          "file_title" => $validated["fileTitle"],
          "file_type" => $validated["fileType"],
          "originator" => $validated["originator"],
          "department" => $validated["department"],
          "revision_number" => $validated["revisionNumber"],
          "revision_details" => $validated["revisionDetails"],
          "revision_date" => now(),
          "approver" => $validated["approver"],
          ...($fileName ? ["file_name" => $fileName] : []),
          ...($filePath ? ["file_path" => $filePath] : []),
        ]);

        $version->request->update([
          "status" => getNextStatus("rev", $version->request->status, $version->request->was_edited),
        ]);

        /**
         * DEV-NOTE: Use user_id for superiors
         */
        $superiors = User::where(['role' => UserRole::Superior])->get();

        foreach($superiors as $superior) {
          Mail::to($superior)->send(new NotifySuperior($requestItem));
        }

      });
    }
}
