<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_purchase_orders', function (Blueprint $table): void {
            $table->string('purchase_order_type', 24)->default('regular');
            $table->string('purchase_order_profile', 24)->default('medix');
            $table->string('requester_name')->nullable();
            $table->string('apj')->nullable();
            $table->string('sia')->nullable();
            $table->string('sipa')->nullable();
        });

        Schema::table('supplier_purchase_order_items', function (Blueprint $table): void {
            $table->text('active_ingredient')->nullable();
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn(['active_ingredient', 'dosage_form', 'strength']);
        });

        Schema::table('supplier_purchase_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'purchase_order_type',
                'purchase_order_profile',
                'requester_name',
                'apj',
                'sia',
                'sipa',
            ]);
        });
    }
};
