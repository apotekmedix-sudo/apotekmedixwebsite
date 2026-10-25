<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\PurchaseStockReservation;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

            return back()
                ->withErrors(['email' => 'Email atau kata sandi kasir tidak sesuai.'])
                ->withInput($request->only('email', 'password'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('cashier.dashboard'));
    }

    public function dashboard(Request $request): View
    {
        $filters = $request->validate([
            'channel' => ['nullable', Rule::in(['all', 'offline', 'b2b', 'b2c'])],
            'status' => ['nullable', Rule::in(['all', 'pending', 'verified', 'printed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'cancelled'])],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = PurchaseHistory::query();
        if (!empty($filters['channel']) && $filters['channel'] !== 'all') {
            match ($filters['channel']) {
                'offline' => $query->where('transaction_channel', 'offline'),
                'b2b' => $query->where('transaction_channel', 'online')
                    ->where(fn ($orders) => $orders->whereIn('buyer_type', ['apotik', 'dokter', 'pbf'])
                        ->orWhere(fn ($namedPbf) => $namedPbf->whereNotNull('pbf_name')->where('pbf_name', '!=', ''))),
                'b2c' => $query->where('transaction_channel', 'online')
                    ->where('buyer_type', 'umum')
                    ->where(fn ($orders) => $orders->whereNull('pbf_name')->orWhere('pbf_name', '')),
            };
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('receipt_status', $filters['status']);
        }
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'all') {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        if (!empty($filters['order_id'])) {
            $query->whereKey($filters['order_id']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $metrics = [
            'total' => PurchaseHistory::count(),
            'pending' => PurchaseHistory::where('receipt_status', 'pending')->count(),
            'verified' => PurchaseHistory::where('receipt_status', 'verified')->count(),
            'printed' => PurchaseHistory::where('receipt_status', 'printed')->count(),
            'cancelled' => PurchaseHistory::where('receipt_status', 'cancelled')->count(),
        ];
        $metrics['latest_pending_id'] = PurchaseHistory::where('receipt_status', 'pending')->max('id');
        $paymentMethods = PurchaseHistory::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        return view('cashier.dashboard', compact('orders', 'filters', 'metrics', 'paymentMethods'));
    }

    public function updates(): \Illuminate\Http\JsonResponse
    {
        $pending = PurchaseHistory::where('receipt_status', 'pending');

        return response()->json([
            'pending' => (clone $pending)->count(),
            'latest_pending_id' => $pending->max('id'),
        ]);
    }

    public function verifyAndPrint(PurchaseHistory $order): RedirectResponse
    {
        $updated = DB::transaction(function () use ($order): bool {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->receipt_status !== 'pending' || $lockedOrder->payment_status === 'paid') {
                return false;
            }

            if (!$lockedOrder->stock_committed) {
                $reservations = PurchaseStockReservation::where('purchase_history_id', $lockedOrder->id)
                    ->orderBy('medicine_id')
                    ->lockForUpdate()
                    ->get();
                $products = Medicine::whereIn('id', $reservations->pluck('medicine_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($reservations as $reservation) {
                    $product = $products->get($reservation->medicine_id);
                    if (!$product || (int) $product->available_stock_units < $reservation->quantity_units) {
                        throw ValidationException::withMessages([
                            'order' => 'Stok produk tidak mencukupi untuk menyelesaikan transaksi ini.',
                        ]);
                    }
                    $remainingUnits = (int) $product->available_stock_units - $reservation->quantity_units;
                    $product->update([
                        'stock_base_units' => $remainingUnits,
                        'stok' => intdiv($remainingUnits, max(1, $product->units_per_box)),
                        'terjual' => (int) $product->terjual + $reservation->sale_quantity,
                    ]);
                }

                PurchaseStockReservation::where('purchase_history_id', $lockedOrder->id)->delete();
                $lockedOrder->stock_committed = true;
            }

            $isCreditTerm = strcasecmp(trim((string) $lockedOrder->payment_method), 'Tempo') === 0;
            $lockedOrder->update([
                'receipt_status' => 'verified',
                'payment_status' => $isCreditTerm ? $lockedOrder->payment_status : 'paid',
                'cashier_id' => Auth::id(),
                'paid_at' => $isCreditTerm ? null : now(),
                'verified_at' => now(),
            ]);

            return true;
        });

        if ($updated) {
            return redirect()->route('cashier.orders.receipt', ['order' => $order->id, 'print' => 1]);
        }

        return redirect()->route('cashier.dashboard')
            ->with('error', 'Transaksi ini sudah diverifikasi atau tidak lagi menunggu.');
    }

    public function receipt(PurchaseHistory $order): View
    {
        abort_unless(in_array($order->receipt_status, ['verified', 'printed'], true), 404);
        $items = is_array($order->items) ? $order->items : [];

        return view('cashier.receipt', compact('order', 'items'));
    }

    public function markPrinted(PurchaseHistory $order): RedirectResponse
    {
        $updated = DB::transaction(function () use ($order): bool {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->receipt_status !== 'verified') {
                return false;
            }

            $lockedOrder->update([
                'receipt_status' => 'printed',
                'printed_at' => now(),
            ]);

            return true;
        });

        return back()->with(
            $updated ? 'success' : 'error',
            $updated ? 'Invoice ditandai sudah dicetak.' : 'Hanya transaksi terverifikasi yang dapat ditandai sudah dicetak.'
        );
    }

    public function cancel(PurchaseHistory $order): RedirectResponse
    {
        $cancelled = DB::transaction(function () use ($order): bool {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->receipt_status !== 'pending' || $lockedOrder->payment_status === 'paid') {
                return false;
            }

            if (!$lockedOrder->stock_committed) {
                PurchaseStockReservation::where('purchase_history_id', $lockedOrder->id)->delete();
            } elseif (!$lockedOrder->is_pbf_purchase_order) {
                $items = is_array($lockedOrder->items) ? $lockedOrder->items : [];
                $productIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();
                $products = Medicine::whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $quantities = [];
                foreach ($items as $item) {
                    if (empty($item['id'])) {
                        if ((int) ($item['quantity'] ?? $item['qty'] ?? 0) > 0) {
                            throw ValidationException::withMessages(['order' => 'Stok tidak dapat dikembalikan karena data produk pada pesanan tidak lengkap.']);
                        }
                        continue;
                    }
                    $productId = (int) $item['id'];
                    $product = $products->get($productId);
                    if (!$product) {
                        throw ValidationException::withMessages(['order' => "Produk #{$productId} tidak ditemukan; pembatalan dihentikan agar stok tidak salah."]);
                    }
                    $factor = $product->getSaleUnitFactor($item['sale_unit'] ?? 'box');
                    $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                    if ($factor < 1 || $quantity < 1) {
                        throw ValidationException::withMessages(['order' => 'Jumlah atau satuan produk tidak valid; pembatalan dihentikan.']);
                    }
                    $quantities[$productId] = ($quantities[$productId] ?? 0) + ($quantity * $factor);
                }

                foreach ($quantities as $productId => $quantity) {
                    $product = $products->get($productId);
                    $remainingUnits = (int) $product->available_stock_units + $quantity;
                    $product->update([
                        'stock_base_units' => $remainingUnits,
                        'stok' => intdiv($remainingUnits, max(1, $product->units_per_box)),
                    ]);
                }
            }

            $lockedOrder->update([
                'receipt_status' => 'cancelled',
                'payment_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return true;
        });

        return back()->with(
            $cancelled ? 'success' : 'error',
            $cancelled ? 'Transaksi dibatalkan dan stok dikembalikan.' : 'Hanya transaksi pending yang dapat dibatalkan.'
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
