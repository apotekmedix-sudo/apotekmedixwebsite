<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_access_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('code_hash', 64)->unique();
            $table->text('code_value');
            $table->string('status', 20)->default('available');
            $table->foreignId('doctor_consultation_id')->nullable()
                ->constrained('doctor_consultations')->nullOnDelete();
            $table->foreignId('generated_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['doctor_consultation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_access_codes');
    }
};
