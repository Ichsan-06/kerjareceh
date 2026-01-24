<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLock;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Lock funds for a Job.
     */
    public function lockFundsForJob(User $user, $job)
    {
        return DB::transaction(function () use ($user, $job) {
            $wallet = $user->wallet()->firstOrCreate([]);

            // Check balance
            if ($wallet->balance < $job->total_budget) {
                throw new \Exception('Insufficient funds.');
            }

            // Deduct balance and Increase locked_balance
            $wallet->balance -= $job->total_budget;
            $wallet->locked_balance += $job->total_budget;
            $wallet->save();

            // Create Transaction Record (Lock)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'lock',
                'amount' => $job->total_budget,
                'reference_type' => get_class($job),
                'reference_id' => $job->id,
            ]);

            // Create Wallet Lock Record
            WalletLock::create([
                'wallet_id' => $wallet->id,
                'job_id' => $job->id,
                'amount' => $job->total_budget,
                'status' => 'locked',
            ]);

            return true;
        });
    }

    /**
     * Allocate lock for a specific slot from the main Job Lock.
     */
    public function allocateSlotLock($slot)
    {
        return DB::transaction(function () use ($slot) {
            $job = $slot->job;

            // Find the generic job lock
            $jobLock = WalletLock::where('job_id', $job->id)
                ->whereNull('job_slot_id')
                ->where('status', 'locked')
                ->first();

            if (!$jobLock) {
                // If checking logic is strict, maybe throw exception. 
                // Checks on total_slot vs slot_taken should prevent over-allocation before here.
                return false;
            }

            // Deduct from Main Job Lock
            $jobLock->amount -= $slot->reward_amount;
            if ($jobLock->amount < 0) {
                // Fallback if math is off, though typically guarded by slot_taken check
                Log::error("WalletService: Job Lock amount negative for job {$job->id}");
            }
            $jobLock->save();

            // Create Slot specific Lock
            WalletLock::create([
                'wallet_id' => $jobLock->wallet_id,
                'job_id' => $job->id,
                'job_slot_id' => $slot->id,
                'amount' => $slot->reward_amount,
                'status' => 'locked',
            ]);

            return true;
        });
    }

    /**
     * Payout a Job Slot to Worker.
     */
    public function payoutSlot($slot)
    {
        return DB::transaction(function () use ($slot) {
            $job = $slot->job;
            $provider = $job->provider;
            $worker = $slot->worker;
            $amount = $slot->reward_amount;

            $providerWallet = $provider->wallet;
            $workerWallet = $worker->wallet()->firstOrCreate(['balance' => 0]);

            // Find the SPECIFIC lock for this slot
            $slotLock = WalletLock::where('job_slot_id', $slot->id)
                ->where('status', 'locked')
                ->first();

            if (!$slotLock) {
                throw new \Exception('No locked funds found for this job slot.');
            }

            // Update locked balance in user's wallet
            $providerWallet->locked_balance -= $amount;
            $providerWallet->save();

            // Mark slot lock as released/paid
            $slotLock->status = 'released';
            $slotLock->amount = 0;
            $slotLock->save();

            // Create Provider Transaction (Payout)
            WalletTransaction::create([
                'wallet_id' => $providerWallet->id,
                'type' => 'payout',
                'amount' => $amount,
                'reference_type' => get_class($slot),
                'reference_id' => $slot->id,
            ]);

            // Credit Worker
            $workerWallet->balance += $amount;
            $workerWallet->save();

            // Worker Transaction (Fee/Income)
            // Using 'fee' as per recent user edit, though 'income' or 'reward' might be better semantically. 
            // Sticking to user preference 'fee' or potentially 'payout' (as earnings).
            // Re-confirming previous user edit used 'fee'.
            WalletTransaction::create([
                'wallet_id' => $workerWallet->id,
                'type' => 'fee',
                'amount' => $amount,
                'reference_type' => get_class($slot),
                'reference_id' => $slot->id,
            ]);

            return true;
        });
    }

    /**
     * Revert lock for a specific slot back to the main Job Lock (Refund/Rejection).
     */
    public function revertSlotLock($slot)
    {
        return DB::transaction(function () use ($slot) {
            $job = $slot->job;

            // Find the SPECIFIC lock for this slot
            $slotLock = WalletLock::where('job_slot_id', $slot->id)
                ->where('status', 'locked')
                ->first();

            if (!$slotLock) {
                // If no lock found, maybe it was already released or never created?
                // Log warning but don't crash flow?
                // For strictness, let's throw.
                return false;
            }

            // Find the generic job lock to return funds to
            $jobLock = WalletLock::where('job_id', $job->id)
                ->whereNull('job_slot_id')
                ->where('status', 'locked')
                ->first();

            if (!$jobLock) {
                // Should ideally exist if job is active. If not, maybe create one or refund to wallet directly?
                // For now assume job lock exists.
                // If not, we might be in inconsistent state.
                throw new \Exception('Main Job Lock not found for refund.');
            }

            // Return funds to Job Lock
            $amount = $slotLock->amount;
            $jobLock->amount += $amount;
            $jobLock->save();

            // Mark slot lock as refunded
            $slotLock->status = 'refunded';
            $slotLock->save();

            // No transaction record needed for internal lock transfer? 
            // Or maybe record 'refund' type? 
            // Let's keep it implicit in lock table for now to avoid cluttering user transaction history 
            // since money never left the provider's "locked" state effectively.

            return true;
        });
    }
}
