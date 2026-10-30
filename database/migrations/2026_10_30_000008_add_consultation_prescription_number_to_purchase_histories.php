<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table): void {
            $table->string('consultation_prescription_number', 16)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_histories', function (Blueprint $table): void {
            $table->dropIndex(['consultation_prescription_number']);
            $table->dropColumn('consultation_prescription_number');
        });
    }
};
