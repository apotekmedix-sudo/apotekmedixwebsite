<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('banners')) {
            return;
        }

        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'judul')) {
                $table->string('judul')->nullable()->change();
            }
            if (Schema::hasColumn('banners', 'label_tombol')) {
                $table->string('label_tombol')->nullable()->default('Lihat Sekarang')->change();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
