<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\VersionResource;
use App\Models\Version;
use Illuminate\Http\Request;

class AdminVersionController extends Controller
{
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

    public function view() {

    }
}
