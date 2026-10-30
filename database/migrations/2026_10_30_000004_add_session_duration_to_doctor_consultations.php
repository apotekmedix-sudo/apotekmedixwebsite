<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_duration_minutes')->default(15);
        });

        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_duration_minutes')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->dropColumn('session_duration_minutes');
        });

        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropColumn('session_duration_minutes');
        });
    }
};
