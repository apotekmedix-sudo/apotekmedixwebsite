<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDiscountCodeController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.promotions.index', ['tab' => 'vouchers']);
    }

    public function store(Request $request)
    {
        $normalizedCode = strtoupper(preg_replace('/\s+/', '', (string) $request->input('code', '')));
        $request->merge(['code' => $normalizedCode]);

        $data = $request->validate([
            'code'           => ['required', 'string', 'max:64', 'unique:discount_codes,code'],
            'description'    => ['nullable', 'string', 'max:255'],
            'discount_type'  => ['required', 'string', 'in:percent,fixed'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'limit_mode'     => ['required', 'string', 'in:limited,unlimited'],
            'usage_limit'    => ['required_if:limit_mode,limited', 'nullable', 'integer', 'min:1'],
            'is_active'      => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode diskon wajib diisi.',
            'code.unique' => 'Kode diskon tersebut sudah terdaftar.',
            'usage_limit.required_if' => 'Batas jumlah pemakaian wajib diisi jika kuota dibatasi.',
        ]);

        if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
            return redirect()
                ->route('admin.promotions.index', ['tab' => 'vouchers'])
                ->withInput()
                ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%.']);
        }

        $isUnlimited = $data['limit_mode'] === 'unlimited';

        DiscountCode::create([
            'code'           => $data['code'],
            'description'    => $data['description'] ?? null,
            'discount_type'  => $data['discount_type'],
            'discount_value' => (int) $data['discount_value'],
            'is_unlimited'   => $isUnlimited,
            'usage_limit'    => $isUnlimited ? null : (int) $data['usage_limit'],
            'used_count'     => 0,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'vouchers'])
            ->with('success', "Kode diskon {$data['code']} berhasil ditambahkan.");
    }

    public function update(Request $request, DiscountCode $discountCode)
    {
        $normalizedCode = strtoupper(preg_replace('/\s+/', '', (string) $request->input('code', '')));
        $request->merge(['code' => $normalizedCode]);

        $data = $request->validate([
            'code'           => ['required', 'string', 'max:64', Rule::unique('discount_codes', 'code')->ignore($discountCode->id)],
            'description'    => ['nullable', 'string', 'max:255'],
            'discount_type'  => ['required', 'string', 'in:percent,fixed'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'limit_mode'     => ['required', 'string', 'in:limited,unlimited'],
            'usage_limit'    => ['required_if:limit_mode,limited', 'nullable', 'integer', 'min:1'],
            'is_active'      => ['nullable', 'boolean'],
            'reset_used'     => ['nullable', 'boolean'],
        ]);

        if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
            return redirect()
                ->route('admin.promotions.index', ['tab' => 'vouchers'])
                ->withInput()
                ->withErrors(['discount_value' => 'Persentase diskon maksimal 100%.']);
        }

        $isUnlimited = $data['limit_mode'] === 'unlimited';

        $updatePayload = [
            'code'           => $data['code'],
            'description'    => $data['description'] ?? null,
            'discount_type'  => $data['discount_type'],
            'discount_value' => (int) $data['discount_value'],
            'is_unlimited'   => $isUnlimited,
            'usage_limit'    => $isUnlimited ? null : (int) $data['usage_limit'],
            'is_active'      => $request->boolean('is_active', false),
        ];

        if ($request->boolean('reset_used')) {
            $updatePayload['used_count'] = 0;
        }

        $discountCode->update($updatePayload);

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'vouchers'])
            ->with('success', "Kode diskon {$discountCode->code} berhasil diperbarui.");
    }

    public function toggle(DiscountCode $discountCode)
    {
        $discountCode->update([
            'is_active' => !$discountCode->is_active,
        ]);

        $statusLabel = $discountCode->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'vouchers'])
            ->with('success', "Kode diskon {$discountCode->code} berhasil {$statusLabel}.");
    }

    public function destroy(DiscountCode $discountCode)
    {
        $codeName = $discountCode->code;
        $discountCode->delete();

        return redirect()
            ->route('admin.promotions.index', ['tab' => 'vouchers'])
            ->with('success', "Kode diskon {$codeName} berhasil dihapus.");
    }
}
