<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\RequestResource;
use App\Models\Request as RequestModel;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RequestService;

class RequestController extends Controller
{ 
  public function __construct(
    protected RequestService $requestService
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

    $this->requestService->createRequest($request);

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
