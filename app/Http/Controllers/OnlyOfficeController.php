<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OnlyOfficeController extends Controller
{
    public function show(Request $request, Version $version) {
      abort_unless($request->hasValidSignature(), 403);

      return Storage::response("/draft/" . $version->file_path);
    }

    public function edit(Version $version) {
      $url = URL::signedRoute('onlyoffice.document', ['version' => $version->id], absolute: true);

      /**
       * Checks if the file is existing on /draft/versions and 
       * copies the original file and put it into /draft/versions.
       */
      if(Storage::missing("/draft/$version->file_path")) {
        Storage::copy($version->file_path, "/draft/$version->file_path");
      }

      $lastModified = Storage::lastModified("/draft/$version->file_path");
      $key = "version-$version->id-$lastModified";

      $config = [
        "document" => [
          "title" => $version->file_name,
          "fileType" => pathinfo($version->file_path, PATHINFO_EXTENSION),
          "key" => $key,
          "url" => $url,
        ],
        "editorConfig" => [
          "callbackUrl" => config("app.url") . "/api/onlyoffice/callback/$version->id",
        ]
      ];

      /**
       * Creates and signs the JWT token
       */
      $token = JWT::encode($config, config("services.onlyoffice.jwt_secret"), 'HS256');
      $config['token'] = $token;
      
      return response()->json([
        'config' => $config,
      ]);
    }

    public function callback(Version $version) {
      return response()->json(["error" => 0]);
    }
}
