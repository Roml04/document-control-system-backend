<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\Version;
use Carbon\Carbon;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{ 
  public function index(Request $request) {
    /**
     * Return all request if user is sysadmin
     */

    $user = $request->user();

    $requestsForApproval = DB::transaction(function() use($user) {

      $requests = [];
      
      if($user->role === "originator") {
        return $requests;
      }

      if($user->role === "coordinator") {
        $requests = RequestModel::where("status", "coordinator_approval")->get();
      }

      if($user->role === "superior") {
        $requests = RequestModel::where("status", "superior_approval")->get();
      }

      if($user->role === "manager") {
        $requests = RequestModel::where("status", "managers_approval")->get();
      }

      if($user->role === "sysadmin") {
        $requests = RequestModel::all();
      }

      return $requests->sortByDesc("created_at")->map(function($requestItem) {
        $user = $requestItem->user;

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
            "id" => $requestItem->version->id ?? null
          ]
        ];
      })->values();


    });

    $requestsByUser = $user->request;

    $requestsByUserSorted = $requestsByUser->sortByDesc("created_at")->map(function($requestItem) use($user) {
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
          "id" => $requestItem->version->id ?? null
        ]
      ];
    })->values();

    return response()->json([
      "ok" => true,
      "data" => [
        "myRequests" => $requestsByUserSorted,
        "forApprovals" => $requestsForApproval
      ],
      "message" => "Successfully retrieved requests from $user->first_name $user->last_name [$user->id]"
    ]);

  }
  
  public function store(Request $request) {

    /**
     * Retrieves the id of the authenticated user.
     */

    $validated = $request->validate([
      "type" => ["required", "in:upl,rev,resub"],
      "title" => ["required", "string"],
      "reason" => ["required", "string"],        

      "originator" => ["required", "string"],
      "department" => ["required", "string"],
      "revisionNumber" => ["required", "string"],
      "revisionDetails" => ["required", "string"],
      "approver" => ["required", "string"],
      "fileId" => ["nullable", "exists:files,id"],
      "fileTitle" => ["required","string"],
      "fileType" => ["in:document,checklist,form"],
      "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
      
      // "fileName" => ["required", "string"],
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
    $user = $request->user();
    $requestingUserId = $user->id;

    $uploadedFile = $request->file("file");

    $uploadedFile->getClientOriginalExtension();

    $fileName = strtolower("$user->first_name$user->last_name") . "-" . now()->format('YmdHsu') . "." . $uploadedFile->getClientOriginalExtension();
    $filePath = $uploadedFile->storeAs('versions', $fileName);

    // return response()->json([
    //   "data" => $fileName
    // ]);

    $validatedWithFileInfo = [
      ...$validated,
      "userId" => $requestingUserId,
      "fileName" => $fileName,
      "filePath" => $filePath,
    ];

    // return response()->json([
    //   "data" => $validatedWithFileInfo
    // ]);

    DB::transaction(function() use($validatedWithFileInfo) {
      $requestModel = RequestModel::create([
        "type" => $validatedWithFileInfo["type"],
        "title" => $validatedWithFileInfo["title"],
        "reason" => $validatedWithFileInfo["reason"],
        "status" => "coordinator_approval",
        "user_id" => $validatedWithFileInfo["userId"],
      ]);
      
      Version::create([
        "file_title" => $validatedWithFileInfo["fileTitle"],
        "file_type" => $validatedWithFileInfo["fileType"],
        "originator" => $validatedWithFileInfo["originator"],
        "department" => $validatedWithFileInfo["department"],
        "revision_number" => $validatedWithFileInfo["revisionNumber"],
        "revision_details" => $validatedWithFileInfo["revisionDetails"],
        "upload_date" => now(),
        "revision_date" => now(),
        "approver" => $validatedWithFileInfo["approver"],
        "status" => "pending",
        "file_name" => $validatedWithFileInfo["fileName"],
        "file_path" => $validatedWithFileInfo["filePath"], 
        "file_id" => $validatedWithFileInfo["fileId"] ?? null,
        "request_id" => $requestModel->id,
      ]);
    });

    return response()->json([
      "ok" => true,
      "data" => [],
      "message" => "Request submitted successfuly"
    ]);
  }
}
