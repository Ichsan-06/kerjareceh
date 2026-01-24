<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Add permission check if needed, e.g. $this->authorize('viewAny', Feedback::class);
        $feedbacks = Feedback::with('user')->latest()->get();
        return response()->json($feedbacks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'type' => 'required|string|in:bug,feature,other',
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => $validated['type'],
            'status' => 'open',
        ]);

        return response()->json($feedback, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(null, 204);
    }
}
