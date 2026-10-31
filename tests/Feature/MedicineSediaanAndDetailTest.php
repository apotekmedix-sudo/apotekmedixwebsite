<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MedicineSediaanAndDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_sediaan_for_produk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Paracetamol',
            'kategori_produk' => 'Bintang',
            'kategori' => 'PT. Test',
            'harga' => 12000,
            'stok' => 10,
            'sediaan' => 'box',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.produk.update', $medicine), [
            'nama_obat' => 'Paracetamol 500mg',
            'kategori_produk' => 'Bintang',
            'kategori' => 'PT. Test',
            'kelompok' => 'APOTEK',
            'sku' => 'SKU-1',
            'brand' => 'Test Brand',
            'terjual' => 0,
            'grade' => 'A',
            'harga' => 15000,
            'stok' => 12,
            'deskripsi' => 'Deskripsi',
            'komposisi' => 'Paracetamol',
            'indikasi' => 'Demam',
            'sediaan' => 'fls',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'sediaan' => 'fls',
        ]);
    }

    public function test_admin_product_forms_offer_homepage_search_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Lama',
            'kategori_produk' => 'OBAT',
            'harga' => 10000,
            'stok' => 5,
        ]);
        $categoryValues = [
            'obat-resep',
            'otc',
            'pil-kb-hormonal',
            'alat-kesehatan',
            'vitamin-suplemen',
            'flu-batuk',
            'ibu-anak',
            'obat-herbal',
            'obat-diabetes',
            'obat-hipertensi',
            'obat-kolesterol',
        ];

        foreach ([
            $this->actingAs($admin)->get(route('admin.produk.create')),
            $this->get(route('admin.produk.edit', $medicine)),
        ] as $response) {
            $response->assertOk();
            foreach ($categoryValues as $category) {
                $response->assertSee('value="' . $category . '"', false);
            }
            $response->assertSee('Stok Satuan')
                ->assertDontSee('Konversi Stok Otomatis')
                ->assertDontSee('name="strips_per_box"', false)
                ->assertDontSee('name="tablets_per_strip"', false);
        }
    }

    public function test_public_detail_page_shows_sediaan_information(): void
    {
        $longDescription = str_repeat('deskripsi-tanpa-spasi-', 30);
        $medicine = Medicine::create([
            'nama_obat' => 'Vitamin C',
            'kategori' => 'PT. Test',
            'harga' => 20000,
            'stok' => 5,
            'sediaan' => 'box',
            'deskripsi' => $longDescription,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('medicines.show', [
                'id' => $medicine->id,
                'from' => 'home',
            ]), false);

        $response = $this->get(route('medicines.show', [
            'id' => $medicine->id,
            'from' => 'home',
        ]));

        $response->assertOk();
        $response->assertSee('Sediaan');
        $response->assertSee('Box');
        $response->assertSee('data-product-share', false)
            ->assertSee('data-share-url="' . route('medicines.show', $medicine->id) . '"', false);
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('href="' . route('home') . '"', false);
        $response->assertSee('overflow-wrap: anywhere;', false);
        $response->assertSee(e($longDescription), false);
        $response->assertSee('id="detailMetaTextDialog"', false);
        $response->assertSee('Lihat selengkapnya');
    }

    public function test_b2b_product_detail_keeps_b2b_price_and_access_until_leaving_the_flow(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Harga B2B',
            'kategori' => 'PT. Test',
            'harga' => 32000,
            'harga_retail' => 32000,
            'harga_grosir' => 17000,
            'stok' => 5,
            'sediaan' => 'box',
        ]);

        $session = [
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ];

        $this->withSession($session)
            ->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('href="' . route('medicines.show', ['id' => $medicine->id, 'from' => 'b2b']) . '"', false)
            ->assertDontSee('navigator.sendBeacon', false);

        $this->get(route('medicines.show', ['id' => $medicine->id, 'from' => 'b2b']))
            ->assertOk()
            ->assertSee('id="detailUnitPriceDisplay">Rp 17.000</div>', false)
            ->assertDontSee('id="detailUnitPriceDisplay">Rp 32.000</div>', false)
            ->assertSee('storageKey: "apotekmedix_cart_b2b"', false)
            ->assertSee('priceChannel: "b2b"', false)
            ->assertSee('wholesaleOrder: true', false)
            ->assertSee('if (CART_CONFIG.wholesaleOrder) {', false)
            ->assertSee('goToCheckoutPage();', false)
            ->assertDontSee('data-product-share', false)
            ->assertSee('href="' . route('products.pbf') . '">Belanja B2B</a>', false)
            ->assertSee('Kembali ke Belanja B2B')
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);

        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Anda masuk sebagai mitra PBF terverifikasi');

        $this->get(route('home'))
            ->assertOk()
            ->assertSessionMissing('pbf_access_code_hash');
        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Masukkan kode aktivasi terbaru');
    }

    public function test_b2b_access_remains_active_from_catalog_through_checkout_submission(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Checkout B2B',
            'kategori' => 'PT. Test',
            'harga' => 32000,
            'harga_retail' => 32000,
            'harga_grosir' => 17000,
            'stok' => 5,
            'sediaan' => 'box',
        ]);

        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])
            ->get(route('products.pbf'))
            ->assertOk()
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);

        $this->get(route('storage.image', [
            'folder' => 'medicines',
            'filename' => 'middleware-regression-test.png',
        ]), [
            'Accept' => 'image/png',
            'Sec-Fetch-Dest' => 'image',
        ])
            ->assertNotFound()
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee("activeCtx.priceChannel = 'b2b';", false)
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);

        $response = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'price_channel' => 'b2b',
            'buyer_name' => 'Mitra B2B',
            'transaction_channel' => 'online',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 1],
            ],
            'total' => 1,
        ])->assertOk();

        $order = \App\Models\PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(17000, $order->total);
        $this->assertSame(17000, $order->items[0]['harga']);
        $this->assertSame('b2b', $order->price_channel);
    }

    public function test_prescription_and_otc_category_choices_filter_products(): void
    {
        $prescriptionMedicine = Medicine::create([
            'nama_obat' => 'Obat Resep Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 10000,
            'stok' => 5,
            'deskripsi' => 'Obat resep untuk pengujian',
        ]);
        $prescriptionMedicine->forceFill(['is_resep' => true])->save();

        Medicine::create([
            'nama_obat' => 'Obat OTC Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 8000,
            'stok' => 5,
            'deskripsi' => 'Obat bebas untuk pengujian',
        ]);

        $this->get(route('home', ['kategori_pilihan' => 'obat-resep']))
            ->assertOk()
            ->assertDontSee('Obat Resep Contoh')
            ->assertDontSee('Obat OTC Contoh');

        $b2cCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2C,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'b2c_access_expires_at' => now()->addDays(20)->timestamp,
            'b2c_access_code_hash' => $b2cCode->code_hash,
        ])->get(route('home', ['kategori_pilihan' => 'obat-resep']))
            ->assertOk()
            ->assertSee('Obat Resep Contoh')
            ->assertDontSee('Obat OTC Contoh');

        $this->get(route('home', ['kategori_pilihan' => 'otc']))
            ->assertOk()
            ->assertSee('Obat OTC Contoh')
            ->assertDontSee('Obat Resep Contoh');
    }

    public function test_home_category_filter_matches_homepage_product_categories(): void
    {
        Medicine::create([
            'nama_obat' => 'Vitamin C Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 10000,
            'stok' => 5,
        ]);
        Medicine::create([
            'nama_obat' => 'Paracetamol Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 8000,
            'stok' => 5,
        ]);

        $response = $this->get(route('home', [
            'kategori_pilihan' => 'vitamin-suplemen',
        ]))->assertOk()
            ->assertSee('Vitamin C Contoh')
            ->assertDontSee('Paracetamol Contoh')
            ->assertSee('value="vitamin-suplemen" selected', false);

        $this->assertSame([
            'obat-resep',
            'otc',
            'pil-kb-hormonal',
            'alat-kesehatan',
            'vitamin-suplemen',
            'flu-batuk',
            'ibu-anak',
            'obat-herbal',
            'obat-diabetes',
            'obat-hipertensi',
            'obat-kolesterol',
            'injeksi-infus',
            'skincare-kosmetik',
        ], array_keys($response->viewData('kategoriPilihanOptions')));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="' . route('home', [
                'kategori_pilihan' => 'vitamin-suplemen',
            ]) . '#homeSearchSection"', false);
    }

    public function test_home_category_filter_includes_injection_infusion_and_skincare_products(): void
    {
        Medicine::create([
            'nama_obat' => 'Cairan Medis Contoh',
            'kategori_produk' => 'OBAT',
            'sediaan' => 'Injeksi Infus',
            'harga' => 10000,
            'stok' => 5,
        ]);
        Medicine::create([
            'nama_obat' => 'Krim Wajah Contoh',
            'kategori_produk' => 'OBAT',
            'kategori' => 'Skincare',
            'sediaan' => 'Krim',
            'harga' => 8000,
            'stok' => 5,
        ]);
        Medicine::create([
            'nama_obat' => 'Produk Kapsul Contoh',
            'kategori_produk' => 'OBAT',
            'sediaan' => 'Kapsul',
            'harga' => 7000,
            'stok' => 5,
        ]);

        $this->get(route('home', ['kategori_pilihan' => 'injeksi-infus']))
            ->assertOk()
            ->assertDontSee('Cairan Medis Contoh');

        $b2cCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2C,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'b2c_access_expires_at' => now()->addDays(20)->timestamp,
            'b2c_access_code_hash' => $b2cCode->code_hash,
        ])->get(route('home', ['kategori_pilihan' => 'injeksi-infus']))
            ->assertOk()
            ->assertSee('Cairan Medis Contoh')
            ->assertDontSee('Krim Wajah Contoh')
            ->assertDontSee('Produk Kapsul Contoh');

        $this->get(route('home', ['kategori_pilihan' => 'skincare-kosmetik']))
            ->assertOk()
            ->assertSee('Krim Wajah Contoh')
            ->assertDontSee('Cairan Medis Contoh')
            ->assertDontSee('Produk Kapsul Contoh');
    }

    public function test_legacy_b2c_catalog_redirects_to_home_search_with_category_selected(): void
    {
        $this->get(route('products.index', [
            'kategori_pilihan' => 'vitamin-suplemen',
        ]))->assertRedirect(route('home', [
            'kategori_pilihan' => 'vitamin-suplemen',
        ]) . '#homeSearchSection');
    }

    public function test_b2b_category_filter_has_all_homepage_categories_and_filters_products(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);

        Medicine::create([
            'nama_obat' => 'Vitamin B2B Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 12000,
            'harga_grosir' => 9000,
            'stok' => 5,
            'komposisi' => 'Vitamin B kompleks',
        ]);
        Medicine::create([
            'nama_obat' => 'Obat Flu B2B Contoh',
            'kategori_produk' => 'OBAT',
            'harga' => 14000,
            'harga_grosir' => 10000,
            'stok' => 5,
            'komposisi' => 'Paracetamol',
        ]);

        $response = $this->withSession([
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->get(route('products.pbf', ['kategori_pilihan' => 'vitamin-suplemen']))
            ->assertOk()
            ->assertSee('Vitamin B2B Contoh')
            ->assertDontSee('Obat Flu B2B Contoh')
            ->assertSee('value="vitamin-suplemen" selected', false);

        foreach ([
            'obat-resep',
            'otc',
            'pil-kb-hormonal',
            'alat-kesehatan',
            'vitamin-suplemen',
            'flu-batuk',
            'ibu-anak',
            'obat-herbal',
            'obat-diabetes',
            'obat-hipertensi',
            'obat-kolesterol',
        ] as $category) {
            $response->assertSee('value="' . $category . '"', false);
        }
    }

    public function test_home_search_engine_combines_keyword_category_company_and_sort_filters(): void
    {
        Medicine::create([
            'nama_obat' => 'Vitamin C Pilihan',
            'kategori' => 'Pabrik Pilihan',
            'kategori_produk' => 'OBAT',
            'harga' => 12000,
            'harga_retail' => 12000,
            'stok' => 5,
            'komposisi' => 'Vitamin C',
        ]);
        Medicine::create([
            'nama_obat' => 'Vitamin C Pabrik Lain',
            'kategori' => 'Pabrik Lain',
            'kategori_produk' => 'OBAT',
            'harga' => 11000,
            'harga_retail' => 11000,
            'stok' => 5,
            'komposisi' => 'Vitamin C',
        ]);
        Medicine::create([
            'nama_obat' => 'Paracetamol Pilihan',
            'kategori' => 'Pabrik Pilihan',
            'kategori_produk' => 'OBAT',
            'harga' => 9000,
            'harga_retail' => 9000,
            'stok' => 5,
            'komposisi' => 'Paracetamol',
        ]);

        $response = $this->get(route('home', [
            'search' => 'vitamin',
            'kategori_pilihan' => 'vitamin-suplemen',
            'perusahaan' => 'Pabrik Pilihan',
            'sort' => 'nama',
        ]))->assertOk()
            ->assertSee('name="kategori_pilihan"', false)
            ->assertSee('name="perusahaan"', false)
            ->assertSee('name="sort"', false)
            ->assertSee('value="vitamin-suplemen" selected', false)
            ->assertSee('value="Pabrik Pilihan" selected', false)
            ->assertSee('value="nama" selected', false)
            ->assertSee('Vitamin C Pilihan')
            ->assertDontSee('Vitamin C Pabrik Lain')
            ->assertDontSee('Paracetamol Pilihan');

        $this->assertTrue($response->viewData('homeHasFilters'));
        $this->assertCount(1, $response->viewData('allB2cProducts'));
    }

    public function test_home_category_filter_matches_product_category(): void
    {
        Medicine::create([
            'nama_obat' => 'Produk Kategori Khusus',
            'kategori' => 'Pabrik Contoh',
            'kategori_produk' => 'vitamin-suplemen',
            'harga' => 12000,
            'harga_retail' => 12000,
            'stok' => 5,
        ]);

        $this->get(route('home', ['kategori_pilihan' => 'vitamin-suplemen']))
            ->assertOk()
            ->assertSee('Produk Kategori Khusus');
    }
}
