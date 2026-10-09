<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Http\Resources\UserResource;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRequestController extends Controller
{
    public function __construct(
      protected RequestService $requestService,
    ) {}
    
    public function index(Request $request) {
      $requestItem = RequestModel::query();

      if($request->has("userid")) {
        $requestItem->whereHas("user", fn($query) => $query->where(["id" => $request->userid]));
      }

      return response()->json([
        "ok" => true,
        "data" => RequestResource::collection($requestItem->get()),
        "request" => $requestItem->get()
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
            "authorId" => ["required", "exists:users,id"],
            "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
          ]);

          $user = User::findOrFail($validated["authorId"]);

          $this->requestService->createUplRequest($validated, $user, $request->file('file'));
          break;

        case "rev":
          $validated = $request->validate([
            "title" => ["required", "string"],
            "reason" => ["required", "string"],
            "latestVersionId" => ["required", "exists:versions,id"],
            "authorId" => ["required", "exists:users,id"]
          ]);


          $this->requestService->createRevRequest($validated, $validated["authorId"]);
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
            "approverId" => ["required", "exists:users,id"],
            "authorId" => ["required", "exists:users,id"],
            "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"]
          ]);

          // $this->requestService->createResubRequest($validated, $validated["authorId], $request->file("file"));
          break;

        case "del":
          $validated = $request->validate([
            "title" => ["required", "string"],
            "reason" => ["required", "string"],
            "latestVersionId" => ["required", "exists:versions,id"],
            "authorId" => ["required", "exists:users,id"],
            "fileId" => ["required", "exists:files,id"],
          ]);

          $this->requestService->createDelRequest($validated, $validated["authorId"]);
          break;

        default:
      }

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Request submitted successfuly"
      ]);
    }
    
    public function view(RequestModel $requestItem) {}
    
    public function edit(RequestModel $requestItem, Request $request) {
      $validated = $request->validate([
        "title" => ["required", "string"],
        "reason" => ["required", "string"],
        "status" => ["required", "in:coordinator_approval,originator_edit,superior_approval,managers_approval,approved,denied"],
        "authorId" => ["required", "exists:users,id"]
      ]);
    
      DB::transaction(function() use($requestItem, $validated) {
        $requestItem->update([
          "title" => $validated["title"],
          "reason" => $validated["reason"],
          "status" => $validated["status"],
          "user_id" => $validated["authorId"]
        ]);
      });
    
      return response()->json([
        "ok" => true,
        "data" => [
          "validated" => $validated,
          "request" => $requestItem
        ]
      ]);
    }

    public function delete(RequestModel $requestItem) {
      return DB::transaction(function() use($requestItem) {
        $requestItem->delete();

        return response()->json([
          'ok' => true,
          'data' => [],
        ]);
      });
    }
}
