<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Layer 2: Halaman kategori dengan sub-kategori dan produk
     */
    public function layer2(Request $request)
    {
        $mainCategory = $request->get('main', 'obat');
        $subCategory = $request->get('sub', '');
        $search = $request->get('search', '');

        // Validasi kategori
        $validCategories = $this->getValidCategories();
        if (!array_key_exists($mainCategory, $validCategories)) {
            abort(404, 'Kategori tidak ditemukan');
        }

        // Query produk berdasarkan kategori
        $query = Medicine::query();

        // Filter berdasarkan kategori dan sub-kategori
        $query = $this->filterByCategory($query, $mainCategory, $subCategory);

        // Filter berdasarkan search & atribut
        if ($search) {
            $query->search($search);
        }
        $query->filterByAttributes($request->only(['indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan']));

        $medicines = $query->latest()->paginate(12)->withQueryString();

        return view('category-layer2', compact(
            'mainCategory',
            'subCategory',
            'medicines',
            'search'
        ));
    }

    /**
     * Mapping kategori dan sub-kategori dengan field di database
     */
    private function getValidCategories()
    {
        return [
            'alkes' => [
                'ortopedi' => 'Alkes Ortopedi',
                'gigi' => 'Alkes Gigi',
                'electrical' => 'Alkes Electrical',
                'non-electrical' => 'Alkes Non Electrical',
            ],
            'kecantikan' => [
                'skincare' => 'Skincare',
                'kosmetik' => 'Kosmetik',
                'material' => 'Material Klinik',
            ],
            'apotik' => [
                'oral' => 'Obat Oral',
                'injeksi' => 'Obat Injeksi',
                'luar' => 'Obat Luar',
                'otc' => 'Obat OTC',
                'susu' => 'Susu',
                'suplemen' => 'Suplemen',
                'herbal' => 'Herbal',
            ],
            'pbf' => [],
        ];
    }

    /**
     * Filter query berdasarkan kategori utama dan sub-kategori
     */
    private function filterByCategory($query, $mainCategory, $subCategory)
    {
        $categories = $this->getValidCategories();

        if (!isset($categories[$mainCategory])) {
            return $query;
        }

        $categoryMap = $categories[$mainCategory];

        if ($subCategory && isset($categoryMap[$subCategory])) {
            $categoryName = $categoryMap[$subCategory];
            // Filter berdasarkan kategori atau kategori produk
            $query->where(function ($q) use ($categoryName, $mainCategory) {
                $q->where('kategori', 'like', "%{$categoryName}%")
                  ->orWhere('kategori_produk', 'like', "%{$categoryName}%");
            });
        } else {
            // Jika tidak ada sub-kategori, filter berdasarkan kategori utama
            $mainCategoryName = ucfirst($mainCategory);
            $query->where(function ($q) use ($mainCategoryName, $mainCategory) {
                if ($mainCategory === 'kecantikan') {
                    $q->where('kategori_produk', 'like', '%SKINCARE%')
                      ->orWhere('kategori_produk', 'like', '%KOSMETIK%');
                } elseif ($mainCategory === 'alkes') {
                    $q->where('kategori_produk', 'like', '%ALAT KESEHATAN%');
                } elseif ($mainCategory === 'apotik') {
                    $q->where('kategori_produk', 'like', '%OBAT%')
                      ->orWhere('kategori_produk', 'like', '%APOTIK%')
                      ->orWhere('kategori', 'like', '%Apotik%')
                      ->orWhere('is_resep', true)
                      ->orWhere('is_resep', false);
                } else {
                    $q->where('kategori', 'like', "%{$mainCategoryName}%");
                }
            });
        }

        return $query;
    }
}
