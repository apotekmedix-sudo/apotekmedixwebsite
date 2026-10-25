<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_histories', 'purchase_order_profile')) {
                $table->string('purchase_order_profile', 20)->default('medix')->after('purchase_order_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_histories', 'purchase_order_profile')) {
                $table->dropColumn('purchase_order_profile');
            }
        });
    }
};
