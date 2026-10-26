<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Banner;
use App\Models\PurchaseHistory;
use App\Models\PurchaseStockReservation;
use App\Services\MedicineStockLedger;
use Illuminate\Support\Facades\DB;
use App\Constants\Companies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalProduk    = Medicine::count();
        $totalStok      = Medicine::sum('stok');
        $lowStok        = Medicine::where('stok', '<', 5)->count();
        $latestProduk   = Medicine::latest()->limit(10)->get();

        // Per kategori produk
        $categoryColumn = Schema::hasColumn('medicines', 'kategori_produk') ? 'kategori_produk' : 'kategori';
        $kategoriList = Schema::hasColumn('medicines', 'kategori_produk') ? Companies::LIST : Medicine::whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        $perKategori = [];
        foreach ($kategoriList as $kat) {
            $perKategori[$kat] = Medicine::where($categoryColumn, $kat)->count();
        }

        $latestBanners = Schema::hasTable('banners') ? Banner::orderBy('urutan')->orderBy('id')->limit(5)->get() : collect();
        $totalBanners  = Schema::hasTable('banners') ? Banner::count() : 0;
        $activeBanners = Schema::hasTable('banners') ? Banner::where('aktif', true)->count() : 0;
        $totalOmzet    = Schema::hasTable('purchase_histories')
            ? $this->revenueOrders()->get()->sum(fn ($order) => $order->effective_total)
            : 0;
        $recentOrders  = Schema::hasTable('purchase_histories') ? PurchaseHistory::latest()->limit(5)->get() : collect();

        return view('admin.dashboard', compact(
            'totalProduk', 'totalStok', 'lowStok', 'latestProduk',
            'perKategori', 'latestBanners', 'totalBanners', 'activeBanners',
            'totalOmzet', 'recentOrders' 
        ));
    }

    public function stats()
    {
        $selectColumns = ['id', 'nama_obat', 'kategori', 'harga', 'stok', 'created_at'];
        if (Schema::hasColumn('medicines', 'kategori_produk')) {
            $selectColumns[] = 'kategori_produk';
        }

        $latestProduk = Medicine::latest()->limit(10)
            ->get($selectColumns)
            ->map(fn($m) => [
                'id'              => $m->id,
                'nama_obat'       => $m->nama_obat,
                'kategori'        => $m->kategori,
                'kategori_produk' => $m->kategori_produk ?? $m->kategori,
                'harga'           => $m->harga,
                'stok'            => $m->stok,
                'created_at'      => $m->created_at->format('d M Y H:i'),
            ]);

        return response()->json([
            'total'         => Medicine::count(),
            'lowStok'       => Medicine::where('stok', '<', 5)->count(),
            'latestProduk'  => $latestProduk,
        ]);
    }

    public function purchaseHistory()
    {
        $orders = Schema::hasTable('purchase_histories')
            ? PurchaseHistory::with('cashier')->latest()->paginate(20)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $total_omzet = Schema::hasTable('purchase_histories')
            ? $this->revenueOrders()->get()->sum(fn ($order) => $order->effective_total)
            : 0;

        return view('admin.purchase-history', compact('orders', 'total_omzet'));
    }

    public function notifications(Request $request)
    {
        $filter = (string) $request->query('filter', 'unpaid');
        $allTempoOrders = Schema::hasTable('purchase_histories')
            ? PurchaseHistory::with('cashier')
                ->whereRaw('LOWER(TRIM(COALESCE(payment_method, ""))) = ?', ['tempo'])
                ->where(fn ($q) => $q->whereNull('receipt_status')->orWhere('receipt_status', '!=', 'cancelled'))
                ->where(fn ($q) => $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'rejected'))
                ->orderBy('created_at', 'asc')
                ->get()
            : collect();

        $overdueOrders = $allTempoOrders->filter(fn (PurchaseHistory $o) => in_array($o->tempo_due_status, ['overdue', 'due_today'], true));
        $dueSoonOrders = $allTempoOrders->filter(fn (PurchaseHistory $o) => $o->tempo_due_status === 'due_soon');
        $unpaidOrders  = $allTempoOrders->filter(fn (PurchaseHistory $o) => $o->payment_status !== 'paid');
        $paidOrders    = $allTempoOrders->filter(fn (PurchaseHistory $o) => $o->payment_status === 'paid');

        $filteredOrders = match ($filter) {
            'overdue'  => $overdueOrders->values(),
            'due_soon' => $dueSoonOrders->values(),
            'paid'     => $paidOrders->sortByDesc('paid_at')->values(),
            'all'      => $allTempoOrders->sortBy(fn (PurchaseHistory $o) => $o->payment_status === 'paid' ? 999999 : ($o->days_until_due ?? 0))->values(),
            default    => $unpaidOrders->sortBy(fn (PurchaseHistory $o) => $o->days_until_due ?? 0)->values(),
        };

        $metrics = [
            'overdue_count'  => $overdueOrders->count(),
            'due_soon_count' => $dueSoonOrders->count(),
            'unpaid_count'   => $unpaidOrders->count(),
            'paid_count'     => $paidOrders->count(),
            'unpaid_total'   => $unpaidOrders->sum(fn (PurchaseHistory $o) => $o->effective_total),
            'overdue_total'  => $overdueOrders->sum(fn (PurchaseHistory $o) => $o->effective_total),
        ];

        return view('admin.notifications.index', compact('filteredOrders', 'metrics', 'filter'));
    }

    public function markTempoPaid(PurchaseHistory $order)
    {
        if (!$order->isTempoPayment()) {
            return back()->with('error', 'Pesanan ini bukan menggunakan metode pembayaran Tempo.');
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at'        => now(),
            'cashier_id'     => $order->cashier_id ?: auth()->id(),
        ]);

        return back()->with('success', "Tagihan Tempo Pesanan #{$order->id} ({$order->buyer_name}) berhasil ditandai LUNAS.");
    }

    private function _unusedPurchaseHistoryEnd()
    {
    }

    public function updateApprovalStatus(Request $request, PurchaseHistory $order)
    {
        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        $data = $request->validate([
            'approval_status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        if (!($order->original_total > 0) || !($order->discounted_total > 0)) {
            $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
            $items = is_array($items) ? $items : [];
            $originalTotal = 0;
            $discountedTotal = 0;

            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                $price = (int) ($item['harga'] ?? $item['price'] ?? 0);
                $discount = (int) ($item['potongan'] ?? 0);
                $subtotal = $qty * $price;
                $originalTotal += $subtotal;
                $discountedTotal += max(0, $subtotal - $discount);
            }

            $order->original_total = $order->original_total ?: $originalTotal;
            $order->discounted_total = $order->discounted_total ?: $discountedTotal;
        }

        DB::transaction(function () use ($order, $data) {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($data['approval_status'] === 'rejected') {
                $this->releaseOrRestoreOrderStock($lockedOrder);
                $data['receipt_status'] = 'cancelled';
                $data['payment_status'] = 'cancelled';
                $data['stock_committed'] = false;
                $data['cancelled_at'] = now();
            } elseif ($data['approval_status'] === 'approved' && !$lockedOrder->stock_committed && !$lockedOrder->is_pbf_purchase_order) {
                $this->commitOrderStock($lockedOrder);
                $data['stock_committed'] = true;
                if ($lockedOrder->receipt_status === 'cancelled') {
                    $data['receipt_status'] = 'verified';
                    $data['cancelled_at'] = null;
                }
            }

            $lockedOrder->original_total = $order->original_total;
            $lockedOrder->discounted_total = $order->discounted_total;
            $lockedOrder->update($data);
        });

        return redirect()->route('admin.purchase-history.index')->with('success', 'Status persetujuan berhasil diperbarui.');
    }

    /**
     * Delete a single purchase history entry
     */
    public function destroy(\App\Models\PurchaseHistory $order)
    {
        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = PurchaseHistory::whereKey($order->id)->lockForUpdate()->first();
            if ($lockedOrder) {
                $this->releaseOrRestoreOrderStock($lockedOrder);
                $lockedOrder->delete();
            }
        });
        return redirect()->route('admin.purchase-history.index')->with('success', 'Riwayat pembelian berhasil dihapus dan stok dikembalikan.');
    }

    /**
     * Delete all purchase history entries
     */
    public function destroyAll()
    {
        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        DB::transaction(function () {
            foreach (PurchaseHistory::query()->lockForUpdate()->get() as $order) {
                $this->releaseOrRestoreOrderStock($order);
                $order->delete();
            }
        });
        return redirect()->route('admin.purchase-history.index')->with('success', 'Semua riwayat pembelian berhasil dihapus dan stok dikembalikan.');
    }

    private function releaseOrRestoreOrderStock(PurchaseHistory $order): void
    {
        if ($order->receipt_status !== 'cancelled' && $order->approval_status !== 'rejected' && !empty($order->discount_code) && Schema::hasTable('discount_codes')) {
            $voucher = \App\Models\DiscountCode::where('code', $order->discount_code)->lockForUpdate()->first();
            if ($voucher && $voucher->used_count > 0) {
                $voucher->decrement('used_count');
            }
        }
        if (Schema::hasTable('purchase_stock_reservations')) {
            $reservations = PurchaseStockReservation::where('purchase_history_id', $order->id)->get();
            foreach ($reservations as $reservation) {
                MedicineStockLedger::recordReservedChange(
                    (int) $reservation->medicine_id,
                    -(int) $reservation->quantity_units,
                    $order->id,
                    'release',
                    'Reservasi dilepas karena pesanan dibatalkan atau dihapus.'
                );
            }
            PurchaseStockReservation::where('purchase_history_id', $order->id)->delete();
        }

        if (!$order->stock_committed || $order->is_pbf_purchase_order || $order->receipt_status === 'cancelled') {
            return;
        }

        $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
        $items = is_array($items) ? $items : [];
        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
            if ($productId < 1 || $qty < 1) {
                continue;
            }
            $product = Medicine::whereKey($productId)->lockForUpdate()->first();
            if (!$product) {
                continue;
            }
            $factor = max(1, $product->getSaleUnitFactor($item['sale_unit'] ?? 'box'));
            $restoredUnits = $product->getPhysicalStockUnits() + ($qty * $factor);
            MedicineStockLedger::withContext(
                'return',
                $order->id,
                fn () => $product->update([
                    'stock_base_units' => $restoredUnits,
                    'stok' => intdiv($restoredUnits, max(1, $product->units_per_box)),
                    'terjual' => max(0, (int) $product->terjual - $qty),
                ]),
                0,
                'Stok dikembalikan karena pesanan dibatalkan atau dihapus.'
            );
        }
    }

    private function commitOrderStock(PurchaseHistory $order): void
    {
        if (Schema::hasTable('purchase_stock_reservations')) {
            $reservations = PurchaseStockReservation::where('purchase_history_id', $order->id)
                ->orderBy('medicine_id')
                ->lockForUpdate()
                ->get();

            if ($reservations->isNotEmpty()) {
                foreach ($reservations as $reservation) {
                    $product = Medicine::whereKey($reservation->medicine_id)->lockForUpdate()->first();
                    if (!$product) {
                        continue;
                    }
                    $remainingUnits = max(0, $product->getPhysicalStockUnits() - (int) $reservation->quantity_units);
                    MedicineStockLedger::withContext(
                        'sale',
                        $order->id,
                        fn () => $product->update([
                            'stock_base_units' => $remainingUnits,
                            'stok' => intdiv($remainingUnits, max(1, $product->units_per_box)),
                            'terjual' => (int) $product->terjual + (int) $reservation->sale_quantity,
                        ]),
                        -(int) $reservation->quantity_units,
                        'Penjualan dicatat saat pesanan disetujui.'
                    );
                }
                PurchaseStockReservation::where('purchase_history_id', $order->id)->delete();
                return;
            }
        }

        $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
        $items = is_array($items) ? $items : [];
        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
            if ($productId < 1 || $qty < 1) {
                continue;
            }
            $product = Medicine::whereKey($productId)->lockForUpdate()->first();
            if (!$product) {
                continue;
            }
            $factor = max(1, $product->getSaleUnitFactor($item['sale_unit'] ?? 'box'));
            $remainingUnits = max(0, $product->getPhysicalStockUnits() - ($qty * $factor));
            MedicineStockLedger::withContext(
                'sale',
                $order->id,
                fn () => $product->update([
                    'stock_base_units' => $remainingUnits,
                    'stok' => intdiv($remainingUnits, max(1, $product->units_per_box)),
                    'terjual' => (int) $product->terjual + $qty,
                ]),
                0,
                'Penjualan dicatat saat pesanan disetujui.'
            );
        }
    }

    /**
     * Export purchase history to XLSX
     */
    public function exportPurchaseHistory()
    {
        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        $orders = PurchaseHistory::with('cashier')->latest()->get();

        $headers = [
            'ID',
            'Tanggal',
            'Nama Pembeli',
            'Jenis',
            'Saluran Transaksi',
            'Status Pembayaran',
            'Kasir',
            'Waktu Pembayaran',
            'Kontak',
            'Alamat',
            'Kecamatan',
            'Kota',
            'SIA',
            'SIPA',
            'Nama Produk',
            'Jumlah',
            'Harga Satuan (Rp)',
            'Subtotal (Rp)',
            'Status Persetujuan',
            'Total Pesanan (Rp)',
        ];

        $widths = [8, 18, 24, 10, 18, 18, 22, 18, 16, 30, 18, 18, 18, 18, 30, 8, 20, 20, 18, 20];

        $rows       = [];
        $grandTotal = 0;

        foreach ($orders as $order) {
            $itemsArray = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
            $itemsArray = is_array($itemsArray) ? $itemsArray : [];

            if (count($itemsArray) > 0) {
                foreach ($itemsArray as $index => $item) {
                    $nama     = $item['nama_obat'] ?? $item['name'] ?? '';
                    $qty      = $item['quantity']  ?? $item['qty']  ?? 0;
                    $harga    = $item['harga']      ?? $item['price'] ?? 0;
                    $subtotal = $qty * $harga;

                    $rows[] = [
                        $index === 0 ? $order->id                                              : '',
                        $index === 0 ? $order->created_at->format('d/m/Y H:i')                : '',
                        $index === 0 ? ($order->buyer_name ?? '')                             : '',
                        $index === 0 ? (match ($order->buyer_type) { 'apotik' => 'Apotik', 'dokter' => 'Dokter / Klinik', 'pbf' => 'B2B / PBF', default => 'Umum' }) : '',
                        $index === 0 ? ($order->transaction_channel === 'offline' ? 'Offline / Kios' : 'Online') : '',
                        $index === 0 ? (match ($order->payment_status) { 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan', default => 'Menunggu pembayaran' }) : '',
                        $index === 0 ? ($order->cashier?->name ?? '') : '',
                        $index === 0 ? ($order->paid_at?->format('d/m/Y H:i') ?? '') : '',
                        $index === 0 ? ($order->phone ?? '')                                   : '',
                        $index === 0 ? ($order->address ?? '')                                 : '',
                        $index === 0 ? ($order->kecamatan ?? '')                               : '',
                        $index === 0 ? ($order->kota ?? '')                                    : '',
                        $index === 0 ? ($order->sia ?? '')                                     : '',
                        $index === 0 ? ($order->sipa ?? '')                                    : '',
                        $nama,
                        $qty,
                        $harga,
                        $subtotal,
                        $index === 0 ? $order->approval_label : '',
                        $index === 0 ? $order->effective_total : '',
                    ];
                }
            } else {
                $rows[] = [
                    $order->id,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->buyer_name ?? '',
                    match ($order->buyer_type) { 'apotik' => 'Apotik', 'dokter' => 'Dokter / Klinik', 'pbf' => 'B2B / PBF', default => 'Umum' },
                    $order->transaction_channel === 'offline' ? 'Offline / Kios' : 'Online',
                    match ($order->payment_status) { 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan', default => 'Menunggu pembayaran' },
                    $order->cashier?->name ?? '',
                    $order->paid_at?->format('d/m/Y H:i') ?? '',
                    $order->phone    ?? '',
                    $order->address  ?? '',
                    $order->kecamatan ?? '',
                    $order->kota     ?? '',
                    $order->sia      ?? '',
                    $order->sipa     ?? '',
                    '',
                    0,
                    0,
                    0,
                    $order->approval_label,
                    $order->effective_total,
                ];
            }

            if (!$order->is_pbf_purchase_order && $order->receipt_status !== 'cancelled' && $order->payment_status !== 'cancelled' && $order->approval_status !== 'rejected') {
                $grandTotal += $order->effective_total;
            }
        }

        // Baris kosong pemisah
        $blankRow = array_fill(0, count($headers), '');
        $rows[] = $blankRow;

        // Baris GRAND TOTAL — tandai dengan prefix khusus agar XlsxWriter tahu ini total row
        $totalRow = array_fill(0, count($headers), '');
        $totalRow[0] = '__TOTAL__';
        $totalRow[1] = 'Total ' . $orders->count() . ' Transaksi';
        $totalRow[count($headers) - 1] = $grandTotal;
        $rows[] = $totalRow;

        $filename = 'purchase_history_' . date('Y-m-d_His') . '.xlsx';

        return \App\Helpers\XlsxWriter::download($filename, $headers, $rows, $widths);
    }

    private function revenueOrders()
    {
        $query = PurchaseHistory::query();
        if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
            $query->where(function ($q) {
                $q->whereNull('is_pbf_purchase_order')
                    ->orWhere('is_pbf_purchase_order', false);
            });
        }
        if (Schema::hasColumn('purchase_histories', 'receipt_status')) {
            $query->where(function ($q) {
                $q->whereNull('receipt_status')
                    ->orWhere('receipt_status', '!=', 'cancelled');
            });
        }
        if (Schema::hasColumn('purchase_histories', 'approval_status')) {
            $query->where(function ($q) {
                $q->whereNull('approval_status')
                    ->orWhere('approval_status', '!=', 'rejected');
            });
        }
        if (Schema::hasColumn('purchase_histories', 'payment_status')) {
            $query->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'cancelled');
            });
        }
        if (Schema::hasColumn('purchase_histories', 'transaction_channel')
            && Schema::hasColumn('purchase_histories', 'payment_status')) {
            $query->where(function ($orders) {
                $orders->where('transaction_channel', 'online')
                    ->orWhere('payment_status', 'paid');
            });
        }

        return $query;
    }
}
