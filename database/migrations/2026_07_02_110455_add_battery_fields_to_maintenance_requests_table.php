<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('battery_type')->nullable()->after('service_id');

            $table->integer('ownership_duration')->nullable()
                ->comment('مدة امتلاك البطارية بالأشهر')
                ->after('battery_type');

            $table->string('image')->nullable()->after('ownership_duration');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'battery_type',
                'ownership_duration',
                'image',
            ]);
        });
    }
};
