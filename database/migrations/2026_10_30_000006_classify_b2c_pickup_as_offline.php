<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_histories') || !Schema::hasColumn('purchase_histories', 'delivery_method')) {
            return;
        }

        DB::table('purchase_histories')
            ->where('buyer_type', 'umum')
            ->where('delivery_method', 'pickup')
            ->where(fn ($query) => $query->whereNull('is_pbf_purchase_order')->orWhere('is_pbf_purchase_order', false))
            ->update(['transaction_channel' => 'offline']);

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex('purchase_histories_buyer_delivery_idx');
            $table->dropColumn('delivery_method');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_histories') || Schema::hasColumn('purchase_histories', 'delivery_method')) {
            return;
        }

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('delivery_method', 20)->nullable();
            $table->index(['buyer_type', 'delivery_method'], 'purchase_histories_buyer_delivery_idx');
        });

        DB::table('purchase_histories')
            ->where('buyer_type', 'umum')
            ->update([
                'delivery_method' => DB::raw("CASE WHEN transaction_channel = 'offline' THEN 'pickup' ELSE 'delivery' END"),
            ]);
    }
};
