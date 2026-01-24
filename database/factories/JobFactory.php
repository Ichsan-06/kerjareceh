<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use App\Models\JobType;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition()
    {
        $totalSlot = $this->faker->numberBetween(1, 10);
        $totalBudget = $this->faker->numberBetween(100000, 1000000);

        return [
            'provider_id' => User::factory(), // Will create a user if not overridden
            'job_type_id' => JobType::inRandomOrder()->first()->id ?? JobType::factory(),
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'reward_per_worker' => $totalBudget / $totalSlot,
            'total_budget' => $totalBudget,
            'total_slot' => $totalSlot,
            'slot_taken' => $this->faker->numberBetween(0, $totalSlot),
            'status' => $this->faker->randomElement(['active', 'completed', 'draft', 'cancelled']),
            'start_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_at' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
}
