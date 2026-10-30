<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Customer;
use App\Models\PurchaseHistory;
use App\Models\PurchasePaymentSetting;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_order_submission_is_no_longer_available(): void
    {
        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'phone' => '081234567890',
            'transaction_channel' => 'offline',
            'items' => [],
            'total' => 0,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('purchase_histories', 0);
    }

    public function test_internal_shopping_page_and_routes_are_removed(): void
    {
        $this->get('/belanja-internal')->assertNotFound();
        $this->get('/belanja-internal/akses')->assertNotFound();
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Belanja Internal');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertDontSee('Belanja Internal');
    }

    public function test_leaving_b2b_catalog_locks_access_until_the_code_is_entered_again(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);

        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->get(route('home'))
            ->assertOk()
            ->assertSessionMissing('pbf_access_expires_at')
            ->assertSessionMissing('pbf_access_code_hash');
        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Masukkan kode aktivasi terbaru');
    }

    public function test_cart_and_checkout_keep_shopping_channel_access_active(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);

        $session = [
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ];

        $this->withSession($session)->get(route('cart.index'))
            ->assertOk()
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);
        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);
    }

    public function test_b2b_catalog_shows_only_active_promos_discounted_from_wholesale_price(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $promoMedicine = Medicine::create([
            'nama_obat' => 'Obat Promo Grosir',
            'harga' => 1000,
            'harga_grosir' => 5000,
            'promo_b2b_percent' => 25,
            'stok' => 10,
        ]);
        Medicine::create([
            'nama_obat' => 'Obat Tanpa Promo Grosir',
            'harga' => 1000,
            'harga_grosir' => 5000,
            'promo_b2b_percent' => 0,
            'stok' => 10,
        ]);
        Medicine::create([
            'nama_obat' => 'Promo Tanpa Harga Grosir',
            'harga' => 1000,
            'promo_b2b_percent' => 50,
            'stok' => 10,
        ]);

        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ])->get(route('products.pbf'))
            ->assertOk()
            ->assertViewHas('promoProducts', function ($products) use ($promoMedicine): bool {
                return $products->count() === 1
                    && $products->first()->is($promoMedicine);
            })
            ->assertSee('Produk Promo B2B')
            ->assertSee('PROMO B2B -25%')
            ->assertSee('Rp 5.000')
            ->assertSee('Rp 3.750')
            ->assertDontSee('Promo Tanpa Harga Grosir')
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);
    }

    public function test_checkout_includes_product_logo_markers_for_cart_thumbnails(): void
    {
        Medicine::create([
            'nama_obat' => 'Produk Logo Hijau',
            'logo' => 'Hijau',
            'harga' => 10000,
            'stok' => 5,
        ]);
        Medicine::create([
            'nama_obat' => 'Produk Logo Merah',
            'logo' => 'Merah',
            'harga' => 10000,
            'stok' => 5,
        ]);
        Medicine::create([
            'nama_obat' => 'Produk Logo Biru',
            'logo' => 'Biru',
            'harga' => 10000,
            'stok' => 5,
        ]);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('"logo_color":"green"', false)
            ->assertSee('"logo_color":"red"', false)
            ->assertSee('"logo_color":"blue"', false)
            ->assertSee('co-sum-logo-marker--${logoColor}', false)
            ->assertSee('${logoColor === \'red\' ? \'K\' : \'\'}', false);
    }

    public function test_b2b_access_stays_active_when_returning_from_invoice_to_catalog(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $session = [
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
        ];

        $orderResponse = $this->withSession($session)->postJson('/orders/history', [
            'buyer_type' => 'pbf',
            'buyer_name' => 'PT Contoh PBF',
            'phone' => '081234567891',
            'address' => 'Jl. PBF No. 10',
            'items' => [
                ['name' => 'Paracetamol', 'qty' => 1, 'price' => 10000],
            ],
            'total' => 10000,
        ])->assertOk();

        $this->get($orderResponse->json('invoice_url'))
            ->assertOk()
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);
        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Anda masuk sebagai mitra PBF terverifikasi')
            ->assertSessionHas('pbf_access_code_hash', $b2bCode->code_hash);
    }

    public function test_explicit_b2b_checkout_uses_b2b_price_with_separate_active_internal_access(): void
    {
        $b2bCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('54321'),
            'expires_at' => now()->addDays(30),
        ]);
        $internalCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Harga B2B Internal',
            'kategori' => 'MEDIX',
            'harga' => 32000,
            'harga_retail' => 32000,
            'harga_internal' => 4500,
            'harga_grosir' => 17000,
            'stok' => 5,
        ]);

        $response = $this->withSession([
            'pbf_access_expires_at' => now()->addDays(20)->timestamp,
            'pbf_access_code_hash' => $b2bCode->code_hash,
            'internal_access_expires_at' => now()->addDays(20)->timestamp,
            'internal_access_code_hash' => $internalCode->code_hash,
        ])->postJson(route('orders.history.store'), [
            'buyer_type' => 'pbf',
            'price_channel' => 'b2b',
            'buyer_name' => 'Mitra B2B',
            'requester_name' => 'Apoteker',
            'phone' => '081234567890',
            'address' => 'Alamat Mitra',
            'items' => [
                ['id' => $medicine->id, 'name' => $medicine->nama_obat, 'quantity' => 1, 'harga' => 1],
            ],
            'total' => 1,
        ])->assertOk();

        $history = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(17000, $history->items[0]['harga']);
        $this->assertSame(17000, $history->total);
    }

    public function test_access_lock_endpoint_only_locks_b2b_sessions(): void
    {
        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(10)->timestamp,
            'pbf_access_code_hash' => 'b2b-hash',
        ])->post(route('shopping.access.lock', ['channel' => 'b2b']))
            ->assertNoContent()
            ->assertSessionMissing('pbf_access_expires_at')
            ->assertSessionMissing('pbf_access_code_hash');

        $this->withSession(['pbf_access_code_hash' => 'b2b-hash'])
            ->post(route('shopping.access.lock', ['channel' => 'internal']))
            ->assertNotFound()
            ->assertSessionHas('pbf_access_code_hash', 'b2b-hash');
    }

    public function test_cashier_can_verify_and_mark_an_order_printed_once(): void
    {
        $cashier = User::factory()->create([
            'role' => 'user',
            'is_cashier' => true,
            'password' => Hash::make('cashier-password'),
        ]);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Transaksi Selesai',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'stok' => 5,
            'terjual' => 2,
            'grade' => 'A',
            'deskripsi' => 'Produk untuk verifikasi penjualan',
        ]);
        Medicine::create([
            'nama_obat' => 'Obat Pembanding Terlaris',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'stok' => 5,
            'terjual' => 3,
            'grade' => 'A',
            'deskripsi' => 'Produk pembanding penjualan',
        ]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'phone' => '081234567890',
            'items' => [[
                'id' => $medicine->id,
                'quantity' => 2,
                'sale_unit' => 'box',
            ]],
            'total' => 10000,
            'original_total' => 10000,
            'discounted_total' => 10000,
            'transaction_channel' => 'offline',
            'payment_status' => 'pending',
            'payment_method' => 'Tunai',
            'stock_committed' => false,
        ]);
        \App\Models\PurchaseStockReservation::create([
            'purchase_history_id' => $order->id,
            'medicine_id' => $medicine->id,
            'quantity_units' => 2,
            'sale_quantity' => 2,
        ]);

        $this->post(route('cashier.login.post'), [
            'email' => $cashier->email,
            'password' => 'cashier-password',
        ])->assertRedirect(route('cashier.dashboard'));

        $this->get(route('cashier.dashboard'))
            ->assertOk()
            ->assertSee('Pembeli')
            ->assertSee('id="cashier-order-notification"', false)
            ->assertSee('class="cashier-order-notification-count" id="cashier-order-notification-count">1</span>', false)
            ->assertSee('id="pending-queue-link"', false)
            ->assertSee('1 pesanan menunggu verifikasi')
            ->assertSee('id="pending-tab-count"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('Verifikasi & Cetak')
            ->assertDontSee('href="' . route('admin.dashboard') . '"', false);
        $this->get(route('cashier.updates'))
            ->assertOk()
            ->assertJson(['pending' => 1, 'latest_pending_id' => $order->id]);

        $this->post(route('cashier.orders.verify', $order))
            ->assertRedirect(route('cashier.orders.receipt', ['order' => $order->id, 'print' => 1]));
        $this->get(route('cashier.orders.receipt', ['order' => $order->id, 'print' => 1]))
            ->assertOk()
            ->assertSee('STRUK PENJUALAN')
            ->assertSee('TRX/')
            ->assertSee('No. Telepon')
            ->assertSee('081234567890')
            ->assertSee($cashier->name)
            ->assertSee('Cetak Struk')
            ->assertSee('window.print()')
            ->assertSee('Lunas');

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'receipt_status' => 'verified',
            'cashier_id' => $cashier->id,
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame(3, $medicine->fresh()->stok);
        $this->assertSame(4, $medicine->fresh()->terjual);
        $this->assertDatabaseMissing('purchase_stock_reservations', [
            'purchase_history_id' => $order->id,
        ]);
        $this->assertDatabaseHas('medicine_stock_movements', [
            'medicine_id' => $medicine->id,
            'movement_type' => 'sale',
            'physical_delta' => -2,
            'reserved_delta' => -2,
            'reference_id' => $order->id,
        ]);
        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('featuredProducts', fn ($products) => !$products->contains('id', $medicine->id));

        $this->post(route('cashier.orders.printed', $order))
            ->assertRedirect();
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'receipt_status' => 'printed',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('cashier.orders.receipt', $order))
            ->assertOk()
            ->assertSee('STRUK PENJUALAN')
            ->assertSee($cashier->name)
            ->assertSee('Kembali ke dashboard admin');
    }

    public function test_admin_can_open_admin_dashboard_from_cashier_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('cashier.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('href="' . route('admin.dashboard') . '"', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('📊 Dashboard')
            ->assertDontSee('ðŸ');
    }

    public function test_cashier_can_set_purchase_qris_and_bank_details_for_checkout(): void
    {
        $cashier = User::factory()->create(['role' => 'user', 'is_cashier' => true]);
        Storage::fake('public');

        $this->actingAs($cashier)
            ->get(route('cashier.dashboard'))
            ->assertOk()
            ->assertSee('Pengaturan QRIS & rekening pembayaran')
            ->assertSee(route('cashier.payment-settings.update'), false);

        $this->put(route('cashier.payment-settings.update'), [
            'qris_image' => UploadedFile::fake()->image('qris.png'),
            'bank_name' => 'Bank Contoh',
            'account_number' => '1234567890',
            'account_holder' => 'Apotek Medix',
            'payment_instructions' => 'Cantumkan nomor pesanan.',
        ])->assertRedirect(route('cashier.dashboard') . '#purchase-payment-settings');

        $settings = PurchasePaymentSetting::firstOrFail();
        Storage::disk('public')->assertExists($settings->qris_image_path);
        $this->get('/storage/' . $settings->qris_image_path)->assertOk();

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee($settings->qris_image_url, false)
            ->assertSee('Bank Contoh')
            ->assertSee('1234567890')
            ->assertSee('Apotek Medix')
            ->assertSee('Cantumkan nomor pesanan.')
            ->assertDontSee('QRIS Dummy')
            ->assertDontSee('6540126787');
    }

    public function test_checkout_does_not_prefill_an_admin_name_in_the_customer_name_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Apotek Medix']);

        $this->actingAs($admin)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('placeholder="Nama" value=""', false)
            ->assertDontSee('value="Admin Apotek Medix"', false);
    }

    public function test_login_forms_can_reveal_password_and_keep_it_after_failed_login(): void
    {
        $cashierPassword = 'wrong-cashier-password';
        $this->from(route('cashier.login'))
            ->post(route('cashier.login.post'), [
                'email' => 'kasir@example.com',
                'password' => $cashierPassword,
            ])
            ->assertRedirect(route('cashier.login'));

        $this->get(route('cashier.login'))
            ->assertOk()
            ->assertSee('type="password" name="password" value="' . $cashierPassword . '"', false)
            ->assertSee('aria-label="Tampilkan kata sandi"', false);

        $adminPassword = 'wrong-admin-password';
        $this->from(route('login'))
            ->post(route('login.post'), [
                'email' => 'admin@example.com',
                'password' => $adminPassword,
            ])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('value="' . $adminPassword . '"', false)
            ->assertSee('aria-label="Tampilkan password"', false);
    }

    public function test_cancelling_a_pending_order_restores_stock_once(): void
    {
        $cashier = User::factory()->create(['role' => 'user', 'is_cashier' => true]);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Pesanan Dibatalkan',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'stok' => 6,
            'deskripsi' => 'Produk untuk uji pengembalian stok',
        ]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 2,
                'sale_unit' => 'box',
            ]],
            'total' => 2000,
            'transaction_channel' => 'offline',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($cashier)
            ->post(route('cashier.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(8, $medicine->fresh()->stock_base_units);
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'receipt_status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);

        $this->post(route('cashier.orders.cancel', $order))->assertRedirect();
        $this->assertSame(8, $medicine->fresh()->stock_base_units);
    }

    public function test_admin_can_cancel_a_printed_receipt_and_restore_stock_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Struk Dicetak',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'stok' => 4,
            'deskripsi' => 'Produk untuk uji pembatalan struk dicetak',
        ]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 2,
                'sale_unit' => 'box',
            ]],
            'total' => 2000,
            'original_total' => 2000,
            'discounted_total' => 2000,
            'transaction_channel' => 'online',
            'payment_status' => 'paid',
            'receipt_status' => 'printed',
            'stock_committed' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('cashier.dashboard', ['status' => 'printed']))
            ->assertOk()
            ->assertSee('Batalkan struk');

        $this->post(route('cashier.orders.cancel', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(6, $medicine->fresh()->stock_base_units);
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'receipt_status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);

        $this->post(route('cashier.orders.cancel', $order))->assertRedirect();
        $this->assertSame(6, $medicine->fresh()->stock_base_units);
    }

    public function test_cancelling_a_reserved_pending_order_releases_reserved_stock(): void
    {
        $cashier = User::factory()->create(['role' => 'user', 'is_cashier' => true]);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Reservasi Batal',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'stok' => 6,
            'deskripsi' => 'Produk untuk uji pelepasan reservasi',
        ]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'items' => [['id' => $medicine->id, 'quantity' => 2, 'sale_unit' => 'box']],
            'total' => 2000,
            'transaction_channel' => 'online',
            'payment_status' => 'pending',
            'stock_committed' => false,
        ]);
        \App\Models\PurchaseStockReservation::create([
            'purchase_history_id' => $order->id,
            'medicine_id' => $medicine->id,
            'quantity_units' => 2,
            'sale_quantity' => 2,
        ]);

        $this->actingAs($cashier)
            ->post(route('cashier.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(6, $medicine->fresh()->stok);
        $this->assertDatabaseMissing('purchase_stock_reservations', [
            'purchase_history_id' => $order->id,
        ]);
        $this->getJson(route('products.stock-availability', ['ids' => [$medicine->id]]))
            ->assertOk()
            ->assertJsonPath("stock.{$medicine->id}.units", 6);
    }

    public function test_credit_term_order_verification_does_not_mark_payment_as_paid(): void
    {
        $cashier = User::factory()->create(['role' => 'user', 'is_cashier' => true]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Mitra',
            'items' => [],
            'total' => 10000,
            'transaction_channel' => 'online',
            'payment_status' => 'pending',
            'payment_method' => 'Tempo',
        ]);

        $this->actingAs($cashier)
            ->post(route('cashier.orders.verify', $order))
            ->assertRedirect(route('cashier.orders.receipt', ['order' => $order->id, 'print' => 1]));

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'receipt_status' => 'verified',
            'payment_status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function test_admin_can_view_customer_records_without_passwords(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create([
            'identity_key' => hash('sha256', 'registered-customer'),
            'buyer_type' => 'umum',
            'name' => 'Customer Contoh',
            'email' => 'customer@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Customer Contoh')
            ->assertSee($customer->email)
            ->assertSee('Tambah Customer')
            ->assertDontSee('password');
    }

    public function test_admin_customer_table_omits_outlet_and_shows_total_order_value(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create([
            'identity_key' => hash('sha256', 'customer-total-value'),
            'buyer_type' => 'umum',
            'name' => 'Customer Total Harga',
            'phone' => '081234567890',
        ]);

        foreach ([12500, 27500] as $total) {
            PurchaseHistory::create([
                'buyer_type' => 'umum',
                'buyer_name' => $customer->name,
                'customer_id' => $customer->id,
                'items' => [],
                'total' => $total,
                'transaction_channel' => 'online',
                'payment_status' => 'pending',
            ]);
        }

        $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('<th>Total pesanan</th>', false)
            ->assertSee('Rp 40.000')
            ->assertDontSee('<th>Outlet / PBF</th>', false);
    }

    public function test_b2b_access_gate_shows_whatsapp_code_request_form(): void
    {
        $this->assertSame('/product-b2b', route('products.pbf', [], false));
        $this->get('/products-pbf')->assertRedirect('/product-b2b');

        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Belum memiliki kode akses?')
            ->assertSee('chat admin.')
            ->assertSee('<dialog', false)
            ->assertSee('Nama apoteker / dokter')
            ->assertSee('Nama outlet')
            ->assertSee('Nomor SIA')
            ->assertSee('Nomor SIPA / SIP')
            ->assertSee('Nomor STR')
            ->assertSee('Nomor telepon')
            ->assertSee('Alamat outlet')
            ->assertSee('name="requester_name" type="text" autocomplete="name" required', false)
            ->assertSee('name="phone" type="tel" autocomplete="tel" required', false)
            ->assertDontSee('name="outlet_name" type="text" autocomplete="organization" required', false)
            ->assertDontSee('name="sia" type="text" required', false)
            ->assertDontSee('name="sipa" type="text" required', false)
            ->assertDontSee('name="str" type="text" required', false)
            ->assertDontSee('name="address" type="text" autocomplete="street-address" required', false)
            ->assertSee('Chat WhatsApp untuk meminta kode');
    }

    public function test_admin_can_generate_and_replace_b2b_access_codes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee('Aktivasi Kode Belanja')
            ->assertDontSee('Kelola Kasir');

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_B2B))
            ->assertRedirect(route('admin.access-codes.index'));
        $b2bCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail()->readCode();
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $b2bCode);
        $b2bRecord = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail();
        $this->assertTrue($b2bRecord->verify($b2bCode));
        $this->assertTrue($b2bRecord->expires_at->between(now()->addDays(29), now()->addDays(30)->addMinute()));
        $this->post(route('products.pbf.verify'), ['kode' => $b2bCode])
            ->assertRedirect(route('products.pbf'));
        $this->assertGreaterThan(now()->timestamp, session('pbf_access_expires_at'));
        $this->assertLessThanOrEqual($b2bRecord->expires_at->timestamp, session('pbf_access_expires_at'));

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_B2B))
            ->assertRedirect(route('admin.access-codes.index'));
        $replacementCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail()->readCode();
        $this->assertNotSame($b2bCode, $replacementCode);
        $this->assertFalse(ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail()->verify($b2bCode));
        $this->assertTrue(ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail()->verify($replacementCode));
    }

    public function test_admin_access_code_page_handles_codes_encrypted_with_an_old_key(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $accessCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('12345'),
            'code_value' => 'invalid-encrypted-value',
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee('Kode tidak dapat dibaca — buat ulang')
            ->assertSee('Tidak dapat ditampilkan — buat ulang');

        $this->assertNull($accessCode->readCode());
        $this->assertTrue($accessCode->verify('12345'));

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_B2B))
            ->assertRedirect(route('admin.access-codes.index'));

        $accessCode->refresh();
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $accessCode->readCode());
    }

    public function test_admin_can_manage_channel_discounts_and_homepage_shows_b2c_promotions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Vitamin Promo Medix',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_retail' => 2500,
            'harga_modal' => 1500,
            'harga_grosir' => 1800,
            'stok' => 8,
            'deskripsi' => 'Vitamin promo untuk pengujian',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.promotions.index'))
            ->assertOk()
            ->assertSee('Diskon per Kanal')
            ->assertSee('Vitamin Promo Medix');

        $this->put(route('admin.promotions.update', $medicine), [
            'promo_b2b_percent' => 20,
            'promo_b2c_percent' => 30,
        ])->assertRedirect(route('admin.promotions.index'));

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'promo_b2b_percent' => 20,
            'promo_b2c_percent' => 30,
        ]);

        $this->get(route('admin.promotions.index'))
            ->assertOk()
            ->assertSee('Harga asli:')
            ->assertSee('Harga promo: Rp 1.440')
            ->assertSee('Harga promo: Rp 1.750')
            ->assertSee('input.addEventListener(\'input\', updateCalculatedPrice)', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Promo B2C')
            ->assertSee('Vitamin Promo Medix')
            ->assertSee('Diskon 30%');
    }

    public function test_generated_b2b_code_protects_catalog_and_expires(): void
    {
        $code = '54321';
        ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addDays(30),
        ]);

        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Masukkan kode angka 5 digit.')
            ->assertSee('type="password"', false);
        $this->from(route('products.pbf'))
            ->post(route('products.pbf.verify'), ['kode' => '12345'])
            ->assertRedirect(route('products.pbf'))
            ->assertSessionHasErrors('kode');

        $this->post(route('products.pbf.verify'), ['kode' => $code])
            ->assertRedirect(route('products.pbf'));
        $this->assertGreaterThan(now()->timestamp, session('pbf_access_expires_at'));

        $this->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('id="pbfWelcomeOverlay"', false)
            ->assertSee('Akses berhasil. Selamat datang di katalog B2B.')
            ->assertSee('Mulai Belanja Sekarang');

        $this->post(route('products.pbf.logout'))
            ->assertRedirect(route('products.pbf'))
            ->assertSessionMissing('pbf_access_expires_at');

        $expiredCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_B2B)->firstOrFail();
        $expiredCode->update(['expires_at' => now()->subSecond()]);
        $this->post(route('products.pbf.verify'), ['kode' => $code])
            ->assertRedirect(route('products.pbf'))
            ->assertSessionHasErrors('kode');
    }

    public function test_admin_can_edit_or_delete_access_codes_and_revoke_existing_sessions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $code = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_B2B,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $oldHash = $code->code_hash;

        $this->actingAs($admin)
            ->put(route('admin.access-codes.update', $code), ['code' => '54321'])
            ->assertRedirect(route('admin.access-codes.index'));

        $code->refresh();
        $this->assertSame('54321', $code->readCode());
        $this->assertFalse($code->verify('12345'));
        $this->assertTrue($code->verify('54321'));
        $this->assertFalse(ShoppingAccessCode::matchesSession(ShoppingAccessCode::CHANNEL_B2B, $oldHash));

        $this->withSession([
            'pbf_access_expires_at' => now()->addDays(20)->timestamp,
            'pbf_access_code_hash' => $oldHash,
        ])->get(route('products.pbf'))
            ->assertOk()
            ->assertSee('Masukkan kode angka 5 digit.')
            ->assertSessionMissing('pbf_access_code_hash');

        $this->delete(route('admin.access-codes.destroy', $code))
            ->assertRedirect(route('admin.access-codes.index'));
        $this->assertDatabaseMissing('shopping_access_codes', ['id' => $code->id]);
    }

    public function test_online_orders_create_and_aggregate_customer_profiles(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Pesanan Online',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_retail' => 1000,
            'stok' => 5,
            'deskripsi' => 'Produk untuk uji sinkronisasi customer',
        ]);
        $b2b = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Mitra',
            'requester_name' => 'Sari',
            'outlet_name' => 'Apotek Mitra',
            'phone' => '082222222222',
            'transaction_channel' => 'online',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $b2c = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Online',
            'phone' => '083333333333',
            'transaction_channel' => 'online',
            'items' => [],
            'total' => 0,
        ])->assertOk();
        $repeatB2c = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Online',
            'phone' => '083333333333',
            'transaction_channel' => 'online',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('purchase_histories', ['id' => $b2b->json('id'), 'customer_id' => Customer::where('phone', '082222222222')->value('id')]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $b2c->json('id'), 'customer_id' => Customer::where('phone', '083333333333')->value('id')]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $repeatB2c->json('id'), 'customer_id' => Customer::where('phone', '083333333333')->value('id')]);
        $this->assertSame(2, Customer::count());
        $this->assertSame(2, Customer::where('phone', '083333333333')->firstOrFail()->purchaseHistories()->count());

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Export Customer per Kanal')
            ->assertSee('Sari')
            ->assertSee('Pembeli Online')
            ->assertSee('<th>Total pesanan</th>', false)
            ->assertSee('Rp 0', false)
            ->assertDontSee('<th>Outlet / PBF</th>', false)
            ->assertSee('B2B 1')
            ->assertSee('B2C 2');

        $this->get(route('admin.customers.index', ['channel' => 'b2b']))
            ->assertOk()
            ->assertSee('Sari')
            ->assertDontSee('Pembeli Online');
    }

    public function test_admin_can_create_update_and_delete_customer_without_deleting_order_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.customers.store'), [
                'buyer_type' => 'umum',
                'name' => 'Customer CRUD',
                'phone' => '081234567899',
                'address' => 'Alamat lama',
            ])
            ->assertRedirect(route('admin.customers.index'));

        $customer = Customer::where('phone', '081234567899')->firstOrFail();
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Customer CRUD',
            'customer_id' => $customer->id,
            'items' => [],
            'total' => 1000,
            'transaction_channel' => 'online',
            'payment_status' => 'pending',
        ]);

        $this->put(route('admin.customers.update', $customer), [
            'buyer_type' => 'umum',
            'name' => 'Customer CRUD Diperbarui',
            'phone' => '081234567899',
            'address' => 'Alamat baru',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Customer CRUD Diperbarui',
            'address' => 'Alamat baru',
        ]);

        $this->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $order->id, 'customer_id' => null]);
    }
}
