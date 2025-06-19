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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم (الموظف)
            $table->foreignId('market_id')->constrained()->onDelete('cascade'); // العميل (السوق)
            $table->dateTime('scheduled_at'); // وقت وتاريخ الموعد
            $table->text('description');// وصف الموعد
            $table->enum('status', ['upcoming', 'completed','not_completed', 'canceled'])->default('upcoming'); // حالة الموعد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
