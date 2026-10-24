<?php

namespace Tests\Feature;

use App\Constants\Companies;
use App\Helpers\ImageHelper;
use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MedicineUnitPackagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_package_stock_sizes_and_each_unit_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Kemasan',
            'kategori_produk' => Companies::LIST[0],
            'kategori' => 'Pabrik Contoh',
            'satuan' => 'Botol',
            'harga_modal' => 5000,
            'harga_internal' => 8000,
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
        $this->assertSame('Botol', $medicine->package_unit_label);
        $this->assertSame('2 Botol', $medicine->stock_breakdown);
        $this->assertSame(10, $medicine->getSaleUnitFactor('strip'));
        $this->assertSame(9900.0, $medicine->getSaleUnitPrice('box'));
        $this->assertSame(8000.0, $medicine->getChannelBasePrice('internal'));
        $this->assertSame(7000.0, $medicine->getChannelBasePrice('b2b'));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Beli per satuan')
            ->assertSee('Tablet · Rp 100', false)
            ->assertSee('data-stock-units="200"', false);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Tablet · Rp 100', false)
            ->assertSee('Strip · Rp 900', false)
            ->assertSee('Botol · Rp 9.900', false)
            ->assertSee('data-package-unit-label="Botol"', false);

        foreach ($medicine->image_gallery as $image) {
            ImageHelper::deleteProductImage($image);
        }
    }

    public function test_customer_can_choose_tablet_strip_or_package_and_stock_uses_package_conversion(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Satuan',
            'satuan' => 'Botol',
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
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 3, 'harga' => 1, 'sale_unit' => 'tablet'],
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 2, 'harga' => 1, 'sale_unit' => 'strip'],
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 1, 'harga' => 1, 'sale_unit' => 'box'],
            ],
            'total' => 3,
        ])->assertOk()->assertJsonStructure(['success', 'id', 'invoice_url']);

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(10100, $order->total);
        $this->assertSame('Tablet', $order->items[0]['unit_label']);
        $this->assertSame('Botol', $order->items[2]['unit_label']);
        $this->assertSame(100, $order->items[0]['harga']);
        $this->assertSame('18 Botol, 7 strip, 7 tablet', $medicine->fresh()->stock_breakdown);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_base_units' => 1877,
            'stok' => 18,
        ]);
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

        $medicine->update(['stock_base_units' => 0]);
        $this->assertSame('0 Tube', $medicine->fresh()->stock_breakdown);
    }

    public function test_customer_cannot_order_more_units_than_remain_in_stock(): void
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
                ['id' => $medicine->id, 'quantity' => 11, 'harga' => 1, 'sale_unit' => 'strip'],
            ],
            'total' => 11,
        ])->assertUnprocessable();

        $this->assertSame(100, $medicine->fresh()->stock_base_units);
    }

    public function test_customer_cannot_buy_a_unit_without_a_configured_b2c_price(): void
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
            'harga_internal' => 900,
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
            'pbf_name' => 'PBF Contoh',
            'pbf_address' => 'Alamat PBF',
            'is_pbf_purchase_order' => true,
            'transaction_channel' => 'online',
            'items' => [
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 2, 'harga' => 1200],
            ],
            'total' => 2400,
        ])->assertOk();

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertTrue($order->is_pbf_purchase_order);
        $this->assertSame(1000, $order->total);
        $this->assertSame(500, $order->items[0]['harga']);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_base_units' => 0,
            'stok' => 0,
        ]);
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

    public function test_products_with_channel_prices_appear_in_internal_b2b_and_b2c_catalogs_without_group(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Semua Kanal',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'harga_internal' => 1200,
            'harga_grosir' => 1400,
            'harga_retail' => 1600,
            'stok' => 10,
            'deskripsi' => 'Produk tanpa penanda kelompok lama',
        ]);

        $internalCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'internal_shopping_expires_at' => now()->addDays(30)->timestamp,
            'internal_shopping_code_hash' => $internalCode->code_hash,
        ])->get(route('shopping.internal'))
            ->assertOk()
            ->assertSee($medicine->nama_obat);

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
            'harga_internal' => 1000,
            'harga_retail' => 1200,
            'stok' => 2,
        ])->assertRedirect(route('admin.produk.index'));
        $this->assertSame([], Medicine::where('nama_obat', 'Obat Tanpa Foto')->firstOrFail()->image_gallery);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Satu Foto',
            'kategori' => 'Pabrik Contoh',
            'harga_internal' => 1000,
            'harga_retail' => 1200,
            'stok' => 2,
            'gambar' => [UploadedFile::fake()->create('produk-satu.jpg', 100, 'image/jpeg')],
        ])->assertRedirect(route('admin.produk.index'));
        $this->assertCount(1, Medicine::where('nama_obat', 'Obat Satu Foto')->firstOrFail()->image_gallery);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat dengan Gambar',
            'kategori' => 'Pabrik Contoh',
            'harga_internal' => 1000,
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
            'harga_internal' => 1000,
            'harga_retail' => 1200,
            'stok' => 2,
            'gambar' => [UploadedFile::fake()->create('produk-ketiga.png', 100, 'image/png')],
        ])->assertSessionHasErrors('gambar');

        $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => $medicine->nama_obat,
            'kategori' => $medicine->kategori,
            'harga_internal' => 1000,
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
            'harga_internal' => 1000,
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
            'harga_internal' => 1000,
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
            'harga_internal' => 1200,
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
            'harga_internal' => 1000,
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

    public function test_product_gallery_uses_primary_and_secondary_images_and_opens_detail(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Galeri Katalog',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'harga_internal' => 1200,
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
