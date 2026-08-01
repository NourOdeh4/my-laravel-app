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
    Schema::table('solar_requests', function (Blueprint $table) {
        $table->foreignId('installation_request_id')
            ->nullable()
            ->after('user_id')
            ->constrained('solar_installation_requests')
            ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('solar_requests', function (Blueprint $table) {
        $table->dropConstrainedForeignId('installation_request_id');
    });
}
};
