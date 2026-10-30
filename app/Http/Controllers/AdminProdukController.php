<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\PurchaseStockReservation;
use App\Constants\Companies;
use App\Helpers\ImageHelper;
use App\Support\StockAvailability;
use App\Support\HomepageProductCategories;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\PbfSupplier;
use App\Models\SupplierProductOffer;
use App\Support\CsvSpreadsheet;
use RuntimeException;

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
        StockAvailability::annotate($medicines->getCollection());
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
        $supplierId = $request->integer('supplier_id') ?: null;
        $indikasi  = $request->get('indikasi', '');
        $komposisi = $request->get('komposisi', '');
        $sediaan   = $request->get('sediaan', '');
        $dosis     = $request->get('dosis', '');
        $golongan  = $request->get('golongan', '');

        $suppliers = Schema::hasTable('pbf_suppliers')
            ? PbfSupplier::orderBy('name')->get(['id', 'name', 'address', 'phone'])
            : collect();
        $selectedSupplier = $supplierId
            ? $suppliers->firstWhere('id', $supplierId)
            : null;
        abort_if($supplierId && !$selectedSupplier, 404);

        $query = SupplierProductOffer::query()
            ->with(['medicine', 'supplier'])
            ->where('is_active', true)
            ->whereHas('medicine', function ($medicineQuery) use ($search, $indikasi, $komposisi, $sediaan, $dosis, $golongan): void {
                if ($search !== '') {
                    $medicineQuery->search($search);
                }
                $medicineQuery->filterByAttributes([
                    'indikasi'  => $indikasi,
                    'komposisi' => $komposisi,
                    'sediaan'   => $sediaan,
                    'dosis'     => $dosis,
                    'golongan'  => $golongan,
                ]);
            });
        if ($selectedSupplier) {
            $query->where('supplier_id', $selectedSupplier->id);
        }
        $attrOptions = Medicine::getAttributeFilterOptions();
        $productOffers = $query->orderBy('medicine_id')
            ->orderBy('supplier_id')
            ->paginate(15)
            ->withQueryString();
        StockAvailability::annotate(
            $productOffers->getCollection()
                ->pluck('medicine')
                ->filter()
                ->unique('id')
                ->values()
        );

        return view('admin.products-ordering', [
            'productOffers' => $productOffers,
            'search'       => $search,
            'indikasi'     => $indikasi,
            'komposisi'    => $komposisi,
            'sediaan'      => $sediaan,
            'dosis'        => $dosis,
            'golongan'     => $golongan,
            'sediaanList'  => $attrOptions['sediaanList'],
            'golonganList' => $attrOptions['golonganList'],
            'total'        => (clone $query)->count(),
            'suppliers'    => $suppliers,
            'selectedSupplier' => $selectedSupplier,
        ]);
    }

    public function storePbfSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:pbf_suppliers,name'],
            'address' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);
        if ($validator->fails()) {
            return $this->supplierManagementRedirect($request, false)
                ->withErrors($validator)
                ->withInput();
        }

        PbfSupplier::create($validator->validated());

        return $this->supplierManagementRedirect($request, false)
            ->with('status', 'Supplier berhasil ditambahkan.');
    }

    public function updatePbfSupplier(Request $request, PbfSupplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:pbf_suppliers,name,' . $supplier->id],
            'address' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:50'],
        ]);
        if ($validator->fails()) {
            return $this->supplierManagementRedirect($request)
                ->withErrors($validator)
                ->withInput();
        }

        $supplier->update($validator->validated());

        return $this->supplierManagementRedirect($request)
            ->with('status', 'Supplier berhasil diperbarui.');
    }

    public function destroyPbfSupplier(Request $request, PbfSupplier $supplier)
    {
        $supplier->delete();

        return $this->supplierManagementRedirect($request)
            ->with('status', 'Supplier berhasil dihapus.');
    }

    public function exportPbfSuppliers()
    {
        $rows = PbfSupplier::query()->orderBy('id')->get(['id', 'name', 'address', 'phone'])->map(
            fn (PbfSupplier $supplier) => [$supplier->id, $supplier->name, $supplier->address, $supplier->phone]
        );

        return \App\Helpers\XlsxWriter::download(
            'data-supplier-pbf-'.now()->format('Ymd-His').'.xlsx',
            ['ID', 'NAMA', 'ALAMAT', 'TELEPON'],
            $rows->all()
        );
    }

    public function downloadPbfSupplierImportTemplate()
    {
        return \App\Helpers\XlsxWriter::download('template-import-supplier-pbf.xlsx', ['ID', 'NAMA', 'ALAMAT', 'TELEPON'], []);
    }

    public function importPbfSuppliers(Request $request)
    {
        $fileValidator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);
        if ($fileValidator->fails()) {
            return $this->supplierManagementRedirect($request)
                ->withErrors($fileValidator, 'supplierImport');
        }

        try {
            [$headers, $rows] = \App\Support\SpreadsheetReader::read($request->file('file'));
        } catch (RuntimeException $exception) {
            return $this->supplierManagementRedirect($request)
                ->withErrors(['file' => $exception->getMessage()], 'supplierImport');
        }

        if (!in_array('nama', $headers, true)) {
            return $this->supplierManagementRedirect($request)
                ->withErrors(['file' => 'Format file supplier tidak sesuai. Kolom wajib: NAMA.'], 'supplierImport');
        }
        if (count($rows) > 5000) {
            return $this->supplierManagementRedirect($request)
                ->withErrors(['file' => 'Maksimal 5.000 data supplier per impor.'], 'supplierImport');
        }

        $prepared = [];
        $errors = [];
        $seenNames = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = [
                'name' => trim((string) ($row['nama'] ?? '')),
                'address' => trim((string) ($row['alamat'] ?? '')),
                'phone' => trim((string) ($row['telepon'] ?? '')) ?: null,
            ];
            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:2000'],
                'phone' => ['nullable', 'string', 'max:50'],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }
                continue;
            }

            $id = filled($row['id'] ?? null) ? filter_var($row['id'], FILTER_VALIDATE_INT) : null;
            if (filled($row['id'] ?? null) && (! $id || $id < 1)) {
                $errors[] = "Baris {$line}: ID supplier harus berupa angka positif.";
                continue;
            }
            $supplier = $id ? PbfSupplier::find($id) : null;
            if ($id && ! $supplier) {
                $errors[] = "Baris {$line}: supplier dengan ID {$id} tidak ditemukan.";
                continue;
            }

            $normalizedName = mb_strtolower(trim($data['name']));
            if (isset($seenNames[$normalizedName])) {
                $errors[] = "Baris {$line}: nama supplier duplikat di dalam file.";
                continue;
            }
            $seenNames[$normalizedName] = true;

            $duplicateQuery = PbfSupplier::whereRaw('LOWER(name) = ?', [$normalizedName]);
            if ($supplier) {
                $duplicateQuery->where('id', '!=', $supplier->id);
            }
            $duplicate = $duplicateQuery->first();
            if ($duplicate) {
                if ($id) {
                    $errors[] = "Baris {$line}: nama supplier sudah digunakan oleh supplier lain.";
                    continue;
                }
                $supplier = $duplicate;
            }

            if (!array_key_exists('telepon', $row) && $supplier) {
                $data['phone'] = $supplier->phone;
            }
            if ($supplier) {
                if (!array_key_exists('alamat', $row) || trim((string) $row['alamat']) === '') {
                    $data['address'] = $supplier->address;
                }
                if (array_key_exists('telepon', $row) && trim((string) $row['telepon']) === '') {
                    $data['phone'] = $supplier->phone;
                }
            } else {
                $data['address'] = $data['address'] ?? '';
            }

            $prepared[] = ['supplier' => $supplier, 'data' => $data];
        }

        if ($errors) {
            return $this->supplierManagementRedirect($request)
                ->withErrors(['file' => $errors], 'supplierImport');
        }
        if (! $prepared) {
            return $this->supplierManagementRedirect($request)
                ->withErrors(['file' => 'File CSV tidak berisi data supplier untuk diimpor.'], 'supplierImport');
        }

        DB::transaction(function () use ($prepared): void {
            foreach ($prepared as $record) {
                $supplier = $record['supplier'] ?? new PbfSupplier();
                $supplier->fill($record['data']);
                $supplier->save();
            }
        });

        return $this->supplierManagementRedirect($request)
            ->with('status', count($prepared).' data supplier berhasil diimpor atau diperbarui.');
    }

    private function supplierManagementRedirect(Request $request, bool $openManager = true)
    {
        if ($request->input('supplier_return_to') === 'procurement') {
            return redirect()->route('admin.supplier-procurement.index', $openManager ? ['manage_suppliers' => 1] : []);
        }

        return redirect()->route('admin.produk-pbf.index', array_filter([
            'manage_suppliers' => $openManager ? 1 : null,
            'search' => $request->input('search'),
        ]));
    }

    public function export(Request $request)
    {
        $products = Medicine::when($request->get('mode') === 'pbf', fn ($query) => $query->where('harga_modal', '>', 0))
            ->orderBy('nama_obat')->get();
        StockAvailability::annotate($products);
        $columns = [
            'NO', 'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'GOLONGAN', 'KATEGORI', 'KOMPOSISI', 'INDIKASI', 'DESKRIPSI',
            'SATUAN', 'KEMASAN', 'SEDIAAN', 'STOK SATUAN', 'MODAL', 'HARGA INTERNAL',
            'HARGA JUAL B2B', 'HARGA JUAL B2C', 'LINK FOTO 1', 'LINK FOTO 2',
        ];
        $rows = $products->map(fn ($product) => [
            $product->id,
            $product->kategori ?: '',
            $product->nama_obat,
            $product->logo ?: '',
            $product->golongan ?: '',
            $product->kategori_produk ?: '',
            $product->komposisi ?: '',
            $product->indikasi ?: '',
            $product->deskripsi ?: '',
            $product->satuan ?: '',
            $product->kemasan ?: '',
            $product->sediaan ?: '',
            round($product->getPhysicalStockUnits() / max(1, $product->units_per_box), 6),
            $product->harga_modal,
            $product->harga_internal ?: '',
            $product->getChannelBasePrice('b2b'),
            $product->getChannelBasePrice('b2c'),
            $product->image_url ?: '',
            !empty($product->gambar_tambahan[0]) ? url('storage/' . $product->gambar_tambahan[0]) : '',
        ])->all();
        $widths = array_fill(0, count($columns), 20);
        $widths[2] = $widths[6] = $widths[7] = $widths[8] = 30;
        $widths[9] = $widths[17] = $widths[18] = 48;
        $numericColumns = [0, 12, 13, 14, 15, 16];

        return \App\Helpers\XlsxWriter::download('produk.xlsx', $columns, $rows, $widths, $numericColumns);
    }

    public function create()
    {
        return view('admin.produk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat'         => ['required', 'string', 'max:255'],
            'kategori'          => ['required', 'string', 'max:255'],
            'kategori_produk'   => ['required', 'in:' . implode(',', array_merge(Companies::LIST, array_keys(HomepageProductCategories::options())))],
            'logo'              => ['nullable', 'string', 'max:255'],
            'golongan'          => ['nullable', 'string', 'max:255'],
            'satuan'            => ['nullable', 'string', 'max:100'],
            'kemasan'           => ['nullable', 'string', 'max:255'],
            'gambar'            => ['nullable', 'array', 'max:2'],
            'gambar.*'          => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'harga'             => ['nullable', 'numeric', 'min:0'],
            'harga_modal'       => ['nullable', 'numeric', 'min:0'],
            'harga_internal'    => ['nullable', 'numeric', 'min:0'],
            'harga_grosir'      => ['nullable', 'numeric', 'min:0'],
            'harga_retail'      => ['required_without:harga', 'nullable', 'numeric', 'min:0'],
            'stok'              => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'           => ['nullable', 'string', 'max:255'],
            'deskripsi'         => ['nullable', 'string'],
            'komposisi'         => ['nullable', 'string'],
            'indikasi'          => ['nullable', 'string'],
            'dosis'             => ['nullable', 'string'],
        ]);

        $validated['deskripsi'] = trim((string) ($validated['deskripsi'] ?? ''));

        if (!Schema::hasColumn('medicines', 'dosis')) {
            unset($validated['dosis']);
        }

        $validated['harga_retail'] = $validated['harga_retail'] ?? ($validated['harga'] ?? 0);
        $validated['harga'] = $validated['harga_retail'];
        $validated['unit_packaging_enabled'] = false;
        $validated['strips_per_box'] = null;
        $validated['tablets_per_strip'] = null;
        $images = $request->file('gambar', []);
        $imagePaths = array_map(
            fn ($image) => ImageHelper::storeProductImage($image),
            $images
        );
        $validated['gambar'] = array_shift($imagePaths) ?: null;
        $validated['gambar_tambahan'] = array_values($imagePaths);
        $medicine = new Medicine($validated);
        $medicine->setStockInUnits((int) $validated['stok']);
        $medicine->save();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Medicine $produk)
    {
        StockAvailability::annotate($produk->newCollection([$produk]));

        return view('admin.produk.edit', ['medicine' => $produk]);
    }

    public function update(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'nama_obat'         => ['required', 'string', 'max:255'],
            'kategori'          => ['required', 'string', 'max:255'],
            'kategori_produk'   => ['required', 'in:' . implode(',', array_merge(Companies::LIST, array_keys(HomepageProductCategories::options())))],
            'logo'              => ['nullable', 'string', 'max:255'],
            'golongan'          => ['nullable', 'string', 'max:255'],
            'satuan'            => ['nullable', 'string', 'max:100'],
            'kemasan'           => ['nullable', 'string', 'max:255'],
            'gambar'            => ['nullable', 'array', 'max:2'],
            'gambar.*'          => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_gambar'      => ['nullable', 'array'],
            'hapus_gambar.*'    => ['required', 'string', 'max:255'],
            'harga'             => ['nullable', 'numeric', 'min:0'],
            'harga_modal'       => ['nullable', 'numeric', 'min:0'],
            'harga_internal'    => ['nullable', 'numeric', 'min:0'],
            'harga_grosir'      => ['nullable', 'numeric', 'min:0'],
            'harga_retail'      => ['required_without:harga', 'nullable', 'numeric', 'min:0'],
            'stok'              => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'           => ['nullable', 'string', 'max:255'],
            'deskripsi'         => ['nullable', 'string'],
            'komposisi'          => ['nullable', 'string'],
            'indikasi'          => ['nullable', 'string'],
            'dosis'             => ['nullable', 'string'],
        ]);

        $validated['harga_retail'] = $validated['harga_retail'] ?? ($validated['harga'] ?? 0);
        $validated['harga'] = $validated['harga_retail'];
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

        $validated['deskripsi'] = trim((string) ($validated['deskripsi'] ?? ''));

        if (!Schema::hasColumn('medicines', 'dosis')) {
            unset($validated['dosis']);
        }

        $stockUnits = (int) $validated['stok'];
        unset($validated['stok']);
        $validated['unit_packaging_enabled'] = false;
        $validated['strips_per_box'] = null;
        $validated['tablets_per_strip'] = null;
        $produk->fill($validated);
        $produk->setStockInUnits($stockUnits);
        $this->assertStockCoversReservations($produk);
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
        $this->assertStockCoversReservations($produk);
        $produk->save();
        return back()->with('success', 'Stok berhasil diupdate!');
    }

    public function updateInline(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'harga_modal'       => ['required', 'numeric', 'min:0'],
            'harga_internal'    => ['nullable', 'numeric', 'min:0'],
            'harga_grosir'      => ['nullable', 'numeric', 'min:0'],
            'harga_retail'      => ['required_without:harga', 'nullable', 'numeric', 'min:0'],
            'price_strip'       => ['nullable', 'numeric', 'min:0'],
            'price_tablet'      => ['nullable', 'numeric', 'min:0'],
            'strips_per_box'    => ['nullable', 'integer', 'min:0', 'max:100000'],
            'tablets_per_strip' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'stok'              => ['required', 'numeric', 'min:0', 'max:10000000'],
        ]);

        $stockPackages = (float) $validated['stok'];
        unset($validated['stok']);
        $validated['harga_grosir'] = $validated['harga_grosir'] ?? $produk->harga_grosir;

        if ($request->hasAny(['strips_per_box', 'tablets_per_strip'])) {
            $strips = (int) ($validated['strips_per_box'] ?? 0);
            $tabletsPerStrip = (int) ($validated['tablets_per_strip'] ?? 0);
            if ($strips > 0 && $tabletsPerStrip > 0
                && Medicine::supportsTabletPackaging((string) $produk->satuan, (string) $produk->sediaan)) {
                $validated['unit_packaging_enabled'] = true;
                $validated['strips_per_box'] = $strips;
                $validated['tablets_per_strip'] = $tabletsPerStrip;
                $validated['price_box'] = $validated['harga_retail'];
            } else {
                $validated['unit_packaging_enabled'] = false;
                $validated['strips_per_box'] = null;
                $validated['tablets_per_strip'] = null;
                $validated['price_box'] = null;
            }
        } else {
            unset($validated['strips_per_box'], $validated['tablets_per_strip']);
            if ($produk->usesTabletPackaging()) {
                $validated['price_box'] = $validated['harga_retail'];
            } else {
            }
        }

        $produk->fill($validated);
        $produk->setStockInPackages($stockPackages);
        $this->assertStockCoversReservations($produk);
        $produk->save();

        return back()->with('success', 'Harga dan stok berhasil diupdate!');
    }

    private function assertStockCoversReservations(Medicine $product): void
    {
        if (!Schema::hasTable('purchase_stock_reservations')) {
            return;
        }

        $reservedUnits = (int) PurchaseStockReservation::where('medicine_id', $product->id)
            ->sum('quantity_units');
        if ($product->getPhysicalStockUnits() < $reservedUnits) {
            throw ValidationException::withMessages([
                'stok' => "Stok fisik tidak dapat kurang dari {$reservedUnits} unit yang sedang direservasi.",
            ]);
        }
    }

    public function show(Medicine $produk)
    {
        return redirect()->route('admin.produk.index');
    }
}
