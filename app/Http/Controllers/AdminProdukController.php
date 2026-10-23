<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Constants\Companies;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;

class AdminProdukController extends Controller
{
    private array $kategoriProduk = Companies::LIST;

    public function index(Request $request)
    {
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $pabrik          = $request->get('pabrik', '');
        $kelompok        = $request->get('kelompok', '');
        $mode             = $request->get('mode', '');

        $query = Medicine::latest();
        if ($kelompok) {
            $query->where('kelompok', $kelompok);
        }
        if ($mode === 'pbf') {
            $query->where('harga_modal', '>', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_obat', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($kategori_produk) {
            $query->where('kategori_produk', $kategori_produk);
        }

        if ($pabrik) {
            $query->where('kategori', $pabrik);
        }

        $medicines       = $query->paginate(15)->withQueryString();
        $total           = (clone $query)->reorder()->count();
        $kategoriOptions = Companies::LIST;

        return view('admin.produk.index', compact(
            'medicines', 'search', 'kategori_produk', 'pabrik', 'kelompok', 'mode', 'total', 'kategoriOptions'
        ));
    }

    public function indexPbf(Request $request)
    {
        $search = $request->get('search', '');
        $query = Medicine::where('harga_modal', '>', 0)->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        return view('admin.products-ordering', [
            'medicines' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'total' => Medicine::where('harga_modal', '>', 0)->count(),
        ]);
    }

    public function export(Request $request)
    {
        $products = Medicine::when($request->get('mode') === 'pbf', fn ($query) => $query->where('harga_modal', '>', 0))
            ->orderBy('nama_obat')->get();
        $columns = ['NO', 'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'RESEP', 'KOMPOSISI', 'SATUAN', 'MODAL', 'HARGA RETAIL'];
        $rows = $products->values()->map(fn ($product, $index) => [
            $index + 1, $product->brand ?: $product->kategori, $product->nama_obat, $product->gambar ?: '',
            $product->grade ?: 'BEBAS', $product->komposisi ?: '', $product->sediaan ?: '',
            $product->harga_modal, $product->harga_retail ?: $product->harga,
        ])->all();

        return \App\Helpers\XlsxWriter::download('produk_tanpa_stok.xlsx', $columns, $rows, [8, 25, 30, 18, 12, 30, 12, 15, 18]);
    }

    public function create()
    {
        return view('admin.produk.create', [
            'kategoriOptions' => $this->kategoriProduk,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat'       => ['required', 'string', 'max:255'],
            'kategori_produk' => ['required', 'in:' . implode(',', Companies::LIST)],
            'kategori'        => ['required', 'string', 'max:255'],
            'distributor'     => ['nullable', 'string', 'max:255'],
            'kelompok'        => ['nullable', 'in:PBF,APOTEK'],
            'sku'             => ['nullable', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'terjual'         => ['nullable', 'integer', 'min:0'],
            'grade'           => ['nullable', 'string'],
            'harga'           => ['required_unless:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'harga_modal'     => ['nullable', 'numeric', 'min:0'],
            'harga_retail'    => ['nullable', 'numeric', 'min:0'],
            'stok'            => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'         => ['nullable', 'string', 'max:255'],
            'unit_packaging_enabled' => ['nullable', 'boolean'],
            'tablets_per_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'strips_per_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'price_tablet' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
            'komposisi'       => ['nullable', 'string'],
            'indikasi'        => ['nullable', 'string'],
        ]);

        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            $validated['gambar'] = ImageHelper::storeProductImage($request->file('gambar'));
        }

        if (!empty(trim($validated['deskripsi'] ?? ''))) {
            $validated['deskripsi'] = trim($validated['deskripsi']);
        } else {
            $validated['deskripsi'] = trim(($validated['komposisi'] ?? '') . ' | ' . ($validated['indikasi'] ?? ''));
        }

        $this->applyPackagingFields($request, $validated);
        $validated['harga_retail'] = $validated['harga_retail'] ?? $validated['harga'];
        $medicine = new Medicine($validated);
        $medicine->setStockInBoxes((float) $validated['stok']);
        $medicine->save();

        return redirect()->route('admin.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit(Medicine $produk)
    {
        return view('admin.produk.edit', [
            'medicine'        => $produk,
            'kategoriOptions' => $this->kategoriProduk,
        ]);
    }

    public function update(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'nama_obat'       => ['required', 'string', 'max:255'],
            'kategori_produk' => ['required', 'in:' . implode(',', Companies::LIST)],
            'kategori'        => ['required', 'string', 'max:255'],
            'distributor'     => ['nullable', 'string', 'max:255'],
            'kelompok'        => ['nullable', 'in:PBF,APOTEK'],
            'sku'             => ['nullable', 'string', 'max:255'],
            'brand'           => ['nullable', 'string', 'max:255'],
            'terjual'         => ['nullable', 'integer', 'min:0'],
            'grade'           => ['nullable', 'string'],
            'harga'           => ['required_unless:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'harga_modal'     => ['nullable', 'numeric', 'min:0'],
            'harga_retail'    => ['nullable', 'numeric', 'min:0'],
            'stok'            => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'         => ['nullable', 'string', 'max:255'],
            'unit_packaging_enabled' => ['nullable', 'boolean'],
            'tablets_per_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'strips_per_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'price_tablet' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
            'komposisi'       => ['nullable', 'string'],
            'indikasi'        => ['nullable', 'string'],
            'delete_gambar'   => ['nullable'],
        ]);

        unset($validated['delete_gambar']);
        $this->applyPackagingFields($request, $validated);

        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            ImageHelper::deleteProductImage($produk->gambar);
            $validated['gambar'] = ImageHelper::storeProductImage($request->file('gambar'));
        } elseif ($request->input('delete_gambar') == '1' && $produk->gambar) {
            ImageHelper::deleteProductImage($produk->gambar);
            $validated['gambar'] = null;
        }
        $validated['harga_retail'] = $validated['harga_retail'] ?? $validated['harga'];

        if (!empty(trim($validated['deskripsi'] ?? ''))) {
            $validated['deskripsi'] = trim($validated['deskripsi']);
        } else {
            $validated['deskripsi'] = trim(($validated['komposisi'] ?? '') . ' | ' . ($validated['indikasi'] ?? ''));
        }

        $stockBoxes = (float) $validated['stok'];
        unset($validated['stok']);
        if ($produk->unit_packaging_enabled) {
            $validated['price_box'] = $validated['harga_retail'];
            $validated['harga'] = $validated['harga_retail'];
        }
        $produk->fill($validated);
        $produk->setStockInBoxes($stockBoxes);
        $produk->save();

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.produk.index', $queryParams)
                         ->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy(Request $request, Medicine $produk)
    {
        ImageHelper::deleteProductImage($produk->gambar);
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
            ImageHelper::deleteProductImage($produk->gambar);
            $produk->delete();
        }

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.produk.index', $queryParams)
                         ->with('success', 'Sebanyak ' . count($products) . ' produk berhasil dihapus!');
    }

    private function buildIndexQueryParams(Request $request): array
    {
        $params = [];

        foreach (['search', 'kategori_produk', 'pabrik', 'kelompok', 'mode', 'page'] as $field) {
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
        $produk->setStockInBoxes((float) $validated['stok']);
        $produk->save();
        return back()->with('success', 'Stok berhasil diupdate!');
    }

    public function updateInline(Request $request, Medicine $produk)
    {
        $validated = $request->validate([
            'harga_modal'  => ['required', 'numeric', 'min:0'],
            'harga_retail' => ['required', 'numeric', 'min:0'],
            'stok'         => ['required', 'numeric', 'min:0', 'max:10000000'],
        ]);

        $stockBoxes = (float) $validated['stok'];
        unset($validated['stok']);
        if ($produk->unit_packaging_enabled) {
            $validated['price_box'] = $validated['harga_retail'];
            $validated['harga'] = $validated['harga_retail'];
        }
        $produk->fill($validated);
        $produk->setStockInBoxes($stockBoxes);
        $produk->save();

        return back()->with('success', 'Harga dan stok berhasil diupdate!');
    }

    private function applyPackagingFields(Request $request, array &$validated): void
    {
        $enabled = $request->boolean('unit_packaging_enabled');
        $validated['unit_packaging_enabled'] = $enabled;

        if ($enabled) {
            $validated['harga'] = $validated['price_box'];
            $validated['harga_retail'] = $validated['price_box'];
            return;
        }

        foreach (['tablets_per_strip', 'strips_per_box', 'price_tablet', 'price_strip', 'price_box'] as $field) {
            $validated[$field] = null;
        }
    }

    public function show(Medicine $produk)
    {
        return redirect()->route('admin.produk.index');
    }
}
