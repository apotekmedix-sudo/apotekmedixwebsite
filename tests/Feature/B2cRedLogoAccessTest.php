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
        $this->createMedicine('Produk Logo Hijau', 'Hijau');
        $accessCode = $this->createB2cAccessCode();

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Produk Logo Hijau')
            ->assertDontSee('Produk Logo Merah')
            ->assertSee('aria-label="Masukkan kode akses B2C"', false)
            ->assertSee(route('products.apotek.activate'), false);

        $this->get(route('products.show', $redMedicine))
            ->assertRedirect(route('products.apotek'))
            ->assertSessionHas('b2c_activation_required');

        $this->post(route('products.apotek.activate'), ['kode' => '00000'])
            ->assertRedirect(route('products.apotek'))
            ->assertSessionHasErrors('kode');

        $this->post(route('products.apotek.activate'), ['kode' => '12345'])
            ->assertRedirect(route('products.apotek'))
            ->assertSessionHas('success');

        $this->get(route('products.apotek'))
            ->assertOk()
            ->assertSee('Produk Logo Merah')
            ->assertSee('Produk khusus terbuka');

        $this->get(route('products.show', $redMedicine))->assertOk();
        $this->assertLessThanOrEqual(
            $accessCode->expires_at->timestamp,
            session('b2c_access_expires_at')
        );
    }

    public function test_red_logo_products_cannot_be_ordered_without_active_b2c_code(): void
    {
        $medicine = $this->createMedicine('Produk Merah Terbatas', 'red');
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
            ->assertJsonPath('message', 'Aktifkan kode akses B2C untuk memesan produk berlogo merah.');
        $this->assertSame(0, PurchaseHistory::count());

        $accessCode = $this->createB2cAccessCode();
        $this->withSession([
            'b2c_access_expires_at' => now()->addDays(20)->timestamp,
            'b2c_access_code_hash' => $accessCode->code_hash,
        ])->postJson(route('orders.history.store'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('purchase_histories', [
            'buyer_name' => 'Pelanggan B2C',
            'payment_method' => null,
        ]);
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
}
