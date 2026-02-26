<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Illuminate\Http\Request;
use Throwable;

class VersionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Version $version)
    {
        //
    }
    
    /**
     * Display the latest resource.
     */
    public function showLatest(Request $request)
    {
      try {
        $validated = $request->validate([
          'document_id' => ['required'],
        ]);

        $version = Version::where('document_id', $validated['document_id'])->first();

        if(!$version) {
          return response()->json([]);
        }

        return response()->json([
          'id' => $version['id'],
          'originator' => $version['originator'],
          'department' => $version['department'],
          'revisionNumber' => $version['revision_number'],
          'revisionDetails' => $version['revision_details'],
          'revisionDate' => $version['revision_date'],
          'approver' => $version['approver'],
          'approvedDate' => $version['approved_date'],
        ]);

      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Version $version)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Version $version)
    {
      return response()->json($request);
        try {
          $validated = $request->validate([
            'originator' => ['required', 'string'], 
            'department' => ['required', 'string'],
            'revisionNumber' => ['required', 'string'],
            'revisionDetails' => ['required', 'string'],
            'revisionDate' => ['required', 'string'],
            'approver' => ['required', 'string'],
            'approvedDate' => ['required', 'string'],
        ]);

        $version->update($validated);

        return response()->json([
          'message' => 'Updated document details submitted successfully',
          'data' => $version
        ]);
        } catch(Throwable $error) {
          return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Version $version)
    {
        //
    }
}
