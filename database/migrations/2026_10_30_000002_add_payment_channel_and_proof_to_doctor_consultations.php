<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->string('payment_channel', 20)->default('online')->after('payment_method');
            $table->string('payment_proof_path')->nullable()->after('payment_channel');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_consultations', function (Blueprint $table) {
            $table->dropColumn(['payment_channel', 'payment_proof_path']);
        });
    }
};
