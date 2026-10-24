<?php

namespace App\Http\Controllers;

use App\Models\ShoppingAccessCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalShoppingAccessController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('internal_shopping_expires_at', 0) > now()->timestamp) {
            return redirect()->intended(route('shopping.internal'));
        }

        return view('internal-shopping.access');
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ]);

        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->first();
        if (!$accessCode || !$accessCode->verify($validated['code'])) {
            return back()->withErrors(['code' => 'Kode akses tidak sesuai.']);
        }

        $request->session()->regenerate();
        $sessionExpiresAt = now()->addDays((int) config('internal.code_validity_days', 30));
        if ($accessCode->expires_at->lt($sessionExpiresAt)) {
            $sessionExpiresAt = $accessCode->expires_at;
        }
        $request->session()->put(
            'internal_shopping_expires_at',
            $sessionExpiresAt->timestamp
        );
        $request->session()->put('internal_shopping_code_hash', $accessCode->code_hash);

        return redirect()->intended(route('shopping.internal'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['internal_shopping_expires_at', 'internal_shopping_code_hash']);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Akses belanja internal telah ditutup.');
    }
}
