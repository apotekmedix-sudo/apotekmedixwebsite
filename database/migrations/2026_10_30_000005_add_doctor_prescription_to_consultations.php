<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->string('prescription_number', 40)->nullable()->unique();
            $table->longText('prescribed_items')->nullable();
            $table->timestamp('prescription_approved_at')->nullable();
            $table->timestamp('prescription_used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->dropUnique(['prescription_number']);
            $table->dropColumn([
                'prescription_number',
                'prescribed_items',
                'prescription_approved_at',
                'prescription_used_at',
            ]);
        });
    }
};
