<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopping_access_codes', function (Blueprint $table) {
            $table->unsignedSmallInteger('validity_days')->default(30)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('shopping_access_codes', function (Blueprint $table) {
            $table->dropColumn('validity_days');
        });
    }
};
