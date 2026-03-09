<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
          'role' => ['required', new Enum(UserRole::class)]
        ]);

        $document = Document::all();

        if(!$document) {
          return response()->json([
            'ok' => false,
            'data' => null,
            'message' => 'No document available'
          ]);
        }

        return response()->json([
          'ok' => true,
          'data' => $document,
          'message' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
