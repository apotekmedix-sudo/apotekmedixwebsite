<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isCashier()) {
            return redirect()->route('cashier.dashboard');
        }

        return view('cashier.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember')) || !Auth::user()?->isCashier()) {
            if (Auth::check()) {
                Auth::logout();
            }

            return back()->withErrors(['email' => 'Email atau kata sandi kasir tidak sesuai.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('cashier.dashboard');
    }

    public function dashboard(): View
    {
        $orders = PurchaseHistory::query()
            ->where('transaction_channel', 'offline')
            ->where('payment_status', 'pending')
            ->latest()
            ->paginate(25);

        return view('cashier.dashboard', compact('orders'));
    }

    public function markPaid(PurchaseHistory $order): RedirectResponse
    {
        abort_unless($order->transaction_channel === 'offline', 404);

        $updated = DB::transaction(function () use ($order): bool {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->payment_status !== 'pending') {
                return false;
            }

            $lockedOrder->update([
                'payment_status' => 'paid',
                'cashier_id' => Auth::id(),
                'paid_at' => now(),
            ]);

            return true;
        });

        return redirect()->route('cashier.dashboard')->with(
            $updated ? 'success' : 'error',
            $updated ? 'Pembayaran diterima dan transaksi berhasil diselesaikan.' : 'Transaksi ini sudah diproses sebelumnya.'
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        abort_unless(Auth::check() && Auth::user()->isCashier(), 403);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cashier.login');
    }
}
