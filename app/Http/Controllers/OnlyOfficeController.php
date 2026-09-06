<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersionResource;
use App\Models\Version;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OnlyOfficeController extends Controller
{
    public function show(Request $request, Version $version) {
      abort_unless($request->hasValidSignature(), 403);

      return Storage::response($version->file_path);
    }

    public function edit(Version $version) {

      $url = URL::signedRoute('onlyoffice.document', ['version' => $version->id], absolute: true);

      $config = [
        // "documentType" => "word",
        "document" => [
          "title" => $version->file_name,
          "fileType" => "docx",
          "key" => $version->file_name,
          "url" => $url,
        ],
        "editorConfig" => [
          "callbackUrl" => config("app.url") . "/api/onlyoffice/callback/$version->id",
        ]
      ];

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
