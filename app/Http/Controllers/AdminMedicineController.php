<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Constants\Companies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class AdminMedicineController extends Controller
{
    private array $companies = Companies::LIST;

    // List obat
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $kategori = $request->input('kategori');
        $tipe     = $request->input('tipe'); // 'biasa' atau 'resep'

        $query = Medicine::where('is_grosir', false)->latest();

        if ($search) {
            $query->search($search);
        }

        $query->filterByAttributes($request->only(['indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan']));

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($tipe === 'resep') {
            $query->where('is_resep', true);
        } elseif ($tipe === 'biasa') {
            $query->where('is_resep', false);
        }

        $medicines  = $query->paginate(10)->withQueryString();
        $categories = Companies::LIST;

        return view('admin.medicines.index', compact('medicines', 'search', 'kategori', 'tipe', 'categories'));
    }

    // Form tambah obat
    public function create()
    {
        return view('admin.medicines.create', ['categories' => $this->companies]);
    }

    // Simpan obat baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kelompok'  => ['nullable', 'in:PBF,APOTEK'],
            'kategori'  => ['required', 'string'],
            'harga'     => ['required_unless:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'stok'      => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'   => ['nullable', 'string', 'max:255'],
            'unit_packaging_enabled' => ['nullable', 'boolean'],
            'tablets_per_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'strips_per_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'price_tablet' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'komposisi' => ['required', 'string', 'max:255'],
            'indikasi'  => ['required', 'string', 'max:255'],
            'golongan'  => ['required', 'in:BEBAS,KERAS'],
            'gambar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
        ]);

        // Tentukan is_resep berdasarkan golongan
        $validated['is_resep'] = ($validated['golongan'] === 'KERAS');
        // Produk retail tidak pernah grosir
        $validated['is_grosir'] = false;
        $this->applyPackagingFields($request, $validated);
        
        // Gabung komposisi dan indikasi untuk deskripsi
        $validated['deskripsi'] = $validated['komposisi'] . ' | ' . $validated['indikasi'];
        
        // Hapus field yang tidak perlu di database
        unset($validated['komposisi']);
        unset($validated['indikasi']);
        unset($validated['golongan']);

        // Handle upload gambar
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = ImageHelper::storeProductImage($request->file('gambar'));
        }

        $medicine = new Medicine($validated);
        $medicine->setStockInPackages((float) $validated['stok']);
        $medicine->save();

        return redirect()->route('admin.medicines.index')
                       ->with('success', 'Obat berhasil ditambahkan!');
    }

    // Form edit obat
    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', [
            'medicine'   => $medicine,
            'categories' => $this->companies,
        ]);
    }

    // Update obat
    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'nama_obat' => ['required', 'string', 'max:255'],
            'kelompok'  => ['nullable', 'in:PBF,APOTEK'],
            'kategori'  => ['required', 'string'],
            'harga'     => ['required_unless:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'stok'      => ['required', 'numeric', 'min:0', 'max:10000000'],
            'sediaan'   => ['nullable', 'string', 'max:255'],
            'unit_packaging_enabled' => ['nullable', 'boolean'],
            'tablets_per_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'strips_per_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'integer', 'min:1', 'max:100000'],
            'price_tablet' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_strip' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'price_box' => ['required_if:unit_packaging_enabled,1', 'nullable', 'numeric', 'min:0'],
            'grade'     => ['nullable', 'string', 'max:10'],
            'komposisi' => ['required', 'string', 'max:255'],
            'indikasi'  => ['required', 'string', 'max:255'],
            'golongan'  => ['required', 'in:BEBAS,KERAS'],
            'gambar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif'],
            'delete_gambar' => ['nullable', 'boolean'],
        ]);

        // Tentukan is_resep berdasarkan golongan
        $validated['is_resep'] = ($validated['golongan'] === 'KERAS');
        $validated['grade'] = !empty(trim((string) ($validated['grade'] ?? ''))) ? strtoupper(trim($validated['grade'])) : null;
        // Produk retail tidak pernah grosir
        $validated['is_grosir'] = false;
        $this->applyPackagingFields($request, $validated);
        
        // Gabung komposisi dan indikasi untuk deskripsi
        $validated['deskripsi'] = $validated['komposisi'] . ' | ' . $validated['indikasi'];
        
        // Hapus field yang tidak perlu di database
        unset($validated['komposisi']);
        unset($validated['indikasi']);
        unset($validated['golongan']);
        unset($validated['delete_gambar']);

        // Handle upload gambar baru
        if ($request->hasFile('gambar')) {
            ImageHelper::deleteProductImage($medicine->gambar);
            $validated['gambar'] = ImageHelper::storeProductImage($request->file('gambar'));
        } elseif ($request->input('delete_gambar') == '1' && $medicine->gambar) {
            ImageHelper::deleteProductImage($medicine->gambar);
            $validated['gambar'] = null;
        }

        $stockPackages = (float) $validated['stok'];
        unset($validated['stok']);
        $medicine->fill($validated);
        $medicine->setStockInPackages($stockPackages);
        $medicine->save();

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.medicines.index', $queryParams)
                       ->with('success', 'Obat berhasil diupdate!');
    }

    // Hapus obat
    public function destroy(Request $request, Medicine $medicine)
    {
        ImageHelper::deleteProductImage($medicine->gambar);
        $medicine->delete();

        $queryParams = $this->buildIndexQueryParams($request);

        return redirect()->route('admin.medicines.index', $queryParams)
                       ->with('success', 'Obat berhasil dihapus!');
    }

    // Update stok
    private function buildIndexQueryParams(Request $request): array
    {
        $params = [];

        foreach (['search', 'kategori', 'tipe', 'page'] as $field) {
            $value = $request->query($field, $request->input($field));
            if ($value !== null && $value !== '') {
                $params[$field] = $value;
            }
        }

        return $params;
    }

    public function updateStock(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'stok' => ['required', 'numeric', 'min:0', 'max:10000000'],
        ]);

        $medicine->setStockInPackages((float) $validated['stok']);
        $medicine->save();

        return back()->with('success', 'Stok berhasil diupdate!');
    }

    private function applyPackagingFields(Request $request, array &$validated): void
    {
        $enabled = $request->boolean('unit_packaging_enabled')
            && Medicine::supportsTabletPackaging(null, isset($validated['sediaan']) ? (string) $validated['sediaan'] : null);
        $validated['unit_packaging_enabled'] = $enabled;

        if ($enabled) {
            $validated['harga'] = $validated['price_box'];
            $validated['harga_retail'] = $validated['price_box'];
            return;
        }

        if ($request->boolean('unit_packaging_enabled')) {
            $validated['harga'] = $validated['harga'] ?? $validated['price_box'] ?? 0;
            $validated['harga_retail'] = $validated['harga_retail'] ?? $validated['price_box'] ?? 0;
        }

        foreach (['tablets_per_strip', 'strips_per_box', 'price_tablet', 'price_strip', 'price_box'] as $field) {
            $validated[$field] = null;
        }
    }
}
