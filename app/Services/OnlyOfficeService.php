<?php

namespace App\Services;

use App\Models\Version;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OnlyOfficeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function internalBaseUrl(): string
    {
      return rtrim(config('services.onlyoffice.internal_url'), '/');
    }

    public function callBackUrl(int $versionId): string
    {
      return $this->internalBaseUrl() . "/api/onlyoffice/callback/$versionId";
    }

    public function signUrl(int $versionId, string $mode) {
      $routeName = $mode === "edit" ? "onlyoffice.edit" : "onlyoffice.view";
      
      URL::forceRootUrl($this->internalBaseUrl());

      try {
        return URL::temporarySignedRoute(
          $routeName, 
          now()->addMinutes(30), 
          ['version' => $versionId], 
          absolute: true);
      } finally {
        URL::forceRootUrl(null);
      }
    }

    /**
     * Builds the config for viewing
     */
    public function buildViewConfig(Version $version) {
      $url = $this->signUrl($version->id, "view");
      $key = "version-$version->id-" . now()->format('YmdHisu');

      $config = [
        "document" => [
          "title" => $version->file_name,
          "fileType" => pathinfo($version->file_path, PATHINFO_EXTENSION),
          "key" => $key,
          "url" => $url
        ],
        "editorConfig" => [
          "mode" => "view",
          "callbackUrl" => $this->callBackUrl($version->id),
        ]
      ];

      $token = JWT::encode($config, config("services.onlyoffice.jwt_secret"), "HS256");
      $config["token"] = $token;

      return $config;
    }

    /**
     * Buidls the config for editing
     */
    public function buildEditConfig(Version $version) {
      $url = $this->signUrl($version->id, "edit");

      $lastModified = Storage::lastModified("/draft/$version->file_name");
      $key = "version-$version->id-$lastModified";

      $config = [
        "document" => [
          "title" => $version->file_name,
          "fileType" => pathinfo($version->file_path, PATHINFO_EXTENSION),
          "key" => $key,
          "url" => $url,
        ],
        "editorConfig" => [
          "callbackUrl" => $this->callBackUrl($version->id),
        ]
      ];

      /**
       * Creates and signs the JWT token
       */
      $token = JWT::encode($config, config("services.onlyoffice.jwt_secret"), 'HS256');
      $config['token'] = $token;

      return $config;
    }
}
