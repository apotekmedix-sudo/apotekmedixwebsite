<?php

namespace App\Support;

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
        ];
    }

    public static function apply(Builder $query, string $category): void
    {
        $categoryFilters = [
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
        ];

        if (!isset($categoryFilters[$category])) {
            return;
        }

        $filter = $categoryFilters[$category];
        if (($filter['type'] ?? null) === 'prescription') {
            $query->where(function (Builder $categoryQuery) use ($category) {
                $categoryQuery->where('is_resep', true)
                    ->orWhere('kategori_produk', $category);
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
                $categoryQuery->where('kategori_produk', $category)
                    ->orWhere(function (Builder $termsQuery) use ($filter) {
                        foreach ($filter['terms'] as $term) {
                            $termsQuery->orWhere(function (Builder $termQuery) use ($term) {
                                foreach (['nama_obat', 'kategori', 'deskripsi', 'indikasi', 'komposisi'] as $column) {
                                    $termQuery->orWhere($column, 'like', '%' . $term . '%');
                                }
                            });
                        }
                    });
            });
        }
    }
}
