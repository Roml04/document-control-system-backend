<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{ 
  public function index(Request $request) {
    /**
     * Return all request if user is sysadmin
     */

    $user = $request->user();
    $requests = $user->request;

    $requestsByUser = $requests->sortByDesc("created_at")->map(function($requestItem) use($user) {
      return [
        "id" => $requestItem["id"],
        "type" => $requestItem["type"],
        "title" => $requestItem["title"],
        "reason" => $requestItem["reason"],
        "status" => $requestItem["status"],
        "uploadDate" => $requestItem["created_at"]->format("F j, Y g:ia"),
        "user" => [
          "id" => $user->id,
          "firstName" => $user->first_name,
          "lastName" => $user->last_name
        ],
        "version" => [
          "id" => $requestItem->version->id
        ]
      ];
    })->values();

    return response()->json([
      "ok" => true,
      "data" => $requestsByUser,
      "message" => "Successfully retrieved requests from $user->first_name $user->last_name [$user->id]"
    ]);

  }
  
  public function store(Request $request) {

    /**
     * Retrieves the id of the authenticated user.
     */
    $requestingUserId = $request->user()->id;

    $validated = $request->validate([
      "type" => ["required", "in:upl,rev,resub"],
      "title" => ["required", "string"],
      "reason" => ["required", "string"],        

      "originator" => ["required", "string"],
      "department" => ["required", "string"],
      "revisionNumber" => ["required", "string"],
      "revisionDetails" => ["required", "string"],
      "approver" => ["required", "string"],
      "fileName" => ["required", "string"],
      "fileId" => ["nullable", "exists:files,id"],
      
      // "requestStatus" => ["required", "in:coordinator_approval,originator_edit,superior_approval,managers_approval,approved, denied"],
      // "uploadDate" => ["nullable", "date_format:Y-m-d"],
      // "revisionDate" => ["nullable", "date_format:Y-m-d"],
      // "approvedDate" => ["nullable", "date_format:Y-m-d"],
      // "versionStatus" => ["required", "in:pending,published,rejected"],
      // "filePath" => ["required", "string"],
    ]);

    /**
     * Switch statement to alter the flow depending on whether the type is upl, rev, or resub
     */

    DB::transaction(function() use($validated, $requestingUserId) {
      $requestModel = RequestModel::create([
        "type" => $validated["type"],
        "title" => $validated["title"],
        "reason" => $validated["reason"],
        "status" => "coordinator_approval",
        "user_id" => $requestingUserId,
      ]);
      
      Version::create([
        "originator" => $validated["originator"],
        "department" => $validated["department"],
        "revision_number" => $validated["revisionNumber"],
        "revision_details" => $validated["revisionDetails"],
        "upload_date" => now(),
        "revision_date" => now(),
        "approver" => $validated["approver"],
        "status" => "pending",
        "file_name" => $validated["fileName"],
        "file_path" => "This should be determined when the file is saved in the system", 
        "file_id" => $validated["fileId"],
        "request_id" => $requestModel->id,
      ]);
    });

    return response()->json([
      "ok" => true,
      "data" => [],
      "message" => "File Saved"
    ]);
  }
}
