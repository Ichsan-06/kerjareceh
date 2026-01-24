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
        Schema::create('gig_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_type_id')->constrained('job_types')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('reward_per_worker', 10, 2);
            $table->decimal('total_budget', 10, 2);
            $table->integer('total_slot');
            $table->integer('slot_taken')->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'expired', 'cancelled'])->default('draft');
            $table->integer('approval_deadline_minutes')->default(1440); // 24 hours
            $table->integer('submit_deadline_minutes')->default(60); // 1 hour
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
