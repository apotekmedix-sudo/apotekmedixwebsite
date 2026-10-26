<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\Medicine;
use App\Models\Customer;
use App\Models\ShoppingAccessCode;
use App\Models\DiscountCode;
use App\Models\PurchaseStockReservation;
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

        $data = $request->validate([
            'buyer_type'      => ['required', 'string', 'in:umum,apotik,dokter,pbf'],
            'buyer_name'      => ['required_unless:buyer_type,apotik,dokter', 'nullable', 'string', 'max:255'],
            'requester_name'  => ['nullable', 'string', 'max:255'],
            'outlet_name'     => ['nullable', 'string', 'max:255'],
            'pbf_name'        => ['nullable', 'string', 'max:255'],
            'pbf_address'     => ['nullable', 'string'],
            'pbf_phone'       => ['nullable', 'string', 'max:50'],
            'purchase_order_type' => ['nullable', 'string', 'in:regular,oot,precursor'],
            'purchase_order_profile' => ['nullable', 'string', 'in:medix,medikpedia'],
            'phone'           => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string'],
            'kecamatan'       => ['nullable', 'string', 'max:255'],
            'kota'            => ['nullable', 'string', 'max:255'],
            'sia'             => ['nullable', 'string', 'max:255'],
            'sipa'            => ['nullable', 'string', 'max:255'],
            'no_izin_pbf'     => ['nullable', 'string', 'max:255'],
            'apj'             => ['nullable', 'string', 'max:255'],
            'items'           => ['nullable', 'array'],
            'items.*.id'      => ['nullable', 'integer', 'exists:medicines,id'],
            'items.*.nama_obat' => ['nullable', 'string', 'max:255'],
            'items.*.name'     => ['nullable', 'string', 'max:255'],
            'items.*.quantity'=> ['nullable', 'integer', 'min:1'],
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
            'transaction_channel' => ['nullable', 'string', 'in:online'],
            'is_pbf_purchase_order' => ['nullable', 'boolean'],
            'discount_code'   => ['nullable', 'string', 'max:64'],
        ]);

        $channel = $data['transaction_channel'] ?? 'online';
        if ($isPbfPurchaseOrder && (
            $data['buyer_type'] !== 'apotik'
            || empty($data['pbf_name'])
            || empty($data['pbf_address'])
            || empty($data['pbf_phone'])
            || empty($data['purchase_order_type'])
            || empty($data['requester_name'])
            || empty($data['apj'])
            || empty($data['sia'])
            || empty($data['sipa'])
            || empty($data['items'])
            || collect($data['items'])->contains(fn ($item) => empty($item['id']) || empty($item['quantity']))
        )) {
            return response()->json([
                'message' => 'Data surat pesanan belum lengkap.',
                'errors' => ['pbf_name' => ['Lengkapi jenis surat pesanan, nama, alamat, dan nomor telepon distributor, data penandatangan, serta produk.']],
            ], 422);
        }

        if ($isPbfPurchaseOrder) {
            $data['buyer_name'] = 'Apotek Medix';
            $data['purchase_order_profile'] = $data['purchase_order_profile'] ?? 'medix';
        } else {
            $data['purchase_order_type'] = 'regular';
            $data['purchase_order_profile'] = 'medix';
            $data['pbf_phone'] = null;
        }

        if (!$isPbfPurchaseOrder && in_array($data['buyer_type'], ['apotik', 'dokter'], true)) {
            $data['buyer_name'] = $data['requester_name'] ?? ($data['buyer_name'] ?? null);
        }

        $paymentMethod = trim((string) ($data['payment_method'] ?? ''));
        $paymentTermDays = (int) ($data['payment_term_days'] ?? 0);
        if (
            $channel === 'online'
            && $data['buyer_type'] === 'umum'
            && $paymentMethod === 'Tempo'
        ) {
            return response()->json([
                'message' => 'Metode pembayaran Tempo tidak tersedia untuk transaksi B2C.',
                'errors' => ['payment_method' => ['Pilih QRIS, Transfer, atau Cash untuk transaksi B2C.']],
            ], 422);
        }

        if ($paymentMethod === 'Tempo' && $paymentTermDays < 1) {
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
        $hasB2cAccess = null;
        $hasB2bAccess = $request->hasSession()
            && ((int) $request->session()->get('pbf_access_expires_at', 0)) > now()->timestamp
            && ShoppingAccessCode::matchesSession(
                ShoppingAccessCode::CHANNEL_B2B,
                $request->session()->get('pbf_access_code_hash')
            );

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

            $promoChannel = (($product && $product->kelompok === 'PBF') || $data['buyer_type'] === 'pbf' || !empty($data['pbf_name']) || ($hasB2bAccess && ($product?->getChannelBasePrice('b2b') ?? 0) > 0)
                    ? 'b2b'
                    : 'b2c');

            if ($product?->hasRedLogo() && $promoChannel === 'b2c' && !$hasB2bAccess && !in_array($data['buyer_type'], ['pbf', 'apotik', 'dokter'], true)) {
                if ($hasB2cAccess === null) {
                    $b2cAccessExpiresAt = (int) $request->session()->get('b2c_access_expires_at', 0);
                    $hasB2cAccess = $b2cAccessExpiresAt > now()->timestamp
                        && ShoppingAccessCode::matchesSession(
                            ShoppingAccessCode::CHANNEL_B2C,
                            $request->session()->get('b2c_access_code_hash')
                        );
                }

                if (!$hasB2cAccess) {
                    return response()->json([
                        'message' => 'Aktifkan kode akses B2C untuk memesan produk berlogo merah.',
                        'errors' => ['items' => ['Produk berlogo merah memerlukan kode akses B2C aktif.']],
                    ], 403);
                }
            }

            if ($product && $promoChannel === 'b2b' && $unit !== 'box') {
                return response()->json([
                    'message' => "Kanal {$promoChannel} hanya mendukung pembelian per kemasan.",
                    'errors' => ['items' => ["Produk {$product->nama_obat} hanya dapat dibeli per kemasan pada kanal ini."]],
                ], 422);
            }

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
                if ($isPbfPurchaseOrder) {
                    $items[$index]['harga_modal'] = $price;
                    $items[$index]['brand'] = $product->brand ?: $product->kategori;
                    $items[$index]['komposisi'] = $product->komposisi;
                    $items[$index]['sediaan'] = $product->sediaan ?: $product->kemasan ?: $product->satuan;
                    $items[$index]['dosis'] = $product->dosis;
                }
            }
            $subtotal = $qty * $price;
            $promoPercent = $isPbfPurchaseOrder ? 0 : ($product?->getPromoPercent($promoChannel) ?? 0);
            $promoDiscount = (int) round($price * $promoPercent / 100) * $qty;
            $manualDiscount = $isPbfPurchaseOrder
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
            $saleQuantities = [];
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
                $saleQuantities[$productId] = ($saleQuantities[$productId] ?? 0) + (int) $item['quantity'];
            }

            if (!$isPbfPurchaseOrder) {
                $reservedUnits = PurchaseStockReservation::query()
                    ->whereIn('medicine_id', array_keys($requiredUnits))
                    ->selectRaw('medicine_id, SUM(quantity_units) as reserved_units')
                    ->groupBy('medicine_id')
                    ->pluck('reserved_units', 'medicine_id');

                foreach ($requiredUnits as $productId => $quantity) {
                    $product = $products->get($productId);
                    $availableUnits = (int) ($product?->available_stock_units ?? 0)
                        - (int) $reservedUnits->get($productId, 0);
                    if (!$product || $availableUnits < $quantity) {
                        $productName = $product?->nama_obat ?? 'Produk #' . $productId;
                        return ['error' => "Stok {$productName} tidak mencukupi. Stok tersisa: " . ($product?->getStockBreakdownForUnits(max(0, $availableUnits)) ?? '0 satuan') . '.'];
                    }
                }
            }

            $data['stock_committed'] = $isPbfPurchaseOrder || $requiredUnits === [];
            $history = PurchaseHistory::create($data);
            if (!$isPbfPurchaseOrder) {
                foreach ($requiredUnits as $productId => $quantity) {
                    PurchaseStockReservation::create([
                        'purchase_history_id' => $history->id,
                        'medicine_id' => $productId,
                        'quantity_units' => $quantity,
                        'sale_quantity' => $saleQuantities[$productId],
                    ]);
                }
            }

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

            if (!$isPbfPurchaseOrder) {
                $customer = Customer::syncFromOrder($history);
                $history->update(['customer_id' => $customer->id]);
            }

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
                $item['sediaan'] = $item['sediaan'] ?? $product->sediaan ?? $product->sediaan_label;
                $item['dosis'] = $item['dosis'] ?? $product->dosis;
                $item['kemasan'] = $item['kemasan'] ?? $product->kemasan ?? $item['unit_label'] ?? $product->package_unit_label;
                if (!isset($item['harga']) && !isset($item['price'])) {
                    $item['harga'] = (int) ($product->harga_modal ?: $product->harga);
                }
            }
            return $item;
        }, $items);

        if ($order->buyer_type === 'umum' && $order->transaction_channel !== 'offline') {
            return view('orders.b2c-receipt', compact('order', 'items'));
        }

        return view('orders.invoice', compact('order', 'items'));
    }

    public function updatePermit(Request $request, PurchaseHistory $order)
    {
        $validated = $request->validate([
            'sia' => ['required', 'string', 'max:255'],
            'sipa' => ['required', 'string', 'max:255'],
            'requester_name' => ['nullable', 'string', 'max:255'],
        ]);

        $order->update([
            'sia' => trim($validated['sia']),
            'sipa' => trim($validated['sipa']),
            'requester_name' => !empty($validated['requester_name']) ? trim($validated['requester_name']) : $order->requester_name,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sia' => $order->sia,
                'sipa' => $order->sipa,
                'requester_name' => $order->requester_name,
            ]);
        }

        return back()->with('success', 'Data SIA dan SIPA berhasil disimpan.');
    }

    private function _unusedPlaceholder(): void {
    }

    public function purchaseOrder(PurchaseHistory $order)
    {
        $items = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
        $items = is_array($items) ? $items : [];
        $items = array_map(function (array $item): array {
            if (!isset($item['harga_modal']) && !empty($item['id'])) {
                $item['harga_modal'] = Medicine::whereKey($item['id'])->value('harga_modal')
                    ?: ($item['harga'] ?? $item['price'] ?? 0);
            }
            return $item;
        }, $items);

        return view('admin.purchase-order', compact('order', 'items'));
    }
}
