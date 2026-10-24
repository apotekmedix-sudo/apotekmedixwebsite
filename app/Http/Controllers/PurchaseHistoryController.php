<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\Medicine;
use App\Models\Customer;
use App\Models\ShoppingAccessCode;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class PurchaseHistoryController extends Controller
{
    public function store(Request $request)
    {
        $isPbfPurchaseOrder = $request->boolean('is_pbf_purchase_order');
        if ($isPbfPurchaseOrder) {
            abort_unless(Auth::check() && Auth::user()->isAdmin(), 403);
        }

        if ($request->input('transaction_channel') === 'offline') {
            abort_unless(
                (int) $request->session()->get('internal_shopping_expires_at', 0) > now()->timestamp
                    && ShoppingAccessCode::matchesSession(
                        ShoppingAccessCode::CHANNEL_INTERNAL,
                        $request->session()->get('internal_shopping_code_hash')
                    ),
                403,
                'Kode akses belanja internal diperlukan untuk membuat transaksi.'
            );
        }

        $data = $request->validate([
            'buyer_type'      => ['required', 'string', 'in:umum,apotik,dokter,pbf'],
            'buyer_name'      => ['required_unless:buyer_type,apotik,dokter', 'nullable', 'string', 'max:255'],
            'requester_name'  => ['nullable', 'string', 'max:255'],
            'outlet_name'     => ['nullable', 'string', 'max:255'],
            'pbf_name'        => ['nullable', 'string', 'max:255'],
            'pbf_address'     => ['nullable', 'string'],
            'phone'           => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string'],
            'kecamatan'       => ['nullable', 'string', 'max:255'],
            'kota'            => ['nullable', 'string', 'max:255'],
            'sia'             => ['nullable', 'string', 'max:255'],
            'sipa'            => ['nullable', 'string', 'max:255'],
            'no_izin_pbf'     => ['nullable', 'string', 'max:255'],
            'apj'             => ['nullable', 'string', 'max:255'],
            'items'           => ['required_if:transaction_channel,offline', 'array'],
            'items.*.id'      => ['required_if:transaction_channel,offline', 'nullable', 'integer', 'exists:medicines,id'],
            'items.*.nama_obat' => ['nullable', 'string', 'max:255'],
            'items.*.name'     => ['nullable', 'string', 'max:255'],
            'items.*.quantity'=> ['required_if:transaction_channel,offline', 'nullable', 'integer', 'min:1'],
            'items.*.qty'     => ['nullable', 'integer', 'min:1'],
            'items.*.sale_unit' => ['nullable', 'string', 'in:tablet,strip,box'],
            'items.*.harga'   => ['nullable', 'integer', 'min:0'],
            'items.*.price'   => ['nullable', 'integer', 'min:0'],
            'items.*.catatan' => ['nullable', 'string'],
            'items.*.note'    => ['nullable', 'string'],
            'items.*.potongan'=> ['nullable', 'integer', 'min:0'],
            'items.*.discount'=> ['nullable', 'integer', 'min:0'],
            'total'           => ['required', 'integer', 'min:0'],
            'original_total'  => ['nullable', 'integer', 'min:0'],
            'discounted_total'=> ['nullable', 'integer', 'min:0'],
            'approval_status' => ['nullable', 'string', 'in:pending,approved,rejected'],
            'payment_method'  => ['nullable', 'string', 'max:255'],
            'payment_term_days' => ['nullable', 'integer', 'min:1'],
            'transaction_channel' => ['nullable', 'string', 'in:online,offline'],
            'is_pbf_purchase_order' => ['nullable', 'boolean'],
            'discount_code'   => ['nullable', 'string', 'max:64'],
        ]);

        $channel = $data['transaction_channel'] ?? 'online';
        if ($isPbfPurchaseOrder && (
            $data['buyer_type'] !== 'apotik'
            || empty($data['pbf_name'])
            || empty($data['pbf_address'])
            || empty($data['items'])
            || collect($data['items'])->contains(fn ($item) => empty($item['id']) || empty($item['quantity']))
        )) {
            return response()->json([
                'message' => 'Data pemesanan PBF tidak lengkap.',
                'errors' => ['pbf_name' => ['Nama dan alamat PBF wajib diisi.']],
            ], 422);
        }

        if ($channel === 'offline') {
            if ($data['buyer_type'] !== 'umum' || empty($data['buyer_name']) || empty($data['phone'])) {
                return response()->json([
                    'message' => 'Nama dan nomor telepon wajib diisi untuk pesanan internal.',
                    'errors' => ['buyer_name' => ['Nama dan nomor telepon wajib diisi untuk pesanan internal.']],
                ], 422);
            }
            $data['payment_method'] = 'Tunai';
            $data['address'] = null;
            $data['kecamatan'] = null;
            $data['kota'] = null;
        }

        if (in_array($data['buyer_type'], ['apotik', 'dokter'], true)) {
            $data['buyer_name'] = $data['requester_name'] ?? ($data['buyer_name'] ?? null);
        }

        $paymentMethod = trim((string) ($data['payment_method'] ?? ''));
        $paymentTermDays = (int) ($data['payment_term_days'] ?? 0);
        if (in_array($data['buyer_type'], ['apotik', 'dokter'], true) && $paymentMethod === 'Tempo' && $paymentTermDays < 1) {
            return response()->json([
                'message' => 'Jumlah hari tempo wajib diisi untuk metode pembayaran Tempo.',
                'errors' => ['payment_term_days' => ['Jumlah hari tempo wajib diisi untuk metode pembayaran Tempo.']],
            ], 422);
        }

        if ($paymentMethod !== 'Tempo') {
            $data['payment_term_days'] = null;
        }

        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $originalTotal = 0;
        $discountedTotal = 0;

        foreach ($items as $index => $item) {
            $qty = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
            $unit = $item['sale_unit'] ?? 'box';
            $price = (int) ($item['harga'] ?? $item['price'] ?? 0);
            $product = !empty($item['id']) ? Medicine::find((int) $item['id']) : null;

            if ($product && $product->getSaleUnitFactor($unit) < 1) {
                return response()->json([
                    'message' => "Satuan {$unit} tidak tersedia untuk produk {$product->nama_obat}.",
                    'errors' => ['items' => ["Satuan {$unit} tidak tersedia untuk produk {$product->nama_obat}."]],
                ], 422);
            }

            $promoChannel = $channel === 'offline'
                ? 'internal'
                : (($product && $product->kelompok === 'PBF') || $data['buyer_type'] === 'pbf' || !empty($data['pbf_name'])
                    ? 'b2b'
                    : 'b2c');

            if ($product) {
                $price = $isPbfPurchaseOrder
                    ? (int) round((float) $product->harga_modal)
                    : (int) round($product->getChannelBasePrice($promoChannel, $unit));
                if ($price <= 0) {
                    return response()->json([
                        'message' => "Harga {$promoChannel} untuk produk {$product->nama_obat} belum diatur.",
                        'errors' => ['items' => ["Harga produk {$product->nama_obat} belum diatur untuk kanal ini."]],
                    ], 422);
                }
            }
            $subtotal = $qty * $price;
            $promoPercent = $isPbfPurchaseOrder ? 0 : ($product?->getPromoPercent($promoChannel) ?? 0);
            $promoDiscount = (int) round($price * $promoPercent / 100) * $qty;
            $manualDiscount = $channel === 'offline' || $isPbfPurchaseOrder
                ? 0
                : (int) ($item['potongan'] ?? $item['discount'] ?? 0);
            $discount = min($promoDiscount + $manualDiscount, $subtotal);
            $originalTotal += $subtotal;
            $discountedTotal += max(0, $subtotal - $discount);

            $items[$index]['harga'] = $price;
            $items[$index]['potongan'] = $discount;
            $items[$index]['promo_percent'] = $promoPercent;
            $items[$index]['sale_unit'] = $unit;
            $items[$index]['unit_label'] = match ($unit) {
                'tablet' => 'Tablet',
                'strip' => 'Strip',
                default => $product?->package_unit_label ?? 'Satuan',
            };
        }

        $data['items'] = $items;
        $data['original_total'] = $originalTotal;
        $data['discounted_total'] = $discountedTotal;
        $data['total'] = $discountedTotal;
        $data['approval_status'] = $data['approval_status'] ?? 'pending';
        $data['transaction_channel'] = $channel;
        $data['is_pbf_purchase_order'] = $isPbfPurchaseOrder;
        $data['user_id'] = Auth::check() && Auth::user()->isUser() && $channel === 'online'
            ? Auth::id()
            : null;
        $data['payment_status'] = 'pending';
        $data['cashier_id'] = null;
        $data['paid_at'] = null;

        $history = DB::transaction(function () use ($data, $items, $isPbfPurchaseOrder) {
            $productIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();
            $products = Medicine::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $requiredUnits = [];
            foreach ($items as $item) {
                if (empty($item['id']) || empty($item['quantity'])) {
                    continue;
                }

                $productId = (int) $item['id'];
                $product = $products->get($productId);
                $unitFactor = $product?->getSaleUnitFactor($item['sale_unit'] ?? 'box') ?? 0;
                if (!$product || $unitFactor < 1) {
                    return ['error' => 'Produk atau satuan yang dipilih sudah tidak tersedia. Silakan muat ulang katalog.'];
                }
                $requiredUnits[$productId] = ($requiredUnits[$productId] ?? 0) + ((int) $item['quantity'] * $unitFactor);
            }

            if (!$isPbfPurchaseOrder) {
                foreach ($requiredUnits as $productId => $quantity) {
                    $product = $products->get($productId);
                    if (!$product || (int) $product->stock_base_units < $quantity) {
                        $productName = $product?->nama_obat ?? 'Produk #' . $productId;
                        return ['error' => "Stok {$productName} tidak mencukupi. Stok tersisa: " . ($product?->stock_breakdown ?? '0 satuan') . '.'];
                    }
                }
            }

            if (!$isPbfPurchaseOrder) {
                foreach ($requiredUnits as $productId => $quantity) {
                    $product = $products->get($productId);
                    $remainingUnits = (int) $product->stock_base_units - $quantity;
                    $product->update([
                        'stock_base_units' => $remainingUnits,
                        'stok' => intdiv($remainingUnits, $product->units_per_box),
                    ]);
                }
            }

            $history = PurchaseHistory::create($data);
            if (!empty($data['discount_code'])) {
                $voucher = DiscountCode::where('code', strtoupper(trim((string) $data['discount_code'])))
                    ->lockForUpdate()
                    ->first();
                if (!$voucher || !$voucher->is_active) {
                    return ['error' => 'Kode diskon tidak ditemukan atau sudah tidak aktif.'];
                }
                if (!$voucher->isAvailable()) {
                    return ['error' => "Kuota pemakaian kode diskon {$voucher->code} sudah habis."];
                }
                $voucherAmount = $voucher->calculateDiscount((int) $history->discounted_total);
                $newDiscountedTotal = max(0, (int) $history->discounted_total - $voucherAmount);
                $history->update([
                    'discount_code' => $voucher->code,
                    'discount_code_amount' => $voucherAmount,
                    'discounted_total' => $newDiscountedTotal,
                    'total' => $newDiscountedTotal,
                ]);
                $voucher->increment('used_count');
            }
            $customer = Customer::syncFromOrder($history);
            $history->update(['customer_id' => $customer->id]);

            return ['history' => $history];
        });

        if (isset($history['error'])) {
            return response()->json([
                'message' => $history['error'],
                'errors' => ['items' => [$history['error']]],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'id' => $history['history']->id,
            'invoice_url' => URL::temporarySignedRoute(
                'orders.invoice',
                now()->addDays(30),
                ['order' => $history['history']->id]
            ),
        ]);
    }

    public function validateDiscountCode(Request $request)
    {
        $rawCode = strtoupper(preg_replace('/\s+/', '', (string) $request->input('code', '')));
        $subtotal = max(0, (int) $request->input('subtotal', 0));

        if ($rawCode === '') {
            return response()->json([
                'valid' => false,
                'message' => 'Masukkan kode diskon terlebih dahulu.',
            ], 422);
        }

        $voucher = DiscountCode::where('code', $rawCode)->first();
        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode diskon tidak ditemukan.',
            ], 404);
        }

        if (!$voucher->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode diskon ini sedang tidak aktif.',
            ], 422);
        }

        if (!$voucher->isAvailable()) {
            return response()->json([
                'valid' => false,
                'message' => 'Kuota pemakaian kode diskon ini sudah habis.',
            ], 422);
        }

        $discountAmount = $voucher->calculateDiscount($subtotal);

        return response()->json([
            'valid' => true,
            'code' => $voucher->code,
            'description' => $voucher->description,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (int) $voucher->discount_value,
            'discount_amount' => $discountAmount,
            'is_unlimited' => (bool) $voucher->is_unlimited,
            'remaining_quota' => $voucher->remainingQuota(),
            'message' => "Kode diskon {$voucher->code} ({$voucher->formatted_value}) berhasil diterapkan.",
        ]);
    }

    public function invoice(PurchaseHistory $order)
    {
        $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
        $items = is_array($items) ? $items : [];
        $productIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique();
        $products = Medicine::whereIn('id', $productIds)->get()->keyBy('id');
        $items = array_map(function (array $item) use ($products): array {
            $product = $products->get((int) ($item['id'] ?? 0));
            if ($product) {
                $item['nama_obat'] = $item['nama_obat'] ?? $item['name'] ?? $product->nama_obat;
                if (!isset($item['harga']) && !isset($item['price'])) {
                    $item['harga'] = (int) ($product->harga_modal ?: $product->harga);
                }
            }
            return $item;
        }, $items);

        return view('orders.invoice', compact('order', 'items'));
    }

    public function purchaseOrder(PurchaseHistory $order)
    {
        $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
        $items = is_array($items) ? $items : [];
        $items = array_map(function (array $item): array {
            if (!empty($item['id'])) {
                $item['harga_modal'] = Medicine::whereKey($item['id'])->value('harga_modal')
                    ?: ($item['harga'] ?? $item['price'] ?? 0);
            }
            return $item;
        }, $items);

        return view('admin.purchase-order', compact('order', 'items'));
    }
}
