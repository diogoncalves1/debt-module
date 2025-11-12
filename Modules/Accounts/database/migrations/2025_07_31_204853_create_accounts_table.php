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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['cash', 'bank_account', 'credit_card', 'digital_wallet']);
            $table->float("balance")->default(0);
            $table->unsignedBigInteger("currency_id")->nullable();
            $table->boolean("active")->default(1);
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
