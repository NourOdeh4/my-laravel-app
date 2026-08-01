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
       Schema::create('solar_requests', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('technician_id')
      ->nullable()
      ->constrained('users')
      ->nullOnDelete();

    $table->foreignId('solar_package_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->enum('status', [
        'pending',
        'accepted',
        'rejected',
        'completed'
    ])->default('pending');

    $table->timestamps();
});}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_requests');
    }
};
