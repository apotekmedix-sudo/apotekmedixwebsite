<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_cashier')->default(false);
        });

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('transaction_channel', 20)->default('online');
            $table->string('payment_status', 20)->default('pending');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->index(['transaction_channel', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex(['transaction_channel', 'payment_status']);
            $table->dropConstrainedForeignId('cashier_id');
            $table->dropColumn(['transaction_channel', 'payment_status', 'paid_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_cashier');
        });
    }
};
