<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class B2cRedLogoAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_red_logo_products_are_hidden_until_b2c_code_is_activated(): void
    {
        $redMedicine = $this->createMedicine('Produk Logo Merah', 'Merah');
        $redMedicine->update(['promo_b2c_percent' => 20, 'kategori_produk' => 'Obat Oral']);
        $this->createMedicine('Produk Logo Hijau', 'Hijau');
        $accessCode = $this->createB2cAccessCode();

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Produk Logo Hijau')
            ->assertDontSee('Produk Logo Merah')
            ->assertSee('storageKey: "apotekmedix_cart_b2c"', false)
            ->assertSee('aria-label="Masukkan kode akses harga internal" aria-haspopup="dialog" onclick="document.getElementById(\'internalAccessDialog\').showModal()"', false)
            ->assertSee('aria-label="Masukkan kode B2C untuk membuka produk berlogo merah" aria-haspopup="dialog" onclick="document.getElementById(\'b2cAccessDialog\').showModal()">K', false)
            ->assertSee('id="b2cAccessDialog"', false)
            ->assertSee('WARNING B2C SALAH KLIK', false)
            ->assertDontSee('placeholder="5 digit"', false)
            ->assertSee('id="b2cAccessCode" name="kode" type="password"', false)
            ->assertSee(route('products.apotek.activate'), false);

        $this->get(route('products.show', $redMedicine))
            ->assertNotFound();
        $this->get(route('medicines.show', $redMedicine))
            ->assertNotFound();
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Logo Merah');
        $this->get(route('category.layer2', ['main' => 'apotik', 'sub' => 'oral']))
            ->assertOk()
            ->assertDontSee('Produk Logo Merah');

        $this->post(route('products.apotek.activate'), [
            'kode' => '00000',
            'access_type' => 'b2c',
            'redirect_to' => 'products.apotek',
        ])
            ->assertRedirect(route('products.apotek'))
            ->assertSessionHasErrors('kode');

        $this->post(route('products.apotek.activate'), [
            'kode' => '12345',
            'access_type' => 'b2c',
            'redirect_to' => 'products.apotek',
        ])
            ->assertRedirect(route('products.apotek'))
            ->assertSessionHas('success');

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Produk Logo Merah')
            ->assertSee('B2C aktif');
        $homeWithAccess = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Produk Logo Merah');
        $this->assertGreaterThanOrEqual(2, substr_count($homeWithAccess->getContent(), 'Produk Logo Merah'));
        $this->get(route('category.layer2', ['main' => 'apotik', 'sub' => 'oral']))
            ->assertOk()
            ->assertSee('Produk Logo Merah');

        $this->get(route('products.show', $redMedicine))
            ->assertOk()
            ->assertSee('isRedLogoLocked: false', false);
        $this->assertLessThanOrEqual(
            $accessCode->expires_at->timestamp,
            session('b2c_access_expires_at')
        );

        $this->post(route('products.apotek.access.logout', 'b2c'), [
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));
        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertDontSee('Produk Logo Merah');
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Logo Merah');
        $this->get(route('products.show', $redMedicine))
            ->assertNotFound();
    }

    public function test_red_logo_products_cannot_be_ordered_without_active_b2c_code(): void
    {
        $medicine = $this->createMedicine('Produk Merah Terbatas', 'red');
        $medicine->update(['harga_internal' => 6000]);
        $payload = [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan B2C',
            'phone' => '081234567890',
            'address' => 'Jalan Contoh',
            'items' => [
                ['id' => $medicine->id, 'name' => $medicine->nama_obat, 'qty' => 1, 'price' => 10000],
            ],
            'total' => 10000,
        ];

        $this->postJson(route('orders.history.store'), $payload)
            ->assertForbidden()
            ->assertJsonPath('message', 'Aktifkan kode akses B2C atau Internal untuk memesan produk berlogo merah.');
        $this->assertSame(0, PurchaseHistory::count());

        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'code_value' => encrypt('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $medicine->update(['harga_grosir' => 9000]);
        $this->post(route('products.apotek.activate'), ['kode' => '54321'])
            ->assertRedirect(route('products.pbf'))
            ->assertSessionHas('pbf_success');
        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(20)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->postJson(route('orders.history.store'), array_merge($payload, [
            'buyer_type' => 'pbf',
            'buyer_name' => 'Mitra PBF B2B',
        ]))->assertOk();

        $accessCode = $this->createB2cAccessCode();
        $this->withSession([
            'b2c_access_expires_at' => now()->addDays(20)->timestamp,
            'b2c_access_code_hash' => $accessCode->code_hash,
        ])->postJson(route('orders.history.store'), $payload)
            ->assertOk();

        $history = PurchaseHistory::latest('id')->firstOrFail();
        $this->assertSame(10000, $history->items[0]['harga']);
        $this->assertDatabaseHas('purchase_histories', [
            'buyer_name' => 'Pelanggan B2C',
            'payment_method' => null,
        ]);
    }

    public function test_b2c_code_opens_red_logo_products_while_internal_code_controls_prices(): void
    {
        $this->createMedicine('Produk Harga Internal', 'red')->update([
            'harga_retail' => 10000,
            'harga_internal' => 4500,
        ]);
        $b2cCode = $this->createB2cAccessCode();
        $accessCode = $this->createInternalAccessCode();

        $this->post(route('products.apotek.activate'), [
            'kode' => '12345',
            'access_type' => 'b2c',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));
        $this->post(route('products.apotek.internal.activate'), [
            'kode' => '54321',
            'access_type' => 'internal',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'))
            ->assertSessionHas('success');

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Rp 4.500')
            ->assertSee('data-price-channel="internal"', false)
            ->assertDontSee('data-red-logo-locked="1"', false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Katalog B2C')
            ->assertSee('Rp 4.500')
            ->assertSee('aria-label="Keluar dari akses harga internal"', false);
        $this->assertLessThanOrEqual(
            $accessCode->expires_at->timestamp,
            session('internal_access_expires_at')
        );
        $this->assertLessThanOrEqual(
            $b2cCode->expires_at->timestamp,
            session('b2c_access_expires_at')
        );
        $this->get(route('medicines.show', Medicine::where('nama_obat', 'Produk Harga Internal')->firstOrFail()))
            ->assertOk()
            ->assertSee('Rp 4.500')
            ->assertSee('isRedLogoLocked: false', false);

        $this->post(route('products.apotek.access.logout', 'internal'), [
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Rp 10.000')
            ->assertSee('Produk Harga Internal');
        $this->post(route('products.apotek.access.logout', 'b2c'), [
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));
        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertDontSee('Produk Harga Internal');
    }

    public function test_internal_code_alone_does_not_show_red_logo_products_in_b2c(): void
    {
        $medicine = $this->createMedicine('Produk Merah Akses B2C Saja', 'red');
        $this->createInternalAccessCode();

        $this->post(route('products.apotek.internal.activate'), [
            'kode' => '54321',
            'access_type' => 'internal',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertDontSee('Produk Merah Akses B2C Saja');
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Merah Akses B2C Saja');
        $this->get(route('medicines.show', $medicine))
            ->assertNotFound();
    }

    public function test_restricted_medicine_categories_are_hidden_until_b2c_code_is_active(): void
    {
        $restrictedProducts = [
            ['Produk Obat Resep Akses', 'obat-resep', true],
            ['Produk Obat Diabetes Akses', 'obat-diabetes', false],
            ['Produk Obat Hipertensi Akses', 'obat-hipertensi', false],
            ['Produk Obat Kolesterol Akses', 'obat-kolesterol', false],
            ['Produk Injeksi Infus Akses', 'injeksi-infus', false],
            ['Produk Pil KB Hormonal Akses', 'pil-kb-hormonal', false],
        ];
        $products = collect($restrictedProducts)->map(function (array $productData): Medicine {
            [$name, $category, $prescription] = $productData;

            return Medicine::create([
                'nama_obat' => $name,
                'logo' => 'Hijau',
                'kategori_produk' => $category,
                'is_resep' => $prescription,
                'harga' => 10000,
                'harga_retail' => 10000,
                'promo_b2c_percent' => 15,
                'stok' => 10,
                'deskripsi' => 'Produk untuk uji pembatasan kategori B2C.',
            ]);
        });
        $productNames = array_column($restrictedProducts, 0);

        $this->createInternalAccessCode();
        $this->post(route('products.apotek.internal.activate'), [
            'kode' => '54321',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));

        foreach ([route('products.apotek'), route('home')] as $catalogUrl) {
            $response = $this->get($catalogUrl)->assertOk();
            foreach ($productNames as $name) {
                $response->assertDontSee($name);
            }
        }
        foreach ($products as $product) {
            $this->get(route('products.show', $product))->assertNotFound();
        }

        $this->createB2cAccessCode();
        $this->post(route('products.apotek.activate'), [
            'kode' => '12345',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));

        foreach ([route('products.apotek'), route('home')] as $catalogUrl) {
            $response = $this->get($catalogUrl)->assertOk();
            foreach ($productNames as $name) {
                $response->assertSee($name);
            }
        }
        foreach ($products as $index => $product) {
            $this->get(route('products.show', $product))
                ->assertOk()
                ->assertSee($productNames[$index]);
        }
    }

    public function test_non_red_product_in_restricted_category_can_be_ordered_with_active_b2c_access(): void
    {
        $medicine = $this->createMedicine('Produk Diabetes Logo Hijau', 'Hijau');
        $medicine->update(['kategori_produk' => 'obat-diabetes']);
        $accessCode = $this->createB2cAccessCode();
        $payload = [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan B2C',
            'phone' => '081234567890',
            'address' => 'Jalan Contoh',
            'items' => [
                ['id' => $medicine->id, 'name' => $medicine->nama_obat, 'qty' => 1, 'price' => 10000],
            ],
            'total' => 10000,
        ];

        $response = $this->withSession([
            'b2c_access_expires_at' => now()->addDays(20)->timestamp,
            'b2c_access_code_hash' => $accessCode->code_hash,
        ])->postJson(route('orders.history.store'), $payload)
            ->assertOk();

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame('Produk Diabetes Logo Hijau', $order->items[0]['nama_obat']);
        $this->assertSame(10000, $order->total);
    }

    public function test_red_logo_inferred_from_obat_keras_is_hidden_without_b2c_code(): void
    {
        $medicine = $this->createMedicine('Logo Merah dari Golongan', '');
        $medicine->update(['golongan' => 'Obat Keras']);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertDontSee('Logo Merah dari Golongan');
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Logo Merah dari Golongan');

        $this->createB2cAccessCode();
        $this->post(route('products.apotek.activate'), [
            'kode' => '12345',
            'redirect_to' => 'products.apotek',
        ])->assertRedirect(route('products.apotek'));

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Logo Merah dari Golongan');
    }

    public function test_internal_price_channel_uses_its_own_cart_on_catalog_and_detail_pages(): void
    {
        $medicine = $this->createMedicine('Produk Keranjang Internal', 'green');
        $medicine->update(['harga_internal' => 4500]);
        $accessCode = $this->createInternalAccessCode();
        $session = [
            'internal_access_expires_at' => now()->addDays(20)->timestamp,
            'internal_access_code_hash' => $accessCode->code_hash,
        ];

        $this->withSession($session)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('storageKey: "apotekmedix_cart_internal"', false)
            ->assertSee('priceChannel: "internal"', false);

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('storageKey: "apotekmedix_cart_internal"', false)
            ->assertSee('priceChannel: "internal"', false);

        $this->get(route('medicines.show', $medicine))
            ->assertOk()
            ->assertSee('storageKey: "apotekmedix_cart_internal"', false)
            ->assertSee('priceChannel: "internal"', false)
            ->assertSee('id="detailUnitPriceDisplay">Rp 4.500</div>', false);
    }

    public function test_internal_checkout_uses_server_price_and_rejects_products_without_internal_price(): void
    {
        $medicine = $this->createMedicine('Produk Harga Internal', 'green');
        $medicine->update(['harga_retail' => 10000, 'harga_internal' => 3000]);
        $unpricedMedicine = $this->createMedicine('Produk Tanpa Harga Internal', 'green');
        $accessCode = $this->createInternalAccessCode();
        $internalSession = [
            'internal_access_expires_at' => now()->addDays(20)->timestamp,
            'internal_access_code_hash' => $accessCode->code_hash,
        ];
        $orderPayload = [
            'buyer_type' => 'umum',
            'price_channel' => 'internal',
            'buyer_name' => 'Pelanggan Internal',
            'phone' => '081234567890',
            'address' => 'Jalan Contoh',
            'items' => [
                [
                    'id' => $medicine->id,
                    'name' => $medicine->nama_obat,
                    'quantity' => 2,
                    'harga' => 1,
                    'potongan' => 100000,
                ],
            ],
            'total' => 2,
        ];

        $this->postJson(route('orders.history.store'), $orderPayload)
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses harga untuk kanal transaksi ini sudah tidak aktif.');

        $response = $this->withSession($internalSession)
            ->postJson(route('orders.history.store'), $orderPayload)
            ->assertOk();
        $history = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(6000, $history->total);
        $this->assertSame(3000, $history->items[0]['harga']);
        $this->assertSame(0, $history->items[0]['potongan']);

        $orderPayload['items'][0]['id'] = $unpricedMedicine->id;
        $orderPayload['items'][0]['name'] = $unpricedMedicine->nama_obat;
        $this->withSession($internalSession)
            ->postJson(route('orders.history.store'), $orderPayload)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Harga internal untuk produk Produk Tanpa Harga Internal belum diatur.');
    }

    public function test_products_with_empty_stock_cannot_be_ordered_in_b2c_internal_or_b2b(): void
    {
        $medicine = $this->createMedicine('Produk Stok Habis', 'green');
        $medicine->update([
            'stok' => 0,
            'satuan' => 'Box',
            'harga_retail' => 10000,
            'harga_grosir' => 8000,
            'harga_internal' => 6000,
        ]);
        $internalCode = $this->createInternalAccessCode();
        $basePayload = [
            'buyer_name' => 'Pelanggan',
            'items' => [
                ['id' => $medicine->id, 'name' => $medicine->nama_obat, 'quantity' => 1, 'harga' => 10000],
            ],
            'total' => 10000,
        ];

        $this->postJson(route('orders.history.store'), $basePayload + [
            'buyer_type' => 'umum',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Stok Produk Stok Habis tidak mencukupi. Stok tersisa: 0 Box.');

        $this->withSession([
            'internal_access_expires_at' => now()->addDays(20)->timestamp,
            'internal_access_code_hash' => $internalCode->code_hash,
        ])->postJson(route('orders.history.store'), $basePayload + [
            'buyer_type' => 'umum',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Stok Produk Stok Habis tidak mencukupi. Stok tersisa: 0 Box.');

        $this->postJson(route('orders.history.store'), $basePayload + [
            'buyer_type' => 'pbf',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Stok Produk Stok Habis tidak mencukupi. Stok tersisa: 0 Box.');

        $this->assertSame(0, PurchaseHistory::count());
    }

    public function test_admin_can_generate_internal_access_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee('Harga Internal')
            ->assertSee('membuka harga internal', false);

        $this->actingAs($admin)
            ->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_INTERNAL))
            ->assertRedirect(route('admin.access-codes.index'))
            ->assertSessionHas('success');

        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $accessCode->readCode());
        $this->assertTrue($accessCode->verify($accessCode->readCode()));
    }

    public function test_admin_can_generate_b2c_activation_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee('Produk Khusus B2C')
            ->assertSee('logo merah');

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_B2C))
            ->assertRedirect(route('admin.access-codes.index'))
            ->assertSessionHas('success');

        $accessCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2C)->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $accessCode->readCode());
        $this->assertTrue($accessCode->verify($accessCode->readCode()));
    }

    private function createMedicine(string $name, string $logo): Medicine
    {
        return Medicine::create([
            'nama_obat' => $name,
            'logo' => $logo,
            'kategori' => 'MEDIX',
            'harga' => 10000,
            'harga_retail' => 10000,
            'stok' => 10,
            'deskripsi' => 'Produk untuk menguji akses produk B2C.',
        ]);
    }

    private function createB2cAccessCode(): ShoppingAccessCode
    {
        return ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2C,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
    }

    private function createInternalAccessCode(): ShoppingAccessCode
    {
        return ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('54321'),
            'code_value' => encrypt('54321'),
            'expires_at' => now()->addDays(30),
        ]);
    }
}
