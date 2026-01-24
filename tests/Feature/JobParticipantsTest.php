<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobSlot;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_view_participants_list()
    {
        $provider = User::factory()->create();
        $type = JobType::create(['name' => 'Test', 'slug' => 'test']);
        $job = Job::create([
            'provider_id' => $provider->id,
            'job_type_id' => $type->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 5,
            'slot_taken' => 2,
            'status' => 'active',
        ]);

        $worker1 = User::factory()->create();
        $worker2 = User::factory()->create();

        JobSlot::create([
            'job_id' => $job->id,
            'worker_id' => $worker1->id,
            'status' => 'reserved',
            'reward_amount' => 10,
        ]);

        JobSlot::create([
            'job_id' => $job->id,
            'worker_id' => $worker2->id,
            'status' => 'submitted',
            'reward_amount' => 10,
        ]);

        // Authenticated user (viewer)
        $viewer = User::factory()->create();
        $response = $this->actingAs($viewer)->getJson("/api/jobs/{$job->id}/participants");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'job_id', 'worker_id', 'status', 'created_at', 'worker']
            ]);
    }
}
