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
       Schema::create('solar_packages', function (Blueprint $table) {
    $table->id();

    $table->string('name'); // اسم التركيبة
    $table->integer('inverter_watt');
    $table->string('battery');
    $table->integer('panels');
    $table->integer('price');
    $table->integer('capacity_watt'); // الاستطاعة

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_packages');
    }
};
