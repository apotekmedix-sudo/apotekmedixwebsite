<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('receipt_status', 20)->default('pending')->after('payment_status');
            $table->timestamp('verified_at')->nullable()->after('paid_at');
            $table->timestamp('printed_at')->nullable()->after('verified_at');
            $table->timestamp('cancelled_at')->nullable()->after('printed_at');
            $table->index(['receipt_status', 'created_at']);
        });

        DB::table('purchase_histories')
            ->where('payment_status', 'paid')
            ->update([
                'receipt_status' => 'verified',
                'verified_at' => DB::raw('paid_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex(['receipt_status', 'created_at']);
            $table->dropColumn(['receipt_status', 'verified_at', 'printed_at', 'cancelled_at']);
        });
    }
};
