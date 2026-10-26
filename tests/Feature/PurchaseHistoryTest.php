<?php

namespace Tests\Feature;

use App\Models\Medicine;
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

        $this->withMiddleware()->get($response->json('invoice_url'))
            ->assertOk()
            ->assertSee('LOGO APOTEK MEDIX.PNG')
            ->assertSee('STRUK PENJUALAN')
            ->assertSee('APJ: Apt. Safwan Nugraha, S. Farm')
            ->assertSee('Vitamin C')
            ->assertSee('QRIS')
            ->assertSee('Rp 45.000')
            ->assertSee('Semoga lekas sembuh')
            ->assertSee('Sehat Bersama Medix')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('class="home-button" href="' . route('home') . '">Kembali ke Beranda', false)
            ->assertDontSee('Uang diterima')
            ->assertDontSee('Kembalian');
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
}
