<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_registration_and_otp_pages_are_unavailable(): void
    {
        $this->get('/customer/login')->assertNotFound();
        $this->post('/customer/login')->assertNotFound();
        $this->get('/customer/register')->assertNotFound();
        $this->post('/customer/register')->assertNotFound();
        $this->get('/customer/verify-email')->assertNotFound();
        $this->post('/customer/verify-email')->assertNotFound();
        $this->post('/customer/verify-email/resend')->assertNotFound();
    }

    public function test_guest_navigation_has_no_customer_login_or_registration_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('customer/login')
            ->assertDontSee('customer/register')
            ->assertDontSee('> Masuk</a>', false)
            ->assertDontSee('> Daftar</a>', false);
    }

    public function test_user_navbar_links_to_b2c_catalog_instead_of_general_products(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<a href="' . route('products.apotek') . '"><i class="fa-solid fa-store"></i> Belanja B2C</a>', false)
            ->assertDontSee('<a href="' . route('products.index') . '"><i class="fa-solid fa-pills"></i> Produk Kami</a>', false);
    }

    public function test_public_footer_uses_google_maps_contact_details_and_requested_email(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('0858 8271 7844')
            ->assertSee('apotekmedix@gmail.com')
            ->assertSee('Jl. Raya Ciawi Prapatan No.256 C')
            ->assertDontSee('hello@apotekmedix.com')
            ->assertDontSee('0858 9000 7359');

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('Persyaratan Dokumen Pemesanan')
            ->assertSee('0858 8271 7844')
            ->assertSee('apotekmedix@gmail.com')
            ->assertSee('Sindangsari')
            ->assertSee('Senin–Sabtu: 08.00–20.00')
            ->assertSee('Plus Code: 9R3R+P9 Sindangsari, Kota Bogor')
            ->assertSee('-6.6456763, 106.8409757')
            ->assertSee('https://www.google.com/maps?q=-6.6456763,106.8409757&z=17&output=embed', false);
    }

    public function test_about_page_uses_only_the_apotek_medix_logo_instead_of_photos(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('LOGO APOTEK MEDIX.PNG')
            ->assertDontSee('page1.jpeg')
            ->assertDontSee('page2.jpeg')
            ->assertDontSee('page3.jpeg')
            ->assertDontSee('page4.jpeg')
            ->assertDontSee('page5.jpeg')
            ->assertDontSee('page6.jpeg')
            ->assertDontSee('page7.jpeg')
            ->assertDontSee('background1.jpeg');
    }

    public function test_admin_can_import_and_export_customer_master_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = \App\Models\Customer::create([
            'identity_key' => hash('sha256', 'customer-csv-import'),
            'buyer_type' => 'apotik',
            'name' => 'Apotek Lama',
            'phone' => '081234567899',
            'outlet_name' => 'Apotek Lama',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Export Master CSV')
            ->assertSee('Download Template CSV')
            ->assertSee('Import Customer');

        $export = $this->get(route('admin.customers.export.master'));
        $export->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('BUYER_TYPE,NAME', $export->streamedContent());

        $csv = implode("\n", [
            'ID,BUYER_TYPE,NAME,REQUESTER_NAME,EMAIL,PHONE,OUTLET_NAME,PBF_NAME,ADDRESS,KECAMATAN,KOTA,SIA,SIPA,NO_IZIN_PBF,APJ,SIKA',
            "{$customer->id},apotik,Apotek Baru,Dokter Contoh,dokter@example.test,081234567899,Apotek Baru,,,Bogor Timur,Bogor,SIA-1,SIPA-1,,,",
            ',umum,Customer Baru,,,081298765432,,,,,,,,,,',
        ]);
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csv);
        $this->post(route('admin.customers.import'), ['file' => $file])
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', '2 data customer berhasil diimpor atau diperbarui.');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Apotek Baru',
            'requester_name' => 'Dokter Contoh',
            'sipa' => 'SIPA-1',
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Customer Baru',
            'phone' => '081298765432',
        ]);
        $this->assertDatabaseCount('customers', 2);
    }

    public function test_shopping_channels_open_their_respective_pages(): void
    {
        $this->get('/kiosk')->assertNotFound();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belanja Internal')
            ->assertSee('Belanja B2B')
            ->assertSee('Belanja B2C')
            ->assertSee('<a href="' . route('products.apotek') . '"><i class="fa-solid fa-store"></i> Belanja B2C</a>', false)
            ->assertDontSee('<a href="' . route('products.index') . '"><i class="fa-solid fa-pills"></i> Produk Kami</a>', false)
            ->assertDontSee('Pesan di Toko')
            ->assertDontSee('Buka Kios Toko');

        $this->get(route('shopping.internal'))
            ->assertOk()
            ->assertSee('Belanja Internal')
            ->assertSee('Pilih produk untuk transaksi internal')
            ->assertSee('apotekmedix_cart_internal');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Katalog Produk')
            ->assertDontSee('Pesan di Toko');

        $this->get(route('products.pbf'))->assertOk()->assertSee('Belanja B2B');
        $this->get(route('products.apotek'))->assertOk()->assertSee('Belanja B2C');
    }

    public function test_customer_can_save_a_default_shipping_address_used_at_checkout(): void
    {
        $customer = User::factory()->create(['role' => 'user']);

        $this->actingAs($customer)->put(route('customer.account.update'), [
            'name' => 'Pelanggan Medix',
            'email' => $customer->email,
            'phone' => '081234567890',
            'address' => 'Jl. Melati No. 10',
            'apartment' => 'Unit 5A',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'is_default_address' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'address' => 'Jl. Melati No. 10',
            'apartment' => 'Unit 5A',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'is_default_address' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('value="081234567890"', false)
            ->assertSee('Jl. Melati No. 10', false)
            ->assertSee('value="Unit 5A"', false)
            ->assertSee('value="Jakarta Pusat"', false)
            ->assertSee('value="DKI Jakarta"', false)
            ->assertSee('value="10110"', false);
    }

    public function test_guest_is_redirected_home_from_customer_account(): void
    {
        $this->get(route('customer.account'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_can_search_indonesian_addresses_and_select_location_details(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'display_name' => 'Jalan Sudirman, Jakarta, Indonesia',
                    'address' => [
                        'road' => 'Jalan Sudirman',
                        'house_number' => '10',
                        'city' => 'Jakarta Pusat',
                        'state' => 'DKI Jakarta',
                        'postcode' => '10220',
                    ],
                ],
            ]),
        ]);

        $this->actingAs($customer)
            ->getJson(route('customer.locations.search', ['q' => 'Jalan Sudirman']))
            ->assertOk()
            ->assertJsonPath('locations.0.address', 'Jalan Sudirman 10')
            ->assertJsonPath('locations.0.city', 'Jakarta Pusat')
            ->assertJsonPath('locations.0.province', 'DKI Jakarta')
            ->assertJsonPath('locations.0.postal_code', '10220');

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://nominatim.openstreetmap.org/search')
            && $request['countrycodes'] === 'id'
            && $request->hasHeader('User-Agent'));
    }

    public function test_guest_can_place_an_online_order_without_a_customer_account(): void
    {
        $response = $this->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pelanggan Tamu',
            'phone' => '081234567890',
            'address' => 'Alamat tamu',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $response->json('id'),
            'user_id' => null,
        ]);
    }

    public function test_online_purchase_created_while_customer_is_signed_in_is_shown_on_profile(): void
    {
        $customer = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($customer)->postJson(route('orders.history.store'), [
            'buyer_type' => 'umum',
            'buyer_name' => $customer->name,
            'phone' => '081234567890',
            'address' => 'Alamat uji',
            'items' => [],
            'total' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('purchase_histories', [
            'id' => $response->json('id'),
            'user_id' => $customer->id,
        ]);

        $this->get(route('customer.account'))
            ->assertOk()
            ->assertSee('Pesanan #' . $response->json('id'));
    }
}
