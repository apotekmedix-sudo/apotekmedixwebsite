<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Medicine;
use App\Models\PromoProduct;
use App\Models\ShoppingAccessCode;
use App\Support\StockAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    // Halaman utama
    public function index(Request $request)
    {
        $b2cAccess = ShoppingAccessCode::hasActiveB2cSession();
        $internalAccess = ShoppingAccessCode::hasActiveInternalSession();
        $redLogoAccess = $b2cAccess || $internalAccess;
        $b2cCatalogQuery = fn () => Medicine::nonPbf()
            ->availableForB2c();

        $featuredProducts = $b2cCatalogQuery()
            ->where('grade', 'A')
            ->where('terjual', '>=', 10)
            ->orderByDesc('terjual')
            ->orderBy('nama_obat')
            ->limit(20)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $b2cCatalogQuery()
                ->where('terjual', '>=', 10)
                ->orderByDesc('terjual')
                ->orderBy('nama_obat')
                ->limit(20)
                ->get();
        }

        $totalProducts = $b2cCatalogQuery()->count();

        // Hitung total produk per kategori produk
        $categoryColumn = Schema::hasColumn('medicines', 'kategori_produk') ? 'kategori_produk' : 'kategori';

        $kategoryCounts = $b2cCatalogQuery()
            ->selectRaw($categoryColumn . ', COUNT(*) as total')
            ->whereNotNull($categoryColumn)
            ->groupBy($categoryColumn)
            ->pluck('total', $categoryColumn);

        // Promo produk aktif & Banner aktif
        $promoProducts = Schema::hasTable('promo_products') ? PromoProduct::aktif()->get() : collect();
        $banners = Schema::hasTable('banners') ? Banner::aktif()->get() : collect();

        $discountedProducts = $b2cCatalogQuery()
            ->where('promo_b2c_percent', '>', 0)
            ->orderByDesc('promo_b2c_percent')
            ->orderByDesc('terjual')
            ->limit(20)
            ->get();

        $allB2cProducts = $b2cCatalogQuery()
            ->latest()
            ->get();

        StockAvailability::annotate(
            $featuredProducts
                ->concat($discountedProducts)
                ->concat($allB2cProducts)
                ->unique('id')
        );

        return view('home', compact(
            'featuredProducts',
            'kategoryCounts',
            'totalProducts',
            'promoProducts',
            'discountedProducts',
            'allB2cProducts',
            'banners',
            'b2cAccess',
            'internalAccess',
            'redLogoAccess'
        ));
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
    public function show(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $b2cAccess = ShoppingAccessCode::hasActiveB2cSession();
        $internalAccess = ShoppingAccessCode::hasActiveInternalSession();
        $redLogoAccess = $b2cAccess || $internalAccess;

        StockAvailability::annotate($medicine->newCollection([$medicine]));

        // Related products dari kategori yang sama
        $relatedMedicines = Medicine::nonPbf()
            ->availableForB2c()
            ->where('kategori', $medicine->kategori)
            ->where('id', '!=', $medicine->id)
            ->limit(4)
            ->get();

        StockAvailability::annotate($relatedMedicines);

        return view('medicines.detail', [
            'medicine' => $medicine,
            'relatedMedicines' => $relatedMedicines,
            'b2cAccess' => $redLogoAccess,
            'internalAccess' => $internalAccess,
            'redLogoAccess' => $redLogoAccess,
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
}
