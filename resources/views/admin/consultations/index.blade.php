@extends('layouts.admin')

@section('title', 'Admin Dokter - Admin Apotek Medix')
@section('page-title', 'Admin Dokter')

@section('content')
<style>
    .consult-admin{display:grid;gap:1rem;color:#1e293b}
    .consult-admin-card{padding:1rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}
    .consult-admin-head{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap}
    .consult-admin-tabs{display:flex;gap:.5rem;flex-wrap:wrap;padding:.4rem;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc}
    .consult-admin-tab{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem .9rem;border-radius:7px;color:#475569;font-size:.85rem;font-weight:800;text-decoration:none}
    .consult-tab-count{display:inline-flex;min-width:1.25rem;height:1.25rem;align-items:center;justify-content:center;padding:0 .3rem;border-radius:999px;background:#dc2626;color:#fff;font-size:.7rem;line-height:1}
    .consult-admin-tab.active{background:#0755c8;color:#fff}
    .consult-admin-settings summary{display:inline-flex;align-items:center;gap:.4rem;list-style:none}
    .consult-admin-settings summary::-webkit-details-marker{display:none}
    .consult-admin-settings[open] summary{margin-bottom:.75rem}
    .consult-admin h2{margin:0 0 .7rem;color:#172b4d;font-size:1.05rem}
    .consult-admin-muted{margin:.25rem 0 .8rem;color:#64748b;font-size:.85rem;line-height:1.5}
    .consult-admin-btn{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.5rem .7rem;border:0;border-radius:7px;background:#0755c8;color:#fff;font-size:.8rem;font-weight:800;text-decoration:none;cursor:pointer}
    .consult-admin-btn.green{background:#15803d}.consult-admin-btn.red{background:#b91c1c}.consult-admin-btn.gray{background:#475569}
    .consult-admin-table-wrap{overflow:auto}.consult-admin-table{width:100%;border-collapse:collapse;font-size:.82rem}
    .consult-admin-table th,.consult-admin-table td{padding:.65rem;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:middle}
    .consult-admin-table th{background:#f8fafc;color:#475569;white-space:nowrap}
    .consult-admin-table td{color:#334155}
    .consult-admin-actions{display:flex;gap:.4rem;flex-wrap:wrap}.consult-admin-actions form{margin:0}
    .consult-admin-badge{display:inline-block;padding:.25rem .5rem;border-radius:999px;background:#fff7ed;color:#9a3412;font-size:.72rem;font-weight:800}
    .consult-admin-badge.paid,.consult-admin-badge.active{background:#dcfce7;color:#166534}
    .consult-admin-badge.cancelled,.consult-admin-badge.expired,.consult-admin-badge.rejected{background:#fee2e2;color:#991b1b}
    .consult-admin-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .consult-admin-form label{display:block;margin-bottom:.3rem;color:#334155;font-size:.8rem;font-weight:700}
    .consult-admin-form input,.consult-admin-form textarea{width:100%;min-height:38px;padding:.5rem .65rem;border:1px solid #cbd5e1;border-radius:7px;color:#172b4d}
    .consult-admin-errors{padding:.75rem 1rem;border-radius:8px;background:#fef2f2;color:#991b1b}
    .consult-chat-modal{width:min(760px,calc(100vw - 2rem));max-height:min(85vh,800px);margin:auto;padding:0;border:1px solid #cbd5e1;border-radius:14px;background:#fff;color:#1e293b;box-shadow:0 24px 80px #0f172a55}
    .consult-chat-modal::backdrop{background:#0f172a88}
    .consult-chat-modal-content{display:grid;grid-template-rows:auto auto minmax(0,1fr);max-height:min(85vh,800px)}
    .consult-chat-modal-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;padding:1rem;border-bottom:1px solid #e2e8f0}
    .consult-chat-modal-header h2{margin:0 0 .3rem;color:#172b4d;font-size:1.05rem}
    .consult-chat-modal-meta{margin:0;color:#64748b;font-size:.82rem;line-height:1.5}
    .consult-chat-modal-close{width:36px;height:36px;flex:none;border:0;border-radius:8px;background:#f1f5f9;color:#334155;font-size:1.2rem;cursor:pointer}
    .consult-chat-modal-status{min-height:1.2rem;margin:0;padding:.6rem 1rem;color:#64748b;font-size:.85rem}
    .consult-chat-transcript{display:flex;flex-direction:column;gap:.65rem;overflow:auto;padding:0 1rem 1rem}
    .consult-chat-message{max-width:min(85%,620px);padding:.7rem .8rem;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;white-space:pre-wrap;overflow-wrap:anywhere}
    .consult-chat-message.doctor{align-self:flex-end;border-color:#bfdbfe;background:#eff6ff}
    .consult-chat-message.patient{align-self:flex-start}
    .consult-chat-message header{display:flex;justify-content:space-between;gap:1rem;margin-bottom:.35rem;color:#475569;font-size:.72rem;font-weight:800}
    .consult-chat-message p{margin:0;color:#1e293b;line-height:1.5}
    @media(max-width:600px){.consult-chat-message{max-width:100%;width:100%}.consult-chat-message header{flex-direction:column;gap:.15rem}}
    @media(max-width:650px){.consult-admin-form{grid-template-columns:1fr}}
</style>

<div class="consult-admin">
    @if($errors->any())<ul class="consult-admin-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    @if(session('success'))<div style="padding:.75rem 1rem;border-radius:8px;background:#dcfce7;color:#166534">{{ session('success') }}</div>@endif
    <nav class="consult-admin-tabs" aria-label="Menu Admin Dokter">
        <a class="consult-admin-tab {{ $activeTab === 'doctors' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'doctors']) }}" @if($activeTab === 'doctors') aria-current="page" @endif><i class="fa-solid fa-user-doctor"></i> Data Dokter</a>
        <a class="consult-admin-tab {{ $activeTab === 'transactions' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'transactions']) }}" @if($activeTab === 'transactions') aria-current="page" @endif><i class="fa-solid fa-receipt"></i> Transaksi @if($pendingConsultationsCount > 0)<span class="consult-tab-count" aria-label="{{ $pendingConsultationsCount }} menunggu verifikasi">{{ $pendingConsultationsCount > 99 ? '99+' : $pendingConsultationsCount }}</span>@endif</a>
        <a class="consult-admin-tab {{ $activeTab === 'patients' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'patients']) }}" @if($activeTab === 'patients') aria-current="page" @endif><i class="fa-solid fa-hospital-user"></i> Data Pasien</a>
    </nav>

    @if($activeTab === 'doctors')
    <section class="consult-admin-card">
        <div class="consult-admin-head"><div><h2>Data Dokter</h2><p class="consult-admin-muted">Kelola profil, nomor WhatsApp, tarif dan durasi sesi, ketersediaan, serta verifikasi STR dokter. Dokter tidak perlu akun login.</p></div><a class="consult-admin-btn" href="{{ route('admin.consultations.doctors.create') }}"><i class="fa-solid fa-user-plus"></i> Tambah dokter</a></div>
        <div class="consult-admin-table-wrap">
            <table class="consult-admin-table">
                <thead><tr><th>Dokter</th><th>Spesialisasi</th><th>Tarif / Durasi</th><th>STR</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($doctors as $doctor)
                    <tr>
                        <td><strong>{{ $doctor->display_name }}</strong><br>WA {{ $doctor->whatsapp_phone }}<br><small>{{ $doctor->str_number }}</small></td>
                        <td>{{ $doctor->specialty }}</td>
                        <td>Rp {{ number_format($doctor->consultation_price, 0, ',', '.') }}<br><small>{{ $doctor->session_duration_minutes }} menit / sesi</small></td>
                        <td><span class="consult-admin-badge {{ $doctor->str_verified ? 'paid' : '' }}">{{ $doctor->str_verified ? 'Terverifikasi' : 'Belum diverifikasi' }}</span></td>
                        <td>{{ !$doctor->is_active ? 'Nonaktif' : ($doctor->is_online ? 'Online' : 'Offline') }}</td>
                        <td><div class="consult-admin-actions"><a class="consult-admin-btn" href="{{ route('admin.consultations.doctors.edit', $doctor) }}">Edit</a>@if($doctor->is_active)<form method="POST" action="{{ route('admin.consultations.doctors.deactivate', $doctor) }}" onsubmit="return confirm('Nonaktifkan profil dokter ini?')">@csrf @method('DELETE')<button class="consult-admin-btn red" type="submit">Nonaktifkan</button></form>@endif</div></td>
                    </tr>
                @empty<tr><td colspan="6">Belum ada dokter terdaftar.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </section>
    @elseif($activeTab === 'transactions')

    <details class="consult-admin-card consult-admin-settings" @if($errors->any()) open @endif>
        <summary class="consult-admin-btn"><i class="fa-solid fa-gear"></i> Setting Rekening</summary>
        <p class="consult-admin-muted">Atur QRIS dan rekening resmi yang ditampilkan kepada pasien. Admin memeriksa bukti online yang diunggah atau bukti yang ditunjukkan di kasir sebelum memverifikasi pembayaran.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.consultations.payment-settings.update') }}">
            @csrf @method('PUT')
            <div class="consult-admin-form">
                <div><label for="qris_image">Gambar QRIS (maks. 3 MB)</label><input id="qris_image" type="file" name="qris_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">@if($settings?->qris_image_path)<img src="{{ $settings->qris_image_url }}" alt="QRIS aktif" style="display:block;width:120px;margin-top:.5rem;border:1px solid #e2e8f0;border-radius:8px"><label style="margin-top:.4rem;font-weight:500"><input type="checkbox" name="remove_qris" value="1"> Hapus QRIS</label>@endif</div>
                <div><label for="payment_instructions">Instruksi pembayaran</label><textarea id="payment_instructions" name="payment_instructions" maxlength="1500" rows="4">{{ old('payment_instructions', $settings?->payment_instructions) }}</textarea></div>
                <div><label for="bank_name">Nama bank transfer</label><input id="bank_name" name="bank_name" maxlength="100" value="{{ old('bank_name', $settings?->bank_name) }}" placeholder="BCA / Mandiri / BRI"></div>
                <div><label for="account_number">Nomor rekening</label><input id="account_number" name="account_number" maxlength="50" value="{{ old('account_number', $settings?->account_number) }}"></div>
                <div><label for="account_holder">Nama pemilik rekening</label><input id="account_holder" name="account_holder" maxlength="150" value="{{ old('account_holder', $settings?->account_holder) }}"></div>
            </div>
            <button type="submit" class="consult-admin-btn green" style="margin-top:.8rem">Simpan instruksi pembayaran</button>
        </form>
    </details>

    <section class="consult-admin-card">
        <h2>Antrean pembayaran dan sesi</h2>
        <p class="consult-admin-muted">Periksa bukti pembayaran yang diunggah atau ditunjukkan di kasir sebelum menandai lunas. Verifikasi akan langsung memulai timer sesuai durasi sesi dokter. Catatan keluhan/alergi hanya dapat dilihat dokter yang menangani.</p>
        <div class="consult-admin-table-wrap">
            <table class="consult-admin-table">
                <thead><tr><th>Waktu</th><th>Pasien / kontak</th><th>Dokter</th><th>Metode</th><th>Bukti pembayaran</th><th>Tagihan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($consultations as $consultation)
                    <tr>
                        <td>{{ $consultation->created_at->format('d-m-Y H:i') }}</td>
                        <td><strong>{{ $consultation->patient_name }}</strong><br>{{ $consultation->patient_phone }}<br>{{ $consultation->patient_email }}</td>
                        <td>{{ $consultation->doctorProfile->display_name }}</td>
                        <td>{{ $consultation->payment_method === 'qris' ? 'QRIS' : 'Transfer' }}<br><small>{{ $consultation->payment_channel === 'offline' ? 'Offline di kasir' : 'Online' }}</small></td>
                        <td>@if($consultation->payment_proof_path)<a class="consult-admin-btn gray" href="{{ route('admin.consultations.payment-proof', $consultation) }}">Lihat bukti</a>@else - @endif</td>
                        <td>Rp {{ number_format($consultation->amount, 0, ',', '.') }}</td>
                        <td><span class="consult-admin-badge {{ $consultation->payment_status === 'paid' ? 'paid' : ($consultation->payment_status === 'rejected' ? 'rejected' : (in_array($consultation->payment_status, ['expired','cancelled'], true) ? 'expired' : '')) }}">{{ $consultation->payment_status === 'paid' ? 'Lunas' : ($consultation->payment_status === 'pending' ? 'Menunggu pembayaran' : ($consultation->payment_status === 'rejected' ? 'Ditolak' : ucfirst($consultation->payment_status))) }}</span><br><small>{{ $consultation->status === 'active' ? 'Sesi aktif sampai ' . $consultation->ends_at?->format('H:i') : ucfirst(str_replace('_', ' ', $consultation->status)) }}</small>@if($consultation->paymentVerifiedBy)<br><small>Diverifikasi: {{ $consultation->paymentVerifiedBy->name }}</small>@endif</td>
                        <td>
                            @if($consultation->status === 'awaiting_payment' && $consultation->payment_status === 'pending')
                                <div class="consult-admin-actions">
                                    <form method="POST" action="{{ route('admin.consultations.mark-paid', $consultation) }}" onsubmit="return confirm('Verifikasi pembayaran? Timer {{ $consultation->session_duration_minutes }} menit baru dimulai saat dokter masuk ke room.')">@csrf<button class="consult-admin-btn green" type="submit">Tandai lunas</button></form>
                                    <form method="POST" action="{{ route('admin.consultations.reject-payment', $consultation) }}" onsubmit="return confirm('Tolak pembayaran ini? Pasien akan diminta menghubungi admin untuk pengembalian dana.')">@csrf<button class="consult-admin-btn red" type="submit">Tolak pembayaran</button></form>
                                </div>
                            @elseif($consultation->isActive())
                                <div class="consult-admin-actions">
                                    @if((int) session('consultation_whatsapp_links.consultation_id') === $consultation->id)
                                        <a class="consult-admin-btn green" href="{{ session('consultation_whatsapp_links.doctor') }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> Kirim tautan dokter</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.consultations.end-session', $consultation) }}" onsubmit="return confirm('Akhiri sesi konsultasi ini sekarang? Pasien dan dokter tidak dapat mengirim pesan lagi.')">@csrf<button class="consult-admin-btn red" type="submit">Akhiri sesi</button></form>
                                    <form method="POST" action="{{ route('admin.consultations.room-invitations', $consultation) }}" onsubmit="return confirm('Buat tautan sekali pakai baru? Tautan lama langsung tidak berlaku.')">@csrf<button class="consult-admin-btn" type="submit">Buat ulang tautan WA</button></form>
                                </div>
                            @elseif($consultation->status === 'completed')
                                <div class="consult-admin-actions">
                                    <button class="consult-admin-btn" type="button" data-chat-url="{{ route('admin.consultations.chat', $consultation) }}" data-chat-patient="{{ $consultation->patient_name }}" data-chat-doctor="{{ $consultation->doctorProfile->display_name }}"><i class="fa-solid fa-comments"></i> Lihat chat</button>
                                    <form method="POST" action="{{ route('admin.consultations.delete', $consultation) }}" onsubmit="return confirm('Hapus sesi dan seluruh riwayat chat ini secara permanen?')">@csrf @method('DELETE')<button class="consult-admin-btn red" type="submit">Hapus sesi</button></form>
                                </div>
                            @else -
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="8">Belum ada permintaan konsultasi.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem">{{ $consultations->links() }}</div>
    </section>
    @else
    <section class="consult-admin-card">
        <div class="consult-admin-head">
            <div>
                <h2>Data Pasien</h2>
                <p class="consult-admin-muted">Data ini tercatat saat pasien mengirim permintaan konsultasi dengan dokter. Profil pasien yang belum menghubungi dokter tidak ditampilkan di sini.</p>
            </div>
            <a class="consult-admin-btn green" href="{{ route('admin.consultations.patients.export') }}"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
        </div>
        <div class="consult-admin-table-wrap">
            <table class="consult-admin-table">
                <thead><tr><th>Waktu permintaan</th><th>Nama pasien</th><th>Tanggal lahir</th><th>Jenis kelamin</th><th>Kontak</th><th>Dokter</th><th>Status konsultasi</th></tr></thead>
                <tbody>
                @forelse($consultations as $consultation)
                    <tr>
                        <td>{{ $consultation->created_at->format('d-m-Y H:i') }}</td>
                        <td><strong>{{ $consultation->patient_name }}</strong></td>
                        <td>{{ $consultation->patient_birth_date ? \Illuminate\Support\Carbon::parse($consultation->patient_birth_date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $consultation->patient_gender ?: '-' }}</td>
                        <td>{{ $consultation->patient_phone }}@if($consultation->patient_email)<br>{{ $consultation->patient_email }}@endif</td>
                        <td>{{ $consultation->doctorProfile->display_name }}</td>
                        <td><span class="consult-admin-badge {{ $consultation->payment_status === 'paid' ? 'paid' : ($consultation->payment_status === 'rejected' ? 'rejected' : (in_array($consultation->payment_status, ['expired','cancelled'], true) ? 'expired' : '')) }}">{{ $consultation->payment_status === 'paid' ? 'Lunas' : ($consultation->payment_status === 'pending' ? 'Menunggu pembayaran' : ($consultation->payment_status === 'rejected' ? 'Ditolak' : ucfirst($consultation->payment_status))) }}</span><br><small>{{ ucfirst(str_replace('_', ' ', $consultation->status)) }}</small></td>
                    </tr>
                @empty<tr><td colspan="7">Belum ada data pasien dari permintaan konsultasi.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem">{{ $consultations->links() }}</div>
    </section>
    @endif
</div>

<dialog class="consult-chat-modal" id="consultChatModal" aria-labelledby="consultChatTitle">
    <div class="consult-chat-modal-content">
        <header class="consult-chat-modal-header">
            <div>
                <h2 id="consultChatTitle">Riwayat percakapan</h2>
                <p class="consult-chat-modal-meta" id="consultChatMeta"></p>
            </div>
            <button type="button" class="consult-chat-modal-close" id="consultChatClose" aria-label="Tutup riwayat chat">&times;</button>
        </header>
        <p class="consult-chat-modal-status" id="consultChatStatus" role="status" aria-live="polite"></p>
        <div class="consult-chat-transcript" id="consultChatTranscript"></div>
    </div>
</dialog>

<script>
(() => {
    const modal = document.getElementById('consultChatModal');
    const closeButton = document.getElementById('consultChatClose');
    const status = document.getElementById('consultChatStatus');
    const meta = document.getElementById('consultChatMeta');
    const transcript = document.getElementById('consultChatTranscript');

    const closeModal = () => modal.close();
    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });

    document.querySelectorAll('[data-chat-url]').forEach(button => {
        button.addEventListener('click', async () => {
            transcript.replaceChildren();
            meta.textContent = `${button.dataset.chatPatient} · ${button.dataset.chatDoctor}`;
            status.textContent = 'Memuat riwayat chat...';
            modal.showModal();

            try {
                const response = await fetch(button.dataset.chatUrl, {
                    headers: {'Accept': 'application/json'},
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Riwayat chat tidak dapat dimuat.');
                const data = await response.json();
                meta.textContent = `${data.patient_name} · ${data.doctor_name} · Sesi selesai ${data.completed_at || '-'}`;

                if (data.messages.length === 0) {
                    status.textContent = 'Belum ada pesan pada sesi ini.';
                    return;
                }

                data.messages.forEach(message => {
                    const item = document.createElement('article');
                    item.className = `consult-chat-message ${message.sender_type === 'doctor' ? 'doctor' : 'patient'}`;
                    const header = document.createElement('header');
                    const sender = document.createElement('span');
                    sender.textContent = message.sender_name;
                    const time = document.createElement('time');
                    time.textContent = message.created_at || '';
                    header.append(sender, time);

                    const body = document.createElement('p');
                    body.textContent = message.body;
                    item.append(header, body);
                    transcript.append(item);
                });
                status.textContent = '';
            } catch (error) {
                status.textContent = error.message || 'Terjadi kesalahan saat memuat riwayat chat.';
            }
        });
    });
})();
</script>
@endsection
