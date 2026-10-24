@extends('layouts.admin')

@section('title', 'Aktivasi Kode Belanja - Admin Apotek Medix')
@section('page-title', 'Aktivasi Kode Belanja')

@section('content')
<style>
    .activation-intro{color:#64748b;margin-top:-.75rem;line-height:1.6}
    .activation-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
    .activation-card{padding:20px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
    .activation-icon{display:grid;width:44px;height:44px;place-items:center;border-radius:12px;background:#eff6ff;color:#0052cc;font-size:20px}
    .activation-card h2{margin:14px 0 6px;font-size:18px}
    .activation-card p{color:#64748b;line-height:1.55;font-size:13px}
    .activation-status{display:inline-block;margin:4px 0 14px;padding:5px 9px;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:11px;font-weight:700}
    .activation-status.active{background:#dcfce7;color:#15803d}
    .activation-expiry{display:block;margin:-8px 0 14px;color:#64748b;font-size:12px}
    .activation-card button{border:0;border-radius:8px;padding:10px 13px;background:#0052cc;color:#fff;font-weight:700;cursor:pointer}
    .code-table{width:100%;border-collapse:collapse;margin-top:20px}
    .code-table th,.code-table td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:middle}
    .code-table th{background:#f8fafc;color:#475569;font-size:12px}
    .code-value{font:700 16px ui-monospace,SFMono-Regular,Consolas,monospace;letter-spacing:.12em;color:#0f172a}
    .code-edit{display:flex;align-items:center;gap:6px;min-width:220px}
    .code-edit input{width:100px;padding:7px;border:1px solid #cbd5e1;border-radius:6px;letter-spacing:.1em}
    .code-edit button,.code-delete{border:0;border-radius:7px;padding:8px 10px;font-weight:700;cursor:pointer}
    .code-edit button{background:#eaf2ff;color:#0052cc}.code-delete{background:#fff1f2;color:#be123c}
    .code-actions form{margin:0}.code-actions{display:flex;gap:6px}
    .code-empty{text-align:center;padding:16px;color:#64748b}
    .activation-warning{margin-top:16px;padding:12px 14px;border-radius:9px;background:#fffbeb;color:#92400e;font-size:13px;line-height:1.5}
    .activation-success{margin-bottom:16px;padding:12px 14px;border-radius:9px;background:#dcfce7;color:#166534}
    .activation-error{margin-bottom:16px;padding:12px 14px;border-radius:9px;background:#fee2e2;color:#991b1b}
    @media(max-width:700px){.code-table{min-width:720px}.code-table-wrap{overflow-x:auto}}
</style>

<div class="card">
    <div class="card-title">Aktivasi kode akses</div>
    <p class="activation-intro">Buat kode angka 5 digit untuk membuka halaman Belanja Internal dan B2B. Kode berlaku 30 hari sejak dibuat dan kode baru langsung menggantikan kode sebelumnya.</p>

    @if(session('success'))<div class="activation-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="activation-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="activation-grid">
        @foreach([
            'internal' => ['title' => 'Belanja Internal', 'description' => 'Kode angka 5 digit untuk membuka katalog dan transaksi staf internal.', 'icon' => 'fa-store'],
            'b2b' => ['title' => 'Belanja B2B', 'description' => 'Kode angka 5 digit untuk membuka katalog khusus mitra dan distributor.', 'icon' => 'fa-boxes-stacked'],
        ] as $channel => $details)
            <article class="activation-card">
                <div class="activation-icon"><i class="fa-solid {{ $details['icon'] }}" aria-hidden="true"></i></div>
                <h2>{{ $details['title'] }}</h2>
                <p>{{ $details['description'] }}</p>
                @php
                    $channelCode = $codes[$channel] ?? null;
                    $codeIsActive = $channelCode !== null
                        && $channelCode->expires_at?->isFuture() === true
                        && $channelCode->readCode() !== null;
                @endphp
                <span class="activation-status {{ $codeIsActive ? 'active' : '' }}">
                    {{ $codeIsActive ? 'Kode aktif' : (isset($codes[$channel]) ? 'Kode lama / kedaluwarsa — perbarui' : 'Kode belum dibuat') }}
                </span>
                @if($codeIsActive)
                    <span class="activation-expiry">Berlaku sampai {{ $codes[$channel]->expires_at->format('d M Y, H:i') }}</span>
                @endif
                <form action="{{ route('admin.access-codes.generate', $channel) }}" method="POST" onsubmit="return confirm('{{ $codeIsActive ? 'Kode lama langsung tidak berlaku. Buat kode baru 5 digit dengan masa berlaku 30 hari?' : 'Buat kode aktivasi 5 digit yang berlaku 30 hari?' }}')">
                    @csrf
                    <button type="submit">{{ isset($codes[$channel]) ? 'Buat Ulang Kode 5 Digit' : 'Buat Kode 5 Digit' }}</button>
                </form>
            </article>
        @endforeach
    </div>

    <div class="card-title" style="margin-top:24px;">Daftar Kode Akses</div>
    <p style="color:#64748b;">Kode aktif selalu tampil di tabel ini. Edit kode memperbarui masa berlakunya selama 30 hari; hapus atau ubah kode langsung membatalkan sesi lama.</p>
    <div class="code-table-wrap">
        <table class="code-table">
            <thead><tr><th>Kanal</th><th>Kode</th><th>Status</th><th>Berlaku sampai</th><th>Terakhir diperbarui</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($codes as $code)
                    @php
                        $readableCode = $code->readCode();
                        $codeIsActive = $code->expires_at?->isFuture() === true && $readableCode !== null;
                    @endphp
                    <tr>
                        <td><strong>{{ $code->channel === 'internal' ? 'Belanja Internal' : 'Belanja B2B' }}</strong></td>
                        <td><code class="code-value">{{ $readableCode ?: 'Tidak dapat ditampilkan — buat ulang' }}</code></td>
                        <td><span class="activation-status {{ $codeIsActive ? 'active' : '' }}">{{ $codeIsActive ? 'Aktif' : 'Kedaluwarsa' }}</span></td>
                        <td>{{ $code->expires_at?->format('d M Y, H:i') ?: '-' }}</td>
                        <td>{{ $code->updated_at?->format('d M Y, H:i') ?: '-' }}</td>
                        <td>
                            <div class="code-actions">
                                <form class="code-edit" action="{{ route('admin.access-codes.update', $code) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <label class="sr-only" for="code-{{ $code->id }}">Kode baru untuk {{ $code->channel }}</label>
                                    <input id="code-{{ $code->id }}" name="code" type="text" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" placeholder="5 digit" required>
                                    <button type="submit">Edit</button>
                                </form>
                                <form action="{{ route('admin.access-codes.destroy', $code) }}" method="POST" onsubmit="return confirm('Hapus kode akses ini? Kode tidak lagi bisa dipakai untuk login.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="code-delete" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="code-empty" colspan="6">Belum ada kode akses. Buat kode melalui tombol di atas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="activation-warning">
        <strong>Catatan keamanan:</strong> kode ditampilkan untuk administrator dan disimpan terenkripsi di database. Gunakan hanya untuk kanal yang berwenang. Kode dan sesi akses berlaku sampai 30 hari sejak kode dibuat.
    </div>
</div>
@endsection
