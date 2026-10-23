<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kiosk_order_is_saved_pending_and_uses_catalog_price(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Vitamin Kios',
            'kategori' => 'MEDIX',
            'harga' => 1000,
            'harga_retail' => 2500,
            'stok' => 8,
            'deskripsi' => 'Produk untuk transaksi kios',
        ]);

        $response = $this->postJson(route('orders.history.store'), [
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
        $this->assertSame(5000, $order->total);
        $this->assertSame(2500, $order->items[0]['harga']);
        $this->assertSame(0, $order->items[0]['potongan']);
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'stok' => 6]);
    }

    public function test_cashier_can_mark_a_kiosk_order_paid_once(): void
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
            ->assertSee('Pembayaran diterima');

        $this->post(route('cashier.orders.paid', $order))
            ->assertRedirect(route('cashier.dashboard'));

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'cashier_id' => $cashier->id,
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_admin_can_create_cashier_and_view_customer_records_without_passwords(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'role' => 'user',
            'name' => 'Customer Contoh',
            'address' => 'Jl. Contoh',
            'is_default_address' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Customer Contoh')
            ->assertSee($customer->email)
            ->assertDontSee($customer->password);

        $this->post(route('admin.cashiers.store'), [
            'name' => 'Kasir Contoh',
            'email' => 'kasir@example.com',
            'password' => 'cashier-password',
            'password_confirmation' => 'cashier-password',
        ])->assertRedirect(route('admin.cashiers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'kasir@example.com',
            'is_cashier' => true,
            'role' => 'user',
        ]);
    }
}
