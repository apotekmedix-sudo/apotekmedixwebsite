<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_histories') || Schema::hasColumn('purchase_histories', 'price_channel')) {
            return;
        }

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->string('price_channel', 20)->default('b2c');
            $table->index(['price_channel', 'transaction_channel'], 'purchase_histories_price_channel_idx');
        });

        DB::table('purchase_histories')
            ->whereIn('buyer_type', ['apotik', 'dokter', 'pbf'])
            ->update(['price_channel' => 'b2b']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_histories') || !Schema::hasColumn('purchase_histories', 'price_channel')) {
            return;
        }

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropIndex('purchase_histories_price_channel_idx');
            $table->dropColumn('price_channel');
        });
    }
};
