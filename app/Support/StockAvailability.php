<?php

namespace App\Support;

use App\Models\Medicine;
use App\Models\PurchaseStockReservation;
use Illuminate\Database\Eloquent\Collection;

class StockAvailability
{
    public static function unitsByMedicineId(iterable $medicines): array
    {
        $medicines = collect($medicines);
        $medicineIds = $medicines->pluck('id')->unique()->values();
        $reservedUnits = PurchaseStockReservation::query()
            ->whereIn('medicine_id', $medicineIds)
            ->selectRaw('medicine_id, SUM(quantity_units) as reserved_units')
            ->groupBy('medicine_id')
            ->pluck('reserved_units', 'medicine_id');

        return $medicines->mapWithKeys(function (Medicine $medicine) use ($reservedUnits) {
            $physicalUnits = (int) ($medicine->stock_base_units ?? $medicine->stok);

            return [$medicine->id => max(0, $physicalUnits - (int) $reservedUnits->get($medicine->id, 0))];
        })->all();
    }

    public static function annotate(Collection $medicines): Collection
    {
        $availableUnits = self::unitsByMedicineId($medicines);

        return $medicines->each(function (Medicine $medicine) use ($availableUnits): void {
            $medicine->setAttribute('available_stock_units', $availableUnits[$medicine->id] ?? 0);
        });
    }
}
