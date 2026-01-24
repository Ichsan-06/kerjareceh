<?php

namespace App\Http\Controllers;

use App\Models\JobSlot;
use App\Models\JobSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobSubmissionController extends Controller
{
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Submission Request Data:', $request->all());
        \Illuminate\Support\Facades\Log::info('Submission Files:', $request->allFiles());

        $request->validate([
            'job_slot_id' => 'required|exists:job_slots,id',
            'screenshot' => 'nullable|image|max:2048', // Allow optional or required based on job types
            'submission_data' => 'nullable|array',
        ]);

        $slot = JobSlot::where('id', $request->job_slot_id)
            ->where('worker_id', auth()->id())
            ->firstOrFail();

        if ($slot->status !== 'reserved') {
            return response()->json(['message' => 'This job slot is not in reserved status.'], 422);
        }

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('submissions', 'public');
        }

        $submission = JobSubmission::create([
            'job_slot_id' => $slot->id,
            'worker_id' => auth()->id(),
            'submission_data' => $request->submission_data,
            'screenshot_path' => $screenshotPath,
            'submitted_at' => now(),
        ]);

        // Update slot status
        $slot->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json($submission, 201);
    }
}
