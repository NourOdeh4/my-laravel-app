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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'verification_code_expires_at')) {
            $table->timestamp('verification_code_expires_at')->nullable();
        }
        if (!Schema::hasColumn('users', 'is_active')) {
            $table->boolean('is_active')->default(false);
        }
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['verification_code', 'verification_code_expires_at', 'is_active']);
    });
}
};
