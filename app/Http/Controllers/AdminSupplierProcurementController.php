<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PbfSupplier;
use App\Models\PurchaseHistory;
use App\Models\SupplierProductOffer;
use App\Models\SupplierPurchaseOrder;
use App\Models\SupplierPurchaseOrderItem;
use App\Services\MedicineStockLedger;
use App\Support\SpreadsheetReader;
use App\Helpers\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AdminSupplierProcurementController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = PbfSupplier::query()->orderBy('name')->get();
        $offersQuery = SupplierProductOffer::with(['supplier:id,name', 'medicine:id,sku,nama_obat,satuan,sediaan']);
        if ($request->filled('offer_supplier_id')) {
            $offersQuery->where('supplier_id', $request->integer('offer_supplier_id'));
        }
        if ($request->filled('offer_search')) {
            $search = trim((string) $request->query('offer_search'));
            $offersQuery->where(function ($query) use ($search): void {
                $query->whereHas('medicine', function ($medicineQuery) use ($search): void {
                    $medicineQuery->where('nama_obat', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                })->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhere('supplier_code', 'like', '%' . $search . '%');
            });
        }
        $offers = $offersQuery->latest()
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'offers_page')
            ->withQueryString();

        return view('admin.supplier-procurement.index', compact(
            'suppliers',
            'offers'
        ));
    }

    public function searchMedicines(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'medicine_id' => ['nullable', 'integer', 'exists:medicines,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:pbf_suppliers,id'],
        ]);
        $query = trim((string) ($data['q'] ?? ''));
        if (empty($data['medicine_id']) && mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $medicinesQuery = Medicine::query()->orderBy('nama_obat');
        if (!empty($data['medicine_id'])) {
            $medicinesQuery->whereKey($data['medicine_id']);
        } else {
            $medicinesQuery->where(function ($builder) use ($query): void {
                $builder->where('nama_obat', 'like', '%' . $query . '%')
                    ->orWhere('sku', 'like', '%' . $query . '%');
            });
        }
        $medicines = $medicinesQuery->limit(20)->get(['id', 'sku', 'nama_obat', 'satuan', 'sediaan']);
        $offers = collect();
        if (!empty($data['supplier_id']) && $medicines->isNotEmpty()) {
            $offers = SupplierProductOffer::where('supplier_id', $data['supplier_id'])
                ->whereIn('medicine_id', $medicines->pluck('id'))
                ->get()
                ->keyBy('medicine_id');
        }

        return response()->json([
            'data' => $medicines->map(function (Medicine $medicine) use ($offers): array {
                $offer = $offers->get($medicine->id);

                return [
                    'id' => $medicine->id,
                    'name' => $medicine->nama_obat,
                    'sku' => $medicine->sku,
                    'unit' => $medicine->package_unit_label,
                    'offer' => $offer ? [
                        'supplier_code' => $offer->supplier_code,
                        'purchase_price' => (float) $offer->purchase_price,
                        'minimum_quantity' => $offer->minimum_quantity,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function storeOffer(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:pbf_suppliers,id'],
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'supplier_code' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'gt:0', 'max:999999999999'],
            'minimum_quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);
        $data['supplier_code'] = trim((string) ($data['supplier_code'] ?? '')) ?: null;
        $data['is_active'] = $request->boolean('is_active', true);

        SupplierProductOffer::updateOrCreate(
            ['supplier_id' => $data['supplier_id'], 'medicine_id' => $data['medicine_id']],
            $data
        );

        return redirect()->route('admin.supplier-procurement.index')
            ->with('status', 'Harga produk supplier berhasil disimpan.');
    }

    public function downloadOfferTemplate(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:pbf_suppliers,id'],
        ]);
        $supplier = PbfSupplier::findOrFail($data['supplier_id']);
        $existingOffers = SupplierProductOffer::where('supplier_id', $supplier->id)
            ->get()
            ->keyBy('medicine_id');
        $medicines = Medicine::query()->orderBy('nama_obat')->get(['id', 'sku', 'nama_obat']);
        $rows = $medicines->map(function (Medicine $medicine) use ($supplier, $existingOffers): array {
            $offer = $existingOffers->get($medicine->id);

            return [
                $medicine->id,
                $medicine->sku ?? '',
                $medicine->nama_obat,
                $supplier->id,
                $supplier->name,
                $offer?->supplier_code ?? '',
                $offer?->purchase_price ?? '',
                $offer?->minimum_quantity ?? '',
            ];
        })->all();

        return XlsxWriter::download(
            'harga-produk-' . Str::slug($supplier->name) . '.xlsx',
            ['ID PRODUK', 'SKU PRODUK', 'NAMA PRODUK', 'ID SUPPLIER', 'NAMA SUPPLIER', 'KODE PRODUK SUPPLIER', 'HARGA MODAL', 'MINIMUM BELI'],
            $rows
        );
    }

    public function importOffers(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        try {
            [$headers, $rows] = SpreadsheetReader::read($request->file('file'));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        $hasProductIdentifier = in_array('id produk', $headers, true)
            || in_array('sku produk', $headers, true)
            || in_array('nama produk', $headers, true);
        $hasSupplierIdentifier = in_array('id supplier', $headers, true)
            || in_array('nama supplier', $headers, true);
        if (!in_array('harga modal', $headers, true) || !$hasProductIdentifier || !$hasSupplierIdentifier) {
            throw ValidationException::withMessages([
                'file' => 'Template tidak sesuai. Wajib ada HARGA MODAL, minimal satu pengenal produk (ID PRODUK, SKU PRODUK, atau NAMA PRODUK), dan minimal satu pengenal supplier (ID SUPPLIER atau NAMA SUPPLIER).',
            ]);
        }
        if (count($rows) > 5000) {
            throw ValidationException::withMessages(['file' => 'Maksimal 5.000 harga supplier per impor.']);
        }

        $prepared = [];
        $seen = [];
        $errors = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $productId = trim((string) ($row['id produk'] ?? ''));
            $sku = trim((string) ($row['sku produk'] ?? ''));
            $productName = trim((string) ($row['nama produk'] ?? ''));
            $supplierId = trim((string) ($row['id supplier'] ?? ''));
            $supplierName = trim((string) ($row['nama supplier'] ?? ''));
            $supplierCode = trim((string) ($row['kode produk supplier'] ?? '')) ?: null;
            $rawPrice = trim((string) ($row['harga modal'] ?? ''));
            if ($rawPrice === '') {
                continue;
            }
            $price = str_replace([',', ' '], '', $rawPrice);
            $minimum = trim((string) ($row['minimum beli'] ?? '')) ?: '1';

            $validator = Validator::make([
                'product_id' => $productId,
                'sku' => $sku,
                'product_name' => $productName,
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
                'purchase_price' => $price,
                'minimum_quantity' => $minimum,
                'supplier_code' => $supplierCode,
            ], [
                'product_id' => ['nullable', 'integer', 'min:1'],
                'sku' => ['nullable', 'string', 'max:100'],
                'product_name' => ['nullable', 'string', 'max:255'],
                'supplier_id' => ['nullable', 'integer', 'min:1'],
                'supplier_name' => ['nullable', 'string', 'max:255'],
                'purchase_price' => ['required', 'numeric', 'gte:0', 'max:999999999999'],
                'minimum_quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
                'supplier_code' => ['nullable', 'string', 'max:100'],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }
                continue;
            }

            if ($productId === '' && $sku === '' && $productName === '') {
                $errors[] = "Baris {$line}: isi ID PRODUK, SKU PRODUK, atau NAMA PRODUK.";
                continue;
            }

            if ($productId !== '') {
                $medicine = Medicine::find((int) $productId);
                if (!$medicine) {
                    $errors[] = "Baris {$line}: ID produk {$productId} tidak ditemukan.";
                    continue;
                }
            } elseif ($sku !== '') {
                $medicine = Medicine::where('sku', $sku)->first();
                if (!$medicine) {
                    $errors[] = "Baris {$line}: SKU produk {$sku} tidak ditemukan.";
                    continue;
                }
            } else {
                $matchingMedicines = Medicine::whereRaw('LOWER(TRIM(nama_obat)) = ?', [mb_strtolower($productName)])->limit(2)->get();
                if ($matchingMedicines->isEmpty()) {
                    $errors[] = "Baris {$line}: nama produk {$productName} tidak ditemukan; pastikan sama dengan nama di katalog.";
                    continue;
                }
                if ($matchingMedicines->count() > 1) {
                    $errors[] = "Baris {$line}: nama produk {$productName} cocok dengan lebih dari satu produk; isi SKU PRODUK untuk memilih produk yang benar.";
                    continue;
                }
                $medicine = $matchingMedicines->first();
            }
            if ($supplierId !== '') {
                $supplier = PbfSupplier::find((int) $supplierId);
            } elseif ($supplierName !== '') {
                $supplier = PbfSupplier::whereRaw('LOWER(name) = ?', [mb_strtolower($supplierName)])->first();
            } else {
                $supplier = null;
            }
            if (!$supplier) {
                $errors[] = "Baris {$line}: supplier {$supplierName} tidak ditemukan; buat supplier terlebih dahulu.";
                continue;
            }

            $key = $supplier->id . ':' . $medicine->id;
            if (isset($seen[$key])) {
                $errors[] = "Baris {$line}: pasangan produk dan supplier duplikat di dalam file.";
                continue;
            }
            $seen[$key] = true;
            $prepared[] = [
                'supplier_id' => $supplier->id,
                'medicine_id' => $medicine->id,
                'supplier_code' => $supplierCode,
                'purchase_price' => $price,
                'minimum_quantity' => (int) $minimum,
                'is_active' => true,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages(['file' => $errors]);
        }
        if (!$prepared) {
            throw ValidationException::withMessages(['file' => 'File tidak berisi data harga supplier untuk diimpor.']);
        }

        DB::transaction(function () use ($prepared): void {
            foreach ($prepared as $data) {
                SupplierProductOffer::updateOrCreate(
                    ['supplier_id' => $data['supplier_id'], 'medicine_id' => $data['medicine_id']],
                    $data
                );
            }
        });

        return redirect()->route('admin.supplier-procurement.index')
            ->with('status', count($prepared) . ' harga produk supplier berhasil diimpor atau diperbarui.');
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:pbf_suppliers,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'unit_prices' => ['nullable', 'array'],
            'unit_prices.*' => ['nullable', 'numeric', 'min:0.01', 'max:999999999999'],
            'purchase_order_type' => ['nullable', 'string', 'in:regular,oot,precursor'],
            'purchase_order_profile' => ['nullable', 'string', 'in:medix,medikpedia'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'apj' => ['nullable', 'string', 'max:255'],
            'sia' => ['nullable', 'string', 'max:255'],
            'sipa' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $selected = collect($data['quantities'])
            ->map(fn ($quantity, $offerId) => [(int) $offerId, (int) ($quantity ?? 0)])
            ->filter(fn (array $entry) => $entry[1] > 0)
            ->values();
        if ($selected->isEmpty()) {
            throw ValidationException::withMessages(['quantities' => 'Masukkan jumlah minimal satu produk untuk dipesan.']);
        }

        $supplier = PbfSupplier::findOrFail($data['supplier_id']);
        $purchaseOrderType = $data['purchase_order_type'] ?? 'regular';
        $unitPrices = $data['unit_prices'] ?? [];
        $order = DB::transaction(function () use ($selected, $supplier, $data, $unitPrices, $purchaseOrderType): SupplierPurchaseOrder {
            $order = SupplierPurchaseOrder::create([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_address' => $supplier->address,
                'supplier_phone' => $supplier->phone,
                'order_number' => $this->nextOrderNumber(),
                'status' => 'ordered',
                'ordered_at' => now()->toDateString(),
                'purchase_order_type' => $purchaseOrderType,
                'purchase_order_profile' => $data['purchase_order_profile'] ?? 'medix',
                'requester_name' => $data['requester_name'] ?? auth()->user()?->name ?? 'Apotek Medix',
                'apj' => $data['apj'] ?? 'Apoteker Penanggung Jawab',
                'sia' => $data['sia'] ?? '13042600890450001',
                'sipa' => $data['sipa'] ?? '1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($selected as [$offerId, $quantity]) {
                $offer = SupplierProductOffer::with('medicine')
                    ->whereKey($offerId)
                    ->where('supplier_id', $supplier->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();
                if (!$offer || !$offer->medicine) {
                    throw ValidationException::withMessages(['quantities' => 'Salah satu produk tidak lagi tersedia dari supplier terpilih.']);
                }
                if ($offer->medicine->getPurchaseOrderCategory() !== $purchaseOrderType) {
                    $categoryName = $offer->medicine->getPurchaseOrderCategoryLabel();
                    $orderTypeName = match ($purchaseOrderType) {
                        'oot' => 'SP OOT',
                        'precursor' => 'SP Prekursor',
                        default => 'SP Reguler',
                    };
                    throw ValidationException::withMessages([
                        'purchase_order_type' => "{$offer->medicine->nama_obat} termasuk kategori {$categoryName} dan tidak dapat dimasukkan ke {$orderTypeName}.",
                    ]);
                }
                if ($quantity < $offer->minimum_quantity) {
                    throw ValidationException::withMessages([
                        'quantities' => "{$offer->medicine->nama_obat}: minimal pembelian {$offer->minimum_quantity} {$offer->medicine->package_unit_label}.",
                    ]);
                }
                $unitPrice = (float) ($unitPrices[$offerId] ?? $offer->purchase_price);
                $order->items()->create([
                    'medicine_id' => $offer->medicine_id,
                    'supplier_product_offer_id' => $offer->id,
                    'product_name' => $offer->medicine->nama_obat,
                    'supplier_code' => $offer->supplier_code,
                    'active_ingredient' => $offer->medicine->komposisi,
                    'dosage_form' => $offer->medicine->sediaan,
                    'strength' => $offer->medicine->dosis,
                    'unit_label' => $offer->medicine->package_unit_label,
                    'quantity' => $quantity,
                    'received_quantity' => 0,
                    'unit_cost' => $unitPrice,
                    'line_total' => round($unitPrice * $quantity, 2),
                    'requested_discount' => 0,
                ]);
            }

            return $order;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'purchase_order_url' => route('admin.supplier-procurement.orders.purchase-order', $order),
            ], 201);
        }

        return redirect()->route('admin.supplier-procurement.index')
            ->with('status', "SP {$order->order_number} berhasil dibuat. Stok belum bertambah sampai barang diterima.");
    }

    public function purchaseOrder(SupplierPurchaseOrder $order)
    {
        $order->load('items');
        return view('admin.supplier-procurement.purchase-order', compact('order'));
    }

    public function receive(Request $request, SupplierPurchaseOrder $order)
    {
        $data = $request->validate([
            'received_now' => ['required', 'array'],
            'received_now.*' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = SupplierPurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($lockedOrder->status, ['cancelled', 'returned', 'received'], true)) {
                throw ValidationException::withMessages(['received_now' => 'Pesanan ini sudah tidak dapat menerima barang lagi.']);
            }

            $receivedAny = false;
            foreach ($data['received_now'] as $itemId => $receivedNow) {
                $receivedNow = (int) $receivedNow;
                if ($receivedNow === 0) {
                    continue;
                }
                $item = SupplierPurchaseOrderItem::whereKey($itemId)
                    ->where('supplier_purchase_order_id', $lockedOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $remaining = $item->quantity - $item->received_quantity;
                if ($receivedNow > $remaining) {
                    throw ValidationException::withMessages([
                        'received_now' => "{$item->product_name}: jumlah penerimaan melebihi sisa pesanan ({$remaining}).",
                    ]);
                }
                if (!$item->medicine_id) {
                    throw ValidationException::withMessages([
                        'received_now' => "{$item->product_name}: produk katalog sudah dihapus; hubungi administrator sebelum menerima barang.",
                    ]);
                }

                $medicine = Medicine::whereKey($item->medicine_id)->lockForUpdate()->firstOrFail();
                $before = $medicine->getPhysicalStockUnits();
                $stockIncrement = $receivedNow * $medicine->units_per_box;
                $newStockUnits = $before + $stockIncrement;
                if ($medicine->usesTabletPackaging()) {
                    $medicine->stock_base_units = $newStockUnits;
                    $medicine->stok = intdiv($newStockUnits, max(1, $medicine->units_per_box));
                } else {
                    $medicine->setStockInUnits($newStockUnits);
                }
                MedicineStockLedger::withContext(
                    'supplier_receipt',
                    $lockedOrder->id,
                    fn () => $medicine->save(),
                    0,
                    "Penerimaan {$lockedOrder->order_number} dari {$lockedOrder->supplier_name}."
                );

                $item->received_quantity += $receivedNow;
                $item->save();
                $receivedAny = true;
            }

            if (!$receivedAny) {
                throw ValidationException::withMessages(['received_now' => 'Masukkan jumlah barang yang diterima.']);
            }

            $allReceived = !$lockedOrder->items()
                ->whereColumn('received_quantity', '<', 'quantity')
                ->exists();
            $lockedOrder->status = $allReceived ? 'received' : 'partially_received';
            $lockedOrder->received_at = $allReceived ? now() : null;
            $lockedOrder->save();
        });

        $redirect = $request->boolean('from_purchase_history')
            ? route('admin.purchase-history.index', ['tab' => 'purchases'])
            : route('admin.supplier-procurement.index');

        return redirect($redirect)->with('status', 'Penerimaan dicatat dan stok diperbarui sesuai jumlah yang diterima.');
    }

    public function returnOrder(SupplierPurchaseOrder $order)
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = SupplierPurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'ordered' || $lockedOrder->items()->where('received_quantity', '>', 0)->exists()) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan hanya dapat dikembalikan sebelum ada barang yang diterima.',
                ]);
            }

            $lockedOrder->status = 'returned';
            $lockedOrder->save();
        });

        return redirect()->route('admin.purchase-history.index', ['tab' => 'purchases'])
            ->with('status', "Pesanan {$order->order_number} dikembalikan. Tidak ada stok yang ditambahkan.");
    }

    public function destroyOrder(SupplierPurchaseOrder $order)
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = SupplierPurchaseOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== 'returned' || $lockedOrder->items()->where('received_quantity', '>', 0)->exists()) {
                throw ValidationException::withMessages([
                    'order' => 'Hanya riwayat yang sudah dikembalikan dan belum menerima barang yang dapat dihapus.',
                ]);
            }

            $lockedOrder->delete();
        });

        return redirect()->route('admin.purchase-history.index', ['tab' => 'purchases'])
            ->with('status', "Riwayat pesanan {$order->order_number} berhasil dihapus.");
    }

    public function receiveLegacyOrder(Request $request, PurchaseHistory $order)
    {
        $data = $request->validate([
            'received_now' => ['required', 'array', 'min:1'],
            'received_now.*' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($order, $data): void {
            $lockedOrder = PurchaseHistory::whereKey($order->id)
                ->where('is_pbf_purchase_order', true)
                ->lockForUpdate()
                ->firstOrFail();
            if (in_array($lockedOrder->receipt_status, ['cancelled', 'returned', 'verified', 'printed'], true)) {
                throw ValidationException::withMessages(['received_now' => 'Pesanan ini sudah tidak dapat menerima barang lagi.']);
            }

            $items = $lockedOrder->items ?? [];
            $receivedAny = false;
            foreach ($data['received_now'] as $itemIndex => $receivedNow) {
                if (!array_key_exists($itemIndex, $items)) {
                    throw ValidationException::withMessages(['received_now' => 'Produk pesanan tidak ditemukan. Muat ulang halaman lalu coba lagi.']);
                }

                $receivedNow = (int) $receivedNow;
                $item = $items[$itemIndex];
                $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 0);
                $alreadyReceived = (int) ($item['received_quantity'] ?? 0);
                $remaining = $quantity - $alreadyReceived;
                if ($receivedNow > $remaining) {
                    $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                    throw ValidationException::withMessages([
                        'received_now' => "{$name}: jumlah penerimaan melebihi sisa pesanan ({$remaining}).",
                    ]);
                }
                if ($receivedNow === 0) {
                    continue;
                }

                $medicineId = (int) ($item['id'] ?? 0);
                $medicine = Medicine::whereKey($medicineId)->lockForUpdate()->first();
                if (!$medicine) {
                    $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                    throw ValidationException::withMessages([
                        'received_now' => "{$name}: produk katalog sudah dihapus; hubungi administrator sebelum menerima barang.",
                    ]);
                }

                $unit = (string) ($item['sale_unit'] ?? 'box');
                $unitFactor = $medicine->getSaleUnitFactor($unit);
                if ($unitFactor < 1) {
                    $name = $item['nama_obat'] ?? $item['name'] ?? 'Produk';
                    throw ValidationException::withMessages([
                        'received_now' => "{$name}: satuan pada pesanan tidak valid sehingga stok tidak dapat diperbarui.",
                    ]);
                }

                $before = $medicine->getPhysicalStockUnits();
                $after = $before + ($receivedNow * $unitFactor);
                if ($medicine->usesTabletPackaging()) {
                    $medicine->stock_base_units = $after;
                    $medicine->stok = intdiv($after, max(1, $medicine->units_per_box));
                } else {
                    $medicine->setStockInUnits($after);
                }
                $medicine->save();
                MedicineStockLedger::recordPhysicalChange(
                    $medicine,
                    $before,
                    $after,
                    'supplier_receipt',
                    $lockedOrder->id,
                    0,
                    "Penerimaan pesanan PBF {$lockedOrder->id}."
                );

                $items[$itemIndex]['received_quantity'] = $alreadyReceived + $receivedNow;
                $receivedAny = true;
            }

            if (!$receivedAny) {
                throw ValidationException::withMessages(['received_now' => 'Masukkan jumlah barang yang diterima untuk memperbarui pesanan.']);
            }

            $allReceived = collect($items)->every(
                fn (array $item) => (int) ($item['received_quantity'] ?? 0) >= (int) ($item['quantity'] ?? $item['qty'] ?? 0)
            );
            $anyReceived = collect($items)->contains(fn (array $item) => (int) ($item['received_quantity'] ?? 0) > 0);
            $lockedOrder->items = $items;
            $lockedOrder->receipt_status = $allReceived ? 'verified' : ($anyReceived ? 'partially_received' : 'pending');
            $lockedOrder->verified_at = $allReceived ? now() : null;
            $lockedOrder->save();
        });

        return redirect()->route('admin.purchase-history.index', ['tab' => 'purchases'])
            ->with('status', 'Penerimaan pesanan PBF dicatat dan stok diperbarui sesuai jumlah yang diterima.');
    }

    public function returnLegacyOrder(PurchaseHistory $order)
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = PurchaseHistory::whereKey($order->id)
                ->where('is_pbf_purchase_order', true)
                ->lockForUpdate()
                ->firstOrFail();
            if (($lockedOrder->receipt_status ?? 'pending') !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Pesanan yang sudah menerima barang tidak dapat dikembalikan.']);
            }

            $lockedOrder->receipt_status = 'returned';
            $lockedOrder->cancelled_at = now();
            $lockedOrder->save();
        });

        return redirect()->route('admin.purchase-history.index', ['tab' => 'purchases'])
            ->with('status', "Pesanan PBF {$order->id} dikembalikan. Tidak ada stok yang ditambahkan.");
    }

    public function destroyLegacyOrder(PurchaseHistory $order)
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = PurchaseHistory::whereKey($order->id)
                ->where('is_pbf_purchase_order', true)
                ->lockForUpdate()
                ->firstOrFail();
            $hasReceivedItems = collect($lockedOrder->items ?? [])
                ->contains(fn (array $item) => (int) ($item['received_quantity'] ?? 0) > 0);
            if ($lockedOrder->receipt_status !== 'returned' || $hasReceivedItems) {
                throw ValidationException::withMessages(['order' => 'Hanya pesanan PBF yang dikembalikan tanpa penerimaan barang yang dapat dihapus.']);
            }

            $lockedOrder->delete();
        });

        return redirect()->route('admin.purchase-history.index', ['tab' => 'purchases'])
            ->with('status', "Riwayat pesanan PBF {$order->id} berhasil dihapus.");
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'SPB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (SupplierPurchaseOrder::where('order_number', $number)->exists());

        return $number;
    }
}
