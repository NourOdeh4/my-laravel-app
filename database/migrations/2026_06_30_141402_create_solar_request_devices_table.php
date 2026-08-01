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
   Schema::create('solar_request_devices', function (Blueprint $table) {

    $table->id();

    $table->foreignId('solar_request_id')
        ->constrained()
        ->cascadeOnDelete();

    // 🔥 بدل device_id
    $table->foreignId('device_user_id')
        ->constrained('device_user')
        ->cascadeOnDelete();

    $table->integer('working_hours');

    $table->timestamps();
});}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_request_devices');
    }
};
