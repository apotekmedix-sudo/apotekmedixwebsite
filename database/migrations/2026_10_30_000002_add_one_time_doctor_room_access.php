<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('whatsapp_phone', 20)->nullable()->after('display_name');
        });

        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->string('patient_room_token_hash', 64)->nullable()->unique();
            $table->string('doctor_room_token_hash', 64)->nullable()->unique();
            $table->timestamp('patient_room_token_used_at')->nullable();
            $table->timestamp('doctor_room_token_used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table): void {
            $table->dropUnique(['patient_room_token_hash']);
            $table->dropUnique(['doctor_room_token_hash']);
            $table->dropColumn([
                'patient_room_token_hash',
                'doctor_room_token_hash',
                'patient_room_token_used_at',
                'doctor_room_token_used_at',
            ]);
        });

        Schema::table('doctor_profiles', function (Blueprint $table): void {
            $table->dropColumn('whatsapp_phone');
        });
    }
};
