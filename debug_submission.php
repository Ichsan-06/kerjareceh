<?php

use App\Models\Job;
use App\Models\JobSlot;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Setup data
$provider = User::factory()->create();
$worker = User::factory()->create();
$type = JobType::firstOrCreate(['slug' => 'debug'], ['name' => 'Debug']);

$job = Job::create([
    'provider_id' => $provider->id,
    'job_type_id' => $type->id,
    'title' => 'Debug Job ' . rand(1000, 9999),
    'reward_per_worker' => 10,
    'total_budget' => 100,
    'total_slot' => 5,
    'status' => 'active',
]);

$slot = JobSlot::create([
    'job_id' => $job->id,
    'worker_id' => $worker->id,
    'status' => 'reserved',
    'reward_amount' => 10,
]);

echo "Created Slot ID: {$slot->id}\n";
echo "Worker ID: {$worker->id}\n";

// Create a fake image
$tmpFile = tempnam(sys_get_temp_dir(), 'img');
file_put_contents($tmpFile, 'fake image content');
// Actually, make it a real-ish jpg to pass 'image' validation
$im = imagecreatetruecolor(10, 10);
imagepng($im, $tmpFile); // png

// Login worker
$token = $worker->createToken('debug')->plainTextToken;

// Send Request
$response = Http::withToken($token)
    ->attach('screenshot', file_get_contents($tmpFile), 'screenshot.png')
    ->post(url('/api/submissions'), [
        'job_slot_id' => $slot->id,
        'submission_data' => ['note' => 'Debug Note']
    ]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";

// Check DB
$submission = \App\Models\JobSubmission::where('job_slot_id', $slot->id)->first();
if ($submission && $submission->screenshot_path) {
    echo "SUCCESS: Screenshot path found: {$submission->screenshot_path}\n";
} else {
    echo "FAILURE: Screenshot path MISSING.\n";
}
