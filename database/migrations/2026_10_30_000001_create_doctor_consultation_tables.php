<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->string('specialty');
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->string('education')->nullable();
            $table->string('practice_place')->nullable();
            $table->string('practice_city')->nullable();
            $table->string('str_number');
            $table->boolean('str_verified')->default(false);
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->unsignedInteger('consultation_price');
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'str_verified', 'specialty']);
            $table->index(['is_active', 'is_online']);
        });

        Schema::create('doctor_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained()->restrictOnDelete();
            $table->string('patient_access_hash', 64)->unique();
            $table->longText('patient_name');
            $table->longText('patient_birth_date');
            $table->longText('patient_gender');
            $table->longText('patient_phone');
            $table->longText('patient_email');
            $table->longText('guardian_name')->nullable();
            $table->longText('guardian_relationship')->nullable();
            $table->longText('height_cm')->nullable();
            $table->longText('weight_kg')->nullable();
            $table->longText('complaint');
            $table->longText('drug_allergies')->nullable();
            $table->unsignedInteger('amount');
            $table->string('payment_method', 20);
            $table->string('payment_status', 20)->default('pending');
            $table->string('status', 30)->default('awaiting_payment');
            $table->timestamp('privacy_consent_at');
            $table->timestamp('guardian_consent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('payment_verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_verified_at')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_profile_id', 'status']);
            $table->index(['payment_status', 'created_at']);
        });

        Schema::create('doctor_consultation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_consultation_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('body');
            $table->timestamps();

            $table->index(['doctor_consultation_id', 'id']);
        });

        Schema::create('doctor_consultation_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('qris_image_path')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_consultation_payment_settings');
        Schema::dropIfExists('doctor_consultation_messages');
        Schema::dropIfExists('doctor_consultations');
        Schema::dropIfExists('doctor_profiles');
    }
};
