<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbf_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('pbf_suppliers', 'phone')) {
                $table->string('phone', 50)->nullable()->after('address');
            }
        });

        Schema::table('purchase_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_histories', 'pbf_phone')) {
                $table->string('pbf_phone', 50)->nullable()->after('pbf_address');
            }
            if (!Schema::hasColumn('purchase_histories', 'purchase_order_type')) {
                $table->string('purchase_order_type', 20)->default('regular')->after('pbf_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_histories', 'purchase_order_type')) {
                $table->dropColumn('purchase_order_type');
            }
            if (Schema::hasColumn('purchase_histories', 'pbf_phone')) {
                $table->dropColumn('pbf_phone');
            }
        });

        Schema::table('pbf_suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('pbf_suppliers', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
