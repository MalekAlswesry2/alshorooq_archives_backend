<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->string('client_number');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // "cash" or "transfer"
            $table->string('check_number')->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
