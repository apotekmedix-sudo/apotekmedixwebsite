<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('qris_image_path')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder', 150)->nullable();
            $table->text('payment_instructions')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('payment_qris_image_path')->nullable();
            $table->string('payment_bank_name', 100)->nullable();
            $table->string('payment_account_number', 50)->nullable();
            $table->string('payment_account_holder', 150)->nullable();
            $table->text('payment_instructions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropColumn([
                'payment_qris_image_path',
                'payment_bank_name',
                'payment_account_number',
                'payment_account_holder',
                'payment_instructions',
            ]);
        });

        Schema::dropIfExists('purchase_payment_settings');
    }
};
