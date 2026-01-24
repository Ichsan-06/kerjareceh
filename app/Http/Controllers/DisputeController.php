<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\JobSlot;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Store a newly created dispute in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'job_slot_id' => 'required|exists:job_slots,id',
            'reason' => 'required|string',
        ]);

        $slot = JobSlot::with('job')->findOrFail($request->job_slot_id);

        // Authorization: Only Worker can open dispute
        if ($slot->worker_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Can only dispute if rejected
        if ($slot->status !== 'rejected') {
            return response()->json(['message' => 'Can only dispute rejected jobs.'], 422);
        }

        // Check if dispute already exists
        $existingDispute = Dispute::where('job_slot_id', $slot->id)
            ->where('status', 'open')
            ->first();

        if ($existingDispute) {
            return response()->json(['message' => 'An open dispute already exists for this job.'], 409);
        }

        $dispute = Dispute::create([
            'job_slot_id' => $slot->id,
            'worker_id' => auth()->id(),
            'provider_id' => $slot->job->provider_id,
            'reason' => $request->reason,
            'status' => 'open',
        ]);

        return response()->json($dispute, 201);
    }
}
