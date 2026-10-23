<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PromoProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    // Halaman utama
    public function index(Request $request)
    {
        $featuredProducts = Medicine::nonPbf()
                                    ->orderByDesc('terjual')
                                    ->orderBy('nama_obat')
                                    ->limit(8)
                                    ->get();

        $totalProducts = Medicine::nonPbf()->count();

        // Hitung total produk per kategori produk
        $categoryColumn = Schema::hasColumn('medicines', 'kategori_produk') ? 'kategori_produk' : 'kategori';

        $kategoryCounts = Medicine::selectRaw($categoryColumn . ', COUNT(*) as total')
                                  ->nonPbf()
                                  ->whereNotNull($categoryColumn)
                                  ->groupBy($categoryColumn)
                                  ->pluck('total', $categoryColumn);

        // Promo produk aktif
        $promoProducts = Schema::hasTable('promo_products') ? PromoProduct::aktif()->get() : collect();

        return view('home', compact('featuredProducts', 'kategoryCounts', 'totalProducts', 'promoProducts'));
    }

    // Halaman Tentang Kami
    public function about()
    {
        return view('about');
    }

    // Halaman Kontak
    public function contact()
    {
        return view('contact');
    }

    // Detail obat
    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);

        // Related products dari kategori yang sama
        $relatedMedicines = Medicine::where('kategori', $medicine->kategori)
                                    ->where('id', '!=', $medicine->id)
                                    ->limit(4)
                                    ->get();

        return view('medicines.detail', [
            'medicine' => $medicine,
            'relatedMedicines' => $relatedMedicines,
        ]);
    }

    // Kategori
    public function byCategory($kategori)
    {
        // Hanya tampilkan produk OTC di halaman kategori publik
        $medicines = Medicine::where('kategori', $kategori)
                            ->where('is_resep', false)
                            ->paginate(12);
        $allCategories = Medicine::where('is_resep', false)
                                ->distinct()
                                ->pluck('kategori');

        return view('medicines.category', [
            'medicines' => $medicines,
            'kategori' => $kategori,
            'allCategories' => $allCategories,
        ]);
    }

    // Farmakologi page removed; legacy template was not used in active routes.
}
