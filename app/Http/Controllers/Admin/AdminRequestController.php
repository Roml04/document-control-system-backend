<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Services\RequestService;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    public function __construct(
      protected RequestService $requestService,
    ) {}
    
    public function index() {}

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
            "approver" => ["required", "string"],
            "fileId" => ["nullable", "exists:files,id"],
            "fileTitle" => ["required","string"],
            "fileType" => ["required", "in:document,checklist,form"],
            "file" => ["required", "file", "mimes:docx,pdf,xlsx,pptx"]
          ]);

          // $this->requestService->createUplRequest($validated, $request->user(), $request->file('file'));
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
            "approver" => ["required", "string"],
            "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"]
          ]);

          // $this->requestService->createResubRequest($validated, $request->user(), $request->file("file"));
          break;

        case "del":
          $validated = $request->validate([
            "title" => ["required", "string"],
            "reason" => ["required", "string"],
            "latestVersionId" => ["required", "exists:versions,id"],
            "authorId" => ["required", "exists:users,id"],
            "fileId" => ["required", "exists:files,id"],
          ]);

          // $this->requestService->createDelRequest($validated, $request->user()->id);
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
    
    public function edit(RequestModel $requestItem, Request $request) {}

    public function delete(RequestModel $requestItem) {}
}
