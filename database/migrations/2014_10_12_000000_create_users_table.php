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
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('name'); // الاسم
            $table->string('phone')->unique()->nullable(); // رقم الهاتف
            // $table->string('address')->nullable(); // خد السير
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('status')->default('active');
            $table->string('role')->nullable(); // 
            $table->string('email')->unique(); // الإيميل
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('password'); // كلمة المرور
            $table->rememberToken();
            $table->timestamps(); // تاريخ الإنشاء والتحديث
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
