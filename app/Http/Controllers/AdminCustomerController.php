<?php

namespace App\Http\Controllers;

use App\Helpers\XlsxWriter;
use App\Models\Customer;
use App\Support\CustomerIdentity;
use App\Support\CsvSpreadsheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use RuntimeException;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'channel' => ['nullable', Rule::in(['all', 'offline', 'b2b', 'b2c'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $customers = Customer::query()
            ->withCount('purchaseHistories')
            ->withSum('purchaseHistories as total_pesanan', 'total')
            ->withCount([
                'purchaseHistories as offline_orders_count' => fn ($query) => $query->where('transaction_channel', 'offline'),
                'purchaseHistories as b2b_orders_count' => fn ($query) => $this->applyB2bFilter($query),
                'purchaseHistories as b2c_orders_count' => fn ($query) => $this->applyB2cFilter($query),
            ]);

        if (!empty($filters['channel']) && $filters['channel'] !== 'all') {
            match ($filters['channel']) {
                'offline' => $customers->whereHas('purchaseHistories', fn ($query) => $query->where('transaction_channel', 'offline')),
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
            'offline' => Customer::whereHas('purchaseHistories', fn ($query) => $query->where('transaction_channel', 'offline'))->count(),
            'b2b' => Customer::whereHas('purchaseHistories', fn ($query) => $this->applyB2bFilter($query))->count(),
            'b2c' => Customer::whereHas('purchaseHistories', fn ($query) => $this->applyB2cFilter($query))->count(),
        ];

        return view('admin.customers.index', compact('customers', 'filters', 'summary'));
    }

    public function export()
    {
        $sheets = [
            $this->customerSheet('Offline', fn (Builder $query) => $query->where('transaction_channel', 'offline')),
            $this->customerSheet('B2B', fn (Builder $query) => $this->applyB2bFilter($query)),
            $this->customerSheet('B2C', fn (Builder $query) => $this->applyB2cFilter($query)),
        ];

        return XlsxWriter::downloadWorkbook(
            'data-customer-per-kanal-'.now()->format('Ymd-His').'.xlsx',
            $sheets
        );
    }

    public function exportMaster()
    {
        $columns = $this->importableCustomerColumns();
        $rows = Customer::query()->orderBy('id')->get()->map(
            fn (Customer $customer) => array_map(fn ($column) => $customer->getAttribute($column), $columns)
        );

        return CsvSpreadsheet::download(
            'data-master-customer-'.now()->format('Ymd-His').'.csv',
            array_map('strtoupper', $columns),
            $rows
        );
    }

    public function downloadImportTemplate()
    {
        $columns = $this->importableCustomerColumns();

        return CsvSpreadsheet::download(
            'template-import-customer.csv',
            array_map('strtoupper', $columns),
            []
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $fileValidator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);
        if ($fileValidator->fails()) {
            return redirect()->route('admin.customers.index')->withErrors($fileValidator);
        }

        try {
            [$headers, $rows] = CsvSpreadsheet::read($request->file('file'));
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.customers.index')->withErrors(['file' => $exception->getMessage()]);
        }

        $requiredHeaders = ['buyer_type', 'name'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if ($missingHeaders) {
            return redirect()->route('admin.customers.index')->withErrors([
                'file' => 'Format CSV customer tidak sesuai. Kolom wajib: BUYER_TYPE dan NAME. Unduh template customer untuk format yang benar.',
            ]);
        }

        if (count($rows) > 5000) {
            return redirect()->route('admin.customers.index')->withErrors(['file' => 'Maksimal 5.000 data customer per impor.']);
        }

        $columns = array_values(array_diff($this->importableCustomerColumns(), ['id']));
        $prepared = [];
        $errors = [];
        $seenIdentities = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = array_intersect_key($row, array_flip($columns));
            foreach ($data as $key => $value) {
                $data[$key] = $value === '' ? null : $value;
            }

            $validator = Validator::make($data, [
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

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }
                continue;
            }

            $id = filled($row['id'] ?? null) ? filter_var($row['id'], FILTER_VALIDATE_INT) : null;
            if (filled($row['id'] ?? null) && (! $id || $id < 1)) {
                $errors[] = "Baris {$line}: ID customer harus berupa angka positif.";
                continue;
            }

            $customer = $id ? Customer::find($id) : null;
            if ($id && ! $customer) {
                $errors[] = "Baris {$line}: customer dengan ID {$id} tidak ditemukan.";
                continue;
            }

            $hasIdentityFields = filled($data['phone'] ?? null)
                || filled($data['email'] ?? null)
                || filled($data['outlet_name'] ?? null)
                || filled($data['pbf_name'] ?? null);
            $identityData = [...$data, 'buyer_name' => $data['name']];
            $identityKey = $hasIdentityFields || ! $customer
                ? CustomerIdentity::keyForOrder($identityData)
                : $customer->identity_key;
            $duplicate = Customer::where('identity_key', $identityKey)
                ->when($customer, fn ($query) => $query->where('id', '!=', $customer->id))
                ->exists();
            if ($duplicate) {
                $errors[] = "Baris {$line}: identitas customer sudah digunakan oleh profil lain.";
                continue;
            }
            if (isset($seenIdentities[$identityKey])) {
                $errors[] = "Baris {$line}: identitas customer duplikat di dalam file.";
                continue;
            }
            $seenIdentities[$identityKey] = true;

            $prepared[] = ['customer' => $customer, 'data' => $data, 'identity_key' => $identityKey];
        }

        if ($errors) {
            return redirect()->route('admin.customers.index')->withErrors(['file' => $errors]);
        }
        if (! $prepared) {
            return redirect()->route('admin.customers.index')->withErrors(['file' => 'File CSV tidak berisi data customer untuk diimpor.']);
        }

        try {
            DB::transaction(function () use ($prepared): void {
                foreach ($prepared as $record) {
                    $customer = $record['customer'] ?? new Customer();
                    $customer->fill($record['data']);
                    $customer->identity_key = $record['identity_key'];
                    $customer->save();
                }
            });
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->route('admin.customers.index')->withErrors([
                'file' => 'Impor customer gagal karena data bertabrakan dengan data yang sudah ada. Tidak ada data yang diubah.',
            ]);
        }

        return redirect()->route('admin.customers.index')->with(
            'success',
            count($prepared).' data customer berhasil diimpor atau diperbarui.'
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

    private function importableCustomerColumns(): array
    {
        return [
            'id',
            'buyer_type',
            'name',
            'requester_name',
            'email',
            'phone',
            'outlet_name',
            'pbf_name',
            'address',
            'kecamatan',
            'kota',
            'sia',
            'sipa',
            'no_izin_pbf',
            'apj',
            'sika',
        ];
    }
}
