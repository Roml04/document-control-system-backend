<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\VersionResource;
use App\Models\Version;
use App\Services\Admin\AdminVersionService;
use Illuminate\Http\Request;

class AdminVersionController extends Controller
{
    public function __construct(public AdminVersionService $adminVersionService) {}
    
    public function index(Request $request) {
      $versions = Version::query();

      /**
       * DEV-NOTE: Extract query parameters goes here
       */

      return response()->json([
        "ok" => true,
        "data" => VersionResource::collection($versions->get())
      ]);
    }

    public function view(Version $version) {
      return response()->json([
        "ok" => true,
        "data" => new VersionResource($version)
      ]);
    }

    public function edit(Version $version, Request $request) {
      $validated = $request->validate([
        "id" => ["required", "exists:versions,id"],
        "fileTitle" => ["required", "string"],
        "fileType" => ["required", "in:document,checklist,form"],
        "originator" => ["required", "string"],
        "department" => ["required", "string"],
        "revisionNumber" => ["required", "string"],
        "revisionDetails" => ["required", "string"],
        "uploadDate" => ["required", "date"],
        "revisionDate" => ["required", "date"],
        "approver" => ["required", "string"],
        "approvedDate" => ["nullable", "date"],
        "status" => ["required", "in:pending,published,rejected"],
        "file" => ["nullable", "file", "mimes:docx,pdf,xlsx,pptx"],
      ]);

      $this->adminVersionService->editVersion($validated, $request, $version);

      return response()->json([
        "ok" => true,
        "data" => []
      ]);
    }

    public function delete(Version $version) {
      $version->delete();

      return response()->json([
        "ok" => true,
        "message" => "Version deleted successfully"
      ]);
    }
}
