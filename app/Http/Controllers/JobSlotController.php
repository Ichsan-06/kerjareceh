<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobSlotController extends Controller
{
    /**
     * Display a list of slots taken by the current user.
     */
    public function index()
    {
        $slots = JobSlot::with(['job', 'submission'])
            ->where('worker_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json($slots);
    }

    /**
     * Take a job (reserve a slot).
     */
    public function store(Request $request, \App\Services\WalletService $walletService)
    {
        $request->validate([
            'job_id' => 'required|exists:gig_jobs,id',
        ]);

        $jobId = $request->job_id;
        $userId = auth()->id();

        // Check if user already took this job
        $existingSlot = JobSlot::where('job_id', $jobId)
            ->where('worker_id', $userId)
            ->first();

        if ($existingSlot) {
            return response()->json(['message' => 'You have already taken this job.'], 409);
        }

        try {
            return DB::transaction(function () use ($jobId, $userId, $walletService) {
                // Lock the job row for update to prevent race conditions
                $job = Job::lockForUpdate()->find($jobId);

                if ($job->slot_taken >= $job->total_slot) {
                    return response()->json(['message' => 'No slots available for this job.'], 422);
                }

                // Create slot
                $slot = JobSlot::create([
                    'job_id' => $jobId,
                    'worker_id' => $userId,
                    'reward_amount' => $job->reward_per_worker,
                    'status' => 'reserved',
                    'reserved_at' => now(),
                ]);

                // Increment slot_taken
                $job->increment('slot_taken');

                // Allocate Funds to Slot Lock
                $allocated = $walletService->allocateSlotLock($slot);

                if (!$allocated) {
                    // Check specific error handling if needed, but for now exception or return false
                    // If we return, we should rollback manually or throw exception to rollback DB transaction
                    throw new \Exception('Failed to allocate funds for this slot.');
                }

                return response()->json($slot, 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id)
    {
        $slot = JobSlot::with(['job.provider', 'submission'])
            ->where('id', $id)
            ->where('worker_id', auth()->id())
            ->firstOrFail();

        return response()->json($slot);
    }

    /**
     * Get all slots for a specific job (Provider only).
     */
    public function getJobSlots($jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->provider_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $slots = JobSlot::with(['worker', 'submission'])
            ->where('job_id', $jobId)
            ->latest()
            ->get();

        return response()->json($slots);
    }

    /**
     * Get public list of participants (User info + Status only).
     */
    public function publicList($jobId)
    {
        $slots = JobSlot::with(['worker:id,name,email']) // Customize fields as needed, maybe avatar later
            ->where('job_id', $jobId)
            ->select('id', 'job_id', 'worker_id', 'status', 'created_at')
            ->latest()
            ->get();

        return response()->json($slots);
    }
}
