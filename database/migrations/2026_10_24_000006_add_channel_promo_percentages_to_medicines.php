<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (!Schema::hasColumn('medicines', 'promo_internal_percent')) {
                $table->unsignedTinyInteger('promo_internal_percent')->default(0);
            }
            if (!Schema::hasColumn('medicines', 'promo_b2b_percent')) {
                $table->unsignedTinyInteger('promo_b2b_percent')->default(0);
            }
            if (!Schema::hasColumn('medicines', 'promo_b2c_percent')) {
                $table->unsignedTinyInteger('promo_b2c_percent')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            foreach (['promo_internal_percent', 'promo_b2b_percent', 'promo_b2c_percent'] as $column) {
                if (Schema::hasColumn('medicines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
