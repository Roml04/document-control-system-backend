<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;
use Throwable;

class RevisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      try {
        $revision = Revision::all();

        return response()->json([
          'title' => $revision->title,
          'reason' => $revision->reason,
          'user_id' => $revision->user_id,
          'document_id' => $revision->document_id
        ]);
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
          'user_id' => ['required'],
          'document_id' => ['required'],
        ]);

        Revision::create([
          'title' => $validated['title'],
          'reason' => $validated['reason'],
          'user_id' => $validated['user_id'],
          'document_id' => $validated['document_id'],
        ]);

      return response()->json(['message' => 'Request submitted successfully']);

      } catch(Throwable $error) {
        return response()->json(['message' => $error->getMessage()]);
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
     * Show the form for editing the specified resource.
     */
    public function edit(Revision $revision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revision $revision)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revision $revision)
    {
        //
    }
}
