<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Services\OnlyOfficeService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OnlyOfficeController extends Controller
{
    public function __construct(
      protected OnlyOfficeService $onlyOfficeService
    ) {}

    /**
     * Fetch the file for viewing
     */
    public function viewFile(Request $request, Version $version) {
      abort_unless($request->hasValidSignature(), 403);

      return Storage::response($version->file_path);
    }

    /**
     * Fetch the file for editing 
     */    
    public function editFile(Request $request, Version $version) {
      abort_unless($request->hasValidSignature(), 403);

      return Storage::response("/draft/" . $version->file_path);
    }

    public function view(Version $version) {
      if(Storage::missing($version->file_path)) {
        return response()->json([
          "message" => "$version->file_path does not exist in the system"
        ], 404);
      }

      $config = $this->onlyOfficeService->buildViewConfig($version);

      return response()->json(["config" => $config]);
    }

    public function edit(Version $version) {
      /**
       * Checks if the file is existing on /draft/versions and 
       * copies the original file and put it into /draft/versions.
       */
      if(Storage::missing("/draft/$version->file_path")) {
        Storage::copy($version->file_path, "/draft/$version->file_path");
      }

      $config = $this->onlyOfficeService->buildEditConfig($version);
      
      return response()->json([
        'config' => $config,
      ]);
    }

    public function callback(Request $request, Version $version) {

      $savedStatuses = [2, 3, 6, 7];
      
      if(in_array($request["status"], $savedStatuses)) {
        /**
         * Saves the file to /draft/versions folder
         */
        $contents = file_get_contents($request["url"]);
        Storage::disk("local")->put("/draft/$version->file_path", $contents);
      }

      return response()->json(["error" => 0]);
    }
}
