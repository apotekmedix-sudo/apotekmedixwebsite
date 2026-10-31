<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_histories') && !Schema::hasColumn('purchase_histories', 'payment_proof_path')) {
            Schema::table('purchase_histories', function (Blueprint $table) {
                $table->string('payment_proof_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_histories') && Schema::hasColumn('purchase_histories', 'payment_proof_path')) {
            Schema::table('purchase_histories', function (Blueprint $table) {
                $table->dropColumn('payment_proof_path');
            });
        }
    }
};
