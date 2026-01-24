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
        Schema::create('wallet_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('gig_jobs')->onDelete('cascade');
            // Nullable slot_id, because initial lock covers ALL slots. 
            // We might create child locks per slot later, but typically we lock the whole budget on the job.
            $table->foreignId('job_slot_id')->nullable()->constrained('job_slots')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['locked', 'released', 'paid', 'refunded'])->default('locked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_locks');
    }
};
