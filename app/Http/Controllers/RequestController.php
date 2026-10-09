<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestResource;
use App\Models\Request as RequestModel;
use App\Services\ManagersApprovalService;
use Illuminate\Http\Request;
use App\Services\RequestService;

class RequestController extends Controller
{ 
  public function __construct(
    protected RequestService $requestService,
    protected ManagersApprovalService $managersApprovalService
  ) {}

  public function index(Request $request) {

    $user = $request->user();

    return response()->json([
      "ok" => true,
      "data" => $this->requestService->showRequests($request),
      "message" => "Successfully retrieved requests from $user->first_name $user->last_name [$user->id]"
    ]);
  }

  public function view(RequestModel $request) {

    $request->load([
      'user:id,first_name,last_name,role',
      'version:id,file_title,file_type,originator,department,revision_number,revision_details,upload_date,revision_date,approver_id,approved_date,file_name,file_path,file_id,request_id', 
      'comment:id,content,user_id,request_id,created_at,updated_at'
    ]);

    return response()->json([
      "ok" => true,
      "data" => new RequestResource($request)
    ]);
  }
  
  public function store(Request $request) {

    $type = $request->validate([
      "type" => ["required", "in:upl,rev,resub,del"],
    ])['type'];
    
    switch($type) {
      case "upl":
        $validated = $request->validate([
          "title" => ["required", "string"],
          "reason" => ["required", "string"],
          "originator" => ["required", "string"],
          "department" => ["required", "string"],
          "revisionNumber" => ["required", "string"],
          "revisionDetails" => ["required", "string"],
          "approverId" => ["required", "exists:users,id"],
          "fileId" => ["nullable", "exists:files,id"],
          "fileTitle" => ["required","string"],
          "fileType" => ["required", "in:document,checklist,form"],
          "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
        ]);

        $this->requestService->createUplRequest($validated, $request->user(), $request->file('file'));
        break;

      case "rev":
        $validated = $request->validate([
          "title" => ["required", "string"],
          "reason" => ["required", "string"],
          "latestVersionId" => ["required", "exists:versions,id"],
        ]);

        $this->requestService->createRevRequest($validated, $request->user()->id);
        break;

      case "resub":
        $validated = $request->validate([
          "requestId" => ["required", "exists:requests,id"],
          "title" => ["required", "string"],
          "reason" => ["required", "string"],
          "versionId" => ["required", "exists:versions,id"],
          "fileTitle" => ["required", "string"],
          "fileType" => ["required", "in:document,checklist,form"],
          "originator" => ["required", "string"],
          "department" => ["required", "string"],
          "revisionNumber" => ["required", "string"],
          "revisionDetails" => ["required", "string"],
          "approver" => ["required", "exists:users,id"],
          "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"]
        ]);

        $this->requestService->createResubRequest($validated, $request->user(), $request->file("file"));
        break;

      case "del":
        $validated = $request->validate([
          "title" => ["required", "string"],
          "reason" => ["required", "string"],
          "latestVersionId" => ["required", "exists:versions,id"],
          "fileId" => ["required", "exists:files,id"],
        ]);

        $this->requestService->createDelRequest($validated, $request->user()->id);
        break;

      default:
    }

    return response()->json([
      "ok" => true,
      "data" => null,
      "message" => "Request submitted successfuly"
    ]);
  }

  public function update(Request $request) {
    $validated = $request->validate([
      "isApproved" => ["required", "bool"],
      "requestId" => ["required", "exists:requests,id"],
      "comment" => ["nullable", "string"]
    ]);

    $userId = $request->user()->id;

    $reqType = RequestModel::findOrFail($validated['requestId'])->type;

    switch($reqType) {
      case "upl":
        $this->requestService->updateUplRequest($validated, $userId);
        break;
      
      case "rev":
        $this->requestService->updateRevRequest($validated, $userId);
        break;
      
      case "del":
        $this->requestService->updateDelRequest($validated, $userId);
        break;
      
      default: 

    }

    return response()->json([
      "ok" => true,
      "data" => null,
      "message" => "Successfully updated request"
    ]);
  }
}
