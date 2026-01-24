<?php

namespace App\Http\Controllers;

use App\Models\JobComment;
use Illuminate\Http\Request;

class JobCommentController extends Controller
{
    /**
     * Get comments for a specific job.
     */
    public function index($jobId)
    {
        $comments = JobComment::with(['user:id,name']) // Load user details
            ->where('job_id', $jobId)
            ->latest()
            ->get(); // Using get() instead of paginate for simple comment thread

        return response()->json($comments);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'job_id' => 'required|exists:gig_jobs,id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = JobComment::create([
            'job_id' => $request->job_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        // Eager load user relationship for instant display on frontend
        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }
}
