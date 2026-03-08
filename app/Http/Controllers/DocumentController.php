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
      $validated = $request->validate([
        'name' => ['required', 'string'],
      ]);

      $document = Document::create([
        'name' => $validated['name']
      ]);

      return response()->json($document);    
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
      try {        
        // $document = Version::where('document_id', $id)->latest()->first();
        $version = Version::with(['document' => function($document) {
          $document->select('id', 'name');
        }])->where(['document_id' => $document['id'], 'status' => VersionStatus::Approved->value])->latest()->first();

        if(!$version) {
          return response()->json(['document' => $document, 'version' => null]);
        }

        return response()->json([
          'document' => $version['document'],
          'version' => [
            'id' => $version['id'],
            'originator' => $version['originator'],
            'department' => $version['department'],
            'revisionNumber' => $version['revision_number'],
            'revisionDetails' => $version['revision_details'],
            'revisionDate' => $version['revision_date'],
            'approver' => $version['approver'],
            'approvedDate' => $version['approved_date'],
            'userId' => $version['user_id'],
            'filePath' => "http://localhost/storage/" . $version['file_path'],
            'fileName' => $version['filename'],
          ],          
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
    
    public function editVersion(Document $document, Revision $revision) {
      $version = Version::where(['document_id' => $document['id']])->latest()->first();

      return response()->json([
        "revision" => $revision, 
        "version" => [
          "id" => $version['id'],
          "originator" => $version['originator'],
          "department" => $version['department'],
          "revisionNumber" => $version['revision_number'],
          "revisionDetails" => $version['revision_details'],
          "revisionDate" => $version['revision_date'],
          "approver" => $version['approver'],
          "approvedDate" => $version['approved_date'],
          "documentId" => $version['document_id'],
          "filePath" => 'http://localhost/storage/' . $version['file_path'],
          "fileName" => $version['filename'],
          "status" => $version['status'],
          "revisionId" => $version['revision_id']
        ], 
        "document" => $document
      ]);
    }
}
