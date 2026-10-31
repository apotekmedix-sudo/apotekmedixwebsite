<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\PurchasePaymentSetting;
use App\Models\Medicine;
use App\Models\Customer;
use App\Models\SupplierPurchaseOrder;
use App\Models\SupplierPurchaseOrderItem;
use App\Models\ShoppingAccessCode;
use App\Models\DoctorConsultation;
use App\Models\DiscountCode;
use App\Models\PurchaseStockReservation;
use App\Services\MedicineStockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'delivery_method' => ['nullable', 'string', 'in:delivery,pickup'],
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
            'items.*.sale_unit' => ['nullable', 'string', 'in:box'],
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
            'payment_proof'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'payment_term_days' => ['nullable', 'integer', 'min:1'],
            'transaction_channel' => ['nullable', 'string', 'in:online'],
            'price_channel'   => ['nullable', 'string', 'in:b2c,internal,b2b'],
            'prescription_confirmation_token' => ['nullable', 'string', 'size:48'],
            'consultation_prescription_number' => ['nullable', 'string', 'size:16', 'regex:/^RSP-[A-Z0-9]{12}$/'],
            'is_pbf_purchase_order' => ['nullable', 'boolean'],
            'discount_code'   => ['nullable', 'string', 'max:64'],
        ], [
            'is_pbf_purchase_order.boolean' => 'Status surat pesanan PBF harus bernilai benar atau salah.',
        ]);

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
            $purchaseOrderType = $data['purchase_order_type'];
            $products = Medicine::whereIn(
                'id',
                collect($data['items'])->pluck('id')->filter()->unique()
            )->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['id']);
                if (!$product || $product->getPurchaseOrderCategory() === $purchaseOrderType) {
                    continue;
                }

                $orderTypeName = match ($purchaseOrderType) {
                    'oot' => 'SP OOT',
                    'precursor' => 'SP Prekursor',
                    default => 'SP Reguler',
                };
                $message = "{$product->nama_obat} termasuk kategori {$product->getPurchaseOrderCategoryLabel()} dan tidak dapat dimasukkan ke {$orderTypeName}.";

                return response()->json([
                    'message' => 'Kategori produk tidak sesuai dengan jenis surat pesanan.',
                    'errors' => ['purchase_order_type' => [$message]],
                ], 422);
            }
        }

        $requestedPriceChannel = $data['price_channel'] ?? null;
        if ($isPbfPurchaseOrder) {
            $data['buyer_name'] = 'Apotek Medix';
            $data['purchase_order_profile'] = $data['purchase_order_profile'] ?? 'medix';
            $requestedPriceChannel = 'b2b';
            $deliveryMethod = null;
        } else {
            $data['purchase_order_type'] = 'regular';
            $data['purchase_order_profile'] = 'medix';
            $data['pbf_phone'] = null;
            $deliveryMethod = $data['delivery_method']
                ?? (str_starts_with(strtolower((string) ($data['address'] ?? '')), 'ambil di apotek medix') ? 'pickup' : 'delivery');
            unset($data['delivery_method']);
        }

        $channel = $data['transaction_channel'] ?? 'online';
        if (!$isPbfPurchaseOrder && $data['buyer_type'] === 'umum' && $deliveryMethod === 'pickup') {
            $channel = 'offline';
        }
        if (!$isPbfPurchaseOrder && in_array($data['buyer_type'], ['apotik', 'dokter'], true)) {
            $data['buyer_name'] = $data['requester_name'] ?? ($data['buyer_name'] ?? null);
        }

        $paymentMethod = trim((string) ($data['payment_method'] ?? ''));
        $paymentTermDays = (int) ($data['payment_term_days'] ?? 0);
        $requiresPaymentProof = !$isPbfPurchaseOrder
            && in_array(strtolower($paymentMethod), ['qris', 'transfer'], true);
        if (
            $data['buyer_type'] === 'umum'
            && $paymentMethod === 'Tempo'
        ) {
            return response()->json([
                'message' => 'Metode pembayaran Tempo tidak tersedia untuk transaksi B2C.',
                'errors' => ['payment_method' => ['Pilih QRIS, Transfer, atau Tunai untuk transaksi B2C.']],
            ], 422);
        }

        $paymentSettings = PurchasePaymentSetting::query()->first();
        $normalizedPaymentMethod = strtolower($paymentMethod);
        if (
            !$isPbfPurchaseOrder
            && $normalizedPaymentMethod === 'qris'
            && !$paymentSettings?->hasQris()
        ) {
            return response()->json([
                'message' => 'Pembayaran QRIS belum tersedia. Silakan pilih metode lain atau hubungi admin.',
                'errors' => ['payment_method' => ['QRIS belum dikonfigurasi.']],
            ], 422);
        }
        if (
            !$isPbfPurchaseOrder
            && $normalizedPaymentMethod === 'transfer'
            && !$paymentSettings?->hasBankTransfer()
        ) {
            return response()->json([
                'message' => 'Pembayaran transfer belum tersedia. Silakan pilih metode lain atau hubungi admin.',
                'errors' => ['payment_method' => ['Rekening transfer belum dikonfigurasi.']],
            ], 422);
        }
        if ($requiresPaymentProof && !$request->hasFile('payment_proof')) {
            return response()->json([
                'message' => 'Unggah bukti pembayaran QRIS atau transfer.',
                'errors' => ['payment_proof' => ['Bukti pembayaran wajib diunggah untuk pembayaran QRIS atau transfer.']],
            ], 422);
        }

        if ($normalizedPaymentMethod === 'qris' && $paymentSettings) {
            $data['payment_qris_image_path'] = $paymentSettings->qris_image_path;
            $data['payment_instructions'] = $paymentSettings->payment_instructions;
        } elseif ($normalizedPaymentMethod === 'transfer' && $paymentSettings) {
            $data['payment_bank_name'] = $paymentSettings->bank_name;
            $data['payment_account_number'] = $paymentSettings->account_number;
            $data['payment_account_holder'] = $paymentSettings->account_holder;
            $data['payment_instructions'] = $paymentSettings->payment_instructions;
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
        $hasB2cAccess = ShoppingAccessCode::hasActiveB2cSession();
        $hasInternalAccess = !$isPbfPurchaseOrder
            && ShoppingAccessCode::hasActiveInternalSession();
        $hasB2bAccess = ShoppingAccessCode::hasActiveB2bSession();
        $consultationPrescriptionNumber = $data['consultation_prescription_number'] ?? null;
        $consultationPrescription = $consultationPrescriptionNumber
            ? DoctorConsultation::query()
                ->where('prescription_number', $consultationPrescriptionNumber)
                ->first()
            : null;
        $consultationCheckoutId = $request->session()->get('consultation_prescription_checkout.id');
        $consultationCheckoutNumber = $request->session()->get('consultation_prescription_checkout.number');
        $isConsultationCheckout = $consultationCheckoutId !== null;
        $prescriptionError = $isConsultationCheckout && !$consultationPrescriptionNumber
            ? 'Tautan checkout resep tidak valid. Buka kembali tautan yang dikirim admin.'
            : ($consultationPrescriptionNumber
            ? $this->consultationPrescriptionError(
                $consultationPrescription,
                $items,
                $requestedPriceChannel,
                $data['buyer_type'],
                $isPbfPurchaseOrder,
                $isConsultationCheckout ? (int) $consultationCheckoutId : null,
                $consultationCheckoutNumber,
                $data['buyer_name'] ?? '',
                $data['phone'] ?? '',
                $data['address'] ?? '',
                $deliveryMethod
            )
            : null);
        if ($prescriptionError !== null) {
            return response()->json([
                'message' => $prescriptionError,
                'errors' => ['consultation_prescription_number' => [$prescriptionError]],
            ], 422);
        }
        $data['consultation_prescription_number'] = $consultationPrescriptionNumber;
        $approvedPrescriptionIds = collect($consultationPrescription?->prescribed_items ?? [])
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $approvedPrescriptionItems = collect($consultationPrescription?->prescribed_items ?? [])
            ->keyBy(fn (array $item): int => (int) $item['id']);
        foreach ($items as $index => $item) {
            $approvedItem = $approvedPrescriptionItems->get((int) ($item['id'] ?? 0));
            if ($approvedItem) {
                $items[$index]['catatan'] = $approvedItem['instructions'];
            }
        }
        $requiresRedLogoConfirmation = !$isPbfPurchaseOrder
            && $data['buyer_type'] === 'umum'
            && $hasB2cAccess
            && !in_array($requestedPriceChannel, ['internal', 'b2b'], true);
        $redLogoProductIds = $requiresRedLogoConfirmation
            ? Medicine::whereIn(
                'id',
                collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()
            )->get()
                ->filter(fn (Medicine $medicine) => $medicine->hasRedLogo())
                ->pluck('id')
                ->reject(fn ($id) => in_array((int) $id, $approvedPrescriptionIds, true))
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all()
            : [];
        if ($redLogoProductIds !== []) {
            $confirmedIds = collect($request->session()->get('b2c_red_prescription_confirmed_ids', []))
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();
            $sessionConfirmationToken = (string) $request->session()->get('b2c_red_prescription_confirmation_token', '');
            $providedConfirmationToken = (string) ($data['prescription_confirmation_token'] ?? '');
            if (
                array_diff($redLogoProductIds, $confirmedIds) !== []
                || $sessionConfirmationToken === ''
                || $providedConfirmationToken === ''
                || !hash_equals($sessionConfirmationToken, $providedConfirmationToken)
            ) {
                $redNames = Medicine::whereIn('id', $redLogoProductIds)->orderBy('nama_obat')->pluck('nama_obat');

                return response()->json([
                    'message' => 'Konfirmasikan kepemilikan resep dokter untuk produk berlogo merah di keranjang sebelum melanjutkan checkout.',
                    'errors' => ['items' => ['Memerlukan resep dokter: ' . $redNames->implode(', ')]],
                ], 422);
            }

            foreach ($items as $index => $item) {
                if (in_array((int) ($item['id'] ?? 0), $redLogoProductIds, true)) {
                    $items[$index]['prescription_confirmed'] = true;
                }
            }
        }

        if (!$isPbfPurchaseOrder && (
            ($requestedPriceChannel === 'internal' && !$hasInternalAccess)
            || ($requestedPriceChannel === 'b2b' && !$hasB2bAccess)
        )) {
            return response()->json([
                'message' => 'Akses harga untuk kanal transaksi ini sudah tidak aktif.',
                'errors' => ['price_channel' => ['Aktifkan kembali kode akses untuk kanal harga yang dipilih.']],
            ], 403);
        }
        if (
            $requestedPriceChannel !== null
            && (
                ($requestedPriceChannel === 'b2b' && $data['buyer_type'] === 'umum')
                || ($requestedPriceChannel !== 'b2b' && $data['buyer_type'] === 'pbf')
            )
        ) {
            return response()->json([
                'message' => 'Jenis transaksi tidak sesuai dengan kanal harga yang dipilih.',
                'errors' => ['price_channel' => ['Gunakan jenis transaksi dan keranjang sesuai kanal B2C, Internal, atau B2B.']],
            ], 422);
        }

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

            $promoChannel = $requestedPriceChannel ?? (
                ($product && $product->kelompok === 'PBF')
                || $data['buyer_type'] === 'pbf'
                || !empty($data['pbf_name'])
                || ($hasB2bAccess && !$hasB2cAccess && ($product?->getChannelBasePrice('b2b') ?? 0) > 0)
                    ? 'b2b'
                    : 'b2c'
            );

            if ($product && $product->kelompok === 'PBF' && $promoChannel !== 'b2b') {
                return response()->json([
                    'message' => 'Produk PBF harus dipesan melalui kanal B2B.',
                    'errors' => ['price_channel' => ['Pilih keranjang dan kanal harga B2B untuk produk ini.']],
                ], 422);
            }

            if (
                $product?->hasRedLogo()
                && $promoChannel === 'b2c'
                && !$hasB2bAccess
                && !in_array((int) $product->id, $approvedPrescriptionIds, true)
                && !in_array($data['buyer_type'], ['pbf', 'apotik', 'dokter'], true)
            ) {
                if (!$hasB2cAccess && !$hasInternalAccess) {
                    return response()->json([
                        'message' => 'Aktifkan kode akses B2C atau Internal untuk memesan produk berlogo merah.',
                        'errors' => ['items' => ['Produk berlogo merah memerlukan kode akses B2C atau Internal yang aktif.']],
                    ], 403);
                }
            }

            if ($product && $promoChannel === 'b2b' && $unit !== 'box') {
                return response()->json([
                    'message' => "Kanal {$promoChannel} hanya mendukung pembelian per kemasan.",
                    'errors' => ['items' => ["Produk {$product->nama_obat} hanya dapat dibeli per kemasan pada kanal ini."]],
                ], 422);
            }
            $priceChannel = $requestedPriceChannel ?? (
                $promoChannel === 'b2c' && $hasInternalAccess ? 'internal' : $promoChannel
            );
            $usesInternalPricing = $priceChannel === 'internal';

            if ($product && $usesInternalPricing && $unit !== 'box') {
                return response()->json([
                    'message' => 'Akses Internal hanya mendukung pembelian per kemasan.',
                    'errors' => ['items' => ["Produk {$product->nama_obat} hanya dapat dibeli per kemasan saat akses Internal aktif."]],
                ], 422);
            }

            if ($product) {
                $price = $isPbfPurchaseOrder
                    ? (int) round((float) ($item['harga'] ?? $item['price'] ?? $product->harga_modal))
                    : (int) round($product->getChannelBasePrice($priceChannel, $unit));
                if ($price <= 0) {
                    return response()->json([
                        'message' => "Harga {$priceChannel} untuk produk {$product->nama_obat} belum diatur.",
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
            $promoPercent = $isPbfPurchaseOrder || $usesInternalPricing
                ? 0
                : ($product?->getPromoPercent($promoChannel) ?? 0);
            $promoDiscount = (int) round($price * $promoPercent / 100) * $qty;
            $manualDiscount = $isPbfPurchaseOrder || $usesInternalPricing
                ? 0
                : (int) ($item['potongan'] ?? $item['discount'] ?? 0);
            $discount = min($promoDiscount + $manualDiscount, $subtotal);
            $requestedDiscount = $isPbfPurchaseOrder ? 0 : $discount;
            $originalTotal += $subtotal;
            $discountedTotal += max(0, $subtotal - $discount);

            $items[$index]['harga'] = $price;
            $items[$index]['potongan'] = $requestedDiscount;
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
        $data['price_channel'] = $requestedPriceChannel
            ?? (in_array($data['buyer_type'], ['apotik', 'dokter', 'pbf'], true) ? 'b2b' : 'b2c');
        $data['is_pbf_purchase_order'] = $isPbfPurchaseOrder;
        $data['user_id'] = Auth::check() && Auth::user()->isUser() && !$isPbfPurchaseOrder
            ? Auth::id()
            : null;
        $data['payment_status'] = 'pending';
        $data['cashier_id'] = null;
        $data['paid_at'] = null;
        unset($data['payment_proof']);
        $paymentProofPath = null;
        if ($requiresPaymentProof) {
            $paymentProofPath = $request->file('payment_proof')->store('purchase-payment-proofs', 'local');
            if (!$paymentProofPath) {
                throw new \RuntimeException('Bukti pembayaran gagal disimpan.');
            }
        }
        $data['payment_proof_path'] = $paymentProofPath;

        $history = DB::transaction(function () use (
            $data,
            $items,
            $isPbfPurchaseOrder,
            $consultationPrescriptionNumber,
            $requestedPriceChannel,
            $approvedPrescriptionIds,
            $isConsultationCheckout,
            $consultationCheckoutId,
            $consultationCheckoutNumber,
            $deliveryMethod
        ) {
            $lockedPrescription = null;
            if ($consultationPrescriptionNumber) {
                $lockedPrescription = DoctorConsultation::query()
                    ->where('prescription_number', $consultationPrescriptionNumber)
                    ->lockForUpdate()
                    ->first();
                $prescriptionError = $this->consultationPrescriptionError(
                    $lockedPrescription,
                    $items,
                    $requestedPriceChannel,
                    $data['buyer_type'],
                    $isPbfPurchaseOrder,
                    $isConsultationCheckout ? (int) $consultationCheckoutId : null,
                    $consultationCheckoutNumber,
                    $data['buyer_name'] ?? '',
                    $data['phone'] ?? '',
                    $data['address'] ?? '',
                    $deliveryMethod
                );
                if ($prescriptionError !== null) {
                    return ['error' => $prescriptionError];
                }
            }

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
                if (
                    $lockedPrescription
                    && in_array($productId, $approvedPrescriptionIds, true)
                    && (
                        $product->kelompok === 'PBF'
                        || $product->getChannelBasePrice('b2c') <= 0
                    )
                ) {
                    return ['error' => 'Produk resep sudah tidak tersedia di katalog B2C. Hubungi dokter untuk konsultasi ulang.'];
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
                    MedicineStockLedger::recordReservedChange(
                        $productId,
                        $quantity,
                        $history->id,
                        'reserve',
                        'Stok ditahan untuk pesanan.'
                    );
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
            if ($lockedPrescription) {
                $lockedPrescription->update(['prescription_used_at' => now()]);
            }

            return ['history' => $history];
        });

        if (isset($history['error'])) {
            if ($paymentProofPath && !Storage::disk('local')->delete($paymentProofPath)) {
                throw new \RuntimeException('Pesanan tidak dapat dibuat dan berkas bukti pembayaran gagal dibersihkan.');
            }
            return response()->json([
                'message' => $history['error'],
                'errors' => ['items' => [$history['error']]],
            ], 422);
        }

        if ($redLogoProductIds !== []) {
            $request->session()->forget([
                'b2c_red_prescription_confirmed_ids',
                'b2c_red_prescription_confirmation_token',
            ]);
        }
        if ($isConsultationCheckout) {
            $request->session()->forget('consultation_prescription_checkout');
        }

        $order = $history['history'];
        $response = [
            'success' => true,
            'id' => $order->id,
            'invoice_url' => URL::temporarySignedRoute(
                'orders.invoice',
                now()->addDays(30),
                ['order' => $order->id]
            ),
        ];
        if ($paymentProofPath) {
            $response['payment_proof_url'] = URL::temporarySignedRoute(
                'orders.payment-proof',
                now()->addDays(30),
                ['order' => $order->id]
            );
        }

        return response()->json($response);
    }

    private function consultationPrescriptionError(
        ?DoctorConsultation $consultation,
        array $items,
        ?string $priceChannel,
        string $buyerType,
        bool $isPbfPurchaseOrder,
        ?int $checkoutConsultationId = null,
        ?string $checkoutPrescriptionNumber = null,
        string $buyerName = '',
        string $phone = '',
        string $address = '',
        ?string $deliveryMethod = null
    ): ?string {
        if (!$consultation) {
            return 'Nomor resep konsultasi tidak ditemukan.';
        }
        if (
            $isPbfPurchaseOrder
            || $buyerType !== 'umum'
            || $priceChannel !== 'b2c'
            || $consultation->payment_channel !== 'online'
            || $consultation->payment_status !== 'paid'
            || !in_array($consultation->status, ['awaiting_whatsapp', 'active', 'completed'], true)
            || $consultation->prescription_approved_at === null
        ) {
            return 'Nomor resep ini tidak berlaku untuk transaksi B2C Online.';
        }
        if (
            $checkoutConsultationId !== null
            && (
                $consultation->id !== $checkoutConsultationId
                || $consultation->prescription_number !== $checkoutPrescriptionNumber
                || $deliveryMethod !== 'delivery'
                || trim($address) === ''
                || trim($buyerName) !== trim((string) $consultation->patient_name)
                || $this->normalizeIndonesianPhone($phone) !== $this->normalizeIndonesianPhone((string) $consultation->patient_phone)
            )
        ) {
            return 'Checkout resep wajib memakai data pasien dan nomor telepon terdaftar, serta alamat pengiriman lengkap. Pengambilan di apotek tidak tersedia.';
        }
        if ($consultation->prescription_used_at !== null) {
            return 'Nomor resep ini sudah pernah digunakan.';
        }

        $approvedItems = collect($consultation->prescribed_items ?? [])
            ->mapWithKeys(fn (array $item): array => [
                (int) $item['id'] => [
                    'quantity' => (int) $item['quantity'],
                    'sale_unit' => $item['sale_unit'] ?? 'box',
                ],
            ])
            ->sortKeys()
            ->all();
        $requestedItems = collect($items)
            ->groupBy(fn (array $item): int => (int) ($item['id'] ?? 0))
            ->map(fn ($productItems): array => [
                'quantity' => $productItems->sum(fn (array $item): int => (int) ($item['quantity'] ?? $item['qty'] ?? 0)),
                'sale_unit' => $productItems->first()['sale_unit'] ?? 'box',
            ])
            ->sortKeys()
            ->all();

        if ($approvedItems === [] || collect($items)->pluck('id')->unique()->count() !== count($items)) {
            return 'Produk resep tidak valid. Muat ulang keranjang lalu coba kembali.';
        }
        foreach ($approvedItems as $productId => $approvedItem) {
            if (($requestedItems[$productId] ?? null) !== $approvedItem) {
                return 'Produk resep dan jumlah yang disetujui dokter harus tetap ada di keranjang.';
            }
        }

        return null;
    }

    private function normalizeIndonesianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return str_starts_with($digits, '8') ? '62' . $digits : $digits;
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

        if ($order->buyer_type === 'umum') {
            return view('orders.b2c-receipt', compact('order', 'items'));
        }

        return view('orders.invoice', compact('order', 'items'));
    }

    public function paymentProof(PurchaseHistory $order)
    {
        $path = $order->payment_proof_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $mimeType = Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="bukti-pembayaran-' . $order->id . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
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
        $medicineIds = collect($items)
            ->filter(fn (array $item) => ($item['harga_modal'] ?? $item['harga'] ?? $item['price'] ?? null) === null && !empty($item['id']))
            ->pluck('id')
            ->unique()
            ->all();
        $currentPrices = $medicineIds === []
            ? collect()
            : Medicine::whereIn('id', $medicineIds)->pluck('harga_modal', 'id');

        $purchaseOrder = new SupplierPurchaseOrder([
            'order_number' => ($order->purchase_order_profile === 'medikpedia' ? 'MDK' : 'MDX') . '/SP/' . strtoupper($order->purchase_order_type ?: 'regular') . '/'
                . $order->created_at?->format('Ymd') . '/'
                . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            'ordered_at' => $order->created_at,
            'purchase_order_type' => $order->purchase_order_type ?: 'regular',
            'purchase_order_profile' => $order->purchase_order_profile ?: 'medix',
            'requester_name' => $order->requester_name,
            'apj' => $order->apj,
            'sia' => $order->sia,
            'sipa' => $order->sipa,
            'supplier_name' => $order->pbf_name ?: 'PBF tidak tercatat',
            'supplier_address' => $order->pbf_address,
            'supplier_phone' => $order->pbf_phone,
        ]);
        $purchaseOrder->setRelation('items', collect($items)->map(function (array $item) use ($currentPrices): SupplierPurchaseOrderItem {
            $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
            $unitCost = $item['harga_modal'] ?? $item['harga'] ?? $item['price'] ?? null;
            if ($unitCost === null && !empty($item['id'])) {
                $unitCost = $currentPrices->get($item['id'], 0);
            }
            $unitCost = (float) $unitCost;

            return new SupplierPurchaseOrderItem([
                'product_name' => $item['nama_obat'] ?? $item['name'] ?? 'Produk',
                'supplier_code' => $item['supplier_code'] ?? null,
                'active_ingredient' => $item['komposisi'] ?? $item['active_ingredient'] ?? null,
                'dosage_form' => $item['sediaan'] ?? $item['dosage_form'] ?? null,
                'strength' => $item['dosis'] ?? $item['strength'] ?? null,
                'unit_label' => $item['unit_label'] ?? $item['package_unit_label'] ?? 'Kemasan',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => (float) ($item['line_total'] ?? $item['subtotal'] ?? ($unitCost * $quantity)),
                'requested_discount' => (float) ($item['potongan'] ?? $item['discount'] ?? 0),
            ]);
        }));

        return view('admin.supplier-procurement.purchase-order', ['order' => $purchaseOrder]);
    }
}
