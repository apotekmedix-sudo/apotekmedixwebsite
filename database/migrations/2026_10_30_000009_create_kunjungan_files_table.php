<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_files', function (Blueprint $table): void {
            $table->id();
            $table->text('original_name');
            $table->string('path')->unique();
            $table->unsignedBigInteger('size_bytes');
            $table->text('mime_type')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_files');
    }
};
