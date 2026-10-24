<?php

namespace App\Http\Controllers;

use App\Helpers\XlsxWriter;
use App\Models\Customer;
use App\Support\CustomerIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'channel' => ['nullable', Rule::in(['all', 'internal', 'b2b', 'b2c'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $customers = Customer::query()
            ->withCount('purchaseHistories')
            ->withCount([
                'purchaseHistories as internal_orders_count' => fn ($query) => $query->where('transaction_channel', 'offline'),
                'purchaseHistories as b2b_orders_count' => fn ($query) => $this->applyB2bFilter($query),
                'purchaseHistories as b2c_orders_count' => fn ($query) => $this->applyB2cFilter($query),
            ]);

        if (!empty($filters['channel']) && $filters['channel'] !== 'all') {
            match ($filters['channel']) {
                'internal' => $customers->whereHas('purchaseHistories', fn ($query) => $query->where('transaction_channel', 'offline')),
                'b2b' => $customers->whereHas('purchaseHistories', fn ($query) => $this->applyB2bFilter($query)),
                'b2c' => $customers->whereHas('purchaseHistories', fn ($query) => $this->applyB2cFilter($query)),
            };
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $customers->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('outlet_name', 'like', "%{$search}%")
                    ->orWhere('pbf_name', 'like', "%{$search}%");
                if (ctype_digit($search)) {
                    $query->orWhereHas('purchaseHistories', fn ($orders) => $orders->whereKey((int) $search));
                }
            });
        }

        $customers = $customers->latest('last_order_at')->latest('id')->paginate(25)->withQueryString();
        $summary = [
            'total' => Customer::count(),
            'internal' => Customer::whereHas('purchaseHistories', fn ($query) => $query->where('transaction_channel', 'offline'))->count(),
            'b2b' => Customer::whereHas('purchaseHistories', fn ($query) => $this->applyB2bFilter($query))->count(),
            'b2c' => Customer::whereHas('purchaseHistories', fn ($query) => $this->applyB2cFilter($query))->count(),
        ];

        return view('admin.customers.index', compact('customers', 'filters', 'summary'));
    }

    public function export()
    {
        $sheets = [
            $this->customerSheet('Internal', fn (Builder $query) => $query->where('transaction_channel', 'offline')),
            $this->customerSheet('B2B', fn (Builder $query) => $this->applyB2bFilter($query)),
            $this->customerSheet('B2C', fn (Builder $query) => $this->applyB2cFilter($query)),
        ];

        return XlsxWriter::downloadWorkbook(
            'data-customer-per-kanal-'.now()->format('Ymd-His').'.xlsx',
            $sheets
        );
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new Customer(), 'editing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['identity_key'] = CustomerIdentity::keyForOrder($data);

        if (Customer::where('identity_key', $data['identity_key'])->exists()) {
            return back()->withErrors([
                'phone' => 'Customer dengan identitas tersebut sudah terdaftar. Gunakan profil yang ada atau periksa kembali kontak dan outlet.',
            ])->withInput();
        }

        Customer::create($data);

        return redirect()->route('admin.customers.index')->with('success', 'Data customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', compact('customer') + ['editing' => true]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validatedData($request);
        $hasIdentityFields = filled($data['phone'] ?? null)
            || filled($data['email'] ?? null)
            || filled($data['outlet_name'] ?? null)
            || filled($data['pbf_name'] ?? null);
        $identityKey = $hasIdentityFields
            ? CustomerIdentity::keyForOrder($data)
            : $customer->identity_key;

        $duplicate = Customer::where('identity_key', $identityKey)
            ->where('id', '!=', $customer->id)
            ->exists();
        if ($duplicate) {
            return back()->withErrors([
                'phone' => 'Data identitas ini sudah terhubung dengan customer lain. Periksa nomor telepon, email, atau outlet.',
            ])->withInput();
        }

        $customer->update([...$data, 'identity_key' => $identityKey]);

        return redirect()->route('admin.customers.index')->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Data customer dihapus. Riwayat pesanan tetap disimpan.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'buyer_type' => ['required', Rule::in(['umum', 'apotik', 'dokter', 'pbf'])],
            'name' => ['required', 'string', 'max:255'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'outlet_name' => ['nullable', 'string', 'max:255'],
            'pbf_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota' => ['nullable', 'string', 'max:255'],
            'sia' => ['nullable', 'string', 'max:255'],
            'sipa' => ['nullable', 'string', 'max:255'],
            'no_izin_pbf' => ['nullable', 'string', 'max:255'],
            'apj' => ['nullable', 'string', 'max:255'],
            'sika' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function applyB2bFilter($query)
    {
        return $query->where('transaction_channel', 'online')
            ->where(fn ($orders) => $orders->whereIn('buyer_type', ['apotik', 'dokter', 'pbf'])
                ->orWhere(fn ($pbf) => $pbf->whereNotNull('pbf_name')->where('pbf_name', '!=', '')));
    }

    private function applyB2cFilter($query)
    {
        return $query->where('transaction_channel', 'online')
            ->where('buyer_type', 'umum')
            ->where(fn ($orders) => $orders->whereNull('pbf_name')->orWhere('pbf_name', ''));
    }

    private function customerSheet(string $channel, callable $filter): array
    {
        $columns = array_merge(
            \Illuminate\Support\Facades\Schema::getColumnListing('customers'),
            ['jumlah_pesanan', 'pesanan_terakhir']
        );
        $customers = Customer::query()
            ->whereHas('purchaseHistories', $filter)
            ->withCount(['purchaseHistories as jumlah_pesanan' => $filter])
            ->withMax(['purchaseHistories as pesanan_terakhir' => $filter], 'created_at')
            ->orderBy('id')
            ->get();

        $rows = $customers->map(function (Customer $customer) use ($columns): array {
            return array_map(
                fn (string $column) => $column === 'pesanan_terakhir'
                    ? $customer->pesanan_terakhir
                    : $customer->getAttribute($column),
                $columns
            );
        })->all();

        return [
            'name' => 'Customer '.$channel,
            'headers' => array_map('strtoupper', $columns),
            'rows' => $rows,
        ];
    }
}
