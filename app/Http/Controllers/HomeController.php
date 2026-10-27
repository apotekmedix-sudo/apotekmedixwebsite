<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Medicine;
use App\Models\PromoProduct;
use App\Models\ShoppingAccessCode;
use App\Support\HomepageProductCategories;
use App\Support\StockAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    // Halaman utama
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'kategori_pilihan' => ['nullable', 'string', 'in:' . implode(',', array_keys(HomepageProductCategories::options()))],
            'perusahaan' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:terbaru,harga_asc,harga_desc,nama'],
        ]);
        $search = trim((string) ($validated['search'] ?? ''));
        $kategoriPilihan = (string) ($validated['kategori_pilihan'] ?? '');
        $kategoriPilihanOptions = HomepageProductCategories::options();
        $perusahaan = (string) ($validated['perusahaan'] ?? '');
        $sort = $validated['sort'] ?? 'terbaru';
        $homeHasFilters = $search !== '' || $kategoriPilihan !== '' || $perusahaan !== '' || $sort !== 'terbaru';
        $b2cAccess = ShoppingAccessCode::hasActiveB2cSession();
        $internalAccess = ShoppingAccessCode::hasActiveInternalSession();
        $redLogoAccess = $b2cAccess || $internalAccess;
        $b2cCatalogQuery = fn () => Medicine::nonPbf()
            ->availableForB2c();

        $featuredQuery = $b2cCatalogQuery()
            ->where('grade', 'A')
            ->where('terjual', '>=', 10);
        StockAvailability::prioritizeAvailableStock($featuredQuery);
        $featuredProducts = $featuredQuery
            ->orderByDesc('terjual')
            ->orderBy('nama_obat')
            ->limit(20)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredQuery = $b2cCatalogQuery()
                ->where('terjual', '>=', 10);
            StockAvailability::prioritizeAvailableStock($featuredQuery);
            $featuredProducts = $featuredQuery
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

        $discountedQuery = $b2cCatalogQuery()
            ->where('promo_b2c_percent', '>', 0);
        StockAvailability::prioritizeAvailableStock($discountedQuery);
        $discountedProducts = $discountedQuery
            ->orderByDesc('promo_b2c_percent')
            ->orderByDesc('terjual')
            ->limit(20)
            ->get();

        $allB2cQuery = $b2cCatalogQuery();
        if ($search !== '') {
            $allB2cQuery->search($search);
        }
        HomepageProductCategories::apply($allB2cQuery, $kategoriPilihan);
        if ($perusahaan !== '') {
            $allB2cQuery->where('kategori', $perusahaan);
        }

        StockAvailability::prioritizeAvailableStock($allB2cQuery);

        match ($sort) {
            'harga_asc' => $allB2cQuery->orderByRaw('COALESCE(NULLIF(harga_retail, 0), NULLIF(price_box, 0), harga) asc'),
            'harga_desc' => $allB2cQuery->orderByRaw('COALESCE(NULLIF(harga_retail, 0), NULLIF(price_box, 0), harga) desc'),
            'nama' => $allB2cQuery->orderBy('nama_obat'),
            default => $allB2cQuery->latest(),
        };
        $allB2cProducts = $allB2cQuery->get();
        $companyOptions = $b2cCatalogQuery()
            ->select('kategori')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

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
            'search',
            'kategoriPilihan',
            'kategoriPilihanOptions',
            'perusahaan',
            'companyOptions',
            'sort',
            'homeHasFilters',
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
        $detailOrigin = $request->query('from');
        if (!in_array($detailOrigin, ['home', 'b2b', 'products', 'apotek'], true)) {
            $detailOrigin = 'products';
        }

        $b2cAccess = ShoppingAccessCode::hasActiveB2cSession();
        $internalAccess = ShoppingAccessCode::hasActiveInternalSession();
        $b2bAccess = $detailOrigin === 'b2b' && ShoppingAccessCode::hasActiveB2bSession();
        $redLogoAccess = $b2cAccess || $internalAccess;

        StockAvailability::annotate($medicine->newCollection([$medicine]));

        $relatedQuery = $b2bAccess
            ? Medicine::query()->where('harga_grosir', '>', 0)
            : Medicine::nonPbf()->availableForB2c();
        $relatedMedicines = StockAvailability::prioritizeAvailableStock($relatedQuery)
            ->where('kategori', $medicine->kategori)
            ->where('id', '!=', $medicine->id)
            ->limit(4)
            ->get();

        StockAvailability::annotate($relatedMedicines);

        return view('medicines.detail', [
            'medicine' => $medicine,
            'relatedMedicines' => $relatedMedicines,
            'detailOrigin' => $detailOrigin,
            'b2bAccess' => $b2bAccess,
            'b2cAccess' => $redLogoAccess,
            'internalAccess' => $internalAccess,
            'redLogoAccess' => $redLogoAccess,
        ]);
    }

    // Kategori
    public function byCategory($kategori)
    {
        // Hanya tampilkan produk OTC di halaman kategori publik
        $categoryQuery = Medicine::where('kategori', $kategori)
            ->where('is_resep', false);
        $medicines = StockAvailability::prioritizeAvailableStock($categoryQuery)
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
