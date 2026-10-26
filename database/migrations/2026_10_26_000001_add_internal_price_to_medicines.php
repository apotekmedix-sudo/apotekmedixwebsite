<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('medicines', 'harga_internal')) {
            Schema::table('medicines', function (Blueprint $table): void {
                $table->decimal('harga_internal', 12, 2)->nullable()->after('harga_modal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('medicines', 'harga_internal')) {
            Schema::table('medicines', function (Blueprint $table): void {
                $table->dropColumn('harga_internal');
            });
        }
    }
};
