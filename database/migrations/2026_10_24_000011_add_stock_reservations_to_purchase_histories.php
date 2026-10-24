<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->boolean('stock_committed')->default(true);
        });

        Schema::create('purchase_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_history_id')->constrained('purchase_histories')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->unsignedInteger('quantity_units');
            $table->unsignedInteger('sale_quantity');
            $table->timestamps();
            $table->unique(['purchase_history_id', 'medicine_id']);
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_stock_reservations');

        Schema::table('purchase_histories', function (Blueprint $table) {
            $table->dropColumn('stock_committed');
        });
    }
};
