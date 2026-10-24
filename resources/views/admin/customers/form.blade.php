@extends('layouts.admin')

@section('title', ($editing ? 'Edit' : 'Tambah') . ' Customer - Admin Apotek Medix')
@section('page-title', $editing ? 'Edit Customer' : 'Tambah Customer')

@section('content')
<div class="card" style="max-width:900px;">
    <div class="card-title">{{ $editing ? 'Perbarui data customer' : 'Buat data customer' }}</div>
    <p style="color:#64748b;">Profil ini digunakan untuk menyatukan riwayat pesanan customer. Menghapus profil tidak akan menghapus transaksi.</p>

    @if($errors->any())
        <div role="alert" style="padding:12px 14px;margin-bottom:16px;border-radius:8px;background:#fee2e2;color:#991b1b;">
            <strong>Periksa kembali data yang dimasukkan:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
        @csrf
        @if($editing)@method('PUT')@endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div>
                <label for="buyer_type">Jenis Customer</label>
                <select id="buyer_type" name="buyer_type" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                    @foreach(['umum' => 'Umum / B2C', 'apotik' => 'Apotek / B2B', 'dokter' => 'Dokter / B2B', 'pbf' => 'PBF / B2B'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('buyer_type', $customer->buyer_type ?: 'umum') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label for="name">Nama Customer / Pemesan</label><input id="name" name="name" value="{{ old('name', $customer->name) }}" required maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="requester_name">Nama Pemesan</label><input id="requester_name" name="requester_name" value="{{ old('requester_name', $customer->requester_name) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" maxlength="255" autocomplete="email" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="phone">Nomor Telepon</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" maxlength="255" autocomplete="tel" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="outlet_name">Nama Outlet / Apotek</label><input id="outlet_name" name="outlet_name" value="{{ old('outlet_name', $customer->outlet_name) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="pbf_name">Nama PBF</label><input id="pbf_name" name="pbf_name" value="{{ old('pbf_name', $customer->pbf_name) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="address">Alamat</label><input id="address" name="address" value="{{ old('address', $customer->address) }}" maxlength="2000" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="kecamatan">Kecamatan</label><input id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $customer->kecamatan) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="kota">Kota / Kabupaten</label><input id="kota" name="kota" value="{{ old('kota', $customer->kota) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sia">SIA</label><input id="sia" name="sia" value="{{ old('sia', $customer->sia) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sipa">SIPA</label><input id="sipa" name="sipa" value="{{ old('sipa', $customer->sipa) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="no_izin_pbf">Nomor Izin PBF</label><input id="no_izin_pbf" name="no_izin_pbf" value="{{ old('no_izin_pbf', $customer->no_izin_pbf) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="apj">APJ</label><input id="apj" name="apj" value="{{ old('apj', $customer->apj) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sika">SIKA</label><input id="sika" name="sika" value="{{ old('sika', $customer->sika) }}" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit" style="padding:10px 16px;border:0;border-radius:8px;background:#0052cc;color:#fff;font-weight:700;cursor:pointer;">{{ $editing ? 'Simpan Perubahan' : 'Simpan Customer' }}</button>
            <a href="{{ route('admin.customers.index') }}" style="padding:10px 16px;border:1px solid #cbd5e1;border-radius:8px;color:#334155;text-decoration:none;">Batal</a>
        </div>
    </form>
</div>
@endsection
