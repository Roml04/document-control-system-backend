<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Mail\RequestDenied;
use App\Mail\RequestUpdated;
use App\Models\Comment;
use App\Models\ManagersApproval;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Services\ManagersApprovalService;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminRequestController extends Controller
{
    public function __construct(
      protected RequestService $requestService,
      protected ManagersApprovalService $managersApprovalService,
    ) {}
    
    public function index(Request $request) {
      $this->authorize("adminViewAny", RequestModel::class);

      $requestItem = RequestModel::query();

      if($request->has("userid")) {
        $requestItem->whereHas("user", fn($query) => $query->where(["id" => $request->userid]));
      }

      return response()->json([
        "ok" => true,
        "data" => RequestResource::collection($requestItem->get()),
        "message" => "Successfully indexed requests"
      ]);
    }

    public function store(Request $request) {
      $this->authorize("adminCreate", RequestModel::class);

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

          $this->requestService->createResubRequest($validated, $validated["authorId"], $request->file("file"));
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
        "message" => "Successfully created request"
      ]);
    }
        
    public function edit(RequestModel $requestItem, Request $request) {
      $this->authorize("update", $requestItem);

      $validated = $request->validate([
        "title" => ["required", "string"],
        "reason" => ["required", "string"],
        "status" => ["required", "in:coordinator_approval,originator_edit,superior_approval,managers_approval,approved,denied"],
        "authorId" => ["required", "exists:users,id"]
      ]);
    
      DB::transaction(function() use($requestItem, $validated) {
        $decisions = ManagersApproval::where(["request_id" => $requestItem->id])->get();

        if(count($decisions) > 0) {
          foreach($decisions as $decision) {
            $decision->delete();
          }
        }

        $requestItem->update([
          "title" => $validated["title"],
          "reason" => $validated["reason"],
          "status" => $validated["status"],
          "user_id" => $validated["authorId"]
        ]);
      });
    
      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Successfully updated request"
      ]);
    }

    public function review(RequestModel $requestItem, Request $request) {
      $this->authorize("adminReview", RequestModel::class);

      $validated = $request->validate([
        "isApproved" => ["required", "bool"],
        "requestId" => ["required", "exists:requests,id"],
        "comment" => ["nullable", "string"]
      ]);

      $userId = $request->user()->id;

      DB::transaction(function() use($requestItem, $validated, $userId) {
        $requestItem->update([
          "status" => getNextStatus($requestItem->type, $requestItem->status, $requestItem->was_edited)
        ]);

        $newReqStatus = $requestItem->status;

        $comment = $validated["comment"];

        if($comment) {
          Comment::create([
            "content" => $comment,
            "user_id" => $userId,
            "request_id" => $requestItem->id
          ]);
        }

        if($newReqStatus !== "denied" && $newReqStatus !== "approved") {
          Mail::to($requestItem->user)->send(new RequestUpdated($requestItem, $userId));
        }

        if($newReqStatus === "managers_approval") {
          $this->managersApprovalService->createManagerDecisions($requestItem->id);

          $this->requestService->notifyApprover(UserRole::Manager, $requestItem);
          return;
        }

        if($newReqStatus === "approved") {
          $this->requestService->finalizeRequest($requestItem, true);
          $decisions = ManagersApproval::where(["request_id" => $requestItem->id, "decision" => "pending"]);

          $decisions->delete();
        }

        if($newReqStatus === "denied") {
          $this->requestService->finalizeRequest($requestItem, false);
          $decisions = ManagersApproval::where(["request_id" => $requestItem->id, "decision" => "pending"]);
          Mail::to($requestItem->user)->send(new RequestDenied($requestItem));

          $decisions->delete();
        }
      });

      return response()->json([
        "ok" => true,
        "data" => null,
        "message" => "Successfully reviewed request"
      ]);
    }

    public function delete(RequestModel $requestItem) {
      $this->authorize("delete", $requestItem);

      DB::transaction(function() use($requestItem) {
        $requestItem->delete();
      });

      return response()->json([
        'ok' => true,
        'data' => null,
        "message" => "Successfully deleted request"
      ]);
    }
}
