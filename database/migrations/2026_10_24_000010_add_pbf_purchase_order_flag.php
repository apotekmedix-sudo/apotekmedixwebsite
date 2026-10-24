<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
                $table->boolean('is_pbf_purchase_order')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
                $table->dropColumn('is_pbf_purchase_order');
            }
        });
    }
};
