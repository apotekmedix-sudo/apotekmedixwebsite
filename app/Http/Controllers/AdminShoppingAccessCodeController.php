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

    public function generate(string $channel): RedirectResponse
    {
        abort_unless(in_array($channel, [
            ShoppingAccessCode::CHANNEL_INTERNAL,
            ShoppingAccessCode::CHANNEL_B2B,
        ], true), 404);

        $code = ShoppingAccessCode::generate($channel);

        return redirect()
            ->route('admin.access-codes.index')
            ->with('success', 'Kode ' . ($channel === ShoppingAccessCode::CHANNEL_INTERNAL ? 'Internal' : 'B2B') . " berhasil dibuat: {$code}");
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
            ->contains(fn (ShoppingAccessCode $existing) => $existing->readCode() === $code);

        if ($duplicate) {
            return back()->withErrors([
                'code' => 'Kode sudah digunakan kanal lain. Silakan pilih 5 digit berbeda.',
            ])->withInput();
        }

        $accessCode->replaceCode($code);

        return redirect()->route('admin.access-codes.index')
            ->with('success', 'Kode berhasil diperbarui dan masa berlakunya dimulai ulang selama 30 hari.');
    }

    public function destroy(ShoppingAccessCode $accessCode): RedirectResponse
    {
        $channel = $accessCode->channel === ShoppingAccessCode::CHANNEL_INTERNAL ? 'Internal' : 'B2B';
        $accessCode->delete();

        return redirect()->route('admin.access-codes.index')
            ->with('success', "Kode akses {$channel} berhasil dihapus.");
    }
}
