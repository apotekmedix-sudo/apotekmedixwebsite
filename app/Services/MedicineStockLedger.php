<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MedicineStockLedger
{
    private static array $context = [];

    public static function withContext(
        string $type,
        ?int $referenceId,
        callable $callback,
        int $reservedDelta = 0,
        ?string $note = null
    ): mixed {
        $previous = self::$context;
        self::$context = [
            'type' => $type,
            'reference_id' => $referenceId,
            'reserved_delta' => $reservedDelta,
            'note' => $note,
        ];

        try {
            return $callback();
        } finally {
            self::$context = $previous;
        }
    }

    public static function physicalUnitsFrom(array $attributes): int
    {
        if (isset($attributes['stock_base_units'])) {
            return max(0, (int) $attributes['stock_base_units']);
        }

        $usesTabletPackaging = (bool) ($attributes['unit_packaging_enabled'] ?? false)
            && Medicine::supportsTabletPackaging(
                (string) ($attributes['satuan'] ?? ''),
                (string) ($attributes['sediaan'] ?? '')
            );
        $unitsPerPackage = $usesTabletPackaging
            ? max(1, (int) ($attributes['tablets_per_strip'] ?? 1))
                * max(1, (int) ($attributes['strips_per_box'] ?? 1))
            : 1;

        return max(0, (int) ($attributes['stok'] ?? 0) * $unitsPerPackage);
    }

    public static function recordOpening(Medicine $medicine): void
    {
        $units = $medicine->getPhysicalStockUnits();
        self::record($medicine, 'opening', $units, 0, 0, $units, 'Saldo awal stok produk.');
    }

    public static function recordReservedChange(
        int $medicineId,
        int $delta,
        ?int $referenceId,
        string $type,
        ?string $note = null
    ): void {
        $medicine = Medicine::find($medicineId);
        if (!$medicine) {
            return;
        }

        $units = $medicine->getPhysicalStockUnits();
        self::record(
            $medicine,
            $type,
            0,
            $delta,
            $units,
            $units,
            $note,
            $referenceId
        );
    }

    public static function recordPhysicalChange(
        Medicine $medicine,
        int $before,
        int $after,
        string $type,
        ?int $referenceId = null,
        int $reservedDelta = 0,
        ?string $note = null
    ): void {
        self::record($medicine, $type, $after - $before, $reservedDelta, $before, $after, $note, $referenceId);
    }

    public static function recordModelUpdate(Medicine $medicine, int $before, int $after): void
    {
        if ($before === $after) {
            return;
        }

        $context = self::$context;
        self::record(
            $medicine,
            $context['type'] ?? 'adjustment',
            $after - $before,
            $context['reserved_delta'] ?? 0,
            $before,
            $after,
            $context['note'] ?? 'Penyesuaian stok dari pengelolaan produk.',
            $context['reference_id'] ?? null
        );
    }

    private static function record(
        Medicine $medicine,
        string $type,
        int $physicalDelta,
        int $reservedDelta,
        int $physicalBefore,
        int $physicalAfter,
        ?string $note,
        ?int $referenceId = null
    ): void {
        if (!Schema::hasTable('medicine_stock_movements')) {
            return;
        }

        $context = self::$context;
        $medicine->stockMovements()->create([
            'movement_type' => $type,
            'physical_delta' => $physicalDelta,
            'reserved_delta' => $reservedDelta,
            'physical_before' => $physicalBefore,
            'physical_after' => $physicalAfter,
            'reference_id' => $referenceId ?? ($context['reference_id'] ?? null),
            'note' => $note ?? ($context['note'] ?? null),
            'created_by' => Auth::id(),
        ]);
    }
}
