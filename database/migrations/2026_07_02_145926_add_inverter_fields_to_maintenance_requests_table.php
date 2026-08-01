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
    Schema::table('maintenance_requests', function (Blueprint $table) {
        $table->string('inverter_code')->nullable();
        $table->integer('input_voltage')->nullable();
        $table->integer('output_voltage')->nullable();
        $table->text('notes')->nullable();
    });
}

public function down(): void
{
    Schema::table('maintenance_requests', function (Blueprint $table) {
        $table->dropColumn([
            'inverter_code',
            'input_voltage',
            'output_voltage',
            'notes'
        ]);
    });
}
};
