<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_purchase_order_items', function (Blueprint $table): void {
            $table->decimal('requested_discount', 16, 2)->default(0)->after('line_total');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn('requested_discount');
        });
    }
};
