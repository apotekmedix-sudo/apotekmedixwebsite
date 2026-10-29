<?php

namespace App\Support;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;

class HomepageProductCategories
{
    public static function options(): array
    {
        return [
            'obat-resep' => '💊 Obat Resep',
            'otc' => '💊 OTC',
            'pil-kb-hormonal' => '💊 Pil KB & Hormonal',
            'alat-kesehatan' => '🩺 Alat Kesehatan',
            'vitamin-suplemen' => '💊 Vitamin & Suplemen',
            'flu-batuk' => '🤧 Flu & Batuk',
            'ibu-anak' => '👶 Ibu & Anak',
            'obat-herbal' => '🌿 Obat Herbal',
            'obat-diabetes' => '💧 Obat Diabetes',
            'obat-hipertensi' => '❤️ Obat Hipertensi',
            'obat-kolesterol' => '💙 Obat Kolesterol',
            'injeksi-infus' => '💉 Injeksi & Infus',
            'skincare-kosmetik' => '🧴 Skincare & Kosmetik',
        ];
    }

    public static function apply(Builder $query, string $category): void
    {
        $categoryFilters = self::categoryFilters();

        if (!isset($categoryFilters[$category])) {
            return;
        }

        self::applyFilter($query, $category, $categoryFilters[$category]);
    }

    public static function excludeB2cRestricted(Builder $query): void
    {
        $restrictedCategories = self::restrictedCategoryFilters();
        $query->whereNotIn('id', Medicine::query()
            ->select('id')
            ->where(function (Builder $restrictedQuery) use ($restrictedCategories): void {
                foreach ($restrictedCategories as $category => $filter) {
                    $restrictedQuery->orWhere(function (Builder $categoryQuery) use ($category, $filter): void {
                        self::applyFilter($categoryQuery, $category, $filter);
                    });
                }
            }));
    }

    public static function isB2cRestricted(Medicine $medicine): bool
    {
        foreach (self::restrictedCategoryFilters() as $category => $filter) {
            if (($filter['type'] ?? null) === 'prescription' && $medicine->is_resep) {
                return true;
            }
            $storedCategory = mb_strtolower(trim((string) $medicine->kategori_produk));
            if (
                $storedCategory === $category
                || $storedCategory === mb_strtolower(self::categoryLabels()[$category])
            ) {
                return true;
            }

            foreach ($filter['terms'] ?? [] as $term) {
                foreach (['nama_obat', 'kategori', 'deskripsi', 'indikasi', 'komposisi', 'sediaan'] as $column) {
                    if (str_contains(mb_strtolower((string) $medicine->{$column}), $term)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private static function categoryFilters(): array
    {
        return [
            'obat-resep' => ['type' => 'prescription'],
            'otc' => ['type' => 'otc'],
            'pil-kb-hormonal' => ['terms' => ['pil kb', 'kontrasepsi', 'hormonal']],
            'alat-kesehatan' => ['type' => 'medical-equipment'],
            'vitamin-suplemen' => ['terms' => ['vitamin', 'suplemen']],
            'flu-batuk' => ['terms' => ['flu', 'batuk', 'pilek']],
            'ibu-anak' => ['terms' => ['ibu', 'anak', 'bayi']],
            'obat-herbal' => ['terms' => ['herbal', 'jamu']],
            'obat-diabetes' => ['terms' => ['diabetes', 'gula darah']],
            'obat-hipertensi' => ['terms' => ['hipertensi', 'darah tinggi']],
            'obat-kolesterol' => ['terms' => ['kolesterol']],
            'injeksi-infus' => ['terms' => ['injeksi', 'injection', 'infus', 'infusion']],
            'skincare-kosmetik' => ['terms' => ['skincare', 'skin care', 'kosmetik', 'cosmetic']],
        ];
    }

    private static function categoryLabels(): array
    {
        return [
            'obat-resep' => 'Obat Resep',
            'obat-diabetes' => 'Obat Diabetes',
            'obat-hipertensi' => 'Obat Hipertensi',
            'obat-kolesterol' => 'Obat Kolesterol',
            'injeksi-infus' => 'Injeksi & Infus',
            'pil-kb-hormonal' => 'Pil KB & Hormonal',
        ];
    }

    private static function restrictedCategoryFilters(): array
    {
        return array_intersect_key(self::categoryFilters(), array_flip([
            'obat-resep',
            'obat-diabetes',
            'obat-hipertensi',
            'obat-kolesterol',
            'injeksi-infus',
            'pil-kb-hormonal',
        ]));
    }

    private static function applyFilter(Builder $query, string $category, array $filter): void
    {
        if (($filter['type'] ?? null) === 'prescription') {
            $query->where(function (Builder $categoryQuery) use ($category) {
                $categoryQuery->where('is_resep', true)
                    ->orWhereRaw('LOWER(TRIM(kategori_produk)) IN (?, ?)', [
                        $category,
                        mb_strtolower(self::categoryLabels()[$category] ?? $category),
                    ]);
            });
        } elseif (($filter['type'] ?? null) === 'otc') {
            $query->where(function (Builder $categoryQuery) use ($category) {
                $categoryQuery->where('is_resep', false)
                    ->orWhere('kategori_produk', $category);
            });
        } elseif (($filter['type'] ?? null) === 'medical-equipment') {
            $query->whereIn('kategori_produk', [$category, 'ALAT KESEHATAN']);
        } else {
            $query->where(function (Builder $categoryQuery) use ($filter, $category) {
                $categoryQuery->whereRaw('LOWER(TRIM(kategori_produk)) IN (?, ?)', [
                    $category,
                    mb_strtolower(self::categoryLabels()[$category] ?? $category),
                ])
                    ->orWhere(function (Builder $termsQuery) use ($filter) {
                        foreach ($filter['terms'] as $term) {
                            $termsQuery->orWhere(function (Builder $termQuery) use ($term) {
                                foreach (['nama_obat', 'kategori', 'deskripsi', 'indikasi', 'komposisi', 'sediaan'] as $column) {
                                    $termQuery->orWhere($column, 'like', '%' . $term . '%');
                                }
                            });
                        }
                    });
            });
        }
    }
}
