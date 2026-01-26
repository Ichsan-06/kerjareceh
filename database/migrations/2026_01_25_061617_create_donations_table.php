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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id')->unique();
            $table->decimal('nominal', 15, 2);
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->text('qris_content')->nullable(); // To store the QR string or URL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
