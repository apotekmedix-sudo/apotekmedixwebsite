<?php

namespace Tests\Feature;

use App\Constants\Companies;
use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineUnitPackagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_box_stock_package_sizes_and_each_unit_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.produk.store'), [
            'nama_obat' => 'Obat Kemasan',
            'kategori_produk' => Companies::LIST[0],
            'kategori' => 'Pabrik Contoh',
            'harga' => '',
            'stok' => 2,
            'unit_packaging_enabled' => 1,
            'tablets_per_strip' => 10,
            'strips_per_box' => 10,
            'price_tablet' => 100,
            'price_strip' => 900,
            'price_box' => 8000,
        ])->assertRedirect(route('admin.produk.index'));

        $medicine = Medicine::where('nama_obat', 'Obat Kemasan')->firstOrFail();
        $this->assertSame(2, $medicine->stok);
        $this->assertSame(200, $medicine->stock_base_units);
        $this->assertSame(10, $medicine->getSaleUnitFactor('strip'));
        $this->assertSame(8000.0, $medicine->getSaleUnitPrice('box'));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Beli per satuan')
            ->assertSee('Tablet · Rp 100', false)
            ->assertSee('data-stock-units="200"', false);
    }

    public function test_customer_can_choose_tablet_strip_or_box_and_stock_uses_box_conversion(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Satuan',
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
        $this->assertSame(100, $order->items[0]['harga']);
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'stock_base_units' => 1877,
            'stok' => 18,
        ]);
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
}
