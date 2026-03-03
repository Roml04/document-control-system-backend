<?php

namespace App\Http\Controllers;

use App\Enums\VersionStatus;
use App\Models\Document;
use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rules\Enum;
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
        try {
          $validated = $request->validate([
            'originator' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'revisionNumber' => ['nullable', 'string'],
            'revisionDetails' => ['nullable', 'string'],
            'revisionDate' => ['nullable', 'string'],
            'approver' => ['nullable', 'string'],
            'approvedDate' => ['nullable', 'string'],
            'documentId' => ['required'],
            'revisionId' => ['required'],
            'file' => ['required', 'file'],
            'fileName' => ['required', 'string'],
            'status' => ['required', new Enum(VersionStatus::class)]
          ]);

          $file = $validated['file'];

          $fileExtension = $file->getClientOriginalExtension();

          $fileName = strtolower(str_replace(' ', '', $validated['fileName'])) . '-' . Date::now()->format('YmdHi') . "." . $fileExtension;
          
          $path = $request->file('file')->storeAs('pending', $fileName, 'public');

          $version = Version::create([
            'originator' => $validated['originator'] ?? null,
            'department' => $validated['department'] ?? null,
            'revision_number' => $validated['revisionNumber'] ?? null,
            'revision_details' => $validated['revisionDetails'] ?? null,
            'revision_date' => $validated['revisionDate'] ?? null,
            'approver' => $validated['approver'] ?? null,
            'approved_date' => $validated['approvedDate'] ?? null,
            'document_id' => $validated['documentId'],
            'revision_id' => $validated['revisionId'],
            'file_path' => $path ?? null,
            'filename' => $fileName,
            'status' => $validated['status'],
          ]);

          return response()->json($version['status']);  
        } catch (Throwable $error) {
          return response()->json(['message' => $error->getMessage()], 500);
        }
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

        $version = Version::where(['document_id' => $validated['document_id'], 'status' => VersionStatus::Approved->value])->first();

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
          'filePath' => "http://localhost/" . $version['file_path'],
        ]);

      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }

    }

    public function showPending(Document $document) {

      try {
        $version = Version::where(['document_id' => $document['id'], 'status' => VersionStatus::Pending->value])->latest()->first();

        // $version = $document->version->where('status', VersionStatus::Pending->value)->first();
        // return response()->json($version);
        if(!$version) {
          return response()->json(['message' => 'No records found']);
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
      } catch (Throwable $error) {
        return response()->json(['message' => $error->getMessage()]);
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
      try {
        $validated = $request->validate([
          'originator' => ['required', 'string'], 
          'department' => ['required', 'string'],
          'revisionNumber' => ['required', 'string'],
          'revisionDetails' => ['required', 'string'],
          'revisionDate' => ['required', 'string'],
          'approver' => ['nullable', 'string'],
          'approvedDate' => ['nullable', 'string'],
          'filePath' => ['nullable', 'string'],
          'status' => ['required', new Enum(VersionStatus::class)]
        ]);

        $version->update([
          'originator' => $validated['originator'],
          'department' => $validated['department'],
          'revision_number' => $validated['revisionNumber'],
          'revision_details' => $validated['revisionDetails'],
          'revision_date' => $validated['revisionDate'],
          'approver' => $validated['approver'],
          'approved_date' => $validated['approvedDate'],
          'file_path' => $validated['filePath'],
          'status' => $validated['status'],
        ]);

      return response()->json([
        'message' => 'Updated document details submitted successfully',
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
