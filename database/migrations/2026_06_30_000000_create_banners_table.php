<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable("banners")) {
            return;
        }

        Schema::create("banners", function (Blueprint $table) {
            $table->id();
            $table->string("judul")->nullable();
            $table->string("subjudul")->nullable();
            $table->string("gambar");
            $table->string("url_tujuan")->nullable();
            $table->string("label_tombol")->nullable()->default("Lihat Sekarang");
            $table->boolean("aktif")->default(true);
            $table->integer("urutan")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("banners");
    }
};
