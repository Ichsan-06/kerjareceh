<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use App\Models\JobType;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have some users and job types
        if (User::count() == 0) {
            User::factory(5)->create();
        }

        // Ensure we have job types. If not, seed them (assuming JobTypeSeeder exists or manually create)
        if (JobType::count() == 0) {
            // Fallback if no job types exist
            \App\Models\JobType::create(['name' => 'General']);
            \App\Models\JobType::create(['name' => 'Social Media']);
        }

        // Get existing users to be providers
        $providers = User::all();

        Job::factory()->count(20)->make()->each(function ($job) use ($providers) {
            $job->provider_id = $providers->random()->id;
            $job->save();
        });
    }
}
