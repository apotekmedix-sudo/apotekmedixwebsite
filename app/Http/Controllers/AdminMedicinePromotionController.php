<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminMedicinePromotionController extends Controller
{
    public function index(Request $request)
    {
        $search    = trim((string) $request->query('search', ''));
        $indikasi  = trim((string) $request->query('indikasi', ''));
        $komposisi = trim((string) $request->query('komposisi', ''));
        $sediaan   = trim((string) $request->query('sediaan', ''));
        $dosis     = trim((string) $request->query('dosis', ''));
        $golongan  = trim((string) $request->query('golongan', ''));

        $activeTab = $request->query('tab', 'channels');
        if (!in_array($activeTab, ['channels', 'vouchers'], true)) {
            $activeTab = 'channels';
        }

        $medicines = Medicine::query()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->filterByAttributes([
                'indikasi'  => $indikasi,
                'komposisi' => $komposisi,
                'sediaan'   => $sediaan,
                'dosis'     => $dosis,
                'golongan'  => $golongan,
            ])
            ->orderBy('nama_obat')
            ->paginate(25)
            ->withQueryString();

        $discountCodes = Schema::hasTable('discount_codes')
            ? DiscountCode::orderByDesc('id')->get()
            : collect();

        $attrOptions  = Medicine::getAttributeFilterOptions();
        $sediaanList  = $attrOptions['sediaanList'];
        $golonganList = $attrOptions['golonganList'];

        return view('admin.promotions.index', compact(
            'medicines', 'search', 'indikasi', 'komposisi', 'sediaan', 'dosis', 'golongan',
            'sediaanList', 'golonganList', 'discountCodes', 'activeTab'
        ));
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
