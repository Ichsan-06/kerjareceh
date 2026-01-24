<?php

namespace Database\Seeders;

use App\Models\JobType;
use Illuminate\Database\Seeder;

class JobTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Survey', 'slug' => 'survey'],
            ['name' => 'App Download', 'slug' => 'app-download'],
            ['name' => 'Social Media Action', 'slug' => 'social-media'],
            ['name' => 'Content Creation', 'slug' => 'content-creation'],
            ['name' => 'Testing', 'slug' => 'testing'],
        ];

        foreach ($types as $type) {
            JobType::firstOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
