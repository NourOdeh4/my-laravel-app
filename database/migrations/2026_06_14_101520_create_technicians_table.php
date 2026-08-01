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
    Schema::create('technicians', function (Blueprint $table) {
        $table->id();

        $table->string('name')->nullable();
        $table->string('last_name')->nullable();

        $table->string('email')->unique();
        $table->string('password');

        $table->string('phone')->nullable();
        $table->string('address')->nullable();
        $table->string('avatar')->nullable();

        $table->boolean('is_active')->default(false);

        $table->string('verification_code')->nullable();
        $table->text('temp_password')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
