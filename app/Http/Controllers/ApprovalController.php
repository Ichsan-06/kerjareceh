<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\JobSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Store a newly created approval in storage.
     */
    public function store(Request $request, \App\Services\WalletService $walletService)
    {
        $request->validate([
            'job_slot_id' => 'required|exists:job_slots,id',
            'decision' => 'required|in:approved,rejected',
            'reason' => 'nullable|string',
        ]);

        $slot = JobSlot::where('id', $request->job_slot_id)
            ->with(['job', 'worker', 'job.provider']) // Load necessary relations
            ->firstOrFail();

        // Authorization check: Only provider can approve
        if ($slot->job->provider_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($slot->status !== 'submitted') {
            return response()->json(['message' => 'Slot is not in submitted status.'], 422);
        }

        try {
            return DB::transaction(function () use ($slot, $request, $walletService) {
                // Get user inside the try block (or outside, but needs to be defined)
                $user = auth()->user();
                // Since we checked provider_id above, we know it's the provider (or we add admin check back if needed)
                // For now, assume provider.

                // Create Approval Record
                $approval = Approval::create([
                    'job_slot_id' => $slot->id,
                    'approver_id' => $user->id,
                    'approver_role' => 'provider', // Hardcoded as we enforce provider check above
                    'decision' => $request->decision,
                    'reason' => $request->reason,
                ]);

                // Update Slot Status
                $slot->status = $request->decision;

                if ($request->decision === 'approved') {
                    $slot->approved_at = now();
                    // Trigger Payment Logic
                    $walletService->payoutSlot($slot);
                } elseif ($request->decision === 'rejected') {
                    // Revert Funds to Main Job Lock (Escrow)
                    $walletService->revertSlotLock($slot);

                    // Decrement slot_taken using DB raw to ensure consistency or just model decrement
                    // Since we incremented on reservation, we decrement on rejection so it becomes available again.
                    // Assuming 'slot_taken' tracks ACTIVE/RESERVED/SUBMITTED/APPROVED slots.
                    // If rejected, the slot is effectively "freed". 
                    // However, the worker still "holds" the *Slot Record* but with status 'rejected'.
                    // To make the "count" available for someone else, we decrement.
                    $slot->job->decrement('slot_taken');
                }

                $slot->save();

                return response()->json($approval, 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
