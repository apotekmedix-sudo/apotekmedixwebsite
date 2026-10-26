<?php

namespace App\Support;

use App\Models\Medicine;
use App\Models\PurchaseStockReservation;
use Illuminate\Support\Collection;

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
            $physicalUnits = $medicine->getPhysicalStockUnits();

            return [$medicine->id => max(0, $physicalUnits - (int) $reservedUnits->get($medicine->id, 0))];
        })->all();
    }

    public static function annotate(Collection $medicines): Collection
    {
        $medicineIds = $medicines->pluck('id')->unique()->values();
        $reservedUnits = PurchaseStockReservation::query()
            ->whereIn('medicine_id', $medicineIds)
            ->selectRaw('medicine_id, SUM(quantity_units) as reserved_units')
            ->groupBy('medicine_id')
            ->pluck('reserved_units', 'medicine_id');

        return $medicines->each(function (Medicine $medicine) use ($reservedUnits): void {
            $physicalUnits = $medicine->getPhysicalStockUnits();
            $reserved = (int) $reservedUnits->get($medicine->id, 0);
            $medicine->setAttribute('physical_stock_units', $physicalUnits);
            $medicine->setAttribute('reserved_stock_units', $reserved);
            $medicine->setAttribute('available_stock_units', max(0, $physicalUnits - $reserved));
        });
    }
}
