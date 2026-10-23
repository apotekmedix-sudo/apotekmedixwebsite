<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Banner;
use App\Models\PurchaseHistory;
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

        $order->update($data);

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

        $order->delete();
        return redirect()->route('admin.purchase-history.index')->with('success', 'Riwayat pembelian berhasil dihapus.');
    }

    /**
     * Delete all purchase history entries
     */
    public function destroyAll()
    {
        if (!\Schema::hasTable('purchase_histories')) {
            return redirect()->route('admin.purchase-history.index')->with('error', 'Tabel purchase_histories tidak tersedia.');
        }

        \App\Models\PurchaseHistory::query()->delete();
        return redirect()->route('admin.purchase-history.index')->with('success', 'Semua riwayat pembelian berhasil dihapus.');
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
                        $index === 0 ? ($order->buyer_type === 'apotik' ? 'Apotik' : 'Umum') : '',
                        $index === 0 ? ($order->transaction_channel === 'offline' ? 'Offline / Kios' : 'Online') : '',
                        $index === 0 ? ($order->payment_status === 'paid' ? 'Lunas' : 'Menunggu pembayaran') : '',
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
                    $order->buyer_type === 'apotik' ? 'Apotik' : 'Umum',
                    $order->transaction_channel === 'offline' ? 'Offline / Kios' : 'Online',
                    $order->payment_status === 'paid' ? 'Lunas' : 'Menunggu pembayaran',
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

            $grandTotal += $order->effective_total;
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
