<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_user', function (Blueprint $table) {
            $table->id();

            // 👤 المستخدم
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // ⚡ الخدمة (طاقة شمسية / صيانة / صناعي...)
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->cascadeOnDelete();

            // 📌 اسم الجهاز من AI
            $table->string('title');

            // ⏱ عدد ساعات التشغيل
            $table->integer('hours');

            // ⚡ الاستطاعة لكل ساعة
            $table->integer('watt_per_hour');

            // 🔋 الاستهلاك الكلي
            $table->integer('consumption');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_user');
    }
};
