<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\ShoppingAccessCode;
use App\Constants\Companies;
use App\Support\StockAvailability;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Halaman katalog produk.
     */
    public function index(Request $request)
    {
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $kategoriPilihan = $request->get('kategori_pilihan', '');
        $perusahaan      = $request->get('perusahaan', '');
        $indikasi        = $request->get('indikasi', '');
        $komposisi       = $request->get('komposisi', '');
        $sediaan         = $request->get('sediaan', '');
        $dosis           = $request->get('dosis', '');
        $golongan        = $request->get('golongan', '');
        $sort            = $request->get('sort', 'terbaru');
        $catalogQuery = Medicine::nonPbf();
        $query = clone $catalogQuery;

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

        if (isset($categoryFilters[$kategoriPilihan])) {
            $filter = $categoryFilters[$kategoriPilihan];

            if (($filter['type'] ?? null) === 'prescription') {
                $query->where('is_resep', true);
            } elseif (($filter['type'] ?? null) === 'otc') {
                $query->where('is_resep', false);
            } elseif (($filter['type'] ?? null) === 'medical-equipment') {
                $query->where('kategori_produk', 'ALAT KESEHATAN');
            } else {
                $query->where(function ($categoryQuery) use ($filter) {
                    foreach ($filter['terms'] as $term) {
                        $categoryQuery->orWhere(function ($termQuery) use ($term) {
                            foreach (['nama_obat', 'kategori', 'deskripsi', 'indikasi', 'komposisi'] as $column) {
                                $termQuery->orWhere($column, 'like', '%' . $term . '%');
                            }
                        });
                    }
                });
            }
        }

        if ($search !== '') {
            $query->search($search);
        }

        $query->filterByAttributes([
            'indikasi'  => $indikasi,
            'komposisi' => $komposisi,
            'sediaan'   => $sediaan,
            'dosis'     => $dosis,
            'golongan'  => $golongan,
        ]);

        if ($kategori_produk) {
            $query->where('kategori_produk', $kategori_produk);
        }

        if ($perusahaan) {
            $query->where('kategori', $perusahaan);
        }

        match ($sort) {
            'harga_asc'  => $query->orderBy('harga', 'asc'),
            'harga_desc' => $query->orderBy('harga', 'desc'),
            'nama'       => $query->orderBy('nama_obat', 'asc'),
            default      => $query->latest(),
        };

        $medicines       = $query->paginate(12)->withQueryString();
        StockAvailability::annotate($medicines->getCollection());
        $total           = (clone $catalogQuery)->count();
        $kategoriOptions = Companies::LIST;
        $perusahaanList  = (clone $catalogQuery)
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');
        $attrOptions     = Medicine::getAttributeFilterOptions();

        return view('products', [
            'medicines'       => $medicines,
            'search'          => $search,
            'kategori_produk' => $kategori_produk,
            'kategoriPilihan' => $kategoriPilihan,
            'perusahaan'      => $perusahaan,
            'indikasi'        => $indikasi,
            'komposisi'       => $komposisi,
            'sediaan'         => $sediaan,
            'dosis'           => $dosis,
            'golongan'        => $golongan,
            'sediaanList'     => $attrOptions['sediaanList'],
            'golonganList'    => $attrOptions['golonganList'],
            'sort'            => $sort,
            'total'           => $total,
            'kategoriOptions' => $kategoriOptions,
            'perusahaanList'  => $perusahaanList,
            'b2cAccess'       => $this->hasB2cAccess($request),
        ]);
    }

    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        if ($request->hasFile('gambar')) {
            \App\Helpers\ImageHelper::deleteProductImage($medicine->gambar);
            $path = \App\Helpers\ImageHelper::storeProductImage($request->file('gambar'));
            $medicine->update(['gambar' => $path]);
        }
        return back()->with('success', 'Foto berhasil diperbarui!');
    }

    /**
     * Halaman show detail produk (public)
     */
    public function show(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        StockAvailability::annotate($medicine->newCollection([$medicine]));
        $relatedQuery = Medicine::nonPbf()
            ->availableForB2c()
            ->where('kategori', $medicine->kategori)
            ->where('id', '!=', $medicine->id);
        $relatedMedicines = $relatedQuery->limit(4)->get();
        StockAvailability::annotate($relatedMedicines);
        return view('medicines.detail', compact('medicine', 'relatedMedicines'));
    }

    /**
     * Halaman Produk PBF (frontend) — dilindungi kode akses
     */
    public function pbf(Request $request)
    {
        if (
            (int) $request->session()->get('pbf_access_expires_at', 0) <= now()->timestamp
            || !ShoppingAccessCode::matchesSession(
                ShoppingAccessCode::CHANNEL_B2B,
                $request->session()->get('pbf_access_code_hash')
            )
        ) {
            $request->session()->forget(['pbf_access_expires_at', 'pbf_access_code_hash']);

            return view('products_pbf_gate');
        }

        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $perusahaan      = $request->get('perusahaan', '');
        $indikasi        = $request->get('indikasi', '');
        $komposisi       = $request->get('komposisi', '');
        $sediaan         = $request->get('sediaan', '');
        $dosis           = $request->get('dosis', '');
        $golongan        = $request->get('golongan', '');
        $sort            = $request->get('sort', 'terbaru');

        $query = Medicine::where('harga_grosir', '>', 0);

        if ($search !== '') {
            $query->search($search);
        }

        $query->filterByAttributes([
            'indikasi'  => $indikasi,
            'komposisi' => $komposisi,
            'sediaan'   => $sediaan,
            'dosis'     => $dosis,
            'golongan'  => $golongan,
        ]);

        if ($kategori_produk) {
            $query->where('kategori_produk', $kategori_produk);
        }

        if ($perusahaan) {
            $query->where('kategori', $perusahaan);
        }

        match ($sort) {
            'harga_asc'  => $query->orderBy('harga_grosir', 'asc'),
            'harga_desc' => $query->orderBy('harga_grosir', 'desc'),
            'nama'       => $query->orderBy('nama_obat', 'asc'),
            default      => $query->latest(),
        };

        $medicines       = $query->paginate(12)->withQueryString();
        StockAvailability::annotate($medicines->getCollection());
        $total           = Medicine::where('harga_grosir', '>', 0)->count();
        $kategoriOptions = Companies::LIST;
        $perusahaanList  = Medicine::where('harga_grosir', '>', 0)
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');
        $attrOptions     = Medicine::getAttributeFilterOptions();
        $sediaanList     = $attrOptions['sediaanList'];
        $golonganList    = $attrOptions['golonganList'];

        return view('products_pbf', compact(
            'medicines', 'search', 'kategori_produk', 'perusahaan',
            'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan',
            'sediaanList', 'golonganList',
            'sort', 'total', 'kategoriOptions', 'perusahaanList'
        ))->with('catalogPrice', 'harga_modal');
    }

    /**
     * Verifikasi kode akses PBF
     */
    public function pbfVerify(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ]);
        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->first();

        if ($accessCode && $accessCode->verify($validated['kode'])) {
            $request->session()->regenerate();
            $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
            if ($accessCode->expires_at->lt($sessionExpiresAt)) {
                $sessionExpiresAt = $accessCode->expires_at;
            }
            $request->session()->put(
                'pbf_access_expires_at',
                $sessionExpiresAt->timestamp
            );
            $request->session()->put('pbf_access_code_hash', $accessCode->code_hash);

            return redirect()->route('products.pbf')
                ->with('pbf_success', 'Akses berhasil. Selamat datang di katalog B2B.');
        }

        return redirect()->route('products.pbf')
            ->withErrors(['kode' => 'Kode akses tidak sesuai atau belum diaktifkan. Hubungi administrator.'])
            ->withInput();
    }

    /**
     * Logout dari sesi akses PBF
     */
    public function pbfLogout(Request $request)
    {
        $request->session()->forget(['pbf_access_expires_at', 'pbf_access_code_hash']);
        $request->session()->regenerate();
        return redirect()->route('products.pbf')
            ->with('pbf_info', 'Sesi akses PBF telah diakhiri.');
    }

    /**
     * Halaman Produk Apotek / Belanja B2C (frontend)
     */
    public function apotek(Request $request)
    {
        $b2cAccess = $this->hasB2cAccess($request);
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $perusahaan      = $request->get('perusahaan', '');
        $indikasi        = $request->get('indikasi', '');
        $komposisi       = $request->get('komposisi', '');
        $sediaan         = $request->get('sediaan', '');
        $dosis           = $request->get('dosis', '');
        $golongan        = $request->get('golongan', '');
        $sort            = $request->get('sort', 'terbaru');

        $catalogQuery = Medicine::nonPbf()->availableForB2c();
        $query = clone $catalogQuery;

        if ($search !== '') {
            $query->search($search);
        }

        $query->filterByAttributes([
            'indikasi'  => $indikasi,
            'komposisi' => $komposisi,
            'sediaan'   => $sediaan,
            'dosis'     => $dosis,
            'golongan'  => $golongan,
        ]);

        if ($kategori_produk) {
            $query->where('kategori_produk', $kategori_produk);
        }

        if ($perusahaan) {
            $query->where('kategori', $perusahaan);
        }

        match ($sort) {
            'harga_asc'  => $query->orderBy('harga', 'asc'),
            'harga_desc' => $query->orderBy('harga', 'desc'),
            'nama'       => $query->orderBy('nama_obat', 'asc'),
            default      => $query->latest(),
        };

        $medicines       = $query->paginate(12)->withQueryString();
        StockAvailability::annotate($medicines->getCollection());
        $total           = (clone $catalogQuery)->count();
        $kategoriOptions = Companies::LIST;
        $perusahaanList  = (clone $catalogQuery)
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');
        $attrOptions     = Medicine::getAttributeFilterOptions();
        $sediaanList     = $attrOptions['sediaanList'];
        $golonganList    = $attrOptions['golonganList'];

        return view('products_apotek', compact(
            'medicines', 'search', 'kategori_produk', 'perusahaan',
            'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan',
            'sediaanList', 'golonganList',
            'sort', 'total', 'kategoriOptions', 'perusahaanList', 'b2cAccess'
        ));
    }

    public function apotekActivate(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ]);
        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2C)->first();

        if (!$accessCode || !$accessCode->verify($validated['kode'])) {
            $b2bCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->first();
            if ($b2bCode && $b2bCode->verify($validated['kode'])) {
                $request->session()->regenerate();
                $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
                if ($b2bCode->expires_at->lt($sessionExpiresAt)) {
                    $sessionExpiresAt = $b2bCode->expires_at;
                }
                $request->session()->put('pbf_access_expires_at', $sessionExpiresAt->timestamp);
                $request->session()->put('pbf_access_code_hash', $b2bCode->code_hash);

                return redirect()->route('products.pbf')
                    ->with('pbf_success', 'Akses berhasil. Selamat datang di katalog B2B.');
            }

            return redirect()
                ->route('products.apotek')
                ->withErrors(['kode' => 'Kode akses tidak sesuai atau belum diaktifkan. Hubungi administrator.'])
                ->withInput();
        }

        $request->session()->regenerate();
        $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
        if ($accessCode->expires_at->lt($sessionExpiresAt)) {
            $sessionExpiresAt = $accessCode->expires_at;
        }
        $request->session()->put('b2c_access_expires_at', $sessionExpiresAt->timestamp);
        $request->session()->put('b2c_access_code_hash', $accessCode->code_hash);

        return redirect()->route('products.apotek')
            ->with('success', 'Kode berhasil. Produk berlogo merah sekarang dapat dilihat.');
    }

    private function hasB2cAccess(Request $request): bool
    {
        return $this->hasShoppingAccess(
            $request,
            ShoppingAccessCode::CHANNEL_B2C,
            'b2c_access_expires_at',
            'b2c_access_code_hash'
        );
    }

    private function hasShoppingAccess(
        Request $request,
        string $channel,
        string $expiresAtKey,
        string $codeHashKey
    ): bool {
        $expiresAt = (int) $request->session()->get($expiresAtKey, 0);
        $codeHash = $request->session()->get($codeHashKey);
        if (
            $expiresAt <= now()->timestamp
            || !ShoppingAccessCode::matchesSession($channel, $codeHash)
        ) {
            $request->session()->forget([$expiresAtKey, $codeHashKey]);

            return false;
        }

        return true;
    }

    public function stockAvailability(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:medicines,id'],
        ]);
        $medicines = Medicine::whereIn('id', $validated['ids'])->get();
        $availableUnits = StockAvailability::unitsByMedicineId($medicines);
        $stock = $medicines->mapWithKeys(fn (Medicine $medicine) => [
            $medicine->id => [
                'units' => $availableUnits[$medicine->id],
                'breakdown' => $medicine->getStockBreakdownForUnits($availableUnits[$medicine->id]),
                'converted_summary' => $medicine->getConvertedStockSummaryForUnits($availableUnits[$medicine->id]),
                'unit_stocks' => $medicine->getConvertedUnitStocksForUnits($availableUnits[$medicine->id]),
                'terjual' => (int) $medicine->terjual,
            ],
        ]);

        return response()->json(['stock' => $stock]);
    }

    /**
     * Halaman Keranjang Belanja (Full Page)
     */
    public function cart(Request $request)
    {
        $productCatalog = Medicine::query()
            ->get(['id', 'nama_obat', 'kategori', 'brand', 'satuan', 'sediaan', 'gambar', 'gambar_tambahan'])
            ->mapWithKeys(fn (Medicine $med) => [
                $med->id => [
                    'image_url' => $med->image_url,
                    'nama_obat' => $med->nama_obat,
                    'kategori' => $med->brand ?: $med->kategori,
                    'unit_label' => $med->package_unit_label,
                    'sediaan_label' => $med->sediaan_label,
                ],
            ])
            ->all();

        return view('cart', compact('productCatalog'));
    }

    /**
     * Halaman Checkout / Transaksi (Full Page)
     */
    public function checkout(Request $request)
    {
        $defaultShippingAddress = auth()->check()
            && auth()->user()->isUser()
            && auth()->user()->is_default_address
            ? auth()->user()
            : null;

        $productCatalog = Medicine::query()
            ->get(['id', 'nama_obat', 'kategori', 'brand', 'satuan', 'sediaan', 'gambar', 'gambar_tambahan'])
            ->mapWithKeys(fn (Medicine $med) => [
                $med->id => [
                    'image_url' => $med->image_url,
                    'nama_obat' => $med->nama_obat,
                    'kategori' => $med->brand ?: $med->kategori,
                    'unit_label' => $med->package_unit_label,
                    'sediaan_label' => $med->sediaan_label,
                ],
            ])
            ->all();

        return view('checkout', compact('defaultShippingAddress', 'productCatalog'));
    }
}
