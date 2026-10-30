<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\PurchaseStockReservation;
use App\Models\DoctorConsultation;
use App\Services\MedicineStockLedger;
use Illuminate\Support\Facades\DB;
use App\Constants\Companies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        $totalOmzet    = Schema::hasTable('purchase_histories')
            ? $this->revenueOrders()->get()->sum(fn ($order) => $order->effective_total)
            : 0;
        $pendingConsultations = $this->pendingConsultationCount();
        $recentOrders  = Schema::hasTable('purchase_histories') ? PurchaseHistory::latest()->limit(5)->get() : collect();

        return view('admin.dashboard', compact(
            'totalProduk', 'totalStok', 'lowStok', 'latestProduk',
            'perKategori', 'totalOmzet', 'recentOrders', 'pendingConsultations'
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
            'pendingConsultations' => $this->pendingConsultationCount(),
            'latestProduk'  => $latestProduk,
        ]);
    }

    private function pendingConsultationCount(): int
    {
        if (!Schema::hasTable('doctor_consultations')) {
            return 0;
        }

        return DoctorConsultation::query()
            ->where('status', 'awaiting_payment')
            ->where('payment_status', 'pending')
            ->where('payment_due_at', '>', now())
            ->count();
    }

    public function purchaseHistory(Request $request)
    {
        $tab = $request->query('tab') === 'purchases' ? 'purchases' : 'sales';
        $orders = null;
        $supplierOrders = null;
        $legacyPbfOrders = null;
        $supplierNames = collect();
        $total_omzet = 0;
        $total_pembelian = 0;
        $fulfillment = 'all';

        if ($tab === 'sales') {
            $fulfillment = $request->validate([
                'fulfillment' => ['nullable', 'in:all,internal,b2b,offline,online'],
            ])['fulfillment'] ?? 'all';
            $ordersQuery = Schema::hasTable('purchase_histories')
                ? PurchaseHistory::with('cashier')->latest()->orderByDesc('id')
                : PurchaseHistory::query()->whereRaw('1 = 0');
            if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
                $ordersQuery->where(fn ($query) => $query->whereNull('is_pbf_purchase_order')->orWhere('is_pbf_purchase_order', false));
            }
            $this->applyConfirmedSalesFilter($ordersQuery);
            $this->applySalesFulfillmentFilter($ordersQuery, $fulfillment);
            $orders = $ordersQuery->paginate(20)->withQueryString();
            $total_omzet = $this->revenueOrders()->get()->sum(fn ($order) => $order->effective_total);
        } else {
            [$purchaseRows, $supplierNames] = $this->supplierPurchaseHistoryRows($request);

            $perPage = 20;
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $supplierOrders = new \Illuminate\Pagination\LengthAwarePaginator(
                $purchaseRows->forPage($currentPage, $perPage)->values(),
                $purchaseRows->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $legacyPbfOrders = collect();
            $total_pembelian = \App\Models\SupplierPurchaseOrderItem::whereHas(
                'order',
                fn ($query) => $query->whereNotIn('status', ['cancelled', 'returned'])
            )->sum('line_total');
            $total_pembelian += PurchaseHistory::query()
                ->where('is_pbf_purchase_order', true)
                ->whereNotIn('receipt_status', ['cancelled', 'returned'])
                ->sum('total');
        }

        return view('admin.purchase-history', compact('tab', 'orders', 'supplierOrders', 'legacyPbfOrders', 'supplierNames', 'total_omzet', 'total_pembelian', 'fulfillment'));
    }

    private function supplierPurchaseHistoryRows(Request $request): array
    {
        $filters = $request->validate([
            'purchase_date' => ['nullable', 'date'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'product_count' => ['nullable', 'integer', 'min:0'],
            'total_min' => ['nullable', 'numeric', 'min:0'],
            'total_max' => ['nullable', 'numeric', 'min:0'],
            'purchase_status' => ['nullable', 'in:ordered,partially_received,received,returned,cancelled'],
            'purchase_sort' => ['nullable', 'in:oldest,newest'],
        ]);

        $purchaseRows = \App\Models\SupplierPurchaseOrder::with('items')
            ->get()
            ->map(function ($order): array {
                return [
                    'source' => 'supplier',
                    'model' => $order,
                    'date' => $order->ordered_at,
                    'sort_date' => $order->created_at,
                    'order_number' => $order->order_number,
                    'supplier' => $order->supplier_name,
                    'product_count' => $order->items->count(),
                    'total' => (float) $order->items->sum(fn ($item) => (float) $item->line_total),
                    'status' => $order->status,
                ];
            });

        if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
            $legacyRows = PurchaseHistory::query()
                ->where('is_pbf_purchase_order', true)
                ->get()
                ->map(function (PurchaseHistory $order): array {
                    $items = is_array($order->items) ? $order->items : [];
                    $status = match (true) {
                        $order->receipt_status === 'returned' => 'returned',
                        $order->receipt_status === 'cancelled' || $order->approval_status === 'rejected' => 'cancelled',
                        $order->receipt_status === 'partially_received' => 'partially_received',
                        in_array($order->receipt_status, ['verified', 'printed'], true) => 'received',
                        default => 'ordered',
                    };

                    return [
                        'source' => 'legacy',
                        'model' => $order,
                        'date' => $order->created_at,
                        'sort_date' => $order->created_at,
                        'order_number' => ($order->purchase_order_profile === 'medikpedia' ? 'MDK' : 'MDX') . '/SP/' . strtoupper($order->purchase_order_type ?: 'regular') . '/'
                            . $order->created_at?->format('Ymd') . '/'
                            . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                        'supplier' => $order->pbf_name ?: 'PBF tidak tercatat',
                        'product_count' => count($items),
                        'total' => (float) $order->effective_total,
                        'status' => $status,
                    ];
                });
            $purchaseRows = $purchaseRows->concat($legacyRows);
        }

        $supplierNames = $purchaseRows->pluck('supplier')->unique()->sort()->values();
        $purchaseRows = $purchaseRows
            ->filter(function (array $row) use ($filters): bool {
                if (!empty($filters['purchase_date']) && $row['date']?->format('Y-m-d') !== $filters['purchase_date']) {
                    return false;
                }
                if (!empty($filters['order_number']) && !Str::contains(Str::lower($row['order_number']), Str::lower(trim($filters['order_number'])))) {
                    return false;
                }
                if (!empty($filters['supplier']) && !Str::contains(Str::lower($row['supplier']), Str::lower(trim($filters['supplier'])))) {
                    return false;
                }
                if (isset($filters['product_count']) && $row['product_count'] !== (int) $filters['product_count']) {
                    return false;
                }
                if (isset($filters['total_min']) && $row['total'] < (float) $filters['total_min']) {
                    return false;
                }
                if (isset($filters['total_max']) && $row['total'] > (float) $filters['total_max']) {
                    return false;
                }

                return empty($filters['purchase_status']) || $row['status'] === $filters['purchase_status'];
            })
            ->sort(function (array $left, array $right) use ($filters): int {
                $comparison = ($left['sort_date']?->getTimestamp() ?? 0) <=> ($right['sort_date']?->getTimestamp() ?? 0);
                if ($comparison === 0) {
                    $comparison = ($left['model']->id ?? 0) <=> ($right['model']->id ?? 0);
                }
                if ($comparison === 0) {
                    $comparison = strcmp($left['order_number'], $right['order_number']);
                }

                return ($filters['purchase_sort'] ?? 'newest') === 'oldest' ? $comparison : -$comparison;
            })
            ->values();

        return [$purchaseRows, $supplierNames];
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
        if ($order->is_pbf_purchase_order) {
            return redirect()->route('admin.purchase-history.index', ['tab' => 'sales'])
                ->with('error', 'Pesanan PBF bukan transaksi penjualan dan tidak dapat dihapus dari riwayat penjualan.');
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
            $query = PurchaseHistory::query();
            if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
                $query->where(fn ($builder) => $builder->whereNull('is_pbf_purchase_order')->orWhere('is_pbf_purchase_order', false));
            }
            foreach ($query->lockForUpdate()->get() as $order) {
                $this->releaseOrRestoreOrderStock($order);
                $order->delete();
            }
        });
        return redirect()->route('admin.purchase-history.index', ['tab' => 'sales'])->with('success', 'Semua riwayat penjualan berhasil dihapus dan stok dikembalikan.');
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
    public function exportPurchaseHistory(Request $request)
    {
        if ($request->query('tab') === 'purchases') {
            [$purchaseRows] = $this->supplierPurchaseHistoryRows($request);
            $statusLabels = [
                'ordered' => 'Dipesan',
                'partially_received' => 'Diterima sebagian',
                'received' => 'Diterima lengkap',
                'returned' => 'Dikembalikan',
                'cancelled' => 'Dibatalkan',
            ];
            $rows = $purchaseRows->map(fn (array $row): array => [
                $row['date']?->format('d/m/Y'),
                $row['order_number'],
                $row['supplier'],
                $row['product_count'],
                $row['total'],
                $statusLabels[$row['status']] ?? $row['status'],
            ])->all();

            return \App\Helpers\XlsxWriter::download(
                'riwayat_pembelian_' . now()->format('Y-m-d_His') . '.xlsx',
                ['Tanggal', 'Nomor SP', 'Supplier / PBF', 'Jumlah Produk', 'Total Modal (Rp)', 'Status'],
                $rows,
                [16, 34, 30, 16, 22, 24],
                [3, 4]
            );
        }

        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        $fulfillment = $request->validate([
            'fulfillment' => ['nullable', 'in:all,internal,b2b,offline,online'],
        ])['fulfillment'] ?? 'all';
        $ordersQuery = PurchaseHistory::with('cashier')->latest()->orderByDesc('id');
        if (Schema::hasColumn('purchase_histories', 'is_pbf_purchase_order')) {
            $ordersQuery->where(fn ($query) => $query->whereNull('is_pbf_purchase_order')->orWhere('is_pbf_purchase_order', false));
        }
        $this->applyConfirmedSalesFilter($ordersQuery);
        $this->applySalesFulfillmentFilter($ordersQuery, $fulfillment);
        $orders = $ordersQuery->get();

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
                        $index === 0 ? ($order->transaction_channel === 'offline' ? 'Offline' : 'Online') : '',
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
                    $order->transaction_channel === 'offline' ? 'Offline' : 'Online',
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

    private function applySalesFulfillmentFilter(\Illuminate\Database\Eloquent\Builder $query, string $fulfillment): void
    {
        if ($fulfillment === 'all') {
            return;
        }

        if ($fulfillment === 'internal') {
            $query->where('price_channel', 'internal');
            return;
        }

        if ($fulfillment === 'b2b') {
            $query->where(function ($builder) {
                $builder->whereIn('buyer_type', ['apotik', 'dokter', 'pbf'])
                    ->orWhere('price_channel', 'b2b');
            });
            return;
        }

        if ($fulfillment === 'online') {
            $query->where('buyer_type', 'umum')
                ->where('price_channel', 'b2c')
                ->where('transaction_channel', 'online');
            return;
        }

        if ($fulfillment === 'offline') {
            $query->where('buyer_type', 'umum')
                ->where('price_channel', 'b2c')
                ->where('transaction_channel', 'offline');
        }
    }

    private function applyConfirmedSalesFilter(\Illuminate\Database\Eloquent\Builder $query): void
    {
        if (!Schema::hasColumn('purchase_histories', 'receipt_status')) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('receipt_status', ['verified', 'printed']);

        if (Schema::hasColumn('purchase_histories', 'payment_status')) {
            $query->where(fn ($orders) => $orders->whereNull('payment_status')->orWhere('payment_status', '!=', 'cancelled'));
        }
        if (Schema::hasColumn('purchase_histories', 'approval_status')) {
            $query->where('approval_status', 'approved');
        }
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
        $this->applyConfirmedSalesFilter($query);

        return $query;
    }
}
