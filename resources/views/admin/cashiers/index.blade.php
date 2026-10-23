@extends('layouts.admin')

@section('title', 'Kelola Kasir - Admin Apotek Medix')
@section('page-title', 'Kelola Kasir')

@section('content')
<div class="card">
    <div class="card-title">Buat Akun Kasir</div>
    <form action="{{ route('admin.cashiers.store') }}" method="POST">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;">
            <div class="form-group">
                <label class="form-label" for="cashier-name">Nama kasir</label>
                <input class="form-control" id="cashier-name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="form-errors">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="cashier-email">Email</label>
                <input class="form-control" id="cashier-email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="form-errors">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="cashier-password">Kata sandi</label>
                <input class="form-control" id="cashier-password" type="password" name="password" minlength="8" required>
                @error('password')<div class="form-errors">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="cashier-password-confirmation">Ulangi kata sandi</label>
                <input class="form-control" id="cashier-password-confirmation" type="password" name="password_confirmation" minlength="8" required>
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-user-plus"></i> Buat akun kasir</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Akun Kasir</div>
    <div class="table-container">
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Dibuat</th></tr></thead>
            <tbody>
                @forelse($cashiers as $cashier)
                    <tr>
                        <td>{{ $cashier->name }}</td>
                        <td>{{ $cashier->email }}</td>
                        <td>{{ $cashier->created_at?->format('d M Y') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;padding:2rem;color:#64748b;">Belum ada akun kasir.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem;">{{ $cashiers->links() }}</div>
</div>
@endsection
