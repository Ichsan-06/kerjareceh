<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobSlot;
use App\Models\JobType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_funds_are_locked_on_job_creation()
    {
        $provider = User::factory()->create();

        // Give provider funds
        Wallet::create(['user_id' => $provider->id, 'balance' => 100, 'locked_balance' => 0]);

        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);

        $response = $this->actingAs($provider)->postJson('/api/jobs', [
            'title' => 'Test Job',
            'job_type_id' => $type->id,
            // 'reward_per_worker' => 10, // Not needed anymore
            'total_budget' => 50, // Lock 50
            'total_slot' => 5, // Reward = 10
        ]);

        $response->assertStatus(201);
        $this->assertEquals(10, $response->json('reward_per_worker'));

        // Verify Wallet state
        $wallet = $provider->wallet->fresh();
        $this->assertEquals(50, $wallet->balance); // 100 - 50
        $this->assertEquals(50, $wallet->locked_balance);

        // Verify Lock record
        $this->assertDatabaseHas('wallet_locks', [
            'wallet_id' => $wallet->id,
            'amount' => 50,
            'status' => 'locked',
            'job_slot_id' => null, // Main Job Lock
        ]);
    }

    public function test_worker_receives_funds_on_approval()
    {
        $provider = User::factory()->create();
        $providerWallet = Wallet::create(['user_id' => $provider->id, 'balance' => 0, 'locked_balance' => 50]);

        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);
        $job = Job::create([
            'provider_id' => $provider->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 50,
            'total_slot' => 5,
        ]);

        // Create Main Job Lock manually
        WalletLock::create([
            'wallet_id' => $providerWallet->id,
            'job_id' => $job->id,
            'job_slot_id' => null,
            'amount' => 50,
            'status' => 'locked',
        ]);

        $worker = User::factory()->create();

        // 1. Worker takes a slot (funds moved from Job Lock to Slot Lock)
        // We use the API endpoint to trigger the Controller logic which uses the Service
        $this->actingAs($worker)->postJson('/api/jobs/take', [
            'job_id' => $job->id,
        ])->assertStatus(201);

        $slot = JobSlot::where('job_id', $job->id)->where('worker_id', $worker->id)->first();
        $slot->update(['status' => 'submitted']);

        // Verify Lock Allocation
        $this->assertDatabaseHas('wallet_locks', [
            'wallet_id' => $providerWallet->id,
            'job_id' => $job->id,
            'job_slot_id' => $slot->id,
            'amount' => 10,
            'status' => 'locked',
        ]);

        // Approve
        $response = $this->actingAs($provider)->postJson('/api/approvals', [
            'job_slot_id' => $slot->id,
            'decision' => 'approved',
        ]);

        $response->assertStatus(201);

        // Verify Provider Wallet (Locked balance reduced)
        $providerWallet->refresh();
        $this->assertEquals(40, $providerWallet->locked_balance); // 50 - 10

        // Verify Worker Wallet (Balance increased)
        $workerWallet = $worker->wallet;
        $this->assertNotNull($workerWallet);
        $this->assertEquals(10, $workerWallet->balance);

        // Verify Transactions
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $workerWallet->id,
            'type' => 'fee',
            'amount' => 10,
        ]);
    }

    public function test_cannot_create_job_with_insufficient_funds()
    {
        $provider = User::factory()->create();
        Wallet::create(['user_id' => $provider->id, 'balance' => 10, 'locked_balance' => 0]); // Only 10

        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);

        $response = $this->actingAs($provider)->postJson('/api/jobs', [
            'title' => 'Test Job',
            'job_type_id' => $type->id,
            'total_budget' => 50, // Request 50
            'total_slot' => 5,
        ]);

        $response->assertStatus(402); // Expecting Insufficient Funds
    }
}
