<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shopping_access_codes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('generated_by');
        });
    }

    public function down(): void
    {
        Schema::table('shopping_access_codes', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
