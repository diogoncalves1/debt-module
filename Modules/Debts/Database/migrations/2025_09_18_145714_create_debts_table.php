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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('name');
            $table->decimal('total_amount', 15);
            $table->decimal('paid_amount', 15)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->integer('installments')->nullable();
            $table->decimal('insterest_rate', 8, 5)->nullable();
            $table->date('start_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->enum('period', ['daily', 'weekly', 'monthly', 'biannual', 'annual']);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
