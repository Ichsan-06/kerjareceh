<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobSlot;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_approve_submission()
    {
        $provider = User::factory()->create();
        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);
        $job = Job::create([
            'provider_id' => $provider->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 10,
            'total_slot' => 1,
            'slot_taken' => 1,
            'status' => 'active',
        ]);

        $worker = User::factory()->create();
        $slot = JobSlot::create([
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'reward_amount' => 10,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($provider)->postJson('/api/approvals', [
            'job_slot_id' => $slot->id,
            'decision' => 'approved',
            'reason' => 'Good job',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('approvals', [
            'job_slot_id' => $slot->id,
            'decision' => 'approved',
        ]);
        $this->assertDatabaseHas('job_slots', [
            'id' => $slot->id,
            'status' => 'approved',
        ]);
    }

    public function test_worker_can_open_dispute_on_rejection()
    {
        $provider = User::factory()->create();
        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);
        $job = Job::create([
            'provider_id' => $provider->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 10,
            'total_slot' => 1,
            'status' => 'active',
        ]);

        $worker = User::factory()->create();
        $slot = JobSlot::create([
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'status' => 'rejected', // Must be rejected first
            'reward_amount' => 10,
        ]);

        $response = $this->actingAs($worker)->postJson('/api/disputes', [
            'job_slot_id' => $slot->id,
            'reason' => 'Unfair rejection',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('disputes', [
            'job_slot_id' => $slot->id,
            'worker_id' => $worker->id,
            'status' => 'open',
        ]);
    }
}
