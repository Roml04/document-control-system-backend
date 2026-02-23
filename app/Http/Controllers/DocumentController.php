<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Version;
use Illuminate\Http\Request;
use Throwable;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
          $documents = Document::with(['version' => function ($version) {
            $version->latest()->first();
          }])->get();

          return response()->json($documents);
        } catch (Throwable $error) {
          return response()->json(['message' => $error->getMessage()], 500);
        }
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
    public function show($id)
    {
      try {
        $document = Version::where('document_id', $id)->latest()->first();

        return response()->json([
          'id' => $document['id'],
          'originator' => $document['originator'],
          'department' => $document['department'],
          'revisionNumber' => $document['revision_number'],
          'revisionDetails' => $document['revision_details'],
          'revisionDate' => $document['revision_date'],
          'approver' => $document['approver'],
          'approvedDate' => $document['approved_date'],
        ]);
      } catch (Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }
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
