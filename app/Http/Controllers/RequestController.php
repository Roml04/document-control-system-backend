<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Models\ManagersApproval;
use App\Models\Request as RequestModel;
use App\Models\Version;
use App\Services\ManagersApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
      'version:id,file_title,file_type,originator,department,revision_number,revision_details,upload_date,revision_date,approver,approved_date,file_name,file_path,file_id,request_id', 
      'comment:id,content,user_id,request_id,created_at,updated_at'
    ]);

    return response()->json([
      "ok" => true,
      "data" => new RequestResource($request)
    ]);
  }
  
  public function store(Request $request) {

    $type = $request->validate([
      "type" => ["required", "in:upl,rev,resub"],
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
          "approver" => ["required", "string"],
          "fileId" => ["nullable", "exists:files,id"],
          "fileTitle" => ["required","string"],
          "fileType" => ["in:document,checklist,form"],
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

        $this->requestService->createRevRequest($validated, $request->user());
        break;

      case "resub":
        $this->requestService->createResubRequest();
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

    $user = $request->user();

    $this->requestService->updateUplRequest($validated, $user);

    return response()->json([
      "ok" => true,
      "data" => null,
      "message" => "Successfully updated request"
    ]);
  }
}
