@extends('layouts.admin')

@section('title', 'Data Customer - Admin Apotek Medix')
@section('page-title', 'Data Customer')

@section('content')
<div class="card">
    <div class="card-title">Akun Customer Terdaftar</div>
    <p style="color:#64748b;margin-top:-.75rem;">Data akun dan alamat tersimpan. Kata sandi tidak ditampilkan.</p>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Nomor Telepon</th>
                    <th>Alamat Default</th>
                    <th>Terdaftar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?: '-' }}</td>
                        <td>
                            @if($customer->address)
                                {{ $customer->address }}
                                @if($customer->apartment), {{ $customer->apartment }}@endif
                                @if($customer->city), {{ $customer->city }}@endif
                                @if($customer->province), {{ $customer->province }}@endif
                                @if($customer->postal_code), {{ $customer->postal_code }}@endif
                                @if($customer->is_default_address)
                                    <span class="badge badge-primary">Default</span>
                                @endif
                            @else
                                <span style="color:#94a3b8;">Belum diisi</span>
                            @endif
                        </td>
                        <td>{{ $customer->created_at?->format('d M Y') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#64748b;">Belum ada customer terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;">{{ $customers->links() }}</div>
</div>
@endsection
