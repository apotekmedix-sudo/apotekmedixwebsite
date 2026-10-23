<?php

namespace App\Http\Controllers;

use App\Mail\CustomerLoginCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\PurchaseHistory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class CustomerAccountController extends Controller
{
    private const EMAIL_VERIFICATION_SESSION_KEY = 'pending_customer_email_verification';

    public function showLogin(): View
    {
        return view('customer.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $customer = User::where('email', $validated['email'])
            ->where('role', 'user')
            ->where('is_cashier', false)
            ->first();

        if (!$this->sendCustomerLoginCode($request, $customer, $validated['email'])) {
            return back()->withErrors(['email' => 'Kode verifikasi belum dapat dikirim. Silakan coba lagi nanti.'])
                ->onlyInput('email');
        }

        return redirect()->route('customer.email-verification')
            ->with('status', 'Jika email terdaftar, kode OTP telah dikirim. Periksa kotak masuk Anda.');
    }

    public function showEmailVerification(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::EMAIL_VERIFICATION_SESSION_KEY);

        if (!$this->isValidPendingChallenge($pending)) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Silakan masuk kembali untuk meminta kode verifikasi.']);
        }

        return view('customer.verify-email', [
            'email' => $pending['email'],
            'resendAvailableAt' => $pending['sent_at'] + 60,
        ]);
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $code = $request->input('code');
        if (!is_string($code) || $code === '') {
            $digits = $request->input('code_digits', []);
            $code = is_array($digits)
                ? implode('', array_map(fn ($digit) => is_string($digit) ? $digit : '', $digits))
                : '';
        }

        $validated = Validator::make(['code' => $code], [
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ])->validate();
        $pending = $request->session()->get(self::EMAIL_VERIFICATION_SESSION_KEY);

        if (!$this->isValidPendingChallenge($pending)) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Kode verifikasi sudah kedaluwarsa. Silakan masuk kembali.']);
        }

        if ($pending['attempts'] >= 5) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Batas percobaan verifikasi tercapai. Silakan masuk kembali untuk meminta kode baru.']);
        }

        $pending['attempts']++;
        $request->session()->put(self::EMAIL_VERIFICATION_SESSION_KEY, $pending);

        if (!Hash::check($validated['code'], $pending['code_hash'])) {
            if ($pending['attempts'] >= 5) {
                $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

                return back()->withErrors(['code' => 'Kode salah. Batas percobaan tercapai; silakan masuk kembali untuk meminta kode baru.']);
            }

            return back()->withErrors(['code' => 'Kode verifikasi tidak sesuai.']);
        }

        $customer = User::find($pending['user_id']);
        if (!$customer || !$customer->isUser() || $customer->email !== $pending['email']) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Akun pelanggan tidak ditemukan. Silakan masuk kembali.']);
        }

        $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);
        if (!$customer->email_verified_at) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($customer, $pending['remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('customer.account'))->with('success', 'Email berhasil diverifikasi dan Anda telah masuk.');
    }

    public function resendEmailVerification(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::EMAIL_VERIFICATION_SESSION_KEY);
        if (!$this->isValidPendingChallenge($pending)) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Silakan masuk kembali untuk meminta kode verifikasi.']);
        }

        if ($pending['sent_at'] > now()->subSeconds(60)->timestamp) {
            return back()->withErrors(['code' => 'Tunggu sebentar sebelum meminta kode baru.']);
        }

        $customer = User::find($pending['user_id']);
        if (!$customer || !$customer->isUser() || $customer->email !== $pending['email']) {
            $request->session()->forget(self::EMAIL_VERIFICATION_SESSION_KEY);

            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Akun pelanggan tidak ditemukan. Silakan masuk kembali.']);
        }

        if (!$this->sendCustomerLoginCode($request, $customer, $pending['email'], false)) {
            return back()->withErrors(['code' => 'Kode baru gagal dikirim. Silakan coba lagi nanti.']);
        }

        return redirect()->route('customer.email-verification')
            ->with('status', 'Kode verifikasi baru telah dikirim ke email Anda.');
    }

    public function showRegister(): View
    {
        return view('customer.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        if (!$this->sendCustomerLoginCode($request, $customer, $customer->email)) {
            return redirect()->route('customer.login')
                ->withErrors(['email' => 'Akun berhasil dibuat, tetapi kode verifikasi belum dapat dikirim. Silakan masuk dan coba lagi nanti.']);
        }

        return redirect()->route('customer.email-verification')
            ->with('status', 'Akun berhasil dibuat. Kode OTP 6 digit telah dikirim ke email Anda.');
    }

    public function showProfile(): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $orders = PurchaseHistory::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('customer.account', ['customer' => Auth::user(), 'orders' => $orders]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        $customer = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'apartment' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'is_default_address' => ['sometimes', 'boolean'],
        ]);

        $validated['is_default_address'] = $request->boolean('is_default_address');
        $customer->update($validated);

        return back()->with('success', 'Profil dan alamat pengiriman berhasil disimpan.');
    }

    public function searchLocations(Request $request)
    {
        if ($redirect = $this->ensureCustomer()) {
            return response()->json(['message' => 'Silakan masuk ke akun pelanggan terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'ApotekMedix/1.0 address-search',
                'Referer' => config('app.url'),
                'Accept' => 'application/json',
            ])->timeout(8)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $validated['q'],
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 5,
                'countrycodes' => 'id',
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Customer location search could not reach the location provider.', [
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Layanan pencarian lokasi sedang tidak dapat dijangkau. Coba lagi nanti.'], 503);
        }

        if (!$response->successful()) {
            Log::warning('Customer location search provider returned an unsuccessful response.', [
                'status' => $response->status(),
            ]);

            return response()->json(['message' => 'Layanan pencarian lokasi sedang bermasalah. Coba lagi nanti.'], 503);
        }

        $locations = collect($response->json())
            ->filter(fn ($result) => is_array($result) && isset($result['address']))
            ->map(function (array $result): array {
                $parts = $result['address'];
                $city = $parts['city'] ?? $parts['town'] ?? $parts['municipality'] ?? $parts['county'] ?? $parts['village'] ?? '';
                $address = collect([
                    trim(($parts['road'] ?? '') . ' ' . ($parts['house_number'] ?? '')),
                    $parts['neighbourhood'] ?? $parts['quarter'] ?? null,
                    $parts['suburb'] ?? $parts['village'] ?? null,
                ])->filter()->unique()->implode(', ');

                return [
                    'label' => $result['display_name'] ?? $address,
                    'address' => $address ?: ($result['display_name'] ?? ''),
                    'city' => $city,
                    'province' => $parts['state'] ?? $parts['province'] ?? '',
                    'postal_code' => $parts['postcode'] ?? '',
                ];
            })
            ->values();

        return response()->json(['locations' => $locations]);
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari akun pelanggan.');
    }

    private function ensureCustomer(): ?RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login');
        }

        abort_unless(Auth::check() && Auth::user()->isUser(), 403);

        return null;
    }

    private function sendCustomerLoginCode(Request $request, ?User $customer, string $email, bool $rotateSession = true): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($customer) {
            try {
                if (!app()->environment('testing') && in_array(config('mail.default'), ['log', 'array'], true)) {
                    Log::error('Customer email verification requires a delivery mailer; logging mailers are disabled.', [
                        'user_id' => $customer->id,
                        'mailer' => config('mail.default'),
                    ]);

                    return false;
                }

                Mail::to($email)->send(new CustomerLoginCode($code));
            } catch (TransportExceptionInterface $exception) {
                Log::error('Customer email verification code could not be sent.', [
                    'user_id' => $customer->id,
                    'exception' => $exception::class,
                ]);

                return false;
            }
        }

        if ($rotateSession) {
            $request->session()->regenerate();
        }

        $request->session()->put(self::EMAIL_VERIFICATION_SESSION_KEY, [
            'user_id' => $customer?->id,
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'sent_at' => now()->timestamp,
            'attempts' => 0,
            'remember' => false,
        ]);

        return true;
    }

    private function isValidPendingChallenge(mixed $pending): bool
    {
        return is_array($pending)
            && array_key_exists('user_id', $pending)
            && isset(
                $pending['email'],
                $pending['code_hash'],
                $pending['expires_at'],
                $pending['sent_at'],
                $pending['attempts'],
                $pending['remember'],
            )
            && $pending['expires_at'] >= now()->timestamp;
    }
}
