<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->string('access_code_hash', 64)->nullable();
            $table->timestamp('access_code_expires_at')->nullable();
            $table->timestamp('access_code_used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->dropColumn([
                'access_code_hash',
                'access_code_expires_at',
                'access_code_used_at',
            ]);
        });
    }
};
