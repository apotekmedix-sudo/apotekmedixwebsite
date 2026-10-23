<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Constants\Companies;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Halaman Produk Kami (frontend)
     */
    public function index(Request $request)
    {
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $perusahaan      = $request->get('perusahaan', '');
        $sort            = $request->get('sort', 'terbaru');

        $query = Medicine::nonPbf();

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
        $total           = Medicine::nonPbf()->count();
        $kategoriOptions = Companies::LIST;
        // Ambil daftar perusahaan unik dari data yang ada di DB
        $perusahaanList  = Medicine::nonPbf()
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');

        return view('products', [
            'medicines' => $medicines,
            'search' => $search,
            'kategori_produk' => $kategori_produk,
            'perusahaan' => $perusahaan,
            'sort' => $sort,
            'total' => $total,
            'kategoriOptions' => $kategoriOptions,
            'perusahaanList' => $perusahaanList,
            'kioskOrder' => $request->routeIs('kiosk'),
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
    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);
        $relatedMedicines = Medicine::where('kategori', $medicine->kategori)
                                    ->where('id', '!=', $medicine->id)
                                    ->limit(4)
                                    ->get();
        return view('medicines.detail', compact('medicine', 'relatedMedicines'));
    }

    /**
     * Kode akses katalog — APOTEKMEDIX1 sampai APOTEKMEDIX10.
     */
    const PBF_ACCESS_CODES = [
        'APOTEKMEDIX1',
        'APOTEKMEDIX2',
        'APOTEKMEDIX3',
        'APOTEKMEDIX4',
        'APOTEKMEDIX5',
        'APOTEKMEDIX6',
        'APOTEKMEDIX7',
        'APOTEKMEDIX8',
        'APOTEKMEDIX9',
        'APOTEKMEDIX10',
    ];

    /**
     * Halaman Produk PBF (frontend) — dilindungi kode akses
     */
    public function pbf(Request $request)
    {
        // Cek apakah session akses PBF sudah ada
        if (! $request->session()->get('pbf_access')) {
            return view('products_pbf_gate');
        }

        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $perusahaan      = $request->get('perusahaan', '');
        $sort            = $request->get('sort', 'terbaru');

        $query = Medicine::where('kelompok', 'PBF');

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
        $total           = Medicine::where('kelompok', 'PBF')->count();
        $kategoriOptions = Companies::LIST;
        $perusahaanList  = Medicine::where('kelompok', 'PBF')
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');

        return view('products_pbf', compact(
            'medicines', 'search', 'kategori_produk', 'perusahaan',
            'sort', 'total', 'kategoriOptions', 'perusahaanList'
        ))->with('catalogPrice', 'harga_modal');
    }

    /**
     * Verifikasi kode akses PBF
     */
    public function pbfVerify(Request $request)
    {
        $kode = strtoupper(trim($request->input('kode', '')));

        if (empty($kode)) {
            return redirect()->route('products.pbf')
                ->withErrors(['kode' => '⚠️ Kode akses tidak boleh kosong. Silakan masukkan kode Anda.'])
                ->withInput();
        }

        if (in_array($kode, self::PBF_ACCESS_CODES)) {
            $request->session()->put('pbf_access', true);
            return redirect()->route('products.pbf')
                ->with('pbf_success', '✅ Akses berhasil! Selamat datang di Katalog PBF.');
        }

        return redirect()->route('products.pbf')
            ->withErrors(['kode' => '❌ Kode akses salah atau tidak valid. Periksa kembali atau hubungi admin via WhatsApp.'])
            ->withInput();
    }

    /**
     * Logout dari sesi akses PBF
     */
    public function pbfLogout(Request $request)
    {
        $request->session()->forget('pbf_access');
        return redirect()->route('products.pbf')
            ->with('pbf_info', 'Sesi akses PBF telah diakhiri.');
    }

    /**
     * Halaman Produk Apotek (frontend)
     */
    public function apotek(Request $request)
    {
        $search          = $request->get('search', '');
        $kategori_produk = $request->get('kategori_produk', '');
        $perusahaan      = $request->get('perusahaan', '');
        $sort            = $request->get('sort', 'terbaru');

        $query = Medicine::where('kelompok', 'APOTEK');

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
        $total           = Medicine::where('kelompok', 'APOTEK')->count();
        $kategoriOptions = Companies::LIST;
        $perusahaanList  = Medicine::where('kelompok', 'APOTEK')
                            ->select('kategori')
                            ->whereNotNull('kategori')
                            ->where('kategori', '!=', '')
                            ->distinct()
                            ->orderBy('kategori')
                            ->pluck('kategori');

        return view('products_apotek', compact(
            'medicines', 'search', 'kategori_produk', 'perusahaan',
            'sort', 'total', 'kategoriOptions', 'perusahaanList'
        ));
    }

}
