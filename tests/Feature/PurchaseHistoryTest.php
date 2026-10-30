<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\PurchasePaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PurchaseHistoryTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_checkout_reserves_stock_until_transaction_is_completed(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Paracetamol',
            'kategori' => 'KIMIA FARMA',
            'harga' => 15000,
            'stok' => 10,
            'deskripsi' => 'Pereda demam dan nyeri',
        ]);

        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Budi',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 3, 'harga' => 15000],
            ],
            'total' => 45000,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stok' => 10,
        ]);
        $this->assertDatabaseHas('purchase_stock_reservations', [
            'purchase_history_id' => $response->json('id'),
            'medicine_id' => $medicine->id,
            'quantity_units' => 3,
        ]);
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $response->json('id'),
            'stock_committed' => false,
        ]);

        $this->getJson(route('products.stock-availability', ['ids' => [$medicine->id]]))
            ->assertOk()
            ->assertJsonPath("stock.{$medicine->id}.units", 7);

        $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Kedua',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 8, 'harga' => 15000],
            ],
            'total' => 120000,
        ])->assertUnprocessable();
        $this->assertDatabaseCount('purchase_stock_reservations', 1);
    }

    public function test_purchase_history_can_be_stored_via_public_endpoint(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotik Sehat',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 1',
            'kecamatan' => 'Kemayoran',
            'kota' => 'Jakarta Pusat',
            'sia' => 'SIA-001',
            'sipa' => 'SIPA-001',
            'items' => [
                ['name' => 'Paracetamol', 'qty' => 2, 'price' => 15000],
            ],
            'total' => 30000,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'id', 'invoice_url']);
        $history = \App\Models\PurchaseHistory::findOrFail($response->json('id'));

        $this->assertSame('Apotik Sehat', $history->buyer_name);
        $this->assertSame(30000, $history->total);
        $this->assertSame('Paracetamol', $history->items[0]['name']);
        $this->assertSame(15000, $history->items[0]['price']);
    }

    public function test_b2c_invoice_uses_the_medix_receipt_format(): void
    {
        PurchasePaymentSetting::create([
            'qris_image_path' => 'purchase-payment-settings/qris.png',
            'payment_instructions' => 'Cantumkan nomor pesanan.',
        ]);

        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Budi',
            'phone' => '081234567890',
            'payment_method' => 'QRIS',
            'items' => [
                ['nama_obat' => 'Vitamin C', 'quantity' => 2, 'harga' => 25000, 'potongan' => 5000],
            ],
            'total' => 45000,
        ])->assertOk();

        $order = \App\Models\PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame('umum', $order->buyer_type);
        $this->assertSame('online', $order->transaction_channel);
        $this->assertSame('purchase-payment-settings/qris.png', $order->payment_qris_image_path);

        $this->withMiddleware()->get($response->json('invoice_url'))
            ->assertOk()
            ->assertSee('LOGO APOTEK MEDIX.PNG')
            ->assertSee('STRUK PENJUALAN')
            ->assertSee('APJ: Apt. Safwan Nugraha, S. Farm')
            ->assertSee('Vitamin C')
            ->assertSee('QRIS')
            ->assertSee('Cantumkan nomor pesanan.')
            ->assertSee('QRIS pembayaran Apotek Medix')
            ->assertSee('Rp 45.000')
            ->assertSee('Semoga lekas sembuh')
            ->assertSee('Sehat Bersama Medix')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('class="home-button" href="' . route('home') . '">Kembali ke Beranda', false)
            ->assertDontSee('Uang diterima')
            ->assertDontSee('Kembalian');
    }

    public function test_transfer_orders_keep_the_custom_bank_details_for_the_invoice(): void
    {
        PurchasePaymentSetting::create([
            'bank_name' => 'Bank Contoh',
            'account_number' => '1234567890',
            'account_holder' => 'Apotek Medix',
            'payment_instructions' => 'Cantumkan ID transaksi.',
        ]);

        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Siti',
            'phone' => '081234567890',
            'payment_method' => 'Transfer',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $order = \App\Models\PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame('Bank Contoh', $order->payment_bank_name);
        $this->assertSame('1234567890', $order->payment_account_number);
        $this->assertSame('Apotek Medix', $order->payment_account_holder);

        $this->withMiddleware()->get($response->json('invoice_url'))
            ->assertOk()
            ->assertSee('Bank Contoh')
            ->assertSee('1234567890')
            ->assertSee('Apotek Medix')
            ->assertSee('Cantumkan ID transaksi.');
    }

    public function test_checkout_rejects_qris_when_no_qris_payment_setting_exists(): void
    {
        $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Budi',
            'payment_method' => 'QRIS',
            'items' => [],
            'total' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');
    }

    public function test_checkout_persists_b2c_delivery_or_pickup_selection(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Budi',
            'phone' => '081234567890',
            'address' => 'Ambil di Apotek Medix',
            'delivery_method' => 'pickup',
            'items' => [],
            'total' => 0,
        ])->assertOk();
        $deliveryResponse = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Siti',
            'phone' => '081234567891',
            'address' => 'Jl. Contoh No. 2',
            'delivery_method' => 'delivery',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $response->json('id'),
            'buyer_type' => 'umum',
            'transaction_channel' => 'offline',
        ]);
        $this->assertDatabaseHas('purchase_histories', [
            'id' => $deliveryResponse->json('id'),
            'buyer_type' => 'umum',
            'transaction_channel' => 'online',
        ]);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('purchase_histories', 'delivery_method'));
    }

    public function test_purchase_history_keeps_item_price_and_calculates_discount(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Budi',
            'items' => [
                ['id' => null, 'nama_obat' => 'Vitamin C', 'quantity' => 2, 'harga' => 25000, 'potongan' => 5000],
            ],
            'total' => 45000,
        ]);

        $response->assertOk();
        $history = \App\Models\PurchaseHistory::findOrFail($response->json('id'));

        $this->assertSame(50000, $history->original_total);
        $this->assertSame(45000, $history->discounted_total);
        $this->assertSame(25000, $history->items[0]['harga']);
        $this->assertSame(5000, $history->items[0]['potongan']);
    }

    public function test_checkout_applies_the_configured_channel_discount_to_catalog_prices(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Vitamin B2C Promo',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_retail' => 2500,
            'harga_modal' => 1500,
            'promo_b2c_percent' => 20,
            'stok' => 10,
            'deskripsi' => 'Produk promo B2C',
        ]);

        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli Promo',
            'transaction_channel' => 'online',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 2, 'harga' => 1],
            ],
            'total' => 2,
        ])->assertOk();

        $history = \App\Models\PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(5000, $history->original_total);
        $this->assertSame(4000, $history->total);
        $this->assertSame(1000, $history->items[0]['potongan']);
        $this->assertSame(20, $history->items[0]['promo_percent']);
    }

    public function test_pbf_checkout_uses_the_b2b_promo_and_wholesale_price(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk B2B Promo',
            'kategori' => 'MEDIX',
            'kelompok' => 'PBF',
            'harga' => 1000,
            'harga_retail' => 4000,
            'harga_modal' => 2000,
            'harga_grosir' => 5000,
            'promo_b2b_percent' => 25,
            'stok' => 10,
            'deskripsi' => 'Produk promo B2B',
        ]);

        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Mitra Promo',
            'transaction_channel' => 'online',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 1],
            ],
            'total' => 1,
        ])->assertOk();

        $history = \App\Models\PurchaseHistory::findOrFail($response->json('id'));
        $this->assertSame(5000, $history->original_total);
        $this->assertSame(3750, $history->total);
        $this->assertSame(1250, $history->items[0]['potongan']);
        $this->assertSame(25, $history->items[0]['promo_percent']);
    }

    public function test_b2b_checkout_does_not_fall_back_to_modal_or_internal_price(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Produk Tanpa Harga B2B',
            'kategori' => 'MEDIX',
            'kelompok' => 'PBF',
            'harga' => 1000,
            'harga_modal' => 2000,
            'stok' => 10,
            'deskripsi' => 'Harga B2B belum dikonfigurasi',
        ]);

        $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Mitra',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 1],
            ],
            'total' => 1,
        ])->assertUnprocessable();

        $this->assertSame(10, $medicine->fresh()->stok);
    }

    public function test_purchase_history_can_store_pbf_buyer_type(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'pbf',
            'buyer_name' => 'PT Contoh PBF',
            'phone' => '081234567891',
            'address' => 'Jl. PBF No. 10',
            'kecamatan' => 'Ilir Timur II',
            'kota' => 'Palembang',
            'sia' => 'SIA-PBF-001',
            'sipa' => 'SIPA-PBF-001',
            'items' => [
                ['name' => 'Paracetamol', 'qty' => 1, 'price' => 10000],
            ],
            'total' => 10000,
        ]);

        $response->assertOk();
        $this->withMiddleware()->get($response->json('invoice_url'))
            ->assertOk()
            ->assertSee('FAKTUR PENJUALAN')
            ->assertSee('APOTEKER PENANGGUNG JAWAB :')
            ->assertSee('No. SIA')
            ->assertSee('No. SIPA')
            ->assertSee('SIA-PBF-001')
            ->assertSee('SIPA-PBF-001')
            ->assertSee('Sehat Bersama Medix')
            ->assertSee('href="' . route('products.pbf') . '"><i class="fa-solid fa-arrow-left"></i> Kembali ke Belanja B2B', false)
            ->assertSee('TOTAL PEMBAYARAN');
        $this->assertDatabaseHas('purchase_histories', [
            'buyer_name' => 'PT Contoh PBF',
            'buyer_type' => 'pbf',
            'total' => 10000,
        ]);
    }

    public function test_purchase_history_can_store_doctor_buyer_details(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'dokter',
            'requester_name' => 'Admin Klinik',
            'outlet_name' => 'Klinik Sehat',
            'phone' => '081234567892',
            'address' => 'Jl. Dokter No. 5',
            'items' => [],
            'total' => 0,
        ]);

        $response->assertOk()->assertJsonStructure(['success', 'id', 'invoice_url']);
        $this->assertDatabaseHas('purchase_histories', [
            'buyer_name' => 'Admin Klinik',
            'requester_name' => 'Admin Klinik',
            'outlet_name' => 'Klinik Sehat',
            'buyer_type' => 'dokter',
        ]);
    }

    public function test_purchase_history_can_store_tempo_payment_with_custom_days_for_apotik_and_doctor(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'apotik',
            'requester_name' => 'Apt. Rina',
            'outlet_name' => 'Apotik Sejahtera',
            'phone' => '081234567893',
            'address' => 'Jl. Tempo No. 2',
            'payment_method' => 'Tempo',
            'payment_term_days' => 14,
            'items' => [
                ['name' => 'Paracetamol', 'qty' => 1, 'price' => 10000],
            ],
            'total' => 10000,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('purchase_histories', [
            'buyer_type' => 'apotik',
            'payment_method' => 'Tempo',
            'payment_term_days' => 14,
        ]);
    }

    public function test_b2c_cannot_create_an_order_with_tempo_payment(): void
    {
        $response = $this->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pembeli B2C',
            'phone' => '081234567894',
            'payment_method' => 'Tempo',
            'payment_term_days' => 14,
            'items' => [],
            'total' => 0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseMissing('purchase_histories', [
            'buyer_name' => 'Pembeli B2C',
            'payment_method' => 'Tempo',
        ]);
    }

    public function test_discount_code_decrements_used_count_when_verified_order_is_cancelled_and_omzet_excludes_cancelled_and_pbf_po(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Uji Bug',
            'kategori' => 'MEDIX',
            'logo' => 'K',
            'golongan' => 'Obat Keras',
            'harga' => 20000,
            'harga_retail' => 20000,
            'harga_modal' => 12000,
            'stok' => 10,
            'deskripsi' => 'Produk uji bug',
        ]);

        $this->assertTrue($medicine->hasRedLogo());

        // Admin edit produk harus 200 OK (tanpa TypeError StockAvailability::annotate)
        $this->withMiddleware()
            ->actingAs($admin)
            ->get(route('admin.produk.edit', $medicine))
            ->assertOk();

        $voucher = \App\Models\DiscountCode::create([
            'code' => 'HEMAT5K',
            'description' => 'Potongan 5rb',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'is_unlimited' => false,
            'usage_limit' => 2,
            'used_count' => 0,
            'is_active' => true,
        ]);

        // Simpan pesanan dengan voucher diskon
        $b2cCode = \App\Models\ShoppingAccessCode::create([
            'channel' => \App\Models\ShoppingAccessCode::CHANNEL_B2C,
            'code_hash' => \Illuminate\Support\Facades\Hash::make('11111'),
            'expires_at' => now()->addDays(30),
        ]);

        $res = $this->withSession([
            'b2c_access_expires_at' => now()->addDay()->timestamp,
            'b2c_access_code_hash' => $b2cCode->code_hash,
        ])->postJson('/orders/history', [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan Voucher',
            'phone' => '08123456000',
            'discount_code' => 'HEMAT5K',
            'items' => [
                ['id' => $medicine->id, 'quantity' => 1, 'harga' => 20000],
            ],
            'total' => 15000,
        ])->assertOk();

        $order = \App\Models\PurchaseHistory::findOrFail($res->json('id'));
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertSame(15000, $order->effective_total);

        // Verifikasi oleh kasir -> effective_total tetap 15000 & approval_status = approved
        $this->withMiddleware()
            ->actingAs($admin)
            ->post(route('cashier.orders.verify', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('verified', $order->receipt_status);
        $this->assertSame('approved', $order->approval_status);
        $this->assertSame(15000, $order->effective_total);

        // Buat PO Admin ke PBF (tidak boleh masuk omzet penjualan atau antrean kasir)
        \App\Models\PurchaseHistory::create([
            'buyer_type' => 'apotik',
            'buyer_name' => 'Apotek Medix',
            'pbf_name' => 'PT PBF Distributor',
            'items' => [['id' => $medicine->id, 'quantity' => 5, 'harga' => 12000]],
            'total' => 60000,
            'original_total' => 60000,
            'discounted_total' => 60000,
            'transaction_channel' => 'online',
            'is_pbf_purchase_order' => true,
            'payment_status' => 'pending',
        ]);

        // Cek totalOmzet di dashboard admin hanya 15000 (bukan 75000)
        $dashboardResp = $this->withMiddleware()
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
        $this->assertSame(15000, (int) $dashboardResp->viewData('totalOmzet'));

        // Batalkan pesanan yang sudah verified -> stok kembali, kuota voucher kembali (used_count = 0), omzet jadi 0
        $this->withMiddleware()
            ->actingAs($admin)
            ->post(route('cashier.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->receipt_status);
        $this->assertSame(0, $voucher->fresh()->used_count);
        $this->assertSame(10, $medicine->fresh()->stok);

        $dashboardAfterCancel = $this->withMiddleware()
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
        $this->assertSame(0, (int) $dashboardAfterCancel->viewData('totalOmzet'));
    }
}
