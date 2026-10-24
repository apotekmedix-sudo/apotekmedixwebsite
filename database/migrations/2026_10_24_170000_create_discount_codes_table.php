<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discount_codes')) {
            Schema::create('discount_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('description')->nullable();
                $table->string('discount_type', 20)->default('percent'); // percent | fixed
                $table->unsignedBigInteger('discount_value')->default(0);
                $table->boolean('is_unlimited')->default(false);
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('purchase_histories')) {
            Schema::table('purchase_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_histories', 'discount_code')) {
                    $table->string('discount_code', 64)->nullable()->after('discounted_total');
                }
                if (!Schema::hasColumn('purchase_histories', 'discount_code_amount')) {
                    $table->unsignedBigInteger('discount_code_amount')->default(0)->after('discount_code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_histories')) {
            Schema::table('purchase_histories', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('purchase_histories', 'discount_code')) {
                    $cols[] = 'discount_code';
                }
                if (Schema::hasColumn('purchase_histories', 'discount_code_amount')) {
                    $cols[] = 'discount_code_amount';
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('discount_codes');
    }
};
