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
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ارتباط بالمستخدم
            $table->foreignId('receipt_id')->nullable()->constrained()->onDelete('set null'); // ارتباط بالإيصال
            $table->enum('type', ['not_received', 'received']); // نوع العملية
            $table->decimal('amount', 15, 2); // مبلغ العملية
            $table->decimal('balance_after', 15, 2); // الرصيد بعد العمل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_transactions');
    }
};
