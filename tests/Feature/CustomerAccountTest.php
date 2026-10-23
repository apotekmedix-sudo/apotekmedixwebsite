<?php

namespace Tests\Feature;

use App\Mail\CustomerLoginCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use App\Models\PurchaseHistory;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_requires_email_verification_before_login(): void
    {
        Mail::fake();

        $this->get(route('customer.register'))
            ->assertOk()
            ->assertSee('Buat akun pelanggan');

        $this->post(route('customer.register.post'), [
            'name' => 'Pelanggan Medix',
            'email' => 'pelanggan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('customer.email-verification'));

        $customer = User::where('email', 'pelanggan@example.com')->firstOrFail();
        $code = null;
        Mail::assertSent(CustomerLoginCode::class, function (CustomerLoginCode $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo('pelanggan@example.com') && preg_match('/^[0-9]{6}$/', $code) === 1;
        });

        $this->assertSame('user', $customer->role);
        $this->assertGuest();

        $this->get(route('customer.email-verification'))
            ->assertOk()
            ->assertSee('pelanggan@example.com')
            ->assertSee('Enam digit kode OTP')
            ->assertSee('Kirim ulang kode dalam');
        $this->post(route('customer.email-verification.verify'), [
            'code_digits' => str_split($code),
        ])
            ->assertRedirect(route('customer.account'));

        $this->assertAuthenticatedAs($customer);
        $this->assertNotNull($customer->fresh()->email_verified_at);
        $this->get(route('customer.account'))->assertOk()->assertSee('Ini alamat default saya');
    }

    public function test_customer_login_requires_the_email_code_and_rejects_admin_accounts(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'user']);

        $this->post(route('customer.login.post'), ['email' => $customer->email])
            ->assertRedirect(route('customer.email-verification'));
        $this->assertGuest();

        $code = null;
        Mail::assertSent(CustomerLoginCode::class, function (CustomerLoginCode $mail) use (&$code, $customer): bool {
            $code = $mail->code;

            return $mail->hasTo($customer->email) && preg_match('/^[0-9]{6}$/', $code) === 1;
        });
        $wrongCode = $code === '000000' ? '000001' : '000000';

        $this->post(route('customer.email-verification.verify'), ['code' => $wrongCode])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post(route('customer.email-verification.verify'), ['code' => $code])
            ->assertRedirect(route('customer.account'));
        $this->assertAuthenticatedAs($customer);

        Auth::logout();
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('admin-password'),
        ]);

        $this->post(route('customer.login.post'), [
            'email' => $admin->email,
        ])->assertRedirect(route('customer.email-verification'));
        $this->assertGuest();
        Mail::assertSent(CustomerLoginCode::class, 1);
    }

    public function test_customer_login_email_error_is_shown_once_without_mail_transport_details(): void
    {
        $message = 'Kode verifikasi belum dapat dikirim. Silakan coba lagi nanti.';
        $errors = new ViewErrorBag();
        $errors->put('default', new MessageBag(['email' => $message]));

        $this->withSession(['errors' => $errors])
            ->get(route('customer.login'))
            ->assertOk()
            ->assertSee('<p class="customer-auth-error" role="alert">' . $message . '</p>', false)
            ->assertDontSee('<div class="alert alert-error" role="alert">' . $message . '</div>', false)
            ->assertDontSee('SMTP')
            ->assertDontSee('.env')
            ->assertDontSee('dikonfigurasi');
    }

    public function test_customer_can_resend_a_code_after_the_cooldown_and_old_code_is_invalidated(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'user']);

        $this->post(route('customer.login.post'), [
            'email' => $customer->email,
        ])->assertRedirect(route('customer.email-verification'));

        $firstCode = Mail::sent(CustomerLoginCode::class)->last()->code;
        $this->post(route('customer.email-verification.resend'))
            ->assertSessionHasErrors('code');

        $this->travel(61)->seconds();
        $this->post(route('customer.email-verification.resend'))
            ->assertRedirect(route('customer.email-verification'));

        Mail::assertSent(CustomerLoginCode::class, 2);
        $newCode = Mail::sent(CustomerLoginCode::class)->last()->code;
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $newCode);

        $this->post(route('customer.email-verification.verify'), ['code' => $firstCode])
            ->assertSessionHasErrors('code');
        $this->post(route('customer.email-verification.verify'), ['code' => $newCode])
            ->assertRedirect(route('customer.account'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_verification_code_expires_after_ten_minutes(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'user']);

        $this->post(route('customer.login.post'), [
            'email' => $customer->email,
        ])->assertRedirect(route('customer.email-verification'));

        $this->travel(11)->minutes();
        $this->get(route('customer.email-verification'))
            ->assertRedirect(route('customer.login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_customer_verification_stops_after_five_incorrect_codes(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['role' => 'user']);

        $this->post(route('customer.login.post'), [
            'email' => $customer->email,
        ])->assertRedirect(route('customer.email-verification'));

        $code = Mail::sent(CustomerLoginCode::class)->last()->code;
        $wrongCode = $code === '000000' ? '000001' : '000000';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('customer.email-verification.verify'), ['code' => $wrongCode])
                ->assertSessionHasErrors('code');
        }

        $this->assertGuest();
        $this->get(route('customer.email-verification'))
            ->assertRedirect(route('customer.login'));
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

    public function test_guest_is_sent_to_customer_login_for_account_page(): void
    {
        $this->get(route('customer.account'))
            ->assertRedirect(route('customer.login'));
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
