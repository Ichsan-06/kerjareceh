<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_take_job()
    {
        $user = User::factory()->create();
        $type = JobType::create(['name' => 'Test Type', 'slug' => 'test-type']);
        $job = Job::create([
            'provider_id' => $user->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 5,
            'slot_taken' => 0,
            'status' => 'active',
        ]);

        $worker = User::factory()->create();

        $response = $this->actingAs($worker)->postJson('/api/jobs/take', [
            'job_id' => $job->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('job_slots', [
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'status' => 'reserved',
        ]);
        $this->assertDatabaseHas('gig_jobs', [
            'id' => $job->id,
            'slot_taken' => 1,
        ]);
    }

    public function test_user_cannot_take_job_if_full()
    {
        $user = User::factory()->create();
        $type = JobType::create(['name' => 'Test Type', 'slug' => 'test-type']);
        $job = Job::create([
            'provider_id' => $user->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 10,
            'total_slot' => 1,
            'slot_taken' => 1, // Full
            'status' => 'active',
        ]);

        $worker = User::factory()->create();

        $response = $this->actingAs($worker)->postJson('/api/jobs/take', [
            'job_id' => $job->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_submit_proof()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $type = JobType::create(['name' => 'Test Type', 'slug' => 'test-type']);
        $job = Job::create([
            'provider_id' => $user->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 5,
            'status' => 'active',
        ]);

        $worker = User::factory()->create();

        // Take Job manually
        $slot = \App\Models\JobSlot::create([
            'job_id' => $job->id,
            'worker_id' => $worker->id,
            'reward_amount' => 10,
            'status' => 'reserved',
            'reserved_at' => now(),
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($worker)->postJson('/api/submissions', [
            'job_slot_id' => $slot->id,
            'screenshot' => $file,
            'submission_data' => ['note' => 'Done'],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('job_submissions', [
            'job_slot_id' => $slot->id,
            'worker_id' => $worker->id,
        ]);

        $this->assertDatabaseHas('job_slots', [
            'id' => $slot->id,
            'status' => 'submitted',
        ]);

        Storage::disk('public')->assertExists('submissions/' . $file->hashName());
    }
}
