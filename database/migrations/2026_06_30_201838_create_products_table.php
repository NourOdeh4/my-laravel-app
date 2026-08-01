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
        Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');                // اسم المنتج
    $table->text('description')->nullable(); // وصف
    $table->decimal('price', 10, 2);       // سعر
    $table->integer('stock')->default(0);  // كمية متوفرة
    $table->string('image')->nullable();   // صورة
    $table->unsignedBigInteger('device_id')->nullable(); // لو مرتبط بجهاز
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
