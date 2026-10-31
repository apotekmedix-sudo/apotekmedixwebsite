<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->longText('patient_address')->nullable()->after('patient_email');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->dropColumn('patient_address');
        });
    }
};
