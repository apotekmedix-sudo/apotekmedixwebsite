<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class AdminMedicinePromotionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $medicines = Medicine::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('nama_obat', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nama_obat')
            ->paginate(25)
            ->withQueryString();

        return view('admin.promotions.index', compact('medicines', 'search'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'promo_internal_percent' => ['required', 'integer', 'between:0,100'],
            'promo_b2b_percent' => ['required', 'integer', 'between:0,100'],
            'promo_b2c_percent' => ['required', 'integer', 'between:0,100'],
        ]);

        $medicine->update($data);

        $search = trim((string) $request->input('search', ''));
        return redirect()
            ->to(route('admin.promotions.index') . ($search !== '' ? '?search=' . urlencode($search) : ''))
            ->with('success', 'Diskon promo berhasil diperbarui untuk ' . $medicine->nama_obat . '.');
    }
}
