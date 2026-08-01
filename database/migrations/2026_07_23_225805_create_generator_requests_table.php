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
       Schema::create('generator_requests', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('installation_request_id')
        ->constrained('solar_installation_requests')
        ->cascadeOnDelete();

    $table->foreignId('generator_id')
        ->constrained('generators')
        ->cascadeOnDelete();

    $table->foreignId('technician_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->enum('status', [
        'pending',
        'accepted',
        'completed',
        'rejected'
    ])->default('pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_requests');
    }
};
