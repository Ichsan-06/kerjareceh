<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('gig_jobs')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('reward_amount', 10, 2);
            $table->enum('status', [
                'available',
                'reserved',
                'submitted',
                'approved',
                'rejected',
                'expired'
            ])->default('available');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_slot_id')->constrained('job_slots')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->json('submission_data')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_submissions');
        Schema::dropIfExists('job_slots');
    }
};
