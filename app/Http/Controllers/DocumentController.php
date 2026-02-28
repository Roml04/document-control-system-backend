<?php

namespace App\Http\Controllers;

use App\Enums\VersionStatus;
use App\Models\Document;
use App\Models\Revision;
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
    public function show(Document $document)
    {
      try {        // $document = Version::where('document_id', $id)->latest()->first();
        $version = Version::with(['document' => function($document) {
          $document->select('id', 'name');
        }])->where(['document_id' => $document['id'], 'status' => VersionStatus::Approved->value])->latest()->first();

        if(!$version) {
          return response()->json(['message' => "No record with an ID of " . $document['id'] . " and an approved status exists in the database"], 500);
        }

        return response()->json(['data' => [
          'id' => $version['id'],
          'originator' => $version['originator'],
          'department' => $version['department'],
          'revisionNumber' => $version['revision_number'],
          'revisionDetails' => $version['revision_details'],
          'revisionDate' => $version['revision_date'],  
          'approver' => $version['approver'],
          'approvedDate' => $version['approved_date'],
          'document' => $version['document']
        ]]);
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
    
    public function updateVersion(Document $document, Revision $revision) {
      
      // // abort_if($revision->document_id !== $document->id, 404);

      // $version = Version::where('document_id', $document->id)
      //     ->where('status', 'originator_edit')
      //     ->latest()
      //     ->first();

      // return response()->json([
      //     "revision" => $revision,
      //     "version" => $version,
      //     "document" => $document
      // ]);
      
      // $relatedRevision = $document->revision->first();
      $version = $document->version->first();
        
      return response()->json(["revision" => $revision, "version" => $version, "document" => $document]);
    }
}
