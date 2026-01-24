<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::with(['provider', 'jobType'])->latest()->paginate(10);
        return response()->json($jobs);
    }

    public function postedJobs()
    {
        $jobs = Job::where('provider_id', Auth::id())
            ->with(['jobType'])
            ->withCount('slots') // Assuming relationship is 'slots'
            ->latest()
            ->paginate(10);
        return response()->json($jobs);
    }

    public function types()
    {
        return response()->json(JobType::all());
    }

    public function store(Request $request, \App\Services\WalletService $walletService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'job_type_id' => 'required|exists:job_types,id',
            'description' => 'nullable|string',
            'total_budget' => 'required|numeric|min:0',
            'total_slot' => 'required|integer|min:1',
            'end_at' => 'nullable|date',
            'start_at' => 'nullable|date',
        ]);

        $validated['provider_id'] = Auth::id();

        // Auto-calculate reward per worker
        if ($validated['total_slot'] > 0) {
            $validated['reward_per_worker'] = $validated['total_budget'] / $validated['total_slot'];
        } else {
            $validated['reward_per_worker'] = 0;
        }

        try {
            $user = Auth::user();

            // Temporary Job instance to validate budget (or check request input directly)
            // But we can just create the job *inside* the transaction in service, or here.
            // If we create here, we need to rollback if wallet fails.
            // Better: use DB transaction here.

            return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $user, $walletService) {
                // 1. Validate Balance BEFORE creating job
                $totalBudget = $validated['total_budget'];
                $wallet = $user->wallet()->firstOrCreate([]);

                if ($wallet->balance < $totalBudget) {
                    return response()->json(['message' => 'Insufficient wallet balance.'], 402);
                }

                // 2. Create Job
                $job = Job::create($validated);

                // 3. Lock Funds
                $walletService->lockFundsForJob($user, $job);

                return response()->json($job, 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Job $job)
    {
        $job->load(['provider', 'jobType']);

        // Check if current user has a slot for this job
        $job->my_slot = \App\Models\JobSlot::where('job_id', $job->id)
            ->where('worker_id', Auth::id())
            ->with('submission')
            ->first();

        return response()->json($job);
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'job_type_id' => 'sometimes|required|exists:job_types,id',
            'description' => 'nullable|string',
            'reward_per_worker' => 'sometimes|required|numeric|min:0',
            'total_budget' => 'sometimes|required|numeric|min:0',
            'total_slot' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|in:draft,active,paused,completed,expired,cancelled',
            'end_at' => 'nullable|date',
            'start_at' => 'nullable|date',
        ]);

        $job->update($validated);

        return response()->json($job);
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return response()->json(null, 204);
    }
}
