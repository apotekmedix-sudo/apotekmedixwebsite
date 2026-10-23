<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->boolean('unit_packaging_enabled')->default(false);
            $table->unsignedInteger('tablets_per_strip')->nullable();
            $table->unsignedInteger('strips_per_box')->nullable();
            $table->decimal('price_tablet', 12, 2)->nullable();
            $table->decimal('price_strip', 12, 2)->nullable();
            $table->decimal('price_box', 12, 2)->nullable();
            $table->unsignedBigInteger('stock_base_units')->default(0);
        });

        DB::table('medicines')->update(['stock_base_units' => DB::raw('stok')]);
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'unit_packaging_enabled',
                'tablets_per_strip',
                'strips_per_box',
                'price_tablet',
                'price_strip',
                'price_box',
                'stock_base_units',
            ]);
        });
    }
};
