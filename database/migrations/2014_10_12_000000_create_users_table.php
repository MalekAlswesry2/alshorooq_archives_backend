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
            $table->string('phone')->unique(); // رقم الهاتف
            $table->string('department'); // القسم
            $table->string('address'); // خد السير
            $table->string('email')->unique(); // الإيميل
            $table->timestamp('email_verified_at')->nullable();
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