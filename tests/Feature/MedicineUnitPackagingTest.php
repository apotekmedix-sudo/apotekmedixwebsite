<?php

namespace Tests\Feature;

use App\Constants\Companies;
use App\Helpers\ImageHelper;
use App\Helpers\XlsxWriter;
use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use App\Support\StockAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MedicineUnitPackagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_package_stock_conversion_with_one_b2c_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Kemasan',
            'kategori_produk' => Companies::LIST[0],
            'kategori' => 'Pabrik Contoh',
            'logo' => 'HIJAU',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'harga_modal' => 5000,
            'harga_grosir' => 7000,
            'harga_retail' => 9900,
            'stok' => 2,
            'unit_packaging_enabled' => 1,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'tablets_per_box' => 100,
            'price_tablet' => 100,
            'price_strip' => 900,
            'price_box' => 8000,
            'gambar' => [
                UploadedFile::fake()->create('kemasan-utama.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('kemasan-kedua.jpg', 100, 'image/jpeg'),
            ],
        ])->assertRedirect(route('admin.produk.index'));

        $medicine = Medicine::where('nama_obat', 'Obat Kemasan')->firstOrFail();
        $medicine->update(['kelompok' => 'APOTEK']);
        $this->assertSame(2, $medicine->stok);
        $this->assertSame(200, $medicine->stock_base_units);
        $this->assertSame('Box', $medicine->package_unit_label);
        $this->assertSame('2 Box', $medicine->stock_breakdown);
        $this->assertSame(10, $medicine->getSaleUnitFactor('strip'));
        $this->assertSame(9900.0, $medicine->getSaleUnitPrice('box'));
        $this->assertSame(9900.0, $medicine->getChannelBasePrice('b2c'));
        $this->assertSame(7000.0, $medicine->getChannelBasePrice('b2b'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rp 9.900')
            ->assertDontSee('Harga Box')
            ->assertDontSee('Box · Rp 9.900', false)
            ->assertDontSee('Tablet · Rp 100', false)
            ->assertSee('Stok: 2')
            ->assertDontSee('Strip ·', false)
            ->assertDontSee('Tablet ·', false)
            ->assertSee('data-stock-units="200"', false)
            ->assertSee('class="product-unit-choice" aria-pressed="true"', false)
            ->assertSee('class="product-unit-card product-unit-card--single is-selected', false)
            ->assertDontSee('<span class="product-unit-heading">', false)
            ->assertSee('onclick="selectProductCardUnit(this)"', false)
            ->assertSee('product-logo-marker--green', false)
            ->assertSeeInOrder([
                '<div class="medicine-card-principal">Pabrik Contoh</div>',
                '<h3 class="prod-name"><a href="' . route('medicines.show', ['id' => $medicine->id, 'from' => 'home']) . '">Obat Kemasan</a></h3>',
            ], false)
            ->assertSee('data-product-id="' . $medicine->id . '"', false)
            ->assertSee('function syncProductButtons()', false)
            ->assertSee('Sudah di Keranjang', false)
            ->assertDontSee('data-detail-url=', false)
            ->assertDontSee('role="link"', false)
            ->assertDontSee('class="medicine-btn"', false);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertDontSee('Tablet · Rp 100', false)
            ->assertDontSee('Strip · Rp 900', false)
            ->assertSee('Rp 9.900')
            ->assertDontSee('Box · Rp 9.900', false)
            ->assertSee('data-package-unit-label="Box"', false);

        foreach ($medicine->image_gallery as $image) {
            ImageHelper::deleteProductImage($image);
        }
    }

    public function test_customer_can_purchase_package_with_one_price_and_stock_uses_package_conversion(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Satuan',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 20,
            'stock_base_units' => 2000,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'price_tablet' => 100,
            'price_strip' => 900,
            'price_box' => 8000,
            'deskripsi' => 'Contoh obat',
        ]);

        $response = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan',
            'phone' => '081234567890',
            'address' => 'Alamat contoh',
            'items' => [
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 1, 'harga' => 1, 'sale_unit' => 'box'],
            ],
            'total' => 3,
        ])->assertOk()->assertJsonStructure(['success', 'id', 'invoice_url']);

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(8000, $order->total);
        $this->assertSame('Box', $order->items[0]['unit_label']);
        $this->assertSame(8000, $order->items[0]['harga']);
        $this->assertSame('20 Box', $medicine->fresh()->stock_breakdown);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_base_units' => 2000,
            'stok' => 20,
        ]);
        $this->assertDatabaseHas('purchase_stock_reservations', [
            'purchase_history_id' => $order->id,
            'medicine_id' => $medicine->id,
            'quantity_units' => 100,
            'sale_quantity' => 1,
        ]);
        $this->assertDatabaseHas('medicine_stock_movements', [
            'medicine_id' => $medicine->id,
            'movement_type' => 'reserve',
            'physical_delta' => 0,
            'reserved_delta' => 100,
            'reference_id' => $order->id,
        ]);
    }

    public function test_b2c_and_b2b_catalogs_offer_their_channel_package_prices(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Harga Kanal',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'kategori' => 'Pabrik Contoh',
            'kelompok' => 'APOTEK',
            'harga' => 1600,
            'harga_grosir' => 1400,
            'harga_retail' => 1600,
            'stok' => 10,
            'stock_base_units' => 1000,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'price_tablet' => 100,
            'price_strip' => 900,
            'price_box' => 1600,
            'harga_internal' => 1200,
            'deskripsi' => 'Produk untuk pengujian harga kanal',
        ]);
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(30)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Rp 1.400')
            ->assertDontSee('Harga Box')
            ->assertDontSee('Box · Rp 1.400', false)
            ->assertDontSee('Tablet · Rp 140', false)
            ->assertDontSee('Strip · Rp 140', false);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Rp 1.600')
            ->assertDontSee('Box · Rp 1.600', false)
            ->assertDontSee('Tablet · Rp 100', false)
            ->assertDontSee('Strip · Rp 900', false);

        $internalCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('67890'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'internal_access_expires_at' => now()->addDays(30)->timestamp,
            'internal_access_code_hash' => $internalCode->code_hash,
        ])->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Rp 1.200')
            ->assertDontSee('Box · Rp 1.200', false)
            ->assertDontSee('Tablet · Rp 100', false)
            ->assertDontSee('Strip · Rp 900', false);
        $this->get(route('medicines.show', $medicine))
            ->assertOk()
            ->assertSee('Rp 1.200')
            ->assertDontSee('detailPriceLabel', false)
            ->assertDontSee('Harga Box')
            ->assertDontSee('detail-unit-pill-name">Tablet', false)
            ->assertDontSee('detail-unit-pill-name">Strip', false);

        $this->get('/belanja-internal')->assertNotFound();

        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan',
            'phone' => '081234567890',
            'transaction_channel' => 'offline',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'sale_unit' => 'tablet'],
            ],
            'total' => 0,
        ])->assertUnprocessable();

        $this->withSession([
            'internal_access_expires_at' => now()->addDays(30)->timestamp,
            'internal_access_code_hash' => $internalCode->code_hash,
        ])->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan Internal',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'sale_unit' => 'tablet'],
            ],
            'total' => 100,
        ])->assertUnprocessable();

        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'pbf',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'sale_unit' => 'strip'],
            ],
            'total' => 0,
        ])->assertUnprocessable();
    }

    public function test_unpacked_product_stock_uses_its_package_unit_label(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Salep Tube',
            'satuan' => 'Tube',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 5,
            'deskripsi' => 'Contoh produk tanpa konversi tablet',
        ]);

        $this->assertSame('Tube', $medicine->package_unit_label);
        $this->assertSame('5 Tube', $medicine->stock_breakdown);
        $this->assertSame(5.0, $medicine->stock_packages);

        $medicine->setStockInPackages(0);
        $medicine->save();
        $this->assertSame('0 Tube', $medicine->fresh()->stock_breakdown);
    }

    public function test_bottle_and_tube_stock_is_not_multiplied_by_tablet_packaging_fields(): void
    {
        foreach (['Botol' => 'Sirup', 'Tube' => 'Krim'] as $unit => $form) {
            $medicine = Medicine::create([
                'nama_obat' => "Produk {$unit}",
                'satuan' => $unit,
                'sediaan' => $form,
                'kategori' => 'Pabrik Contoh',
                'harga' => 8000,
                'harga_retail' => 8000,
                'stok' => 3,
                'stock_base_units' => 300,
                'unit_packaging_enabled' => true,
                'tablets_per_strip' => 10,
                'strips_per_box' => 10,
                'price_tablet' => 100,
                'price_strip' => 900,
                'price_box' => 8000,
                'deskripsi' => 'Produk pengujian satuan kemasan',
            ]);

            $this->assertFalse($medicine->usesTabletPackaging());
            $this->assertSame(1, $medicine->units_per_box);
            $this->assertSame(3, $medicine->available_stock_units);
            $this->assertSame(3.0, $medicine->stock_packages);
            $this->assertSame("3 {$unit}", $medicine->stock_breakdown);
            $this->assertSame(0, $medicine->getSaleUnitFactor('tablet'));
            $this->assertSame(0, $medicine->getSaleUnitFactor('strip'));
            $this->assertSame(1, $medicine->getSaleUnitFactor('box'));
            $annotatedProducts = new \Illuminate\Database\Eloquent\Collection([$medicine]);
            StockAvailability::annotate($annotatedProducts);
            StockAvailability::annotate($annotatedProducts);
            $this->assertSame(3, $medicine->available_stock_units);

            $this->getJson(route('products.stock-availability', ['ids' => [$medicine->id]]))
                ->assertOk()
                ->assertJsonPath("stock.{$medicine->id}.units", 3)
                ->assertJsonPath("stock.{$medicine->id}.converted_summary", "3 {$unit}");

            $this->postJson(route('orders.history.store'), [
                'buyer_type' => 'umum',
                'buyer_name' => 'Pelanggan',
                'phone' => '081234567890',
                'address' => 'Alamat contoh',
                'items' => [
                    ['id' => $medicine->id, 'quantity' => 1, 'sale_unit' => 'box'],
                ],
                'total' => 8000,
            ])->assertOk();

            $this->assertDatabaseHas('purchase_stock_reservations', [
                'medicine_id' => $medicine->id,
                'quantity_units' => 1,
                'sale_quantity' => 1,
            ]);
        }
    }

    public function test_cart_pages_restrict_non_box_packages_to_the_package_unit(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('function normalizeCartPackageUnits(item)', false)
            ->assertSee("item.saleUnit = 'box'", false)
            ->assertSee("item.unitLabels = { box: packageLabel }", false)
            ->assertSee("if (!isCartBoxPackage(item) && newUnit !== 'box')", false);
    }

    public function test_admin_cannot_enable_tablet_conversion_for_bottle_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Sirup Botol',
            'kategori_produk' => Companies::LIST[0],
            'kategori' => 'Pabrik Contoh',
            'satuan' => 'Botol',
            'sediaan' => 'Sirup',
            'harga_modal' => 5000,
            'harga_grosir' => 7000,
            'harga_retail' => 9900,
            'stok' => 4,
            'strips_per_box' => 10,
            'tablets_per_strip' => 10,
            'price_tablet' => 100,
            'price_strip' => 900,
        ])->assertRedirect(route('admin.produk.index'));

        $medicine = Medicine::where('nama_obat', 'Sirup Botol')->firstOrFail();
        $this->assertFalse($medicine->unit_packaging_enabled);
        $this->assertSame(1, $medicine->units_per_box);
        $this->assertSame(4, $medicine->stock_base_units);
        $this->assertSame(4, $medicine->available_stock_units);
        $this->assertSame(0, $medicine->getSaleUnitFactor('tablet'));
    }

    public function test_admin_product_list_uses_only_the_main_stock_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $box = Medicine::create([
            'nama_obat' => 'Tablet BOX',
            'satuan' => 'BOX',
            'sediaan' => 'Tablet',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 5,
            'deskripsi' => 'Produk tablet dalam box',
        ]);
        $bottle = Medicine::create([
            'nama_obat' => 'Sirup Botol',
            'satuan' => 'Botol',
            'sediaan' => 'Sirup',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 5,
            'deskripsi' => 'Produk cair dalam botol',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.produk.index'))->assertOk();
        $response->assertSee('id="stok-box-' . $box->id . '"', false)
            ->assertSee('Fisik:</strong> 5 Box', false)
            ->assertSee('Tersedia:</strong> 5 Box', false)
            ->assertDontSee('id="strips-per-box-' . $box->id . '"', false)
            ->assertDontSee('id="tablets-per-strip-' . $box->id . '"', false)
            ->assertDontSee('class="stock-quantity-input js-row-strip-quantity"', false)
            ->assertDontSee('class="stock-quantity-input js-row-tablet-quantity"', false)
            ->assertDontSee('id="price-strip-' . $box->id . '"', false)
            ->assertDontSee('id="strips-per-box-' . $bottle->id . '"', false)
            ->assertDontSee('id="tablets-per-strip-' . $bottle->id . '"', false)
            ->assertDontSee('id="price-strip-' . $bottle->id . '"', false)
            ->assertDontSee('id="price-tablet-' . $bottle->id . '"', false);

        $this->assertTrue(Medicine::supportsTabletPackaging('BOX', 'Tablet'));
        $this->assertFalse(Medicine::supportsTabletPackaging('Botol', 'Tablet'));
        $this->assertFalse(Medicine::supportsTabletPackaging('Tube', 'Tablet'));
    }

    public function test_inventory_report_shows_stock_and_movements_only_in_main_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Laporan Box',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 3,
            'stock_base_units' => 300,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'deskripsi' => 'Produk untuk pengujian laporan stok.',
        ]);
        $medicine->stockMovements()->create([
            'movement_type' => 'sale',
            'physical_delta' => -100,
            'physical_before' => 400,
            'physical_after' => 300,
            'note' => 'Penjualan satu box.',
        ]);

        $this->actingAs($admin)->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Semua jumlah ditampilkan dalam satuan utama produk.')
            ->assertSee('3 Box')
            ->assertSee('-1 Box')
            ->assertSee('4 Box')
            ->assertDontSee('strip')
            ->assertDontSee('tablet')
            ->assertDontSee('unit dasar');
    }

    public function test_inventory_report_uses_compact_pagination_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 31) as $number) {
            Medicine::create([
                'nama_obat' => "Produk Laporan {$number}",
                'kategori' => 'Pabrik Contoh',
                'harga' => 1000,
                'stok' => 1,
                'stock_base_units' => 1,
                'deskripsi' => 'Produk untuk menguji pagination laporan stok.',
            ]);
        }

        $this->actingAs($admin)->get(route('admin.inventory.index', ['search' => 'Produk']))
            ->assertOk()
            ->assertSee('class="inventory-pagination-nav" aria-label="Halaman produk"', false)
            ->assertSee('class="inventory-page-link is-disabled"', false)
            ->assertSee('products_page=2')
            ->assertDontSee('relative inline-flex');
    }

    public function test_customer_cannot_order_more_packages_than_remain_in_stock(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Stok Terbatas',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'stok' => 1,
            'stock_base_units' => 100,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'price_tablet' => 100,
            'price_strip' => 900,
            'price_box' => 8000,
            'deskripsi' => 'Contoh obat',
        ]);

        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan',
            'phone' => '081234567890',
            'address' => 'Alamat contoh',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 2, 'harga' => 1, 'sale_unit' => 'box'],
            ],
            'total' => 11,
        ])->assertUnprocessable();

        $this->assertSame(100, $medicine->fresh()->stock_base_units);
    }

    public function test_customer_cannot_buy_a_strip_unit(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Harga Strip Belum Diatur',
            'kategori' => 'Pabrik Contoh',
            'harga' => 8000,
            'harga_retail' => 8000,
            'stok' => 10,
            'stock_base_units' => 1000,
            'unit_packaging_enabled' => true,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'price_box' => 8000,
            'deskripsi' => 'Produk hanya memiliki harga kemasan',
        ]);

        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 1, 'sale_unit' => 'strip'],
            ],
            'total' => 1,
        ])->assertUnprocessable();

        $this->assertSame(1000, $medicine->fresh()->stock_base_units);
    }

    public function test_admin_pbf_purchase_order_uses_modal_price_and_does_not_reduce_store_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Pesanan PBF',
            'kategori' => 'Pabrik Contoh',
            'harga' => 700,
            'harga_grosir' => 1200,
            'harga_retail' => 1400,
            'harga_modal' => 500,
            'stok' => 0,
            'stock_base_units' => 0,
            'deskripsi' => 'Produk untuk pengadaan',
        ]);

        $response = $this->actingAs($admin)->postJson(route('orders.history.store'), [
            'buyer_type' => 'apotik',
            'requester_name' => 'Apoteker',
            'apj' => 'Apoteker',
            'sia' => 'SIA-123',
            'sipa' => 'SIPA-123',
            'pbf_name' => 'PBF Contoh',
            'pbf_address' => 'Alamat PBF',
            'pbf_phone' => '021-12345678',
            'purchase_order_type' => 'oot',
            'purchase_order_profile' => 'medikpedia',
            'is_pbf_purchase_order' => true,
            'transaction_channel' => 'online',
            'items' => [
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 2, 'harga' => 1200],
            ],
            'total' => 2400,
        ])->assertOk();

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertTrue($order->is_pbf_purchase_order);
        $this->assertSame('oot', $order->purchase_order_type);
        $this->assertSame('medikpedia', $order->purchase_order_profile);
        $this->assertSame('021-12345678', $order->pbf_phone);
        $this->assertSame('Apotek Medix', $order->buyer_name);
        $this->assertNull($order->customer_id);
        $this->assertSame(1000, $order->total);
        $this->assertSame(500, $order->items[0]['harga']);
        $this->assertSame(500, $order->items[0]['harga_modal']);
        $this->assertSame('Pabrik Contoh', $order->items[0]['brand']);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_base_units' => 0,
            'stok' => 0,
        ]);

        $medicine->update(['harga_modal' => 900]);
        $this->actingAs($admin)
            ->get(route('admin.purchase-history.purchase-order', $order))
            ->assertOk()
            ->assertSee('SURAT PESANAN')
            ->assertSee('OBAT-OBAT TERTENTU')
            ->assertSee('Nomor telepon')
            ->assertSee('021-12345678')
            ->assertSee('Zat Aktif OOT')
            ->assertSee('PBF Contoh')
            ->assertSee('APOTEK MEDIKPEDIA')
            ->assertSee('ITC CEMPAKA MAS LT.1 NO.88, KEL. SUMUR BATU, KEC. KEMAYORAN, JAKARTA PUSAT')
            ->assertSee('Telepon: 085890007359')
            ->assertSee('Kode Pos: 10640')
            ->assertSee('Website: medikpedia.com')
            ->assertSee('LOGO MEDIKPEDIA.png')
            ->assertSee('stempel apotek.png')
            ->assertSee('stempel apoteker.png')
            ->assertSee('mix-blend-mode:multiply')
            ->assertSee('signature-stamp-pharmacist')
            ->assertSee('signature-stamp-pharmacy')
            ->assertSee('width:155px')
            ->assertSee('left:138px')
            ->assertSee('SIPA-123')
            ->assertSee('Obat Pesanan PBF')
            ->assertSee('Bentuk / Kekuatan')
            ->assertDontSee('MODAL')
            ->assertDontSee('Rp 500')
            ->assertDontSee('Rp 900');
    }

    public function test_precursor_purchase_order_shows_active_ingredient_and_quantity_in_words(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = PurchaseHistory::create([
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Medix',
            'requester_name' => 'Apt. Penguji',
            'apj' => 'Apoteker Penanggung Jawab',
            'sipa' => 'SIPA-123',
            'pbf_name' => 'PT PBF Contoh',
            'pbf_address' => 'Alamat PBF',
            'pbf_phone' => '021-12345678',
            'purchase_order_type' => 'precursor',
            'is_pbf_purchase_order' => true,
            'items' => [[
                'nama_obat' => 'Obat Prekursor',
                'komposisi' => 'Pseudoephedrine',
                'sediaan' => 'Tablet',
                'dosis' => '30 mg',
                'quantity' => 2,
                'unit_label' => 'Box',
            ]],
            'total' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.purchase-history.purchase-order', $order))
            ->assertOk()
            ->assertSee('PREKURSOR FARMASI')
            ->assertSee('Zat Aktif Prekursor')
            ->assertSee('Pseudoephedrine')
            ->assertSee('2 (Dua) Box');
    }

    public function test_regular_purchase_order_uses_regular_medicine_columns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = PurchaseHistory::create([
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Medix',
            'requester_name' => 'Apt. Penguji',
            'apj' => 'Apoteker Penanggung Jawab',
            'sipa' => 'SIPA-123',
            'pbf_name' => 'PT PBF Contoh',
            'pbf_address' => 'Alamat PBF',
            'pbf_phone' => '021-12345678',
            'items' => [[
                'nama_obat' => 'Obat Reguler',
                'sediaan' => 'Tablet',
                'dosis' => '500 mg',
                'quantity' => 3,
                'unit_label' => 'Box',
            ]],
            'total' => 0,
        ]);

        $this->assertSame('regular', $order->fresh()->purchase_order_type);
        $response = $this->actingAs($admin)
            ->get(route('admin.purchase-history.purchase-order', $order))
            ->assertOk()
            ->assertSee('SURAT PESANAN OBAT')
            ->assertSee('OBAT REGULER')
            ->assertSee('Bentuk / Kekuatan')
            ->assertSee('Satuan')
            ->assertSee('Jumlah')
            ->assertSee('Tablet / 500 mg')
            ->assertDontSee('MODAL')
            ->assertDontSee('CONTOH ILUSTRASI');

        preg_match('/<tbody>(.*?)<\\/tbody>/s', $response->getContent(), $tableBody);
        $this->assertArrayHasKey(1, $tableBody);
        $this->assertSame(1, preg_match_all('/<tr(?:\\s|>)/', $tableBody[1]));
    }

    public function test_admin_pbf_ordering_page_uses_dedicated_purchase_order_checkout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Medicine::create([
            'nama_obat' => 'Obat PBF Logo Merah',
            'satuan' => 'Box',
            'kategori' => 'Pabrik Contoh',
            'logo' => 'Merah',
            'golongan' => 'Obat Keras',
            'harga' => 8000,
            'harga_modal' => 5000,
            'stok' => 5,
            'deskripsi' => 'Produk untuk pengujian kartu PBF.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk-pbf.index'))
            ->assertOk()
            ->assertSee('Pesan ke PBF')
            ->assertSee('Logo:</strong> Merah', false)
            ->assertSee('Golongan:</strong> Obat Keras', false)
            ->assertSee('product-logo-marker--red', false)
            ->assertSee('pengadaan Apotek Medix ke distributor')
            ->assertSee('Buat Surat Pesanan')
            ->assertSee('adminPbfOrder: true', false)
            ->assertSee('function openOrder()', false)
            ->assertSee('if (CART_CONFIG.adminPbfOrder)', false)
            ->assertSee("document.getElementById('cartDrawer').classList.add('open')", false)
            ->assertSee("document.getElementById('cartOverlay').classList.add('open')", false)
            ->assertSee('openPbfCheckout();', false)
            ->assertSee('id="pbfOrderType"', false)
            ->assertSee('id="pbfOrderProfile"', false)
            ->assertSee('SP Biasa (Obat Reguler)')
            ->assertSee('SP OOT (Obat-Obat Tertentu)')
            ->assertSee('SP Prekursor')
            ->assertSee('id="pbfOrderPhone"', false)
            ->assertSee('.purchase-order-preview-panel iframe { display:block; flex:1 1 auto; width:100%; height:0; min-height:0; border:0; }', false)
            ->assertSee('window.location.href = \'' . route('cart.index') . '\'', false)
            ->assertDontSee('id="orderModal"', false)
            ->assertDontSee('id="f_payment"', false);
    }

    public function test_admin_pbf_product_search_matches_medicine_details_and_dose_in_description(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Pencarian PBF',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'harga_modal' => 500,
            'stok' => 5,
            'deskripsi' => 'Dosis harian 5 mg untuk pemakaian sesuai anjuran.',
            'indikasi' => 'Membantu meredakan gejala pencarian indikasi',
            'komposisi' => 'Kandungan pencarian komposisi',
            'golongan' => 'Pencarian golongan',
            'sediaan' => 'Pencarian sediaan',
        ]);

        foreach (['indikasi', 'komposisi', 'golongan', 'sediaan', '5 mg'] as $search) {
            $this->actingAs($admin)
                ->get(route('admin.produk-pbf.index', ['search' => $search]))
                ->assertOk()
                ->assertSee($medicine->nama_obat);
        }

        $this->actingAs($admin)
            ->get(route('admin.produk-pbf.index'))
            ->assertSee('Cari nama, indikasi, komposisi, golongan, sediaan, dosis');
    }

    public function test_admin_can_save_pbf_supplier_and_search_it_from_purchase_order_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.produk-pbf.suppliers.store'), [
                'name' => 'PT Supplier Obat Sehat',
                'address' => 'Jl. Distributor No. 10, Jakarta',
                'phone' => '021-12345678',
            ])
            ->assertRedirect(route('admin.produk-pbf.index'))
            ->assertSessionHas('status', 'Supplier berhasil ditambahkan.');

        $this->assertDatabaseHas('pbf_suppliers', [
            'name' => 'PT Supplier Obat Sehat',
            'address' => 'Jl. Distributor No. 10, Jakarta',
            'phone' => '021-12345678',
        ]);

        $this->get(route('admin.produk-pbf.index'))
            ->assertOk()
            ->assertSee('Tambah Supplier')
            ->assertSee('Kelola Supplier')
            ->assertSee('id="supplierManagerBackdrop"', false)
            ->assertSee('id="supplierManagerSearch"', false)
            ->assertSee('data-supplier-search="pt supplier obat sehat jl. distributor no. 10, jakarta 021-12345678"', false)
            ->assertSee('supplierManagerSearch.addEventListener(\'input\'', false)
            ->assertSee('.supplier-modal-body { flex:1; min-height:0; overflow-y:auto;', false)
            ->assertSee('id="pbfOrderDistributor"', false)
            ->assertSee('id="pbfOrderPhone"', false)
            ->assertSee('id="pbfSupplierMenu" role="listbox"', false)
            ->assertSee('autocomplete="off"', false)
            ->assertSee('"name":"PT Supplier Obat Sehat","address":"Jl. Distributor No. 10, Jakarta","phone":"021-12345678"', false)
            ->assertSee('supplierAddress.value = selected.address', false)
            ->assertSee('supplierPhone.value = selected.phone ||', false)
            ->assertSee('class="pbf-supplier-option${index === activeSupplierIndex', false)
            ->assertSee(route('admin.produk-pbf.suppliers.update', 1), false)
            ->assertSee(route('admin.produk-pbf.suppliers.destroy', 1), false)
            ->assertDontSee('<datalist', false);
    }

    public function test_admin_can_update_and_delete_pbf_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = \App\Models\PbfSupplier::create([
            'name' => 'Supplier Lama',
            'address' => 'Alamat Lama',
            'phone' => '021-11111111',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.produk-pbf.suppliers.update', $supplier), [
                'name' => 'Supplier Baru',
                'address' => 'Alamat Baru',
                'phone' => '021-22222222',
            ])
            ->assertRedirect(route('admin.produk-pbf.index', ['manage_suppliers' => 1]))
            ->assertSessionHas('status', 'Supplier berhasil diperbarui.');

        $this->assertDatabaseHas('pbf_suppliers', [
            'id' => $supplier->id,
            'name' => 'Supplier Baru',
            'address' => 'Alamat Baru',
            'phone' => '021-22222222',
        ]);

        $this->delete(route('admin.produk-pbf.suppliers.destroy', $supplier))
            ->assertRedirect(route('admin.produk-pbf.index', ['manage_suppliers' => 1]))
            ->assertSessionHas('status', 'Supplier berhasil dihapus.');

        $this->assertDatabaseMissing('pbf_suppliers', ['id' => $supplier->id]);
    }

    public function test_pbf_supplier_requires_name_address_and_phone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.produk-pbf.index'))
            ->post(route('admin.produk-pbf.suppliers.store'), [
                'name' => '',
                'address' => '',
                'phone' => '',
            ])
            ->assertRedirect(route('admin.produk-pbf.index'))
            ->assertSessionHasErrors(['name', 'address', 'phone']);

        $this->assertDatabaseCount('pbf_suppliers', 0);
    }

    public function test_admin_can_import_and_export_pbf_supplier_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = \App\Models\PbfSupplier::create([
            'name' => 'Supplier Lama',
            'address' => 'Alamat Lama',
            'phone' => '021-12345678',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk-pbf.index', ['manage_suppliers' => 1]))
            ->assertOk()
            ->assertSee('Export Supplier')
            ->assertSee('Template Excel')
            ->assertSee('Import Supplier');

        $export = $this->get(route('admin.produk-pbf.suppliers.export'));
        $export->assertOk()->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $this->assertStringStartsWith('PK', $export->getContent());

        $template = $this->get(route('admin.produk-pbf.suppliers.import.template'));
        $template->assertOk()->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $this->assertStringStartsWith('PK', $template->getContent());

        $csv = "ID,NAMA,ALAMAT,TELEPON\n{$supplier->id},Supplier Baru,\"Alamat Baru, Bogor\",021-12345678\n,PT Supplier Baru,Jakarta,021-87654321\n,PT Supplier Tanpa Alamat,,\n";
        $file = UploadedFile::fake()->createWithContent('suppliers.csv', $csv);
        $this->post(route('admin.produk-pbf.suppliers.import'), ['file' => $file])
            ->assertRedirect(route('admin.produk-pbf.index', ['manage_suppliers' => 1]))
            ->assertSessionHas('status', '3 data supplier berhasil diimpor atau diperbarui.');

        $this->assertDatabaseHas('pbf_suppliers', [
            'id' => $supplier->id,
            'name' => 'Supplier Baru',
            'address' => 'Alamat Baru, Bogor',
            'phone' => '021-12345678',
        ]);
        $this->assertDatabaseHas('pbf_suppliers', [
            'name' => 'PT Supplier Baru',
            'address' => 'Jakarta',
            'phone' => '021-87654321',
        ]);
        $this->assertDatabaseHas('pbf_suppliers', [
            'name' => 'PT Supplier Tanpa Alamat',
            'address' => '',
            'phone' => null,
        ]);
        $this->assertDatabaseCount('pbf_suppliers', 3);

        $legacyCsv = "ID,NAMA,ALAMAT\n{$supplier->id},Supplier Impor Lama,Alamat Impor Lama\n";
        $legacyFile = UploadedFile::fake()->createWithContent('legacy-suppliers.csv', $legacyCsv);
        $this->post(route('admin.produk-pbf.suppliers.import'), ['file' => $legacyFile])
            ->assertRedirect(route('admin.produk-pbf.index', ['manage_suppliers' => 1]));
        $this->assertDatabaseHas('pbf_suppliers', [
            'id' => $supplier->id,
            'name' => 'Supplier Impor Lama',
            'phone' => '021-12345678',
        ]);
    }

    public function test_admin_can_import_pbf_supplier_xlsx_with_blank_optional_columns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $path = tempnam(sys_get_temp_dir(), 'supplier-xlsx-');
        file_put_contents($path, XlsxWriter::build(
            ['ID', 'NAMA', 'ALAMAT', 'TELEPON'],
            [['', 'Supplier XLSX', '', '']]
        ));
        $file = new UploadedFile(
            $path,
            'suppliers.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        try {
            $this->actingAs($admin)
                ->post(route('admin.produk-pbf.suppliers.import'), ['file' => $file])
                ->assertRedirect(route('admin.produk-pbf.index', ['manage_suppliers' => 1]))
                ->assertSessionHas('status', '1 data supplier berhasil diimpor atau diperbarui.');
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseHas('pbf_suppliers', [
            'name' => 'Supplier XLSX',
            'address' => '',
            'phone' => null,
        ]);
    }

    public function test_cart_page_uses_pbf_purchase_order_popup_without_changing_regular_checkout(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('id="pbfPopupBackdrop"', false)
            ->assertSee('id="pbfPopupOrderType"', false)
            ->assertSee('id="pbfPopupProfile"', false)
            ->assertSee('id="pbfPopupDistributor"', false)
            ->assertSee('id="pbfPopupAddress"', false)
            ->assertSee('id="pbfPopupPhone"', false)
            ->assertSee('id="pbfPopupSigner"', false)
            ->assertSee('id="pbfInvoiceBackdrop"', false)
            ->assertSee('if (activeCtx.adminPbfOrder)', false)
            ->assertSee('openPbfPurchaseOrderPopup();', false)
            ->assertSee('window.location.href = \'' . route('checkout.index') . '\'', false)
            ->assertSee('is_pbf_purchase_order: true', false)
            ->assertSee("invoiceBase.replace('__ORDER__', window.createdPbfOrderId)", false);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('id="co_pbf_order_type"', false)
            ->assertSee('id="co_pbf_profile"', false)
            ->assertSee('id="co_pbf_phone"', false);
    }

    public function test_admin_pbf_purchase_order_requires_signatory_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat untuk Surat Pesanan',
            'kategori' => 'Pabrik Contoh',
            'harga' => 700,
            'harga_modal' => 500,
            'stok' => 0,
            'deskripsi' => 'Produk pengadaan test',
        ]);

        $this->actingAs($admin)->postJson(route('orders.history.store'), [
            'buyer_type' => 'apotik',
            'requester_name' => 'Apoteker',
            'pbf_name' => 'PBF Contoh',
            'pbf_address' => 'Alamat PBF',
            'is_pbf_purchase_order' => true,
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 500],
            ],
            'total' => 500,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('pbf_name');

        $this->assertDatabaseCount('purchase_histories', 0);
    }

    public function test_guest_cannot_create_admin_pbf_purchase_order(): void
    {
        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'apotik',
            'is_pbf_purchase_order' => true,
            'items' => [],
            'total' => 0,
        ])->assertForbidden();
    }

    public function test_products_with_channel_prices_appear_in_b2b_and_b2c_catalogs_without_group(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Semua Kanal',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'harga_grosir' => 1400,
            'harga_retail' => 1600,
            'stok' => 10,
            'deskripsi' => 'Produk tanpa penanda kelompok lama',
        ]);

        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(30)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->get(route('products.pbf'))
            ->assertOk()
            ->assertSee($medicine->nama_obat);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee($medicine->nama_obat);

        $this->get('/belanja-internal')->assertNotFound();
    }

    public function test_b2c_catalog_excludes_pbf_products_and_products_without_a_b2c_price(): void
    {
        Medicine::create([
            'nama_obat' => 'Produk Retail B2C',
            'kategori' => 'Pabrik Contoh',
            'harga' => 0,
            'harga_retail' => 12000,
            'harga_grosir' => 8000,
            'stok' => 10,
            'deskripsi' => 'Produk retail untuk pelanggan B2C.',
        ]);
        Medicine::create([
            'nama_obat' => 'Produk PBF',
            'kategori' => 'Pabrik Contoh',
            'harga' => 0,
            'kelompok' => 'PBF',
            'harga_retail' => 12000,
            'stok' => 10,
            'deskripsi' => 'Produk khusus distributor.',
        ]);
        Medicine::create([
            'nama_obat' => 'Produk Tanpa Harga B2C',
            'kategori' => 'Pabrik Contoh',
            'harga' => 0,
            'harga_grosir' => 8000,
            'stok' => 10,
            'deskripsi' => 'Produk yang hanya memiliki harga B2B.',
        ]);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Produk Retail B2C')
            ->assertDontSee('Produk PBF')
            ->assertDontSee('Produk Tanpa Harga B2C');
    }

    public function test_admin_can_create_products_with_zero_one_or_two_images_and_gallery_is_capped_at_two(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.produk.create'))
            ->assertOk()
            ->assertSee('productCropModal', false)
            ->assertSee('Atur potongan gambar')
            ->assertSee('Maksimal 2 foto')
            ->assertSee('Pilih Foto')
            ->assertSee('max-height:calc(100dvh - 2rem)', false)
            ->assertSee('display:flex; width:min(900px,100%); max-height:calc(100vh - 2rem); flex-direction:column; overflow:hidden', false)
            ->assertSee('min-height:0; flex:1 1 auto', false)
            ->assertSee('flex-shrink:0; justify-content:space-between', false)
            ->assertSee('id="productCropConfirm"', false);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Tanpa Foto',
            'kategori' => 'Pabrik Contoh',
            'harga_retail' => 1200,
            'stok' => 2,
        ])->assertRedirect(route('admin.produk.index'));
        $this->assertSame([], Medicine::where('nama_obat', 'Obat Tanpa Foto')->firstOrFail()->image_gallery);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Satu Foto',
            'kategori' => 'Pabrik Contoh',
            'harga_retail' => 1200,
            'stok' => 2,
            'gambar' => [UploadedFile::fake()->create('produk-satu.jpg', 100, 'image/jpeg')],
        ])->assertRedirect(route('admin.produk.index'));
        $this->assertCount(1, Medicine::where('nama_obat', 'Obat Satu Foto')->firstOrFail()->image_gallery);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat dengan Gambar',
            'kategori' => 'Pabrik Contoh',
            'harga_retail' => 1200,
            'stok' => 2,
            'gambar' => [
                UploadedFile::fake()->create('produk-awal.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('produk-kedua.png', 100, 'image/png'),
            ],
        ])->assertRedirect(route('admin.produk.index'));

        $medicine = Medicine::where('nama_obat', 'Obat dengan Gambar')->firstOrFail();
        $originalImage = $medicine->gambar;
        $originalGallery = $medicine->image_gallery;
        $this->assertCount(2, $originalGallery);
        $this->assertNotEmpty($originalImage);
        $this->assertFileExists(storage_path($originalImage));

        $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => $medicine->nama_obat,
            'kategori' => $medicine->kategori,
            'harga_retail' => 1200,
            'stok' => 2,
            'gambar' => [UploadedFile::fake()->create('produk-ketiga.png', 100, 'image/png')],
        ])->assertSessionHasErrors('gambar');

        $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => $medicine->nama_obat,
            'kategori' => $medicine->kategori,
            'harga_retail' => 1200,
            'stok' => 2,
            'hapus_gambar' => [$originalImage],
            'gambar' => [UploadedFile::fake()->create('produk-baru.png', 100, 'image/png')],
        ])->assertRedirect(route('admin.produk.index'));

        $medicine->refresh();
        $this->assertSame($originalGallery[1], $medicine->gambar);
        $this->assertCount(2, $medicine->image_gallery);
        $this->assertFileDoesNotExist(storage_path($originalImage));
        $this->assertFileExists(storage_path($medicine->gambar));

        $remainingImages = $medicine->image_gallery;
        $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => $medicine->nama_obat,
            'kategori' => $medicine->kategori,
            'harga_retail' => 1200,
            'stok' => 2,
            'hapus_gambar' => $remainingImages,
        ])->assertRedirect(route('admin.produk.index'));
        $medicine->refresh();
        $this->assertSame([], $medicine->image_gallery);
        foreach ($remainingImages as $image) {
            $this->assertFileDoesNotExist(storage_path($image));
        }

        foreach (['Obat Satu Foto', 'Obat dengan Gambar'] as $name) {
            foreach (Medicine::where('nama_obat', $name)->firstOrFail()->image_gallery as $image) {
                ImageHelper::deleteProductImage($image);
            }
        }
    }

    public function test_admin_cannot_upload_more_than_two_product_images(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from(route('admin.produk.create'))->post(route('admin.produk.store'), [
            'nama_obat' => 'Produk Tiga Foto',
            'kategori' => 'Pabrik Contoh',
            'stok' => 1,
            'gambar' => [
                UploadedFile::fake()->create('foto-1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('foto-2.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('foto-3.jpg', 100, 'image/jpeg'),
            ],
        ])->assertRedirect(route('admin.produk.create'))
            ->assertSessionHasErrors('gambar');
    }

    public function test_admin_can_remove_existing_product_photo_without_replacing_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Hapus Foto',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'gambar' => 'medicines/foto-hapus-utama.jpg',
            'gambar_tambahan' => ['medicines/foto-hapus-kedua.jpg'],
            'stok' => 2,
            'deskripsi' => 'Uji penghapusan foto',
        ]);
        $primaryPath = storage_path($medicine->gambar);
        $secondaryPath = storage_path($medicine->gambar_tambahan[0]);
        @mkdir(dirname($primaryPath), 0755, true);
        file_put_contents($primaryPath, 'primary');
        file_put_contents($secondaryPath, 'secondary');

        $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => $medicine->nama_obat,
            'kategori' => $medicine->kategori,
            'harga_retail' => 1200,
            'stok' => 2,
            'hapus_gambar' => [$medicine->gambar],
        ])->assertRedirect(route('admin.produk.index'));

        $medicine->refresh();
        $this->assertSame('medicines/foto-hapus-kedua.jpg', $medicine->gambar);
        $this->assertSame([], $medicine->gambar_tambahan);
        $this->assertFileDoesNotExist($primaryPath);
        $this->assertFileExists($secondaryPath);

        ImageHelper::deleteProductImage($medicine->gambar);
    }

    public function test_stock_conversion_remains_available_but_customer_purchase_uses_package_unit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Paracetamol 500mg Box',
            'kategori_produk' => Companies::LIST[0],
            'kategori' => 'Kimia Farma',
            'logo' => 'HIJAU',
            'satuan' => 'Box',
            'harga_modal' => 10000,
            'harga_grosir' => 13000,
            'harga_retail' => 15000,
            'stok' => 10,
            'strips_per_box' => 10,
            'tablets_per_strip' => 10,
            'price_strip' => 1600,
            'price_tablet' => 200,
        ])->assertRedirect(route('admin.produk.index'));

        $medicine = Medicine::where('nama_obat', 'Paracetamol 500mg Box')->firstOrFail();
        $this->assertTrue($medicine->usesTabletPackaging());
        $this->assertSame(10, $medicine->strips_per_box);
        $this->assertSame(10, $medicine->tablets_per_strip);
        $this->assertSame(1000, $medicine->stock_base_units);
        $this->assertSame([
            'box' => 10,
            'box_decimal' => 10.0,
            'strip' => 100,
            'tablet' => 1000,
        ], $medicine->getConvertedUnitStocksForUnits());
        $this->assertSame('10 Box · 100 Strip · 1.000 Tablet', $medicine->converted_stock_summary);

        // Customer buys 1 Box (100 Tablet)
        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan Box',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh',
            'items' => [
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 1, 'harga' => 15000, 'sale_unit' => 'box'],
            ],
            'total' => 15000,
        ])->assertOk();

        // Check stock availability endpoint after buying 1 Box -> 9 Box, 90 Strip, 900 Tablet
        $this->getJson(route('products.stock-availability', ['ids' => [$medicine->id]]))
            ->assertOk()
            ->assertJsonPath("stock.{$medicine->id}.units", 900)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.box", 9)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.strip", 90)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.tablet", 900)
            ->assertJsonPath("stock.{$medicine->id}.converted_summary", '9 Box · 90 Strip · 900 Tablet');

        $this->getJson(route('products.stock-availability', ['ids' => [$medicine->id]]))
            ->assertOk()
            ->assertJsonPath("stock.{$medicine->id}.units", 900)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.box", 9)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.strip", 90)
            ->assertJsonPath("stock.{$medicine->id}.unit_stocks.tablet", 900)
            ->assertJsonPath("stock.{$medicine->id}.converted_summary", '9 Box · 90 Strip · 900 Tablet');
    }

    public function test_product_gallery_uses_primary_and_secondary_images_and_opens_detail(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Galeri Katalog',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'harga_grosir' => 1400,
            'harga_retail' => 1600,
            'gambar' => 'medicines/foto-utama.jpg',
            'gambar_tambahan' => ['medicines/foto-kedua.jpg'],
            'stok' => 10,
            'deskripsi' => 'Produk dengan dua foto',
        ]);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('product-gallery-secondary', false)
            ->assertSee('foto-kedua.jpg', false)
            ->assertSee('object-fit: contain !important', false)
            ->assertSee('transition: opacity .7s cubic-bezier(.22, 1, .36, 1)', false)
            ->assertSee('.product-gallery-link:hover .product-gallery-secondary', false)
            ->assertSee(route('medicines.show', $medicine->id), false);

        $this->get(route('medicines.show', $medicine->id))
            ->assertOk()
            ->assertSee('detail-gallery-thumb', false)
            ->assertSee('foto-kedua.jpg', false);
    }
}
