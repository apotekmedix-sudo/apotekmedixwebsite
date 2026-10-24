<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Constants\Companies;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminProdukController extends Controller
{
    public function index(Request $request)
    {
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $pabrik          = $request->get('pabrik', '');
        $kelompok        = $request->get('kelompok', '');
        $mode            = $request->get('mode', '');
        $indikasi        = $request->get('indikasi', '');
        $komposisi       = $request->get('komposisi', '');
        $sediaan         = $request->get('sediaan', '');
        $dosis           = $request->get('dosis', '');
        $golongan        = $request->get('golongan', '');

        $query = Medicine::latest();
        if ($kelompok) {
            $query->where('kelompok', $kelompok);
        }
        if ($mode === 'pbf') {
            $query->where('harga_modal', '>', 0);
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

        if ($pabrik) {
            $query->where('kategori', $pabrik);
        }

        $medicines       = $query->paginate(15)->withQueryString();
        $total           = (clone $query)->reorder()->count();
        $kategoriOptions = Companies::LIST;
        $attrOptions     = Medicine::getAttributeFilterOptions();
        $sediaanList     = $attrOptions['sediaanList'];
        $golonganList    = $attrOptions['golonganList'];

        return view('admin.produk.index', compact(
            'medicines', 'search', 'kategori_produk', 'pabrik', 'kelompok', 'mode',
            'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan',
            'sediaanList', 'golonganList', 'total', 'kategoriOptions'
        ));
    }

    public function indexPbf(Request $request)
    {
        $search    = $request->get('search', '');
        $indikasi  = $request->get('indikasi', '');
        $komposisi = $request->get('komposisi', '');
        $sediaan   = $request->get('sediaan', '');
        $dosis     = $request->get('dosis', '');
        $golongan  = $request->get('golongan', '');

        $query = Medicine::where('harga_modal', '>', 0)->latest();

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

        $attrOptions = Medicine::getAttributeFilterOptions();

        return view('admin.products-ordering', [
            'medicines'    => $query->paginate(15)->withQueryString(),
            'search'       => $search,
            'indikasi'     => $indikasi,
            'komposisi'    => $komposisi,
            'sediaan'      => $sediaan,
            'dosis'        => $dosis,
            'golongan'     => $golongan,
            'sediaanList'  => $attrOptions['sediaanList'],
            'golonganList' => $attrOptions['golonganList'],
            'total'        => Medicine::where('harga_modal', '>', 0)->count(),
        ]);
    }

    public function export(Request $request)
    {
        $products = Medicine::when($request->get('mode') === 'pbf', fn ($query) => $query->where('harga_modal', '>', 0))
            ->orderBy('nama_obat')->get();
        $columns = [
            'NO', 'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'GOLONGAN', 'KOMPOSISI', 'INDIKASI',
            'SATUAN', 'KEMASAN', 'SEDIAAN', 'STOK SATUAN', 'PER STRIP', 'PER TABLET',
            'MODAL', 'HARGA JUAL INTERNAL', 'HARGA JUAL B2B', 'HARGA JUAL B2C',
            'HARGA JUAL PER STRIP', 'HARGA JUAL PER TABLET',
        ];
        $rows = $products->values()->map(fn ($product, $index) => [
            $index + 1,
            $product->kategori ?: '',
            $product->nama_obat,
            $product->logo ?: '',
            $product->golongan ?: '',
            $product->komposisi ?: '',
            $product->indikasi ?: '',
            $product->satuan ?: '',
            $product->kemasan ?: '',
            $product->sediaan ?: '',
            $product->stock_packages,
            $product->unit_packaging_enabled ? $product->strips_per_box : 0,
            $product->unit_packaging_enabled ? $product->units_per_box : 0,
            $product->harga_modal,
            $product->getChannelBasePrice('internal'),
            $product->getChannelBasePrice('b2b'),
            $product->getChannelBasePrice('b2c'),
            $product->price_strip ?: 0,
            $product->price_tablet ?: 0,
        ])->all();

        return \App\Helpers\XlsxWriter::download('produk.xlsx', $columns, $rows, [8, 25, 30, 12, 18, 28, 28, 12, 22, 12, 12, 12, 12, 15, 20, 18, 18, 20, 20]);
    }

    public function create()
    {
        return view('admin.produk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat'       => ['required', 'string', 'max:255'],
            'kategori'        => ['required', 'string', 'max:255'],
            'logo'            => ['nullable', 'string', 'max:255'],
            'golongan'        => ['nullable', 'string', 'max:255'],
            'satuan'          => ['nullable', 'string', 'max:100'],
            'kemasan'         => ['nullable', 'string', 'max:255'],
            'gambar'          => ['nullable', 'array', 'max:2'],
            'gambar.*'        => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'harga_internal'  => ['required_without:harga', 'nullable', 'numeric', 'min:0'],
            'harga'           => ['required_without:harga_internal', 'nullable', 'numeric', 'min:0'],
            'harga_modal'     => ['nullable', 'numeric', 'min:0'],
            'harga_grosir'    => ['nullable', 'numeric', 'min:0'],
            'harga_retail'    => ['nullable', 'numeric', 'min:0'],
            'stok'            => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'         => ['nullable', 'string', 'max:255'],
            'strips_per_box' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'tablets_per_box' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'price_tablet' => ['nullable', 'numeric', 'min:0'],
            'price_strip' => ['nullable', 'numeric', 'min:0'],
            'komposisi'       => ['nullable', 'string'],
            'indikasi'        => ['nullable', 'string'],
            'dosis'           => ['nullable', 'string'],
        ]);

        $this->validateTabletPackaging($request, $validated);
        if (!empty(trim($validated['deskripsi'] ?? ''))) {
            $validated['deskripsi'] = trim($validated['deskripsi']);
        } else {
            $parts = array_filter([
                trim((string) ($validated['komposisi'] ?? '')),
                trim((string) ($validated['indikasi'] ?? '')),
                trim((string) ($validated['dosis'] ?? '')),
            ]);
            $validated['deskripsi'] = implode(' | ', $parts);
        }

        if (!Schema::hasColumn('medicines', 'dosis')) {
            unset($validated['dosis']);
        }

        $validated['kategori_produk'] = 'OBAT';
        $validated['harga_internal'] = $validated['harga_internal'] ?? $validated['harga'];
        $validated['harga'] = $validated['harga_internal'];
        $validated['harga_retail'] = $validated['harga_retail'] ?? $validated['harga_internal'];
        $this->applyPackagingFields($request, $validated);
        $images = $request->file('gambar', []);
        $imagePaths = array_map(
            fn ($image) => ImageHelper::storeProductImage($image),
            $images
        );
        $validated['gambar'] = array_shift($imagePaths) ?: null;
        $validated['gambar_tambahan'] = array_values($imagePaths);
        $medicine = new Medicine($validated);
        $medicine->setStockInPackages((float) $validated['stok']);
        $medicine->save();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Medicine $produk)
    {
        return view('admin.produk.edit', ['medicine' => $produk]);
    }

    public function update(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'nama_obat'       => ['required', 'string', 'max:255'],
            'kategori'        => ['required', 'string', 'max:255'],
            'logo'            => ['nullable', 'string', 'max:255'],
            'golongan'        => ['nullable', 'string', 'max:255'],
            'satuan'          => ['nullable', 'string', 'max:100'],
            'kemasan'         => ['nullable', 'string', 'max:255'],
            'gambar'          => ['nullable', 'array', 'max:2'],
            'gambar.*'        => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_gambar'    => ['nullable', 'array'],
            'hapus_gambar.*'  => ['required', 'string', 'max:255'],
            'harga_internal'  => ['required_without:harga', 'nullable', 'numeric', 'min:0'],
            'harga'           => ['required_without:harga_internal', 'nullable', 'numeric', 'min:0'],
            'harga_modal'     => ['nullable', 'numeric', 'min:0'],
            'harga_grosir'    => ['nullable', 'numeric', 'min:0'],
            'harga_retail'    => ['nullable', 'numeric', 'min:0'],
            'stok'            => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'         => ['nullable', 'string', 'max:255'],
            'strips_per_box' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'tablets_per_box' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'price_tablet' => ['nullable', 'numeric', 'min:0'],
            'price_strip' => ['nullable', 'numeric', 'min:0'],
            'komposisi'       => ['nullable', 'string'],
            'indikasi'        => ['nullable', 'string'],
            'dosis'           => ['nullable', 'string'],
        ]);

        $this->validateTabletPackaging($request, $validated);
        $validated['harga_internal'] = $validated['harga_internal'] ?? $validated['harga'];
        $validated['harga'] = $validated['harga_internal'];
        $validated['harga_retail'] = $validated['harga_retail'] ?? $validated['harga_internal'];
        $this->applyPackagingFields($request, $validated);
        $images = $request->file('gambar', []);
        unset($validated['gambar']);
        $currentGallery = $produk->image_gallery;
        $imagesToDelete = array_values(array_unique($validated['hapus_gambar'] ?? []));
        unset($validated['hapus_gambar']);

        if (array_diff($imagesToDelete, $currentGallery) !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hapus_gambar' => 'Foto yang akan dihapus tidak cocok dengan galeri produk ini. Muat ulang halaman dan coba lagi.',
            ]);
        }

        $remainingGallery = array_values(array_diff($currentGallery, $imagesToDelete));
        if (count($remainingGallery) + count($images) > 2) {
            return back()->withErrors([
                'gambar' => 'Galeri produk maksimal 2 foto. Hapus foto lama atau pilih lebih sedikit foto baru.',
            ])->withInput();
        }

        if ($imagesToDelete !== [] || $images !== []) {
            $newPaths = array_map(
                fn ($image) => ImageHelper::storeProductImage($image),
                $images
            );
            $updatedGallery = array_values(array_merge($remainingGallery, $newPaths));
            $validated['gambar'] = array_shift($updatedGallery) ?: null;
            $validated['gambar_tambahan'] = $updatedGallery;
        }

        if (!empty(trim($validated['deskripsi'] ?? ''))) {
            $validated['deskripsi'] = trim($validated['deskripsi']);
        } else {
            $parts = array_filter([
                trim((string) ($validated['komposisi'] ?? '')),
                trim((string) ($validated['indikasi'] ?? '')),
                trim((string) ($validated['dosis'] ?? '')),
            ]);
            $validated['deskripsi'] = implode(' | ', $parts);
        }

        if (!Schema::hasColumn('medicines', 'dosis')) {
            unset($validated['dosis']);
        }

        $stockPackages = (float) $validated['stok'];
        unset($validated['stok']);
        $produk->fill($validated);
        $produk->setStockInPackages($stockPackages);
        $produk->save();

        foreach ($imagesToDelete as $image) {
            ImageHelper::deleteProductImage($image);
        }

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.produk.index', $queryParams)
                         ->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy(Request $request, Medicine $produk)
    {
        foreach ($produk->image_gallery as $image) {
            ImageHelper::deleteProductImage($image);
        }
        $produk->delete();

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.produk.index', $queryParams)
                         ->with('success', 'Produk berhasil dihapus!');
    }

    public function destroyMany(Request $request)
    {
        $selectedIds = $request->input('produk_ids', []);

        if (empty($selectedIds) || !is_array($selectedIds)) {
            return redirect()->route('admin.produk.index', $this->buildIndexQueryParams($request))
                             ->with('error', 'Pilih minimal satu produk untuk dihapus.');
        }

        $selectedIds = array_filter(array_map('intval', $selectedIds));

        if (empty($selectedIds)) {
            return redirect()->route('admin.produk.index', $this->buildIndexQueryParams($request))
                             ->with('error', 'Pilih minimal satu produk untuk dihapus.');
        }

        $products = Medicine::whereIn('id', $selectedIds)->get();

        foreach ($products as $produk) {
            foreach ($produk->image_gallery as $image) {
                ImageHelper::deleteProductImage($image);
            }
            $produk->delete();
        }

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.produk.index', $queryParams)
                         ->with('success', 'Sebanyak ' . count($products) . ' produk berhasil dihapus!');
    }

    private function buildIndexQueryParams(Request $request): array
    {
        $params = [];

        foreach (['search', 'kategori_produk', 'pabrik', 'kelompok', 'mode', 'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan', 'page'] as $field) {
            $value = $request->query($field, $request->input($field));
            if ($value !== null && $value !== '') {
                $params[$field] = $value;
            }
        }

        return $params;
    }

    public function updateStock(Request $request, Medicine $produk)
    {
        $validated = $request->validate(['stok' => ['required', 'numeric', 'min:0', 'max:10000000']]);
        $produk->setStockInPackages((float) $validated['stok']);
        $produk->save();
        return back()->with('success', 'Stok berhasil diupdate!');
    }

    public function updateInline(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'harga_modal'  => ['required', 'numeric', 'min:0'],
            'harga_internal' => ['required', 'numeric', 'min:0'],
            'harga_grosir' => ['nullable', 'numeric', 'min:0'],
            'harga_retail' => ['required', 'numeric', 'min:0'],
            'stok'         => ['required', 'numeric', 'min:0', 'max:10000000'],
        ]);

        $stockPackages = (float) $validated['stok'];
        unset($validated['stok']);
        $validated['harga_grosir'] = $validated['harga_grosir'] ?? $produk->harga_grosir;
        if ($produk->unit_packaging_enabled) {
            $validated['price_box'] = $validated['harga_retail'];
        }
        $produk->fill($validated);
        $produk->setStockInPackages($stockPackages);
        $produk->save();

        return back()->with('success', 'Harga dan stok berhasil diupdate!');
    }

    private function applyPackagingFields(Request $request, array &$validated): void
    {
        $strips = (int) ($validated['strips_per_box'] ?? 0);
        $tablets = (int) ($request->input('tablets_per_box') ?? 0);
        $enabled = $strips > 0 && $tablets > 0;
        $validated['unit_packaging_enabled'] = $enabled;

        if ($enabled) {
            $validated['tablets_per_strip'] = intdiv($tablets, $strips);
            $validated['price_box'] = $validated['harga_retail'] ?? null;
            return;
        }

        foreach (['tablets_per_strip', 'strips_per_box', 'price_tablet', 'price_strip', 'price_box'] as $field) {
            $validated[$field] = null;
        }
    }

    private function validateTabletPackaging(Request $request, array $validated): void
    {
        $strips = (int) ($validated['strips_per_box'] ?? 0);
        $tablets = (int) $request->input('tablets_per_box', 0);
        if ($strips === 0 && $tablets === 0) {
            return;
        }

        if ($strips < 1 || $tablets < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tablets_per_box' => 'Isi jumlah strip dan tablet per kemasan dengan benar, atau kosongkan keduanya.',
            ]);
        }

        if ($strips < 1 || $tablets % $strips !== 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tablets_per_box' => 'Jumlah tablet per kemasan harus habis dibagi jumlah strip.',
            ]);
        }
    }

    public function show(Medicine $produk)
    {
        return redirect()->route('admin.produk.index');
    }
}
