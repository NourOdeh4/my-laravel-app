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
    {Schema::create('maintenance_requests', function (Blueprint $table) {

    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->foreignId('service_id')->constrained()->cascadeOnDelete();

    $table->text('problem_description');

    $table->integer('damaged_panels_count')->nullable();

    $table->integer('battery_count')->nullable();

    $table->string('location');

    $table->enum('priority', ['urgent', 'normal', 'low'])->default('normal');

    $table->enum('status', ['pending', 'accepted', 'completed'])
          ->default('pending');

    $table->timestamps();
});}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
