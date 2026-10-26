<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('movement_type', 32);
            $table->bigInteger('physical_delta')->default(0);
            $table->bigInteger('reserved_delta')->default(0);
            $table->unsignedBigInteger('physical_before')->default(0);
            $table->unsignedBigInteger('physical_after')->default(0);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['medicine_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
        });

        DB::table('medicines')->orderBy('id')->chunk(500, function ($medicines): void {
            foreach ($medicines as $medicine) {
                $usesTabletPackaging = (bool) ($medicine->unit_packaging_enabled ?? false)
                    && \App\Models\Medicine::supportsTabletPackaging(
                        (string) ($medicine->satuan ?? ''),
                        (string) ($medicine->sediaan ?? '')
                    );
                $unitsPerPackage = $usesTabletPackaging
                    ? max(1, (int) ($medicine->strips_per_box ?? 1))
                        * max(1, (int) ($medicine->tablets_per_strip ?? 1))
                    : 1;
                $physicalUnits = max(0, (int) (
                    $medicine->stock_base_units
                    ?? ((int) ($medicine->stok ?? 0) * $unitsPerPackage)
                ));
                DB::table('medicine_stock_movements')->insert([
                    'medicine_id' => $medicine->id,
                    'movement_type' => 'opening',
                    'physical_delta' => $physicalUnits,
                    'reserved_delta' => 0,
                    'physical_before' => 0,
                    'physical_after' => $physicalUnits,
                    'note' => 'Saldo awal saat pencatatan mutasi stok diaktifkan.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($medicine->stock_base_units === null) {
                    DB::table('medicines')
                        ->where('id', $medicine->id)
                        ->update(['stock_base_units' => $physicalUnits]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_stock_movements');
    }
};
