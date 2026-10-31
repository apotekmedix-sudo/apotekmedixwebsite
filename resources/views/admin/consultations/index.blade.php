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
    .consult-access-pagination{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem 1rem;margin-top:.8rem}
    .consult-access-pagination-summary{margin:0;color:#64748b;font-size:.8rem}
    .consult-access-pagination-nav{display:flex;align-items:center;flex-wrap:wrap;gap:.35rem}
    .consult-access-page-link{display:inline-flex;min-width:2rem;min-height:2rem;align-items:center;justify-content:center;padding:.25rem .55rem;border:1px solid #dbe2ea;border-radius:.4rem;background:#fff;color:#334155;font-size:.8rem;font-weight:600;line-height:1.25;text-decoration:none}
    .consult-access-page-link:hover{border-color:#94a3b8;background:#f8fafc}
    .consult-access-page-link.is-current{border-color:#0755c8;background:#0755c8;color:#fff}
    .consult-access-page-link.is-disabled{color:#94a3b8;background:#f8fafc}
    @media(max-width:600px){.consult-access-pagination{align-items:flex-start;flex-direction:column}.consult-access-pagination-nav{width:100%}}
    .consult-admin-actions{display:flex;gap:.4rem;flex-wrap:wrap}.consult-admin-actions form{margin:0}
    .consult-admin-badge{display:inline-block;padding:.25rem .5rem;border-radius:999px;background:#fff7ed;color:#9a3412;font-size:.72rem;font-weight:800}
    .consult-admin-badge.paid,.consult-admin-badge.active{background:#dcfce7;color:#166534}
    .consult-admin-badge.cancelled,.consult-admin-badge.expired,.consult-admin-badge.rejected{background:#fee2e2;color:#991b1b}
    .consult-admin-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}
    .consult-admin-form label{display:block;margin-bottom:.3rem;color:#334155;font-size:.8rem;font-weight:700}
    .consult-admin-form input,.consult-admin-form textarea{width:100%;min-height:38px;padding:.5rem .65rem;border:1px solid #cbd5e1;border-radius:7px;color:#172b4d}
    .consult-qris-preview{display:block;width:min(180px,100%);max-height:180px;margin-top:.5rem;border:1px solid #e2e8f0;border-radius:8px;background:#fff;object-fit:contain}
    .consult-qris-actions{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem}
    .consult-qris-error{margin:.5rem 0 0;color:#b91c1c;font-size:.8rem;font-weight:700}
    .consult-qris-crop-dialog{width:min(900px,calc(100% - 1.5rem));max-width:none;max-height:calc(100% - 1.5rem);padding:0;border:0;border-radius:14px;overflow:auto;background:#fff;color:#1e293b;box-shadow:0 24px 70px #0f172a55}
    .consult-qris-crop-dialog::backdrop{background:#0f172acc}
    .consult-qris-crop-header,.consult-qris-crop-footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid #e2e8f0}
    .consult-qris-crop-header h2{margin:0;color:#172b4d}
    .consult-qris-crop-header p{margin:.25rem 0 0;color:#64748b;font-size:.8rem}
    .consult-qris-crop-content{display:grid;grid-template-columns:minmax(0,1fr) 190px;gap:1rem;align-items:center;padding:1rem}
    .consult-qris-crop-stage{width:100%;aspect-ratio:1.2;overflow:hidden;border:1px solid #cbd5e1;border-radius:8px;background:#111827;touch-action:none}
    #doctorQrisCropCanvas{display:block;width:100%;height:100%;touch-action:none}
    .consult-qris-crop-tools{display:grid;gap:.65rem}
    .consult-qris-crop-tools label{display:grid;gap:.35rem;color:#334155;font-size:.8rem;font-weight:700}
    .consult-qris-crop-tools input{width:100%}
    .consult-qris-crop-tools p{margin:0;color:#64748b;font-size:.78rem;line-height:1.5}
    .consult-qris-crop-result{display:block;width:min(100%,160px);max-height:160px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;object-fit:contain}
    .consult-qris-crop-footer{justify-content:flex-end;border-top:1px solid #e2e8f0;border-bottom:0}
    @media(max-width:650px){.consult-qris-crop-content{grid-template-columns:1fr;padding:.75rem}.consult-qris-crop-stage{aspect-ratio:1}.consult-qris-crop-tools{grid-template-columns:1fr auto;align-items:center}.consult-qris-crop-tools p{grid-column:1/-1}.consult-qris-crop-result{width:80px;height:80px}}
    .consult-admin-errors{padding:.75rem 1rem;border-radius:8px;background:#fef2f2;color:#991b1b}
    .consult-prescription-form{display:grid;gap:.6rem;min-width:280px;margin-top:.6rem;padding:.7rem;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff}
    .consult-prescription-item{display:grid;grid-template-columns:minmax(0,1.5fr) 70px minmax(0,1fr) auto;gap:.35rem;align-items:center}
    .consult-prescription-item select,.consult-prescription-item input{min-width:0;width:100%;padding:.4rem;border:1px solid #cbd5e1;border-radius:6px;color:#172b4d}
    @media(max-width:700px){.consult-prescription-item{grid-template-columns:1fr 70px}.consult-prescription-item input[name$="[instructions]"]{grid-column:1}.consult-prescription-item button{grid-column:2;grid-row:2}}
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
    @if(session('consultation_prescription_link'))
        <section class="consult-admin-card" role="status">
            <h2>Resep konsultasi #{{ session('consultation_prescription_link.consultation_id') }} siap</h2>
            <p class="consult-admin-muted">Nomor resep {{ session('consultation_prescription_link.number') }} · Tautan berlaku 7 hari dan hanya dapat digunakan satu kali.</p>
            <p><a href="{{ session('consultation_prescription_link.url') }}">{{ session('consultation_prescription_link.url') }}</a></p>
            <a class="consult-admin-btn green" href="{{ session('consultation_prescription_link.send_url') }}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> Kirim tautan ke pasien</a>
        </section>
    @endif
    <nav class="consult-admin-tabs" aria-label="Menu Admin Dokter">
        <a class="consult-admin-tab {{ $activeTab === 'doctors' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'doctors']) }}" @if($activeTab === 'doctors') aria-current="page" @endif><i class="fa-solid fa-user-doctor"></i> Data Dokter</a>
        <a class="consult-admin-tab {{ $activeTab === 'transactions' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'transactions']) }}" @if($activeTab === 'transactions') aria-current="page" @endif><i class="fa-solid fa-receipt"></i> Transaksi @if($pendingConsultationsCount > 0)<span class="consult-tab-count" aria-label="{{ $pendingConsultationsCount }} menunggu verifikasi">{{ $pendingConsultationsCount > 99 ? '99+' : $pendingConsultationsCount }}</span>@endif</a>
        <a class="consult-admin-tab {{ $activeTab === 'patients' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'patients']) }}" @if($activeTab === 'patients') aria-current="page" @endif><i class="fa-solid fa-hospital-user"></i> Data Pasien</a>
        <a class="consult-admin-tab {{ $activeTab === 'access-codes' ? 'active' : '' }}" href="{{ route('admin.consultations.index', ['tab' => 'access-codes']) }}" @if($activeTab === 'access-codes') aria-current="page" @endif><i class="fa-solid fa-key"></i> Stok Kode Akses</a>
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
        <p class="consult-admin-muted">Atur QRIS dan rekening resmi yang ditampilkan kepada pasien. Konfirmasi pembayaran dilakukan lewat WhatsApp; kode akses dari stok dikirim kepada pasien melalui chat.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.consultations.payment-settings.update') }}">
            @csrf @method('PUT')
            <div class="consult-admin-form">
                <div>
                    <label for="doctorQrisInput">Gambar QRIS (maks. 3 MB)</label>
                    <input id="doctorQrisInput" type="file" name="qris_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="consult-qris-actions">
                        <button class="consult-admin-btn" type="button" id="doctorQrisCropButton"><i class="fa-solid fa-crop-simple" aria-hidden="true"></i> Crop gambar QRIS</button>
                    </div>
                    <img
                        class="consult-qris-preview"
                        id="doctorQrisPreview"
                        src="{{ $settings?->qris_image_url }}"
                        alt="Pratinjau gambar QRIS"
                        @if(!$settings?->qris_image_path) hidden @endif
                    >
                    <p class="consult-qris-error" id="doctorQrisCropError" role="alert" hidden></p>
                    @if($settings?->qris_image_path)<label style="margin-top:.4rem;font-weight:500"><input type="checkbox" name="remove_qris" value="1"> Hapus QRIS</label>@endif
                </div>
                <div><label for="payment_instructions">Instruksi pembayaran</label><textarea id="payment_instructions" name="payment_instructions" maxlength="1500" rows="4">{{ old('payment_instructions', $settings?->payment_instructions) }}</textarea></div>
                <div><label for="bank_name">Nama bank transfer</label><input id="bank_name" name="bank_name" maxlength="100" value="{{ old('bank_name', $settings?->bank_name) }}" placeholder="BCA / Mandiri / BRI"></div>
                <div><label for="account_number">Nomor rekening</label><input id="account_number" name="account_number" maxlength="50" value="{{ old('account_number', $settings?->account_number) }}"></div>
                <div><label for="account_holder">Nama pemilik rekening</label><input id="account_holder" name="account_holder" maxlength="150" value="{{ old('account_holder', $settings?->account_holder) }}"></div>
            </div>
            <button type="submit" class="consult-admin-btn green" style="margin-top:.8rem">Simpan instruksi pembayaran</button>
        </form>
    </details>
    <dialog class="consult-qris-crop-dialog" id="doctorQrisCropDialog" aria-labelledby="doctorQrisCropTitle">
        <div class="consult-qris-crop-header">
            <div>
                <h2 id="doctorQrisCropTitle">Potong gambar QRIS</h2>
                <p>Geser bingkai untuk memilih area. Tarik sisi atau sudut untuk mengubah ukurannya.</p>
            </div>
            <button type="button" class="consult-admin-btn gray" id="doctorQrisCropClose" aria-label="Tutup crop">&times;</button>
        </div>
        <div class="consult-qris-crop-content">
            <div class="consult-qris-crop-stage">
                <canvas id="doctorQrisCropCanvas" width="720" height="600" aria-label="Bingkai crop QRIS yang bisa dipindah dan diubah ukurannya"></canvas>
            </div>
            <div class="consult-qris-crop-tools">
                <label for="doctorQrisCropZoom">Zoom <output id="doctorQrisCropZoomValue">1.0×</output>
                    <input id="doctorQrisCropZoom" type="range" min="1" max="3" value="1" step="0.01">
                </label>
                <p>Pastikan seluruh kode QR tetap berada di dalam bingkai crop.</p>
                <img class="consult-qris-crop-result" id="doctorQrisCropResult" alt="Pratinjau hasil crop">
            </div>
        </div>
        <div class="consult-qris-crop-footer">
            <button class="consult-admin-btn gray" type="button" id="doctorQrisCropCancel">Batal</button>
            <button class="consult-admin-btn green" type="button" id="doctorQrisCropConfirm"><i class="fa-solid fa-check" aria-hidden="true"></i> Gunakan hasil crop</button>
        </div>
    </dialog>

    <section class="consult-admin-card">
        <h2>Antrean pembayaran dan sesi</h2>
        <p class="consult-admin-muted">Pantau status pembayaran dan konsultasi. Konfirmasi pembayaran dilakukan lewat WhatsApp; pasien memasukkan kode yang admin kirim untuk memperbarui status menjadi lunas. Kode berlaku 24 jam dan hanya dapat dipakai satu kali. Untuk konsultasi Online, admin dapat menyiapkan resep produk.</p>
        <template id="consultPrescriptionItemTemplate">
            <div class="consult-prescription-item">
                <select data-field="id" required>
                    <option value="">Pilih obat</option>
                    @foreach($prescriptionMedicines as $medicine)
                        <option value="{{ $medicine->id }}">{{ $medicine->nama_obat }} ({{ $medicine->brand ?: $medicine->kategori }})</option>
                    @endforeach
                </select>
                <input data-field="quantity" type="number" min="1" max="100" value="1" aria-label="Jumlah" required>
                <input data-field="instructions" type="text" maxlength="500" placeholder="Aturan pakai" required>
                <button class="consult-admin-btn red" type="button" data-remove-prescription-item aria-label="Hapus obat">Hapus</button>
            </div>
        </template>
        <div class="consult-admin-table-wrap">
            <table class="consult-admin-table">
                <thead><tr><th>Waktu</th><th>Pasien / kontak</th><th>Dokter</th><th>Jalur / metode</th><th>Tagihan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($consultations as $consultation)
                    <tr>
                        <td>{{ $consultation->created_at->format('d-m-Y H:i') }}</td>
                        <td><strong>{{ $consultation->patient_name }}</strong><br>{{ $consultation->patient_phone }}<br>{{ $consultation->patient_email }}</td>
                        <td>{{ $consultation->doctorProfile->display_name }}</td>
                        <td>{{ $consultation->payment_channel === 'offline' ? 'Offline' : 'Online' }}<br><small>{{ match($consultation->payment_method) { 'qris' => 'QRIS', 'transfer' => 'Transfer', 'Cash' => 'Tunai', default => ucfirst($consultation->payment_method) } }}</small></td>
                        <td>Rp {{ number_format($consultation->amount, 0, ',', '.') }}</td>
                        <td><span class="consult-admin-badge {{ $consultation->payment_status === 'paid' ? 'paid' : ($consultation->payment_status === 'rejected' ? 'rejected' : (in_array($consultation->payment_status, ['expired','cancelled'], true) ? 'expired' : '')) }}">{{ $consultation->payment_status === 'paid' ? 'Lunas' : ($consultation->payment_status === 'pending' ? 'Menunggu konfirmasi WhatsApp' : ($consultation->payment_status === 'rejected' ? 'Tidak terverifikasi' : ucfirst($consultation->payment_status))) }}</span><br><small>{{ $consultation->status === 'active' ? 'Konsultasi WhatsApp aktif sampai ' . $consultation->ends_at?->format('H:i') : ($consultation->status === 'awaiting_whatsapp' ? ($consultation->access_code_expires_at?->isPast() ? 'Kode akses kedaluwarsa' : 'Kode dikonfirmasi · menunggu konsultasi') : ucfirst(str_replace('_', ' ', $consultation->status))) }}</small>@if($consultation->payment_verified_at && !$consultation->paymentVerifiedBy)<br><small>Dikonfirmasi melalui kode akses</small>@elseif($consultation->paymentVerifiedBy)<br><small>Diverifikasi: {{ $consultation->paymentVerifiedBy->name }}</small>@endif</td>
                        <td>
                            @if($consultation->isActive())
                                <div class="consult-admin-actions">
                                    <form method="POST" action="{{ route('admin.consultations.end-session', $consultation) }}" onsubmit="return confirm('Tandai konsultasi WhatsApp ini selesai dan bebaskan slot dokter?')">@csrf<button class="consult-admin-btn red" type="submit">Tandai selesai</button></form>
                                </div>
                            @elseif($consultation->status === 'completed')
                                <div class="consult-admin-actions">
                                    <button class="consult-admin-btn" type="button" data-chat-url="{{ route('admin.consultations.chat', $consultation) }}" data-chat-patient="{{ $consultation->patient_name }}" data-chat-doctor="{{ $consultation->doctorProfile->display_name }}"><i class="fa-solid fa-comments"></i> Lihat chat</button>
                                    <form method="POST" action="{{ route('admin.consultations.delete', $consultation) }}" onsubmit="return confirm('Hapus sesi dan seluruh riwayat chat ini secara permanen?')">@csrf @method('DELETE')<button class="consult-admin-btn red" type="submit">Hapus sesi</button></form>
                                </div>
                            @else -
                            @endif
                            @if($consultation->payment_status === 'paid' && $consultation->payment_channel === 'online')
                                @if(!$consultation->prescription_number)
                                    <details style="margin-top:.5rem">
                                        <summary class="consult-admin-btn green">Buat resep</summary>
                                        <form class="consult-prescription-form" method="POST" action="{{ route('admin.consultations.prescription.store', $consultation) }}" data-consult-prescription-form>
                                            @csrf
                                            <div data-consult-prescription-items></div>
                                            <div class="consult-admin-actions">
                                                <button class="consult-admin-btn" type="button" data-add-prescription-item>Tambah obat</button>
                                                <button class="consult-admin-btn green" type="submit">Buat resep &amp; tautan checkout</button>
                                            </div>
                                        </form>
                                    </details>
                                @else
                                    <div class="consult-admin-muted" style="margin-top:.4rem">Resep {{ $consultation->prescription_number }} sudah dibuat.</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty<tr><td colspan="7">Belum ada permintaan konsultasi.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:.8rem">{{ $consultations->links() }}</div>
    </section>
    @elseif($activeTab === 'patients')
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
    @else
    <section class="consult-admin-card">
        <div class="consult-admin-head">
            <div>
                <h2>Stok Kode Akses Konsultasi</h2>
                <p class="consult-admin-muted">Kirim kode berstatus tersedia kepada pasien lewat WhatsApp setelah konfirmasi pembayaran. Saat pasien memasukkan kode, sistem mengaitkannya ke konsultasi dan memperbarui status pembayaran; kode berlaku 24 jam, lalu berubah menjadi terpakai ketika konsultasi dimulai.</p>
            </div>
            <div>
                <strong>{{ $availableAccessCodesCount }}</strong> tersedia · {{ $remainingAccessCodeCapacity }} slot kode tersisa
                <button type="button" class="consult-admin-btn" data-copy-all-access-codes @disabled($availableAccessCodesForCopy->isEmpty()) style="margin-left:.5rem">
                    <i class="fa-regular fa-copy"></i> Salin semua kode tersedia
                </button>
                <span data-copy-access-codes-status role="status" aria-live="polite"></span>
                <textarea id="all-available-access-codes" hidden readonly>{{ $availableAccessCodesForCopy->implode("\n") }}</textarea>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.consultations.access-codes.generate') }}" class="consult-admin-actions" style="align-items:end;margin:.75rem 0 1rem">
            @csrf
            <label for="quantity">Jumlah kode baru</label>
            <input id="quantity" name="quantity" type="number" min="1" max="1000" value="{{ old('quantity', 10) }}" required style="width:120px;min-height:38px;padding:.5rem .65rem;border:1px solid #cbd5e1;border-radius:7px">
            <button type="submit" class="consult-admin-btn green"><i class="fa-solid fa-plus"></i> Buat stok kode</button>
        </form>
        <div class="consult-admin-table-wrap">
            <table class="consult-admin-table">
                <thead><tr><th>Kode</th><th>Status</th><th>Pasien / dokter</th><th>Dibuat</th><th>Kedaluwarsa / terpakai</th></tr></thead>
                <tbody>
                @forelse($consultationAccessCodes as $accessCode)
                    <tr>
                        <td><strong style="letter-spacing:.12em">{{ $accessCode->readCode() ?? 'Tidak dapat dibaca' }}</strong></td>
                        <td><span class="consult-admin-badge {{ $accessCode->status === 'available' ? 'paid' : (in_array($accessCode->status, ['used','expired','revoked'], true) ? 'expired' : '') }}">{{ match($accessCode->status) { 'available' => 'Tersedia', 'assigned' => 'Dialokasikan', 'used' => 'Terpakai (tidak berlaku)', 'expired' => 'Kedaluwarsa', 'revoked' => 'Dibatalkan', default => ucfirst($accessCode->status) } }}</span></td>
                        <td>@if($accessCode->consultation)<strong>{{ $accessCode->consultation->patient_name }}</strong><br><small>{{ $accessCode->consultation->doctorProfile->display_name }}</small>@else - @endif</td>
                        <td>{{ $accessCode->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ $accessCode->used_at?->format('d-m-Y H:i') ?? $accessCode->expires_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    </tr>
                @empty<tr><td colspan="5">Tidak ada kode akses yang masih berlaku.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if($consultationAccessCodes->total() > 0)
            <div class="consult-access-pagination">
                <p class="consult-access-pagination-summary">
                    Menampilkan {{ number_format($consultationAccessCodes->firstItem(), 0, ',', '.') }}–{{ number_format($consultationAccessCodes->lastItem(), 0, ',', '.') }}
                    dari {{ number_format($consultationAccessCodes->total(), 0, ',', '.') }} kode akses
                </p>
                @if($consultationAccessCodes->hasPages())
                    <nav class="consult-access-pagination-nav" aria-label="Halaman stok kode akses">
                        @if($consultationAccessCodes->onFirstPage())
                            <span class="consult-access-page-link is-disabled" aria-disabled="true">‹ Sebelumnya</span>
                        @else
                            <a class="consult-access-page-link" href="{{ $consultationAccessCodes->previousPageUrl() }}">‹ Sebelumnya</a>
                        @endif

                        @foreach(range(max(1, $consultationAccessCodes->currentPage() - 2), min($consultationAccessCodes->lastPage(), $consultationAccessCodes->currentPage() + 2)) as $page)
                            @if($page === $consultationAccessCodes->currentPage())
                                <span class="consult-access-page-link is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="consult-access-page-link" href="{{ $consultationAccessCodes->url($page) }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($consultationAccessCodes->hasMorePages())
                            <a class="consult-access-page-link" href="{{ $consultationAccessCodes->nextPageUrl() }}">Berikutnya ›</a>
                        @else
                            <span class="consult-access-page-link is-disabled" aria-disabled="true">Berikutnya ›</span>
                        @endif
                    </nav>
                @endif
            </div>
        @endif
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
    const copyButton = document.querySelector('[data-copy-all-access-codes]');
    const codesField = document.getElementById('all-available-access-codes');
    const status = document.querySelector('[data-copy-access-codes-status]');
    if (!copyButton || !codesField || !status) return;

    copyButton.addEventListener('click', async () => {
        const codes = codesField.value.trim();
        if (!codes) {
            status.textContent = 'Tidak ada kode tersedia untuk disalin.';
            return;
        }

        try {
            await navigator.clipboard.writeText(codes);
            status.textContent = 'Semua kode tersedia berhasil disalin.';
        } catch {
            codesField.hidden = false;
            let copied = false;
            try {
                codesField.focus();
                codesField.select();
                copied = document.execCommand('copy');
            } catch {
                copied = false;
            } finally {
                codesField.hidden = true;
            }
            status.textContent = copied
                ? 'Semua kode tersedia berhasil disalin.'
                : 'Gagal menyalin kode. Silakan salin kode dari daftar.';
        }
    });
})();
</script>

<script>
(() => {
    const template = document.getElementById('consultPrescriptionItemTemplate');
    if (!template) return;

    document.querySelectorAll('[data-consult-prescription-form]').forEach(form => {
        const items = form.querySelector('[data-consult-prescription-items]');
        let nextIndex = 0;
        const addItem = () => {
            if (items.children.length >= 10) return;
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-field]').forEach(field => {
                field.name = `items[${nextIndex}][${field.dataset.field}]`;
            });
            nextIndex++;
            row.querySelector('[data-remove-prescription-item]').addEventListener('click', () => {
                if (items.children.length > 1) row.remove();
            });
            items.append(row);
        };

        form.querySelector('[data-add-prescription-item]').addEventListener('click', addItem);
        addItem();
    });
})();

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

(() => {
    const input = document.getElementById('doctorQrisInput');
    const preview = document.getElementById('doctorQrisPreview');
    const cropButton = document.getElementById('doctorQrisCropButton');
    const dialog = document.getElementById('doctorQrisCropDialog');
    const canvas = document.getElementById('doctorQrisCropCanvas');
    const context = canvas?.getContext('2d');
    const result = document.getElementById('doctorQrisCropResult');
    const zoom = document.getElementById('doctorQrisCropZoom');
    const zoomValue = document.getElementById('doctorQrisCropZoomValue');
    const error = document.getElementById('doctorQrisCropError');
    if (!input || !preview || !cropButton || !dialog || !canvas || !context || !result || !zoom || !zoomValue || !error) return;

    let sourceImage = null;
    let sourceObjectUrl = null;
    let previewObjectUrl = null;
    let imageBounds = null;
    let cropRect = null;
    let imageScale = 1;
    let zoomScale = 1;
    let pointerAction = null;

    const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
    const sourceCrop = () => ({
        x: (cropRect.x - imageBounds.x) / imageScale,
        y: (cropRect.y - imageBounds.y) / imageScale,
        width: cropRect.width / imageScale,
        height: cropRect.height / imageScale,
    });
    const draw = () => {
        if (!sourceImage || !imageBounds || !cropRect) return;
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = '#111827';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(sourceImage, imageBounds.x, imageBounds.y, imageBounds.width, imageBounds.height);

        const left = cropRect.x;
        const top = cropRect.y;
        const right = left + cropRect.width;
        const bottom = top + cropRect.height;
        context.fillStyle = 'rgba(2, 6, 23, .62)';
        context.fillRect(imageBounds.x, imageBounds.y, imageBounds.width, top - imageBounds.y);
        context.fillRect(imageBounds.x, bottom, imageBounds.width, imageBounds.y + imageBounds.height - bottom);
        context.fillRect(imageBounds.x, top, left - imageBounds.x, cropRect.height);
        context.fillRect(right, top, imageBounds.x + imageBounds.width - right, cropRect.height);

        context.save();
        context.beginPath();
        context.rect(left, top, cropRect.width, cropRect.height);
        context.clip();
        context.strokeStyle = 'rgba(255,255,255,.82)';
        context.lineWidth = 1;
        context.beginPath();
        context.moveTo(left + cropRect.width / 3, top);
        context.lineTo(left + cropRect.width / 3, bottom);
        context.moveTo(left + cropRect.width * 2 / 3, top);
        context.lineTo(left + cropRect.width * 2 / 3, bottom);
        context.moveTo(left, top + cropRect.height / 3);
        context.lineTo(right, top + cropRect.height / 3);
        context.moveTo(left, top + cropRect.height * 2 / 3);
        context.lineTo(right, top + cropRect.height * 2 / 3);
        context.stroke();
        context.restore();

        context.strokeStyle = '#fff';
        context.lineWidth = 2;
        context.strokeRect(left, top, cropRect.width, cropRect.height);
        [
            [left, top], [left + cropRect.width / 2, top], [right, top],
            [right, top + cropRect.height / 2], [right, bottom],
            [left + cropRect.width / 2, bottom], [left, bottom], [left, top + cropRect.height / 2],
        ].forEach(([x, y]) => {
            context.fillStyle = '#fff';
            context.strokeStyle = '#0755c8';
            context.lineWidth = 2;
            context.fillRect(x - 5, y - 5, 10, 10);
            context.strokeRect(x - 5, y - 5, 10, 10);
        });

        const selection = sourceCrop();
        const scaled = Math.min(1, 320 / selection.width, 240 / selection.height);
        const resultCanvas = document.createElement('canvas');
        resultCanvas.width = Math.max(1, Math.round(selection.width * scaled));
        resultCanvas.height = Math.max(1, Math.round(selection.height * scaled));
        resultCanvas.getContext('2d').drawImage(
            sourceImage,
            selection.x,
            selection.y,
            selection.width,
            selection.height,
            0,
            0,
            resultCanvas.width,
            resultCanvas.height
        );
        result.src = resultCanvas.toDataURL('image/png');
        zoomValue.value = `${zoomScale.toFixed(1)}×`;
    };
    const openCrop = (url) => {
        error.hidden = true;
        error.textContent = '';
        const image = new Image();
        image.onload = () => {
            sourceImage = image;
            imageScale = Math.min((canvas.width - 40) / image.width, (canvas.height - 40) / image.height);
            imageBounds = {
                x: (canvas.width - image.width * imageScale) / 2,
                y: (canvas.height - image.height * imageScale) / 2,
                width: image.width * imageScale,
                height: image.height * imageScale,
            };
            const insetX = imageBounds.width * .1;
            const insetY = imageBounds.height * .1;
            cropRect = {
                x: imageBounds.x + insetX,
                y: imageBounds.y + insetY,
                width: imageBounds.width - insetX * 2,
                height: imageBounds.height - insetY * 2,
            };
            zoomScale = 1;
            zoom.value = '1';
            dialog.showModal();
            draw();
        };
        image.onerror = () => {
            error.textContent = 'Gambar QRIS tidak dapat dibuka. Pilih file gambar lain.';
            error.hidden = false;
            if (sourceObjectUrl) URL.revokeObjectURL(sourceObjectUrl);
            sourceObjectUrl = null;
        };
        image.src = url;
    };
    const closeCrop = () => {
        if (dialog.open) dialog.close();
        sourceImage = null;
        imageBounds = null;
        cropRect = null;
        pointerAction = null;
        if (sourceObjectUrl) URL.revokeObjectURL(sourceObjectUrl);
        sourceObjectUrl = null;
    };
    const pointFromEvent = (event) => {
        const bounds = canvas.getBoundingClientRect();
        return {
            x: (event.clientX - bounds.left) * canvas.width / bounds.width,
            y: (event.clientY - bounds.top) * canvas.height / bounds.height,
        };
    };
    const hitTest = (point) => {
        const left = cropRect.x;
        const top = cropRect.y;
        const right = left + cropRect.width;
        const bottom = top + cropRect.height;
        const tolerance = 14;
        const nearLeft = Math.abs(point.x - left) <= tolerance;
        const nearRight = Math.abs(point.x - right) <= tolerance;
        const nearTop = Math.abs(point.y - top) <= tolerance;
        const nearBottom = Math.abs(point.y - bottom) <= tolerance;
        const withinY = point.y >= top - tolerance && point.y <= bottom + tolerance;
        const withinX = point.x >= left - tolerance && point.x <= right + tolerance;
        if (nearLeft && nearTop) return 'nw';
        if (nearRight && nearTop) return 'ne';
        if (nearLeft && nearBottom) return 'sw';
        if (nearRight && nearBottom) return 'se';
        if (nearTop && withinX) return 'n';
        if (nearBottom && withinX) return 's';
        if (nearLeft && withinY) return 'w';
        if (nearRight && withinY) return 'e';
        if (point.x >= left && point.x <= right && point.y >= top && point.y <= bottom) return 'move';
        return 'new';
    };
    const resizeAroundCenter = (width, height) => {
        const centerX = cropRect.x + cropRect.width / 2;
        const centerY = cropRect.y + cropRect.height / 2;
        const nextWidth = clamp(width, 24, imageBounds.width);
        const nextHeight = clamp(height, 24, imageBounds.height);
        cropRect.width = nextWidth;
        cropRect.height = nextHeight;
        cropRect.x = clamp(centerX - nextWidth / 2, imageBounds.x, imageBounds.x + imageBounds.width - nextWidth);
        cropRect.y = clamp(centerY - nextHeight / 2, imageBounds.y, imageBounds.y + imageBounds.height - nextHeight);
        draw();
    };

    input.addEventListener('change', () => {
        error.hidden = true;
        const file = input.files?.[0];
        if (!file) return;
        if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = URL.createObjectURL(file);
        preview.src = previewObjectUrl;
        preview.hidden = false;
        const removeCheckbox = document.querySelector('input[name="remove_qris"]');
        if (removeCheckbox) removeCheckbox.checked = false;
    });
    cropButton.addEventListener('click', () => {
        const file = input.files?.[0];
        const currentImage = preview.getAttribute('src');
        if (file) {
            sourceObjectUrl = URL.createObjectURL(file);
            openCrop(sourceObjectUrl);
        } else if (currentImage && !preview.hidden) {
            openCrop(currentImage);
        } else {
            error.textContent = 'Pilih gambar QRIS terlebih dahulu.';
            error.hidden = false;
        }
    });
    zoom.addEventListener('input', () => {
        if (!cropRect) return;
        const nextZoom = Number(zoom.value);
        const ratio = zoomScale / nextZoom;
        zoomScale = nextZoom;
        resizeAroundCenter(cropRect.width * ratio, cropRect.height * ratio);
    });
    canvas.addEventListener('pointerdown', (event) => {
        if (!cropRect) return;
        const rawPoint = pointFromEvent(event);
        const point = {
            x: clamp(rawPoint.x, imageBounds.x, imageBounds.x + imageBounds.width),
            y: clamp(rawPoint.y, imageBounds.y, imageBounds.y + imageBounds.height),
        };
        const mode = hitTest(point);
        pointerAction = {
            id: event.pointerId,
            mode,
            x: point.x,
            y: point.y,
            rect: { ...cropRect },
        };
        canvas.setPointerCapture(event.pointerId);
        event.preventDefault();
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!pointerAction || pointerAction.id !== event.pointerId) return;
        const point = pointFromEvent(event);
        const dx = point.x - pointerAction.x;
        const dy = point.y - pointerAction.y;
        const start = pointerAction.rect;
        if (pointerAction.mode === 'move') {
            cropRect.x = clamp(start.x + dx, imageBounds.x, imageBounds.x + imageBounds.width - start.width);
            cropRect.y = clamp(start.y + dy, imageBounds.y, imageBounds.y + imageBounds.height - start.height);
            draw();
            return;
        }
        let left = start.x;
        let top = start.y;
        let right = start.x + start.width;
        let bottom = start.y + start.height;
        const edges = pointerAction.mode;
        if (edges === 'new') {
            left = clamp(Math.min(pointerAction.x, point.x), imageBounds.x, imageBounds.x + imageBounds.width);
            top = clamp(Math.min(pointerAction.y, point.y), imageBounds.y, imageBounds.y + imageBounds.height);
            right = clamp(Math.max(pointerAction.x, point.x), imageBounds.x, imageBounds.x + imageBounds.width);
            bottom = clamp(Math.max(pointerAction.y, point.y), imageBounds.y, imageBounds.y + imageBounds.height);
        } else {
            if (edges.includes('w')) left = clamp(start.x + dx, imageBounds.x, right - 24);
            if (edges.includes('e')) right = clamp(start.x + start.width + dx, left + 24, imageBounds.x + imageBounds.width);
            if (edges.includes('n')) top = clamp(start.y + dy, imageBounds.y, bottom - 24);
            if (edges.includes('s')) bottom = clamp(start.y + start.height + dy, top + 24, imageBounds.y + imageBounds.height);
        }
        if (right - left < 24 || bottom - top < 24) return;
        cropRect = { x: left, y: top, width: right - left, height: bottom - top };
        draw();
    });
    canvas.addEventListener('pointerup', () => { pointerAction = null; });
    canvas.addEventListener('pointercancel', () => { pointerAction = null; });
    document.getElementById('doctorQrisCropClose').addEventListener('click', closeCrop);
    document.getElementById('doctorQrisCropCancel').addEventListener('click', closeCrop);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeCrop();
    });
    document.getElementById('doctorQrisCropConfirm').addEventListener('click', () => {
        if (!sourceImage || !cropRect) return;
        const selection = sourceCrop();
        const output = document.createElement('canvas');
        const scale = Math.min(1, 4000 / selection.width, 4000 / selection.height);
        output.width = Math.max(1, Math.round(selection.width * scale));
        output.height = Math.max(1, Math.round(selection.height * scale));
        const outputContext = output.getContext('2d');
        if (!outputContext) {
            error.textContent = 'Browser tidak mendukung proses crop gambar.';
            error.hidden = false;
            closeCrop();
            return;
        }
        outputContext.imageSmoothingQuality = 'high';
        outputContext.drawImage(
            sourceImage,
            selection.x,
            selection.y,
            selection.width,
            selection.height,
            0,
            0,
            output.width,
            output.height
        );
        output.toBlob((blob) => {
            if (!blob) {
                error.textContent = 'Hasil crop gagal dibuat. Coba pilih gambar lain.';
                error.hidden = false;
                closeCrop();
                return;
            }
            if (blob.size > 3 * 1024 * 1024) {
                error.textContent = 'Hasil crop melebihi batas 3 MB. Perkecil area crop atau gunakan gambar lain.';
                error.hidden = false;
                return;
            }
            const transfer = new DataTransfer();
            transfer.items.add(new File([blob], 'qris-cropped.png', { type: 'image/png' }));
            input.files = transfer.files;
            if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = URL.createObjectURL(blob);
            preview.src = previewObjectUrl;
            preview.hidden = false;
            const removeCheckbox = document.querySelector('input[name="remove_qris"]');
            if (removeCheckbox) removeCheckbox.checked = false;
            error.hidden = true;
            closeCrop();
        }, 'image/png');
    });
})();
</script>
@endsection
