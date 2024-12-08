<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم السوق
            $table->string('phone')->nullable(); // رقم الهاتف
            $table->unsignedBigInteger('user_id'); // معرف المندوب
            $table->string('address'); // المنطقة
            $table->string('status'); // الحالة
            $table->string('system_market_number')->unique(); // رقم السوق في المنظومة
            $table->string('role')->default('user');
            $table->timestamps();

            // إعداد المفتاح الخارجي
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('markets');
    }
};
