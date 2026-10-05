<?php

namespace App\Services\Admin;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\UserRole;
use App\Mail\NotifySuperior;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminVersionService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function editVersion(array $validated, Request $request, Version $version) {
      DB::transaction(function () use($validated, $request, $version) {
        $fileName = null;
        $filePath = null;

        $version->load(["request"]);
        $requestItem = $version->request;
        $user = $requestItem->user;

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
          "upload_date" => $validated["uploadDate"],
          "revision_date" => $validated["revisionDate"] ?? now(),
          "approver" => $validated["approver"],
          "approved_date" => $validated["approvedDate"] ?? now(),
          "status" => $validated["status"],
          ...($fileName ? ["file_name" => $fileName] : []),
          ...($filePath ? ["file_path" => $filePath] : []),
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
