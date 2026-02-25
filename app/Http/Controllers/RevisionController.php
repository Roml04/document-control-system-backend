<?php

namespace App\Http\Controllers;

use App\Enums\RevisionStatus;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Throwable;

class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      try {
        $revision = Revision::with(['user' => function ($user) {
          $user->get('id', 'first_name', 'last_name');
        }, 'document' => function ($document) {
          $document->get('id', 'type');
        }])->get();

        return response()->json($revision);
      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()], 500);
      }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
      // return response()->json(['data' => $request]);
      try {
        $validated = $request->validate([
          'title' => ['required', 'string'],
          'reason' => ['required', 'string'],
          'status' => ['required', new Enum(RevisionStatus::class)],
          'user_id' => ['required'],
          'document_id' => ['required'],
          'comment' => ['sometimes', 'string']
        ]);

        Revision::create([
          'title' => $validated['title'],
          'reason' => $validated['reason'],
          'status' => $validated['status'],
          'user_id' => $validated['user_id'],
          'document_id' => $validated['document_id'],
          'comment' => $validated['comment'] ?? null,
        ]);

      return response()->json(['message' => 'Request submitted successfully']);

      } catch(Throwable $error) {
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
    public function show(Revision $revision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revision $revision)
    {
      try {
        $validated = $request->validate([
          'title' => ['sometimes', 'string'],
          'reason' => ['sometimes', 'string'],
          'status' => ['sometimes', new Enum(RevisionStatus::class)],
          'user_id' => ['sometimes'],
          'document_id' => ['sometimes'],
          'comment' => ['sometimes']
        ]);

        $revision->update($validated);

        return response()->json([
          'message' => 'Resource updated successfully',
          'data' => $revision
        ]);
      } catch(Throwable $error) {
        return response()->json([
          'message' => $error->getMessage()
        ], 500);
      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revision $revision)
    {
        //
    }
}
