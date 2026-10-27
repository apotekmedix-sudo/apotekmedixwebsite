<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_product_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('pbf_suppliers')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('supplier_code', 100)->nullable();
            $table->decimal('purchase_price', 14, 2);
            $table->unsignedInteger('minimum_quantity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['supplier_id', 'medicine_id']);
            $table->index(['supplier_id', 'is_active']);
        });

        Schema::create('supplier_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('pbf_suppliers')->nullOnDelete();
            $table->string('supplier_name');
            $table->text('supplier_address')->nullable();
            $table->string('supplier_phone', 50)->nullable();
            $table->string('order_number', 40)->unique();
            $table->string('status', 24)->default('ordered');
            $table->date('ordered_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'ordered_at']);
        });

        Schema::create('supplier_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_product_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('supplier_code', 100)->nullable();
            $table->string('unit_label', 100)->default('Unit');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('received_quantity')->default(0);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('line_total', 16, 2);
            $table->timestamps();
            $table->index(['supplier_purchase_order_id', 'medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_purchase_order_items');
        Schema::dropIfExists('supplier_purchase_orders');
        Schema::dropIfExists('supplier_product_offers');
    }
};
