<?php

namespace App\Support;

use App\Models\Medicine;
use App\Models\PurchaseStockReservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StockAvailability
{
    public static function prioritizeLowestAvailableStock(Builder $query): Builder
    {
        return $query
            ->orderByRaw("
                CASE
                    WHEN (
                        CASE
                            WHEN COALESCE(medicines.unit_packaging_enabled, 0) = 1
                                THEN COALESCE(medicines.stock_base_units, medicines.stok)
                            ELSE medicines.stok
                        END
                        - COALESCE((
                            SELECT SUM(purchase_stock_reservations.quantity_units)
                            FROM purchase_stock_reservations
                            WHERE purchase_stock_reservations.medicine_id = medicines.id
                        ), 0)
                    ) > 0 THEN (
                        CASE
                            WHEN COALESCE(medicines.unit_packaging_enabled, 0) = 1
                                THEN COALESCE(medicines.stock_base_units, medicines.stok)
                            ELSE medicines.stok
                        END
                        - COALESCE((
                            SELECT SUM(purchase_stock_reservations.quantity_units)
                            FROM purchase_stock_reservations
                            WHERE purchase_stock_reservations.medicine_id = medicines.id
                        ), 0)
                    )
                    ELSE 0
                END
            ")
            ->orderBy('nama_obat');
    }

    public static function prioritizeAvailableStock(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE WHEN (
                CASE
                    WHEN COALESCE(medicines.unit_packaging_enabled, 0) = 1
                        THEN COALESCE(medicines.stock_base_units, medicines.stok)
                    ELSE medicines.stok
                END
                - COALESCE((
                    SELECT SUM(purchase_stock_reservations.quantity_units)
                    FROM purchase_stock_reservations
                    WHERE purchase_stock_reservations.medicine_id = medicines.id
                ), 0)
            ) > 0 THEN 0 ELSE 1 END
        ");
    }

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
