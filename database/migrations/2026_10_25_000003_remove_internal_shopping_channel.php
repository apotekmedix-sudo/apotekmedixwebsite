<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shopping_access_codes')) {
            DB::table('shopping_access_codes')->where('channel', 'internal')->delete();
        }

        $columns = array_values(array_filter(
            ['harga_internal', 'promo_internal_percent'],
            fn (string $column): bool => Schema::hasColumn('medicines', $column)
        ));

        if ($columns !== []) {
            Schema::table('medicines', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table): void {
            if (!Schema::hasColumn('medicines', 'harga_internal')) {
                $table->decimal('harga_internal', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'promo_internal_percent')) {
                $table->unsignedTinyInteger('promo_internal_percent')->default(0);
            }
        });
    }
};
