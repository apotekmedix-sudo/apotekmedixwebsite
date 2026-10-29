<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\ShoppingAccessCode;
use App\Constants\Companies;
use App\Support\HomepageProductCategories;
use App\Support\StockAvailability;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Redirect legacy B2C catalog links to the homepage search and shopping flow.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'kategori_pilihan', 'perusahaan', 'sort']);
        if (empty($filters['kategori_pilihan'])) {
            $legacyCategory = strtoupper(trim((string) $request->query('kategori_produk', '')));
            $filters['kategori_pilihan'] = match ($legacyCategory) {
                'VITAMIN & SUPLEMEN' => 'vitamin-suplemen',
                'ALAT KESEHATAN' => 'alat-kesehatan',
                'OBAT' => 'otc',
                default => null,
            };
        }

        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');
        $url = route('home', $filters) . '#homeSearchSection';

        return redirect()->to($url);
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
        $similarityConditions = array_filter([
            'indikasi' => trim((string) $medicine->indikasi),
            'komposisi' => trim((string) $medicine->komposisi),
        ], static fn (string $value): bool => $value !== '');

        $relatedMedicines = collect();
        if ($similarityConditions !== []) {
            $relatedQuery = Medicine::nonPbf()
                ->availableForB2c()
                ->where('id', '!=', $medicine->id)
                ->where(function ($query) use ($similarityConditions): void {
                    foreach ($similarityConditions as $attribute => $value) {
                        $query->orWhere($attribute, $value);
                    }
                });
            $relatedMedicines = StockAvailability::prioritizeAvailableStock($relatedQuery)
                ->limit(4)
                ->get();
        }
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
        $kategoriPilihan = (string) $request->get('kategori_pilihan', '');
        $perusahaan      = $request->get('perusahaan', '');
        $indikasi        = $request->get('indikasi', '');
        $komposisi       = $request->get('komposisi', '');
        $sediaan         = $request->get('sediaan', '');
        $dosis           = $request->get('dosis', '');
        $golongan        = $request->get('golongan', '');
        $sort            = $request->get('sort', 'terbaru');

        $promoQuery = Medicine::where('harga_grosir', '>', 0)
            ->where('promo_b2b_percent', '>', 0);
        if ($search !== '') {
            $promoQuery->search($search);
        }
        HomepageProductCategories::apply($promoQuery, $kategoriPilihan);
        $promoQuery->filterByAttributes([
            'indikasi'  => $indikasi,
            'komposisi' => $komposisi,
            'sediaan'   => $sediaan,
            'dosis'     => $dosis,
            'golongan'  => $golongan,
        ]);
        if ($perusahaan) {
            $promoQuery->where('kategori', $perusahaan);
        }
        StockAvailability::prioritizeAvailableStock($promoQuery);
        $promoProducts = $promoQuery
            ->orderByDesc('promo_b2b_percent')
            ->orderByDesc('terjual')
            ->limit(8)
            ->get();
        StockAvailability::annotate($promoProducts);

        $query = Medicine::where('harga_grosir', '>', 0);

        if ($search !== '') {
            $query->search($search);
        }

        HomepageProductCategories::apply($query, $kategoriPilihan);

        $query->filterByAttributes([
            'indikasi'  => $indikasi,
            'komposisi' => $komposisi,
            'sediaan'   => $sediaan,
            'dosis'     => $dosis,
            'golongan'  => $golongan,
        ]);

        if ($perusahaan) {
            $query->where('kategori', $perusahaan);
        }

        StockAvailability::prioritizeAvailableStock($query);

        match ($sort) {
            'harga_asc'  => $query->orderBy('harga_grosir', 'asc'),
            'harga_desc' => $query->orderBy('harga_grosir', 'desc'),
            'nama'       => $query->orderBy('nama_obat', 'asc'),
            default      => $query->latest(),
        };

        $medicines       = $query->paginate(12)->withQueryString();
        StockAvailability::annotate($medicines->getCollection());
        $total           = Medicine::where('harga_grosir', '>', 0)->count();
        $kategoriPilihanOptions = HomepageProductCategories::options();
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
            'medicines', 'promoProducts', 'search', 'kategoriPilihan', 'kategoriPilihanOptions', 'perusahaan',
            'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan',
            'sediaanList', 'golonganList',
            'sort', 'total', 'perusahaanList'
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
        $internalAccess = $this->hasInternalAccess($request);
        $redLogoAccess = $b2cAccess || $internalAccess;
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

        StockAvailability::prioritizeAvailableStock($query);

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
            'sort', 'total', 'kategoriOptions', 'perusahaanList',
            'b2cAccess', 'internalAccess', 'redLogoAccess'
        ));
    }

    public function apotekActivate(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'redirect_to' => ['nullable', 'in:home,products.apotek'],
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
                ->route($this->catalogReturnRoute($validated['redirect_to'] ?? null))
                ->withErrors(['kode' => 'Kode akses tidak sesuai atau belum diaktifkan. Hubungi administrator.'])
                ->with('access_dialog', 'b2c')
                ->withInput();
        }

        $request->session()->regenerate();
        $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
        if ($accessCode->expires_at->lt($sessionExpiresAt)) {
            $sessionExpiresAt = $accessCode->expires_at;
        }
        $request->session()->put('b2c_access_expires_at', $sessionExpiresAt->timestamp);
        $request->session()->put('b2c_access_code_hash', $accessCode->code_hash);

        return redirect()->route($this->catalogReturnRoute($validated['redirect_to'] ?? null))
            ->with('success', 'Kode berhasil. Produk berlogo merah sekarang dapat dilihat.');
    }

    public function internalActivate(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'redirect_to' => ['nullable', 'in:home,products.apotek'],
        ]);
        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->first();

        if (!$accessCode || !$accessCode->verify($validated['kode'])) {
            return redirect()
                ->route($this->catalogReturnRoute($validated['redirect_to'] ?? null))
                ->withErrors(['kode' => 'Kode akses Internal tidak sesuai atau belum diaktifkan. Hubungi administrator.'])
                ->with('access_dialog', 'internal')
                ->withInput();
        }

        $request->session()->regenerate();
        $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
        if ($accessCode->expires_at->lt($sessionExpiresAt)) {
            $sessionExpiresAt = $accessCode->expires_at;
        }
        $request->session()->put('internal_access_expires_at', $sessionExpiresAt->timestamp);
        $request->session()->put('internal_access_code_hash', $accessCode->code_hash);

        return redirect()->route($this->catalogReturnRoute($validated['redirect_to'] ?? null))
            ->with('success', 'Harga internal dan akses produk berlogo merah berhasil dibuka.');
    }

    public function apotekAccessLogout(Request $request, string $channel)
    {
        abort_unless(in_array($channel, ['b2c', 'internal'], true), 404);
        $sessionKeys = $channel === 'b2c'
            ? ['b2c_access_expires_at', 'b2c_access_code_hash']
            : ['internal_access_expires_at', 'internal_access_code_hash'];
        $request->session()->forget($sessionKeys);

        return redirect()->route($this->catalogReturnRoute($request->input('redirect_to')))
            ->with('success', $channel === 'internal'
                ? 'Akses harga internal telah ditutup.'
                : 'Akses produk khusus B2C telah ditutup.');
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

    private function hasInternalAccess(Request $request): bool
    {
        return $this->hasShoppingAccess(
            $request,
            ShoppingAccessCode::CHANNEL_INTERNAL,
            'internal_access_expires_at',
            'internal_access_code_hash'
        );
    }

    private function catalogReturnRoute(?string $route): string
    {
        return $route === 'home' ? 'home' : 'products.apotek';
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
            ->get(['id', 'nama_obat', 'kategori', 'brand', 'satuan', 'sediaan', 'gambar', 'gambar_tambahan', 'logo'])
            ->mapWithKeys(function (Medicine $med): array {
                $rawLogo = mb_strtolower(trim((string) $med->logo));
                $logoColor = match ($rawLogo) {
                    'hijau', 'green', 'logo hijau' => 'green',
                    'merah', 'red', 'logo merah', 'k' => 'red',
                    'biru', 'blue', 'logo biru' => 'blue',
                    default => $med->isRedLogo() ? 'red' : null,
                };

                return [$med->id => [
                    'image_url' => $med->image_url,
                    'nama_obat' => $med->nama_obat,
                    'kategori' => $med->brand ?: $med->kategori,
                    'unit_label' => $med->package_unit_label,
                    'sediaan_label' => $med->sediaan_label,
                    'logo_color' => $logoColor,
                ]];
            })
            ->all();

        return view('checkout', compact('defaultShippingAddress', 'productCatalog'));
    }
}
