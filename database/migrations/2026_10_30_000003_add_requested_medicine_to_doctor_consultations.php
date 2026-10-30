<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->longText('requested_medicine')->nullable()->after('drug_allergies');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->dropColumn('requested_medicine');
        });
    }
};
