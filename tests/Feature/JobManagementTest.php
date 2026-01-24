<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $jobType;

    public function setUp(): void
    {
        parent::setUp();
        // Create user manually if factory not available or use factory if standard
        $this->user = User::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => bcrypt('password')
        ]);

        $this->jobType = JobType::create(['name' => 'Test Type', 'slug' => 'test-type']);
    }

    public function test_can_list_jobs()
    {
        Job::create([
            'provider_id' => $this->user->id,
            'job_type_id' => $this->jobType->id,
            'title' => 'Test Job',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 10
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/jobs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'provider', 'job_type']]]);
    }

    public function test_can_create_job()
    {
        $jobData = [
            'title' => 'New Job',
            'job_type_id' => $this->jobType->id,
            'description' => 'Job Description',
            'reward_per_worker' => 50,
            'total_budget' => 500,
            'total_slot' => 10,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/jobs', $jobData);

        $response->assertStatus(201)
            ->assertJson(['title' => 'New Job']);

        $this->assertDatabaseHas('gig_jobs', ['title' => 'New Job']);
    }

    public function test_can_update_job()
    {
        $job = Job::create([
            'provider_id' => $this->user->id,
            'job_type_id' => $this->jobType->id,
            'title' => 'Old Title',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 10
        ]);

        $updateData = ['title' => 'New Title'];

        $response = $this->actingAs($this->user)->putJson("/api/jobs/{$job->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['title' => 'New Title']);
    }

    public function test_can_delete_job()
    {
        $job = Job::create([
            'provider_id' => $this->user->id,
            'job_type_id' => $this->jobType->id,
            'title' => 'To Delete',
            'reward_per_worker' => 10,
            'total_budget' => 100,
            'total_slot' => 10
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/jobs/{$job->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('gig_jobs', ['id' => $job->id]);
    }
}
