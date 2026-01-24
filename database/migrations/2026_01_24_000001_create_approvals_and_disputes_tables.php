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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_slot_id')->constrained('job_slots')->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->enum('approver_role', ['provider', 'admin']);
            $table->enum('decision', ['approved', 'rejected']);
            $table->text('reason')->nullable();
            $table->timestamps(); // includes created_at
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_slot_id')->constrained('job_slots')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->enum('status', ['open', 'resolved', 'rejected'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps(); // includes created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('approvals');
    }
};
