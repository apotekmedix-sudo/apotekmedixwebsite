<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\PbfSupplier;
use App\Models\PurchaseHistory;
use App\Models\SupplierProductOffer;
use App\Models\SupplierPurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Support\SpreadsheetReader;
use Tests\TestCase;

class AdminSupplierProcurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_snapshots_supplier_price_and_stock_changes_only_on_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplierA = PbfSupplier::create(['name' => 'Supplier A', 'address' => 'Alamat A', 'phone' => '021111']);
        $supplierB = PbfSupplier::create(['name' => 'Supplier B', 'address' => 'Alamat B', 'phone' => '021222']);
        $medicine = $this->medicine([
            'stok' => 5,
            'harga_modal' => 9000,
            'sediaan' => 'Tablet',
            'komposisi' => 'Paracetamol',
            'dosis' => '500 mg',
        ]);
        $offerA = SupplierProductOffer::create([
            'supplier_id' => $supplierA->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'A-001',
            'purchase_price' => 8000,
            'minimum_quantity' => 1,
        ]);
        SupplierProductOffer::create([
            'supplier_id' => $supplierB->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'B-009',
            'purchase_price' => 7500,
            'minimum_quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.orders.store'), [
                'supplier_id' => $supplierA->id,
                'quantities' => [$offerA->id => 2],
            ])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $order = SupplierPurchaseOrder::with('items')->firstOrFail();
        $this->assertSame('ordered', $order->status);
        $this->assertSame('8000.00', $order->items->first()->unit_cost);
        $this->assertSame(5, $medicine->fresh()->stok);
        $this->actingAs($admin)
            ->get(route('admin.supplier-procurement.index', ['tab' => 'orders']))
            ->assertOk()
            ->assertDontSee('Riwayat pesanan');

        $this->actingAs($admin)
            ->get(route('admin.supplier-procurement.orders.purchase-order', $order))
            ->assertOk()
            ->assertSee('Supplier A')
            ->assertDontSee('Kode Supplier')
            ->assertDontSee('A-001')
            ->assertSee('8.000')
            ->assertSee('Tablet / Paracetamol / 500 mg')
            ->assertDontSee('7.500');

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.orders.receive', $order), [
                'received_now' => [$order->items->first()->id => 1],
            ])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $this->assertSame(6, $medicine->fresh()->stok);
        $this->assertSame('partially_received', $order->fresh()->status);
        $this->assertDatabaseHas('medicine_stock_movements', [
            'medicine_id' => $medicine->id,
            'movement_type' => 'supplier_receipt',
            'physical_delta' => 1,
            'reference_id' => $order->id,
        ]);

        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.receive', $order), [
            'received_now' => [$order->items->first()->id => 1],
        ])->assertRedirect();

        $this->assertSame(7, $medicine->fresh()->stok);
        $this->assertSame('received', $order->fresh()->status);
    }

    public function test_pbf_ordering_page_uses_selected_supplier_catalog_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier di Pesan PBF', 'address' => 'Alamat Supplier', 'phone' => '021']);
        $offeredMedicine = $this->medicine(['nama_obat' => 'Produk dengan harga supplier', 'harga_modal' => 99999]);
        $unofferedMedicine = $this->medicine(['nama_obat' => 'Produk tanpa harga supplier', 'harga_modal' => 88888]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $offeredMedicine->id,
            'supplier_code' => 'PBF-001',
            'purchase_price' => 7500,
            'minimum_quantity' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk-pbf.index', ['supplier_id' => $supplier->id]))
            ->assertOk()
            ->assertSee('Pesan ke PBF')
            ->assertSee('Supplier di Pesan PBF')
            ->assertSee('Produk dengan harga supplier')
            ->assertSee('7.500')
            ->assertSee('PBF-001')
            ->assertSee((string) $offer->id)
            ->assertDontSee('Produk tanpa harga supplier');
    }

    public function test_pbf_ordering_page_creates_supplier_sp_with_selected_prices_and_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier SP PBF', 'address' => 'Alamat Supplier SP', 'phone' => '021987']);
        $medicine = $this->medicine([
            'nama_obat' => 'Produk untuk SP',
            'golongan' => 'OOT',
            'komposisi' => 'Bahan aktif',
            'sediaan' => 'Tablet',
            'dosis' => '500 mg',
        ]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'PBF-SP-12',
            'purchase_price' => 6400,
            'minimum_quantity' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.supplier-procurement.orders.store'), [
                'supplier_id' => $supplier->id,
                'quantities' => [$offer->id => 2],
                'requested_discounts' => [$offer->id => 1200],
                'purchase_order_type' => 'oot',
                'purchase_order_profile' => 'medix',
                'requester_name' => 'Apt. Penguji',
                'apj' => 'Apoteker',
                'sia' => 'SIA-TEST',
                'sipa' => 'SIPA-TEST',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'order_number', 'purchase_order_url']);

        $order = SupplierPurchaseOrder::with('items')->findOrFail($response->json('id'));
        $this->assertSame('oot', $order->purchase_order_type);
        $this->assertSame('Apt. Penguji', $order->requester_name);
        $this->assertSame('6400.00', $order->items->first()->unit_cost);
        $this->assertSame('1200.00', $order->items->first()->requested_discount);
        $this->assertSame('Bahan aktif', $order->items->first()->active_ingredient);
        $this->assertSame(5, $medicine->fresh()->stok);

        $this->get($response->json('purchase_order_url'))
            ->assertOk()
            ->assertSee('SURAT PESANAN OBAT-OBAT TERTENTU')
            ->assertSee('Supplier SP PBF')
            ->assertDontSee('PBF-SP-12')
            ->assertSee('Apt. Penguji')
            ->assertSee('Harga Modal')
            ->assertSee('Subtotal')
            ->assertSee('class="order-totals"', false)
            ->assertDontSee('<tfoot>', false)
            ->assertSeeInOrder(['</table>', 'Total ajuan potongan: Rp 1.200', 'Total: Rp 12.800'])
            ->assertDontSee('<th class="number">Ajuan Potongan</th>', false)
            ->assertSee('Rp 6.400')
            ->assertSee('Rp 12.800')
            ->assertSee('Ajuan potongan: Rp 1.200')
            ->assertSee('potongan menunggu persetujuan supplier');
    }

    public function test_supplier_sp_rejects_mixed_product_categories_for_selected_order_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Kategori', 'address' => 'Alamat Supplier', 'phone' => '021']);
        $ootMedicine = $this->medicine(['nama_obat' => 'Produk OOT', 'golongan' => 'Obat-Obat Tertentu']);
        $regularMedicine = $this->medicine(['nama_obat' => 'Produk Reguler', 'golongan' => 'Obat Keras']);
        $ootOffer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $ootMedicine->id,
            'purchase_price' => 1000,
            'minimum_quantity' => 1,
        ]);
        $regularOffer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $regularMedicine->id,
            'purchase_price' => 1000,
            'minimum_quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.supplier-procurement.orders.store'), [
                'supplier_id' => $supplier->id,
                'quantities' => [$ootOffer->id => 1, $regularOffer->id => 1],
                'purchase_order_type' => 'oot',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('purchase_order_type')
            ->assertJsonPath('errors.purchase_order_type.0', 'Produk Reguler termasuk kategori Reguler dan tidak dapat dimasukkan ke SP OOT.');

        $this->assertDatabaseCount('supplier_purchase_orders', 0);
    }

    public function test_supplier_offer_can_be_imported_from_csv_using_product_sku(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Excel', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['sku' => 'SKU-EXCEL']);
        $csv = "SKU PRODUK,NAMA SUPPLIER,KODE PRODUK SUPPLIER,HARGA MODAL,MINIMUM BELI\n"
            . "SKU-EXCEL,Supplier Excel,EXT-22,7650,3\n";
        $file = UploadedFile::fake()->createWithContent('offers.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.offers.import'), ['file' => $file])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $this->assertDatabaseHas('supplier_product_offers', [
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'EXT-22',
            'purchase_price' => 7650,
            'minimum_quantity' => 3,
        ]);
    }

    public function test_supplier_offer_import_accepts_zero_and_prices_below_one_thousand(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Harga Rendah', 'address' => 'Alamat', 'phone' => '021']);
        $freeMedicine = $this->medicine(['sku' => 'SKU-GRATIS']);
        $lowPriceMedicine = $this->medicine(['sku' => 'SKU-MURAH']);
        $csv = "SKU PRODUK,NAMA SUPPLIER,HARGA MODAL\n"
            . "SKU-GRATIS,Supplier Harga Rendah,0\n"
            . "SKU-MURAH,Supplier Harga Rendah,500\n";
        $file = UploadedFile::fake()->createWithContent('low-price-offers.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.offers.import'), ['file' => $file])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $this->assertDatabaseHas('supplier_product_offers', [
            'supplier_id' => $supplier->id,
            'medicine_id' => $freeMedicine->id,
            'purchase_price' => 0,
        ]);
        $this->assertDatabaseHas('supplier_product_offers', [
            'supplier_id' => $supplier->id,
            'medicine_id' => $lowPriceMedicine->id,
            'purchase_price' => 500,
        ]);
    }

    public function test_supplier_offer_can_be_imported_by_product_name_when_sku_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Nama', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['sku' => null, 'nama_obat' => 'Paracetamol 500 mg']);
        $csv = "SKU PRODUK,NAMA PRODUK,NAMA SUPPLIER,KODE PRODUK SUPPLIER,HARGA MODAL,MINIMUM BELI\n"
            . ",Paracetamol 500 mg,Supplier Nama,EXT-33,8100,2\n";
        $file = UploadedFile::fake()->createWithContent('offers-by-name.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.offers.import'), ['file' => $file])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $this->assertDatabaseHas('supplier_product_offers', [
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'EXT-33',
            'purchase_price' => 8100,
            'minimum_quantity' => 2,
        ]);
    }

    public function test_import_requires_sku_when_product_name_is_ambiguous(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        PbfSupplier::create(['name' => 'Supplier Nama Ganda', 'address' => 'Alamat', 'phone' => '021']);
        $this->medicine(['sku' => null, 'nama_obat' => 'Produk Kembar']);
        $this->medicine(['sku' => null, 'nama_obat' => 'Produk Kembar']);
        $csv = "NAMA PRODUK,NAMA SUPPLIER,HARGA MODAL\n"
            . "Produk Kembar,Supplier Nama Ganda,8100\n";
        $file = UploadedFile::fake()->createWithContent('ambiguous-products.csv', $csv);

        $this->actingAs($admin)
            ->from(route('admin.supplier-procurement.index', ['tab' => 'offers']))
            ->post(route('admin.supplier-procurement.offers.import'), ['file' => $file])
            ->assertRedirect(route('admin.supplier-procurement.index', ['tab' => 'offers']))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('supplier_product_offers', 0);
    }

    public function test_exported_supplier_sheet_contains_all_catalog_products_and_existing_supplier_prices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Export', 'address' => 'Alamat', 'phone' => '021']);
        $medicineWithOffer = $this->medicine(['sku' => null, 'nama_obat' => 'Produk sudah terdaftar']);
        $medicineWithoutOffer = $this->medicine(['sku' => null, 'nama_obat' => 'Produk baru untuk supplier']);
        SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicineWithOffer->id,
            'supplier_code' => 'SUP-OLD',
            'purchase_price' => 8500,
            'minimum_quantity' => 4,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.supplier-procurement.offers.template', ['supplier_id' => $supplier->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $file = UploadedFile::fake()->createWithContent('export.xlsx', $response->getContent());
        [$headers, $rows] = SpreadsheetReader::read($file);
        $this->assertContains('id produk', $headers);
        $this->assertContains('nama produk', $headers);
        $this->assertCount(2, $rows);

        $existingProductRow = collect($rows)->firstWhere('id produk', (string) $medicineWithOffer->id);
        $newProductRow = collect($rows)->firstWhere('id produk', (string) $medicineWithoutOffer->id);
        $this->assertSame('Produk sudah terdaftar', $existingProductRow['nama produk']);
        $this->assertSame('Supplier Export', $existingProductRow['nama supplier']);
        $this->assertSame('8500', $existingProductRow['harga modal']);
        $this->assertSame('SUP-OLD', $existingProductRow['kode produk supplier']);
        $this->assertSame('', $newProductRow['harga modal']);
    }

    public function test_supplier_offer_import_uses_product_and_supplier_ids_instead_of_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier ID', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['sku' => null, 'nama_obat' => 'Nama katalog benar']);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'KODE-LAMA',
            'purchase_price' => 7000,
            'minimum_quantity' => 1,
        ]);
        $csv = "ID PRODUK,NAMA PRODUK,ID SUPPLIER,NAMA SUPPLIER,KODE PRODUK SUPPLIER,HARGA MODAL,MINIMUM BELI\n"
            . "{$medicine->id},Nama yang diubah,{$supplier->id},Nama supplier yang diubah,KODE-BARU,9100,3\n"
            . "{$medicine->id},Nama katalog benar,{$supplier->id},Supplier ID,,, \n";
        $file = UploadedFile::fake()->createWithContent('edited-export.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.offers.import'), ['file' => $file])
            ->assertRedirect(route('admin.supplier-procurement.index'));

        $this->assertDatabaseCount('supplier_product_offers', 1);
        $this->assertSame($offer->id, $offer->fresh()->id);
        $this->assertSame('9100.00', $offer->fresh()->purchase_price);
        $this->assertSame('KODE-BARU', $offer->fresh()->supplier_code);
        $this->assertSame(3, $offer->fresh()->minimum_quantity);
    }

    public function test_manual_offer_product_search_returns_supplier_specific_existing_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplierA = PbfSupplier::create(['name' => 'Supplier Cari A', 'address' => 'Alamat', 'phone' => '021']);
        $supplierB = PbfSupplier::create(['name' => 'Supplier Cari B', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['sku' => 'SEARCH-001', 'nama_obat' => 'Produk untuk dicari']);
        SupplierProductOffer::create([
            'supplier_id' => $supplierA->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 7300,
            'minimum_quantity' => 2,
        ]);
        SupplierProductOffer::create([
            'supplier_id' => $supplierB->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 6900,
            'minimum_quantity' => 4,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.supplier-procurement.medicines.search', [
                'q' => 'SEARCH-001',
                'supplier_id' => $supplierA->id,
            ]))
            ->assertOk()
            ->assertJsonPath('data.0.id', $medicine->id)
            ->assertJsonPath('data.0.offer.purchase_price', 7300)
            ->assertJsonPath('data.0.offer.minimum_quantity', 2);

        $this->actingAs($admin)
            ->getJson(route('admin.supplier-procurement.medicines.search', [
                'medicine_id' => $medicine->id,
                'supplier_id' => $supplierB->id,
            ]))
            ->assertJsonPath('data.0.offer.purchase_price', 6900);

        $this->actingAs($admin)
            ->getJson(route('admin.supplier-procurement.medicines.search', ['q' => 'S']))
            ->assertJsonCount(0, 'data');
    }

    public function test_supplier_offer_list_can_be_filtered_by_supplier_and_product_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplierA = PbfSupplier::create(['name' => 'Supplier Filter A', 'address' => 'Alamat', 'phone' => '021']);
        $supplierB = PbfSupplier::create(['name' => 'Supplier Filter B', 'address' => 'Alamat', 'phone' => '021']);
        $medicineA = $this->medicine(['nama_obat' => 'Nama produk yang dicari']);
        $medicineB = $this->medicine(['nama_obat' => 'Produk lainnya']);
        SupplierProductOffer::create([
            'supplier_id' => $supplierA->id,
            'medicine_id' => $medicineA->id,
            'purchase_price' => 8000,
            'minimum_quantity' => 1,
        ]);
        SupplierProductOffer::create([
            'supplier_id' => $supplierB->id,
            'medicine_id' => $medicineB->id,
            'purchase_price' => 9000,
            'minimum_quantity' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.supplier-procurement.index', [
                'offer_supplier_id' => $supplierA->id,
                'offer_search' => 'Nama produk',
            ]))
            ->assertOk()
            ->assertSee('Pembelian Supplier')
            ->assertSee('Pesan ke PBF')
            ->assertDontSee('Pesanan &amp; Penerimaan')
            ->assertDontSee('Harga Produk Supplier</a>')
            ->assertSee('Nama produk yang dicari')
            ->assertSee('Supplier Filter A')
            ->assertDontSee('Produk lainnya');
    }

    public function test_supplier_offer_list_shows_ten_products_per_page_with_next_navigation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Pagination', 'address' => 'Alamat', 'phone' => '021']);

        for ($index = 1; $index <= 11; $index++) {
            $medicine = $this->medicine(['nama_obat' => sprintf('Produk Pagination %02d', $index)]);
            SupplierProductOffer::create([
                'supplier_id' => $supplier->id,
                'medicine_id' => $medicine->id,
                'purchase_price' => 1000 + $index,
                'minimum_quantity' => 1,
            ]);
        }

        $query = [
            'offer_supplier_id' => $supplier->id,
            'offer_search' => 'Produk Pagination',
        ];
        $this->actingAs($admin)
            ->get(route('admin.supplier-procurement.index', $query))
            ->assertOk()
            ->assertSee('Menampilkan 1–10 dari 11 produk')
            ->assertSee('Next')
            ->assertSee('Produk Pagination 02')
            ->assertDontSee('Produk Pagination 01');

        $this->get(route('admin.supplier-procurement.index', array_merge($query, ['offers_page' => 2])))
            ->assertOk()
            ->assertSee('Menampilkan 11–11 dari 11 produk')
            ->assertSee('Produk Pagination 01')
            ->assertDontSee('Produk Pagination 02');
    }

    public function test_receiving_supplier_boxes_updates_tablet_stock_in_base_units_and_box_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Box', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine([
            'nama_obat' => 'Tablet dalam Box',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'stok' => 5,
            'stock_base_units' => 500,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
        ]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 9000,
            'minimum_quantity' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.store'), [
            'supplier_id' => $supplier->id,
            'quantities' => [$offer->id => 1],
        ]);
        $order = SupplierPurchaseOrder::with('items')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.receive', $order), [
            'received_now' => [$order->items->first()->id => 1],
        ])->assertRedirect();

        $this->assertSame(6, $medicine->fresh()->stok);
        $this->assertSame(600, $medicine->fresh()->stock_base_units);
    }

    public function test_supplier_prices_and_codes_are_not_exposed_on_public_product_page(): void
    {
        $supplier = PbfSupplier::create(['name' => 'Supplier Rahasia', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['nama_obat' => 'Produk Contoh']);
        SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'supplier_code' => 'SECRET-CODE',
            'purchase_price' => 4321,
            'minimum_quantity' => 1,
        ]);

        $this->get(route('medicines.show', $medicine->id))
            ->assertOk()
            ->assertDontSee('Supplier Rahasia')
            ->assertDontSee('SECRET-CODE')
            ->assertDontSee('4.321');
    }

    public function test_transactions_page_separates_supplier_purchase_history_from_sales(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Riwayat', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine();
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 6200,
            'minimum_quantity' => 1,
        ]);
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.store'), [
            'supplier_id' => $supplier->id,
            'quantities' => [$offer->id => 1],
        ]);
        PurchaseHistory::create([
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'PBF dari alur lama',
            'pbf_address' => 'Alamat PBF',
            'pbf_phone' => '021',
            'purchase_order_type' => 'regular',
            'purchase_order_profile' => 'medix',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 2,
                'harga' => 6200,
            ]],
            'total' => 12400,
            'original_total' => 12400,
            'discounted_total' => 12400,
            'is_pbf_purchase_order' => true,
            'receipt_status' => 'verified',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.purchase-history.index', ['tab' => 'purchases']))
            ->assertOk()
            ->assertSee('Riwayat Pembelian')
            ->assertSee('Supplier Riwayat')
            ->assertSee('PBF dari alur lama')
            ->assertSee('Semua Riwayat Pembelian')
            ->assertSee('Supplier / PBF')
            ->assertSee('purchase_date')
            ->assertSee('order_number')
            ->assertSee('product_count')
            ->assertSee('total_min')
            ->assertSee('purchase_status')
            ->assertSee('Diterima lengkap')
            ->assertSee('Download Excel')
            ->assertDontSee('Pesanan PBF dari alur lama')
            ->assertSee('Lihat SP')
            ->assertSee('Selesai')
            ->assertSee('Tidak sesuai jumlah')
            ->assertSee('Kembalikan')
            ->assertSee('received_now[')
            ->assertSee('Rp 6.200');

        $this->get(route('admin.purchase-history.index', ['tab' => 'sales']))
            ->assertOk()
            ->assertSee('Riwayat Penjualan')
            ->assertDontSee('PBF dari alur lama');
    }

    public function test_purchase_history_filters_all_fields_and_sorts_both_order_sources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $olderOrder = PurchaseHistory::create([
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'Supplier Filter Beta',
            'items' => [[
                'id' => null,
                'nama_obat' => 'Produk Satu',
                'quantity' => 1,
                'harga' => 10000,
            ]],
            'total' => 10000,
            'original_total' => 10000,
            'discounted_total' => 10000,
            'is_pbf_purchase_order' => true,
            'receipt_status' => 'pending',
        ]);
        $targetOrder = PurchaseHistory::create([
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'Supplier Filter Alpha',
            'items' => [
                ['id' => null, 'nama_obat' => 'Produk Satu', 'quantity' => 2, 'harga' => 10000],
                ['id' => null, 'nama_obat' => 'Produk Dua', 'quantity' => 1, 'harga' => 5000],
            ],
            'total' => 25000,
            'original_total' => 25000,
            'discounted_total' => 25000,
            'is_pbf_purchase_order' => true,
            'receipt_status' => 'returned',
        ]);
        $olderOrder->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();
        $targetOrder->forceFill(['created_at' => now()->subDay()])->saveQuietly();

        $olderSp = 'MDX/SP/REGULAR/' . $olderOrder->created_at->format('Ymd') . '/' . str_pad((string) $olderOrder->id, 5, '0', STR_PAD_LEFT);
        $targetSp = 'MDX/SP/REGULAR/' . $targetOrder->created_at->format('Ymd') . '/' . str_pad((string) $targetOrder->id, 5, '0', STR_PAD_LEFT);
        $baseUrl = route('admin.purchase-history.index', ['tab' => 'purchases']);

        $filters = [
            'tab' => 'purchases',
            'purchase_date' => $targetOrder->created_at->format('Y-m-d'),
            'order_number' => $targetSp,
            'supplier' => 'Filter Alpha',
            'product_count' => 2,
            'total_min' => 20000,
            'total_max' => 30000,
            'purchase_status' => 'returned',
        ];

        $this->actingAs($admin)
            ->get($baseUrl . '&' . http_build_query($filters))
            ->assertOk()
            ->assertSee($targetSp)
            ->assertSee('Supplier Filter Alpha')
            ->assertDontSee($olderSp)
            ->assertDontSee('<td>Supplier Filter Beta</td>', false);

        $export = $this->get(route('admin.purchase-history.export', $filters))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition');
        $temporaryFile = tempnam(sys_get_temp_dir(), 'purchase-history-export-');
        file_put_contents($temporaryFile, $export->getContent());
        try {
            [$headers, $exportedRows] = SpreadsheetReader::read(
                new UploadedFile($temporaryFile, 'riwayat_pembelian.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)
            );
        } finally {
            unlink($temporaryFile);
        }
        $this->assertSame(['tanggal', 'nomor sp', 'supplier / pbf', 'jumlah produk', 'total modal (rp)', 'status'], $headers);
        $this->assertCount(1, $exportedRows);
        $this->assertSame($targetSp, $exportedRows[0]['nomor sp']);
        $this->assertSame('Supplier Filter Alpha', $exportedRows[0]['supplier / pbf']);

        $this->get($baseUrl . '&purchase_sort=oldest')
            ->assertOk()
            ->assertSeeInOrder([$olderSp, $targetSp]);

        $this->get($baseUrl . '&purchase_sort=newest')
            ->assertOk()
            ->assertSeeInOrder([$targetSp, $olderSp]);
    }

    public function test_deleting_all_sales_keeps_legacy_pbf_purchase_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sale = PurchaseHistory::create([
            'buyer_name' => 'Pelanggan',
            'items' => [],
            'total' => 10000,
            'is_pbf_purchase_order' => false,
        ]);
        $pbfOrder = PurchaseHistory::create([
            'buyer_name' => 'Supplier PBF lama',
            'items' => [],
            'total' => 20000,
            'is_pbf_purchase_order' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.purchase-history.destroyAll'))
            ->assertRedirect(route('admin.purchase-history.index', ['tab' => 'sales']));

        $this->assertDatabaseMissing('purchase_histories', ['id' => $sale->id]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $pbfOrder->id]);
    }

    public function test_legacy_pbf_receipts_update_stock_and_prevent_return_after_partial_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = $this->medicine(['stok' => 5]);
        $order = PurchaseHistory::create([
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'Supplier Lawas',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 3,
                'sale_unit' => 'box',
                'harga' => 10000,
            ]],
            'total' => 30000,
            'is_pbf_purchase_order' => true,
        ]);
        $purchaseHistoryUrl = route('admin.purchase-history.index', ['tab' => 'purchases']);

        $this->actingAs($admin)
            ->post(route('admin.purchase-history.receive', $order), [
                'received_now' => [0 => 1],
            ])
            ->assertRedirect($purchaseHistoryUrl);

        $this->assertSame(6, $medicine->fresh()->stok);
        $this->assertSame('partially_received', $order->fresh()->receipt_status);
        $this->assertSame(1, $order->fresh()->items[0]['received_quantity']);

        $this->actingAs($admin)
            ->post(route('admin.purchase-history.return', $order))
            ->assertSessionHasErrors('order');
        $this->assertSame(6, $medicine->fresh()->stok);

        $this->actingAs($admin)
            ->post(route('admin.purchase-history.receive', $order), [
                'received_now' => [0 => 2],
            ])
            ->assertRedirect($purchaseHistoryUrl);

        $this->assertSame(8, $medicine->fresh()->stok);
        $this->assertSame('verified', $order->fresh()->receipt_status);
    }

    public function test_legacy_pbf_order_can_be_returned_and_deleted_without_stock_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = $this->medicine(['stok' => 5]);
        $order = PurchaseHistory::create([
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'Supplier Lawas',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 3,
                'sale_unit' => 'box',
                'harga' => 10000,
            ]],
            'total' => 30000,
            'is_pbf_purchase_order' => true,
        ]);
        $purchaseHistoryUrl = route('admin.purchase-history.index', ['tab' => 'purchases']);

        $this->actingAs($admin)
            ->post(route('admin.purchase-history.return', $order))
            ->assertRedirect($purchaseHistoryUrl);

        $this->assertSame(5, $medicine->fresh()->stok);
        $this->assertSame('returned', $order->fresh()->receipt_status);

        $this->actingAs($admin)
            ->get($purchaseHistoryUrl)
            ->assertOk()
            ->assertSee('Dikembalikan')
            ->assertSee('Hapus riwayat');

        $this->actingAs($admin)
            ->delete(route('admin.purchase-history.destroy-returned', $order))
            ->assertRedirect($purchaseHistoryUrl);

        $this->assertDatabaseMissing('purchase_histories', ['id' => $order->id]);
        $this->assertSame(5, $medicine->fresh()->stok);
    }

    public function test_finishing_purchase_from_history_receives_all_remaining_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Selesai', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['stok' => 3]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 5000,
            'minimum_quantity' => 1,
        ]);
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.store'), [
            'supplier_id' => $supplier->id,
            'quantities' => [$offer->id => 4],
        ]);
        $order = SupplierPurchaseOrder::with('items')->firstOrFail();
        $item = $order->items->first();

        $this->actingAs($admin)
            ->from(route('admin.purchase-history.index', ['tab' => 'purchases']))
            ->post(route('admin.supplier-procurement.orders.receive', $order), [
                'from_purchase_history' => 1,
                'received_now' => [$item->id => $item->quantity],
            ])
            ->assertRedirect(route('admin.purchase-history.index', ['tab' => 'purchases']));

        $this->assertSame(7, $medicine->fresh()->stok);
        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame(4, $item->fresh()->received_quantity);
    }

    public function test_returned_purchase_does_not_add_stock_and_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Kembali', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['stok' => 3]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 5000,
            'minimum_quantity' => 1,
        ]);
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.store'), [
            'supplier_id' => $supplier->id,
            'quantities' => [$offer->id => 4],
        ]);
        $order = SupplierPurchaseOrder::with('items')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.supplier-procurement.orders.return', $order))
            ->assertRedirect(route('admin.purchase-history.index', ['tab' => 'purchases']));

        $this->assertSame(3, $medicine->fresh()->stok);
        $this->assertSame('returned', $order->fresh()->status);
        $this->assertSame(0, $order->items->first()->fresh()->received_quantity);

        $this->actingAs($admin)
            ->get(route('admin.purchase-history.index', ['tab' => 'purchases']))
            ->assertOk()
            ->assertSee('Dikembalikan')
            ->assertSee('Hapus riwayat')
            ->assertDontSee('Kembalikan');

        $this->actingAs($admin)
            ->delete(route('admin.supplier-procurement.orders.destroy', $order))
            ->assertRedirect(route('admin.purchase-history.index', ['tab' => 'purchases']));

        $this->assertDatabaseMissing('supplier_purchase_orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('supplier_purchase_order_items', ['supplier_purchase_order_id' => $order->id]);
        $this->assertSame(3, $medicine->fresh()->stok);
    }

    public function test_partially_received_purchase_cannot_be_returned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = PbfSupplier::create(['name' => 'Supplier Sebagian', 'address' => 'Alamat', 'phone' => '021']);
        $medicine = $this->medicine(['stok' => 3]);
        $offer = SupplierProductOffer::create([
            'supplier_id' => $supplier->id,
            'medicine_id' => $medicine->id,
            'purchase_price' => 5000,
            'minimum_quantity' => 1,
        ]);
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.store'), [
            'supplier_id' => $supplier->id,
            'quantities' => [$offer->id => 4],
        ]);
        $order = SupplierPurchaseOrder::with('items')->firstOrFail();
        $item = $order->items->first();
        $this->actingAs($admin)->post(route('admin.supplier-procurement.orders.receive', $order), [
            'received_now' => [$item->id => 1],
        ]);

        $this->actingAs($admin)
            ->from(route('admin.purchase-history.index', ['tab' => 'purchases']))
            ->post(route('admin.supplier-procurement.orders.return', $order))
            ->assertSessionHasErrors('order');

        $this->assertSame(4, $medicine->fresh()->stok);
        $this->assertSame('partially_received', $order->fresh()->status);
    }

    private function medicine(array $overrides = []): Medicine
    {
        return Medicine::create(array_merge([
            'nama_obat' => 'Produk Supplier',
            'kategori' => 'Pabrik Contoh',
            'harga' => 12000,
            'harga_modal' => 10000,
            'stok' => 5,
            'deskripsi' => 'Produk untuk pengujian supplier',
        ], $overrides));
    }
}
