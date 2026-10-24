<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Customer;
use App\Models\PurchaseHistory;
use App\Models\ShoppingAccessCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kiosk_order_is_saved_pending_and_uses_catalog_price(): void
    {
        $accessCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $medicine = Medicine::create([
            'nama_obat' => 'Vitamin Kios',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_internal' => 2000,
            'harga_retail' => 2500,
            'promo_internal_percent' => 10,
            'stok' => 8,
            'deskripsi' => 'Produk untuk transaksi kios',
        ]);

        $response = $this->withSession([
            'internal_shopping_expires_at' => now()->addDays(30)->timestamp,
            'internal_shopping_code_hash' => $accessCode->code_hash,
        ])->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Toko',
            'phone' => '081234567890',
            'transaction_channel' => 'offline',
            'items' => [
                ['id' => $medicine->id, 'nama_obat' => $medicine->nama_obat, 'quantity' => 2, 'harga' => 1, 'potongan' => 5000],
            ],
            'total' => 2,
        ])->assertOk()->assertJsonStructure(['success', 'id', 'invoice_url']);

        $order = PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame('offline', $order->transaction_channel);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('Tunai', $order->payment_method);
        $this->assertSame(3600, $order->total);
        $this->assertSame(2000, $order->items[0]['harga']);
        $this->assertSame(400, $order->items[0]['potongan']);
        $this->assertSame(10, $order->items[0]['promo_percent']);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stok' => 6]);
    }

    public function test_internal_shopping_requires_access_code(): void
    {
        $this->get(route('shopping.internal'))
            ->assertRedirect(route('shopping.internal.access'));

        $accessCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('58294'),
            'expires_at' => now()->addDays(30),
        ]);

        $this->get(route('shopping.internal.access'))->assertOk()->assertSee('Kode akses');
        $this->from(route('shopping.internal.access'))
            ->post(route('shopping.internal.verify'), ['code' => '00000'])
            ->assertRedirect(route('shopping.internal.access'))
            ->assertSessionHasErrors('code');

        $this->post(route('shopping.internal.verify'), ['code' => '58294'])
            ->assertRedirect(route('shopping.internal'));
        $this->assertSame($accessCode->code_hash, session('internal_shopping_code_hash'));

        $this->assertLessThanOrEqual(
            now()->addDays(30)->timestamp,
            session('internal_shopping_expires_at')
        );
        $this->get(route('shopping.internal'))
            ->assertOk()
            ->assertSee('Belanja Internal');
    }

    public function test_internal_access_code_can_be_closed_and_requires_reentry(): void
    {
        $accessCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $this->withSession([
            'internal_shopping_expires_at' => now()->addHour()->timestamp,
            'internal_shopping_code_hash' => $accessCode->code_hash,
        ])->post(route('shopping.internal.logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing('internal_shopping_expires_at');

        $this->get(route('shopping.internal'))
            ->assertRedirect(route('shopping.internal.access'));
    }

    public function test_access_is_not_granted_when_code_is_not_configured(): void
    {
        $this        ->post(route('shopping.internal.verify'), ['code' => '12345'])
            ->assertSessionHasErrors('code');

        $this->get(route('shopping.internal'))
            ->assertRedirect(route('shopping.internal.access'));
    }

    public function test_guest_cannot_create_internal_order_directly_without_access_code(): void
    {
        $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'phone' => '081234567890',
            'transaction_channel' => 'offline',
            'items' => [],
            'total' => 0,
        ])->assertForbidden();

        $this->withSession([
            'internal_shopping_expires_at' => now()->subMinute()->timestamp,
        ])->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'phone' => '081234567890',
            'transaction_channel' => 'offline',
            'items' => [],
            'total' => 0,
        ])->assertForbidden();
    }

    public function test_cashier_can_verify_and_mark_an_order_printed_once(): void
    {
        $cashier = User::factory()->create([
            'role' => 'user',
            'is_cashier' => true,
            'password' => Hash::make('cashier-password'),
        ]);
        $order = PurchaseHistory::create([
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli',
            'phone' => '081234567890',
            'items' => [],
            'total' => 10000,
            'original_total' => 10000,
            'discounted_total' => 10000,
            'transaction_channel' => 'offline',
            'payment_status' => 'pending',
            'payment_method' => 'Tunai',
        ]);

        $this->post(route('cashier.login.post'), [
            'email' => $cashier->email,
            'password' => 'cashier-password',
        ])->assertRedirect(route('cashier.dashboard'));

        $this->get(route('cashier.dashboard'))
            ->assertOk()
            ->assertSee('Pembeli')
            ->assertSee('Verifikasi & Cetak')
            ->assertDontSee('href="' . route('admin.dashboard') . '"', false);
        $this->get(route('cashier.updates'))
            ->assertOk()
            ->assertJson(['pending' => 1, 'latest_pending_id' => $order->id]);

        $this->post(route('cashier.orders.verify', $order))
            ->assertRedirect(route('cashier.orders.receipt', ['order' => $order->id, 'print' => 1]));
        $this->get(route('cashier.orders.receipt', $order))
            ->assertOk()
            ->assertSee('LUNAS');

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'receipt_status' => 'verified',
            'cashier_id' => $cashier->id,
        ]);
        $this->assertNotNull($order->fresh()->paid_at);

        $this->post(route('cashier.orders.printed', $order))
            ->assertRedirect();
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'receipt_status' => 'printed',
        ]);
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

    public function test_admin_can_generate_replacement_codes_for_internal_and_b2b(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee('Aktivasi Kode Belanja')
            ->assertDontSee('Kelola Kasir');

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_INTERNAL))
            ->assertRedirect(route('admin.access-codes.index'));
        $internalCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail()->readCode();
        $this->assertMatchesRegularExpression('/^[0-9]{5}$/', $internalCode);
        $internalRecord = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail();
        $this->assertTrue($internalRecord->verify($internalCode));
        $this->assertNotSame($internalCode, $internalRecord->code_hash);
        $this->assertSame($internalCode, $internalRecord->readCode());
        $this->get(route('admin.access-codes.index'))
            ->assertOk()
            ->assertSee($internalCode)
            ->assertSee('Daftar Kode Akses');
        $this->assertTrue($internalRecord->expires_at->between(now()->addDays(29), now()->addDays(30)->addMinute()));
        $this->post(route('shopping.internal.verify'), ['code' => $internalCode])
            ->assertRedirect(route('shopping.internal'));
        $this->assertGreaterThan(now()->timestamp, session('internal_shopping_expires_at'));
        $this->assertLessThanOrEqual($internalRecord->expires_at->timestamp, session('internal_shopping_expires_at'));

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

        $this->post(route('admin.access-codes.generate', ShoppingAccessCode::CHANNEL_INTERNAL))
            ->assertRedirect(route('admin.access-codes.index'));
        $replacementCode = ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail()->readCode();
        $this->assertNotSame($internalCode, $replacementCode);
        $this->assertFalse(ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail()->verify($internalCode));
        $this->assertTrue(ShoppingAccessCode::where('channel', ShoppingAccessCode::CHANNEL_INTERNAL)->firstOrFail()->verify($replacementCode));
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
            'promo_internal_percent' => 10,
            'promo_b2b_percent' => 20,
            'promo_b2c_percent' => 30,
        ])->assertRedirect(route('admin.promotions.index'));

        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'promo_internal_percent' => 10,
            'promo_b2b_percent' => 20,
            'promo_b2c_percent' => 30,
        ]);

        $this->get(route('admin.promotions.index'))
            ->assertOk()
            ->assertSee('Harga asli:')
            ->assertSee('Harga promo: Rp 2.250')
            ->assertSee('Harga promo: Rp 1.440')
            ->assertSee('Harga promo: Rp 1.750')
            ->assertSee('input.addEventListener(\'input\', updateCalculatedPrice)', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Produk Promo')
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
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
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
        $this->assertFalse(ShoppingAccessCode::matchesSession(ShoppingAccessCode::CHANNEL_INTERNAL, $oldHash));

        $this->withSession([
            'internal_shopping_expires_at' => now()->addDays(20)->timestamp,
            'internal_shopping_code_hash' => $oldHash,
        ])->get(route('shopping.internal'))
            ->assertRedirect(route('shopping.internal.access'))
            ->assertSessionMissing('internal_shopping_code_hash');

        $this->delete(route('admin.access-codes.destroy', $code))
            ->assertRedirect(route('admin.access-codes.index'));
        $this->assertDatabaseMissing('shopping_access_codes', ['id' => $code->id]);
    }

    public function test_orders_from_all_channels_create_and_aggregate_customer_profiles(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Pesanan Internal',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_retail' => 1000,
            'stok' => 5,
            'deskripsi' => 'Produk untuk uji sinkronisasi customer',
        ]);
        $accessCode = ShoppingAccessCode::create([
            'channel' => ShoppingAccessCode::CHANNEL_INTERNAL,
            'code_hash' => Hash::make('12345'),
            'code_value' => encrypt('12345'),
            'expires_at' => now()->addDays(30),
        ]);
        $internal = $this->withSession([
            'internal_shopping_expires_at' => now()->addHour()->timestamp,
            'internal_shopping_code_hash' => $accessCode->code_hash,
        ])->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Internal',
            'phone' => '081111111111',
            'transaction_channel' => 'offline',
            'items' => [[
                'id' => $medicine->id,
                'nama_obat' => $medicine->nama_obat,
                'quantity' => 1,
                'sale_unit' => 'box',
            ]],
            'total' => 1000,
        ])->assertOk();

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

        $this->assertDatabaseHas('purchase_histories', ['id' => $internal->json('id'), 'customer_id' => Customer::where('phone', '081111111111')->value('id')]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $b2b->json('id'), 'customer_id' => Customer::where('phone', '082222222222')->value('id')]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $b2c->json('id'), 'customer_id' => Customer::where('phone', '083333333333')->value('id')]);
        $this->assertDatabaseHas('purchase_histories', ['id' => $repeatB2c->json('id'), 'customer_id' => Customer::where('phone', '083333333333')->value('id')]);
        $this->assertSame(3, Customer::count());
        $this->assertSame(2, Customer::where('phone', '083333333333')->firstOrFail()->purchaseHistories()->count());

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Pembeli Internal')
            ->assertSee('Apotek Mitra')
            ->assertSee('Pembeli Online')
            ->assertSee('Internal 1')
            ->assertSee('B2B 1')
            ->assertSee('B2C 2');

        $this->get(route('admin.customers.index', ['channel' => 'b2b']))
            ->assertOk()
            ->assertSee('Apotek Mitra')
            ->assertDontSee('Pembeli Internal')
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
