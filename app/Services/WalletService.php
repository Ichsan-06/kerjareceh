<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLock;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
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
    /**
     * Credit User Wallet from Top Up.
     */
    public static function creditTopUp($user, $amount, $ref)
    {
        return DB::transaction(function () use ($user, $amount, $ref) {
            $wallet = $user->wallet()->firstOrCreate(['balance' => 0]);

            $wallet->balance += $amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'topup',
                'amount' => $amount,
                'reference_type' => get_class($ref),
                'reference_id' => $ref->id,
            ]);

            return true;
        });
    }

    /**
     * Request a Withdraw (Lock Funds).
     */
    public function requestWithdraw(User $user, $amount)
    {
        return DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallet()->firstOrCreate([]);

            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient funds.');
            }

            // Lock funds
            $wallet->balance -= $amount;
            $wallet->locked_balance += $amount;
            $wallet->save();

            // Transaction Record
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw_lock', // Custom type for internal tracking
                'amount' => $amount,
                'reference_type' => WithdrawRequest::class,
                'reference_id' => 0, // Placeholder, will update after create? Or context handles it. 
                // Service is called *before* or *during* request creation. 
                // Let's pass the request object ideally, but circular dependency if creating.
                // Simplified: Just lock here. Controller creates Request.
            ]);

            return true;
        });
    }

    /**
     * Approve Withdraw (Burn Locked Funds).
     */
    public function approveWithdraw($withdrawRequest)
    {
        return DB::transaction(function () use ($withdrawRequest) {
            $user = $withdrawRequest->user;
            $wallet = $user->wallet;

            // Burn locked funds (remove from system as it is sent via bank)
            $wallet->locked_balance -= $withdrawRequest->amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw',
                'amount' => $withdrawRequest->amount,
                'reference_type' => get_class($withdrawRequest),
                'reference_id' => $withdrawRequest->id,
            ]);

            return true;
        });
    }

    /**
     * Reject Withdraw (Refund Locked Funds).
     */
    public function rejectWithdraw($withdrawRequest)
    {
        return DB::transaction(function () use ($withdrawRequest) {
            $user = $withdrawRequest->user;
            $wallet = $user->wallet;

            // Refund
            $wallet->locked_balance -= $withdrawRequest->amount;
            $wallet->balance += $withdrawRequest->amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $withdrawRequest->amount,
                'reference_type' => get_class($withdrawRequest),
                'reference_id' => $withdrawRequest->id,
            ]);

            return true;
        });
    }

    /**
     * Refund remaining frozen funds for a Job (Cancellation).
     */
    public function refundRemainingJobLock($job)
    {
        return DB::transaction(function () use ($job) {
            // Find the generic job lock
            $jobLock = WalletLock::where('job_id', $job->id)
                ->whereNull('job_slot_id')
                ->where('status', 'locked')
                ->first();

            if ($jobLock) {
                // Return funds to provider wallet
                $wallet = Wallet::find($jobLock->wallet_id);
                $amount = $jobLock->amount;

                if ($amount > 0) {
                    $wallet->locked_balance -= $amount;
                    $wallet->balance += $amount; // Return to available balance
                    $wallet->save();

                    // Transaction Record
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'refund',
                        'amount' => $amount,
                        'reference_type' => get_class($job),
                        'reference_id' => $job->id,
                    ]);
                }

                $jobLock->status = 'refunded';
                $jobLock->amount = 0;
                $jobLock->save();
            }
            // Also need to handle all "reserved" slots?
            // Ideally cancel should only be possible if no active slots?
            // Or cancel invalidates all reserved slots and refunds them too?
            // For simplicity, let's assume Provider cancels remainder. 
            // If there are reserved slots, they might have their own locks.

            // Check for any slot locks that are strictly 'locked' (reserved but not paid)
            // If we cancel the job, we should probably void these too.
            $slotLocks = WalletLock::where('job_id', $job->id)
                ->whereNotNull('job_slot_id')
                ->where('status', 'locked')
                ->get();

            foreach ($slotLocks as $sLock) {
                $wallet = Wallet::find($sLock->wallet_id);
                $amount = $sLock->amount;

                if ($amount > 0) {
                    $wallet->locked_balance -= $amount;
                    $wallet->balance += $amount;
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'refund',
                        'amount' => $amount,
                        'reference_type' => get_class($job), // Or slot?
                        'reference_id' => $job->id,
                    ]);
                }
                $sLock->status = 'refunded';
                $sLock->amount = 0;
                $sLock->save();
            }

            return true;
        });
    }
}
