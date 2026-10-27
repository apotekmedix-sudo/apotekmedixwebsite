<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Constants\Companies;
use App\Support\StockAvailability;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->get('search', '');
        $perusahaan = $request->get('perusahaan', '');
        $sort       = $request->get('sort', 'terbaru');

        // Produk Grosir = produk dengan is_grosir = true
        $query = Medicine::where('is_grosir', true);

        if ($search) {
            $query->search($search);
        }

        $query->filterByAttributes($request->only(['indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan']));

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

        $medicines   = $query->paginate(12)->withQueryString();
        $perusahaans = Companies::LIST;
        $total       = Medicine::where('is_grosir', true)->count();

        return view('prescriptions', compact('medicines', 'search', 'perusahaan', 'sort', 'perusahaans', 'total'));
    }
}
