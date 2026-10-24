<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PurchaseHistory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
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
            return redirect()->route('home');
        }

        abort_unless(Auth::check() && Auth::user()->isUser(), 403);

        return null;
    }
}
