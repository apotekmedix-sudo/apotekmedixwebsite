<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_histories')) {
            return;
        }

        if (!Schema::hasColumn('purchase_histories', 'delivery_method')) {
            Schema::table('purchase_histories', function (Blueprint $table) {
                $table->string('delivery_method', 20)->nullable();
                $table->index(['buyer_type', 'delivery_method'], 'purchase_histories_buyer_delivery_idx');
            });
        }

        DB::table('purchase_histories')
            ->where('buyer_type', 'umum')
            ->where('transaction_channel', 'online')
            ->whereNull('delivery_method')
            ->where(function ($query) {
                $query->whereNull('is_pbf_purchase_order')
                    ->orWhere('is_pbf_purchase_order', false);
            })
            ->update([
                'delivery_method' => DB::raw("CASE WHEN LOWER(COALESCE(address, '')) LIKE 'ambil di apotek medix%' THEN 'pickup' ELSE 'delivery' END"),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_histories') || !Schema::hasColumn('purchase_histories', 'delivery_method')) {
            return;
        }

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex('purchase_histories_buyer_delivery_idx');
            $table->dropColumn('delivery_method');
        });
    }
};
