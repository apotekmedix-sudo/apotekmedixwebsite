<?php

namespace App\Http\Controllers;

use App\Models\ShoppingAccessCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShoppingAccessCodeController extends Controller
{
    public function index(): View
    {
        $codes = ShoppingAccessCode::query()->get()->keyBy('channel');

        return view('admin.access-codes.index', compact('codes'));
    }

    public function generate(Request $request, string $channel): RedirectResponse
    {
        abort_unless(in_array($channel, [
            ShoppingAccessCode::CHANNEL_B2B,
            ShoppingAccessCode::CHANNEL_B2C,
            ShoppingAccessCode::CHANNEL_INTERNAL,
        ], true), 404);

        $validated = $request->validate([
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);
        $validityDays = (int) ($validated['validity_days'] ?? config('internal.code_validity_days', 30));
        $code = ShoppingAccessCode::generate($channel, $validityDays);
        $channelLabel = strtoupper($channel);

        return redirect()
            ->route('admin.access-codes.index')
            ->with('success', "Kode {$channelLabel} berhasil dibuat dan berlaku {$validityDays} hari: {$code}");
    }

    public function update(Request $request, ShoppingAccessCode $accessCode): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ]);
        $code = $validated['code'];
        $duplicate = ShoppingAccessCode::query()
            ->where('id', '!=', $accessCode->id)
            ->get()
            ->contains(fn (ShoppingAccessCode $existing) => $existing->matchesCode($code));

        if ($duplicate) {
            return back()->withErrors([
                'code' => 'Kode sudah digunakan kanal lain. Silakan pilih 5 digit berbeda.',
            ])->withInput();
        }

        $accessCode->replaceCode($code, (int) ($accessCode->validity_days ?: config('internal.code_validity_days', 30)));

        return redirect()->route('admin.access-codes.index')
            ->with('success', "Kode berhasil diperbarui dan masa berlakunya dimulai ulang selama {$accessCode->validity_days} hari.");
    }

    public function updateValidity(Request $request, ShoppingAccessCode $accessCode): RedirectResponse
    {
        abort_unless(in_array($accessCode->channel, [
            ShoppingAccessCode::CHANNEL_B2B,
            ShoppingAccessCode::CHANNEL_B2C,
            ShoppingAccessCode::CHANNEL_INTERNAL,
        ], true), 404);

        $validated = $request->validate([
            'validity_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);
        $validityDays = (int) $validated['validity_days'];
        $accessCode->updateValidity($validityDays);

        return redirect()->route('admin.access-codes.index')
            ->with('success', "Masa berlaku kode diperbarui menjadi {$validityDays} hari sejak sekarang.");
    }

    public function destroy(ShoppingAccessCode $accessCode): RedirectResponse
    {
        abort_unless(in_array($accessCode->channel, [
            ShoppingAccessCode::CHANNEL_B2B,
            ShoppingAccessCode::CHANNEL_B2C,
            ShoppingAccessCode::CHANNEL_INTERNAL,
        ], true), 404);
        $channel = strtoupper($accessCode->channel);
        $accessCode->delete();

        return redirect()->route('admin.access-codes.index')
            ->with('success', "Kode akses {$channel} berhasil dihapus.");
    }
}
