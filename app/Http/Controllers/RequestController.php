<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Models\Request as RequestModel;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comment;
use App\Models\File;
use App\Services\RequestService;

class RequestController extends Controller
{ 
  public function __construct(
    protected RequestService $requestService
  ) {}

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
        $requests = RequestModel::where("status", "managers_approval")->whereHas('managersApproval', function ($query) use($user) {
          $query->where(['manager_id' => $user->id, 'decision' => 'pending']);
        })->get();
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

  public function view(RequestModel $request) {

    $request->load([
      'user:id,first_name,last_name,role', 
      'version:id,file_title,file_type,originator,department,revision_number,revision_details,upload_date,revision_date,approver,approved_date,file_name,file_path,file_id,request_id', 
      'comment:id,content,user_id,request_id,created_at,updated_at'
    ]);

    return response()->json([
      "ok" => true,
      "data" => new RequestResource($request)
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

    $validatedWithFileInfo = [
      ...$validated,
      "userId" => $requestingUserId,
      "fileName" => $fileName,
      "filePath" => $filePath,
    ];

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

  public function update(Request $request) {
    $validated = $request->validate([
      "isApproved" => ["required", "bool"],
      "requestId" => ["required", "exists:requests,id"],
      "comment" => ["nullable", "string"]
    ]);

    $userRole = $request->user()->role;

    if($userRole === UserRole::Manager->value) {
      return $this->requestService->updateManagerDecision($validated['requestId'], $request->user()->id, $validated['isApproved'], $validated['comment']);
    } else {
      $this->requestService->updateStatus($validated['isApproved'], $validated['requestId'], $request->user()->id, $validated['comment']);
    }

    return response()->json([
      "ok" => true,
      "data" => null,
      "message" => "Successfully updated request status"
    ]);
  }
  
  public function getComments(RequestModel $request) {
    $requestComments = $request->comment;

    return response()->json([
      "ok" => true,
      "data" => $requestComments,
      "message" => "Successfully retrieved comments"
    ]);
  }
}
