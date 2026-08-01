<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_user', function (Blueprint $table) {

            $table->foreignId('installation_request_id')
                ->nullable()
                ->after('service_id')
                ->constrained('solar_installation_requests')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('device_user', function (Blueprint $table) {

            $table->dropForeign(['installation_request_id']);
            $table->dropColumn('installation_request_id');

        });
    }
};
