@extends('layouts.frontend')

@section('title', 'Ringkasan Pembayaran - Apotek Medix')

@section('styles')
<style>
    body.public-theme main.payment-page{max-width:820px;margin:1.25rem auto;padding:1rem 1rem 3rem;color:#172b4d;background:#f8fafc!important}
    body.public-theme main.payment-page h1,body.public-theme main.payment-page h2{color:#10243e!important}
    body.public-theme main.payment-page .payment-back,
    body.public-theme main.payment-page .payment-back:visited{display:inline-flex;align-items:center;gap:.45rem;margin-bottom:.9rem;padding:.55rem .8rem;border:1px solid #0755c8;border-radius:8px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff;font-weight:700;text-decoration:none}
    body.public-theme main.payment-page .payment-back i{color:#fff!important}
    body.public-theme main.payment-page .payment-back:hover,
    body.public-theme main.payment-page .payment-back:focus-visible{border-color:#063583;background:#063583!important;color:#fff!important;-webkit-text-fill-color:#fff;text-decoration:none}
    body.public-theme main.payment-page .payment-patient{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin:.25rem 0 .75rem;padding:.8rem 1rem;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#334155}
    body.public-theme main.payment-page .payment-patient>div{display:grid;gap:.2rem;min-width:0}
    body.public-theme main.payment-page .payment-eyebrow{color:#475569!important;-webkit-text-fill-color:#475569;font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    body.public-theme main.payment-page .payment-patient strong{overflow-wrap:anywhere;color:#10243e!important;-webkit-text-fill-color:#10243e}
    body.public-theme main.payment-page .payment-panel{margin:.75rem 0;padding:1rem;border:1px solid #cbd5e1!important;border-radius:12px;background:#fff!important;color:#172b4d!important;box-shadow:0 4px 14px #0f172a12}
    body.public-theme main.payment-page .payment-doctor{display:flex;align-items:flex-start;gap:1rem;padding:1rem}
    body.public-theme main.payment-page .payment-doctor img{width:76px;height:88px;flex:none;object-fit:cover;object-position:center top;border:1px solid #dbe4f0;border-radius:10px;background:#f1f5f9}
    body.public-theme main.payment-page .payment-doctor-info{min-width:0}
    body.public-theme main.payment-page .payment-doctor-label{display:block;margin-bottom:.2rem;color:#475569!important;-webkit-text-fill-color:#475569;font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
    body.public-theme main.payment-page .payment-doctor strong{color:#172b4d!important}
    body.public-theme main.payment-page .payment-doctor-name{display:block;font-size:1.05rem;line-height:1.35;overflow-wrap:anywhere}
    body.public-theme main.payment-page .payment-specialty{display:inline-flex;margin:.35rem 0 .55rem;padding:.2rem .55rem;border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff;color:#1e40af!important;-webkit-text-fill-color:#1e40af;font-size:.78rem;font-weight:700}
    body.public-theme main.payment-page .payment-doctor-meta{display:grid;gap:.35rem}
    body.public-theme main.payment-page .payment-doctor-meta p{display:flex;align-items:flex-start;gap:.45rem;margin:0;color:#334155!important;-webkit-text-fill-color:#334155;font-size:.84rem;line-height:1.45;overflow-wrap:anywhere}
    body.public-theme main.payment-page .payment-doctor-meta i{width:14px;flex:none;margin-top:.18rem;color:#475569!important}
    body.public-theme main.payment-page .payment-line{display:flex;justify-content:space-between;gap:1rem;padding:.3rem 0;color:#475569!important}
    body.public-theme main.payment-page .payment-line strong{color:#334155!important}
    body.public-theme main.payment-page .payment-total{margin-top:.3rem;padding-top:.6rem;border-top:1px solid #e2e8f0;color:#10243e!important;font-size:1.05rem;font-weight:800}
    body.public-theme main.payment-page .payment-total strong{color:#10243e!important}
    body.public-theme main.payment-page .payment-muted{color:#52657e!important;line-height:1.5}
    body.public-theme main.payment-page .consult-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
    body.public-theme main.payment-page .consult-field label{display:block;margin-bottom:.35rem;color:#243b53;font-size:.88rem;font-weight:700}
    body.public-theme main.payment-page .consult-field input,body.public-theme main.payment-page .consult-field textarea{width:100%;min-height:44px;padding:.65rem .75rem;border:1px solid #b8c5d6;border-radius:8px;background:#fff;color:#172b4d;font:inherit}
    body.public-theme main.payment-page .consult-field textarea{min-height:95px;resize:vertical}
    body.public-theme main.payment-page .payment-methods{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;margin:.75rem 0}
    body.public-theme main.payment-page .payment-method-choices{display:flex;flex-direction:column;gap:.5rem;margin:.75rem 0}
    body.public-theme main.payment-page .payment-method-card{width:100%;transition:transform .18s ease}
    body.public-theme main.payment-page .payment-method-card:hover{transform:translateY(-1px)}
    body.public-theme main.payment-page .payment-method-reveal{display:grid;grid-template-rows:0fr;opacity:0;transition:grid-template-rows .28s ease,opacity .2s ease}
    body.public-theme main.payment-page .payment-method-card.is-selected .payment-method-reveal{grid-template-rows:1fr;opacity:1}
    body.public-theme main.payment-page .payment-method-reveal-inner{min-height:0;overflow:hidden}
    body.public-theme main.payment-page .payment-or{align-self:center;color:#64748b;font-size:.82rem;font-weight:700;line-height:1}
    body.public-theme main.payment-page .payment-or[hidden]{display:none!important}
    body.public-theme main.payment-page .payment-option{display:flex;align-items:center;gap:.55rem;min-height:50px;padding:.7rem;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#243b53;font-weight:700}
    body.public-theme main.payment-page [data-cash-option][hidden]{display:none!important}
    body.public-theme main.payment-page .payment-option input{accent-color:#0755c8}
    body.public-theme main.payment-page .payment-method-details{display:grid;gap:.45rem;width:100%;margin:.65rem 0;padding:.9rem 1rem;border:1px solid #dbe4f0;border-radius:10px;background:#f8fafc;color:#334155}
    body.public-theme main.payment-page .payment-account-holder{display:grid;gap:.12rem;padding-bottom:.55rem;border-bottom:1px solid #e2e8f0}
    body.public-theme main.payment-page .payment-detail-label{color:#64748b;font-size:.74rem;font-weight:600}
    body.public-theme main.payment-page .payment-detail-value{color:#10243e!important;font-size:.95rem;font-weight:800;overflow-wrap:anywhere}
    body.public-theme main.payment-page .payment-bank-name{margin-top:.1rem;color:#1e40af;font-size:.84rem;font-weight:800}
    body.public-theme main.payment-page .payment-account-number{color:#10243e!important;font-size:1.15rem;font-weight:800;letter-spacing:.045em;line-height:1.4;overflow-wrap:anywhere}
    body.public-theme main.payment-page .payment-utility-button{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:38px;padding:.5rem .75rem;border:1px solid #0755c8;border-radius:7px;background:#fff;color:#0755c8!important;font:inherit;font-size:.82rem;font-weight:700;text-decoration:none;cursor:pointer}
    body.public-theme main.payment-page .payment-utility-button:hover{background:#eff6ff;color:#063583!important}
    body.public-theme main.payment-page .payment-copy-button{justify-self:start}
    body.public-theme main.payment-page .payment-qris-download{justify-self:center}
    body.public-theme main.payment-page .payment-copy-status{min-height:1.1rem;color:#166534;font-size:.78rem}
    body.public-theme main.payment-page .qris-preview{display:block;width:min(220px,100%);height:auto;margin:.25rem auto 0;border:1px solid #dbe4f0;border-radius:8px;background:#fff}
    body.public-theme main.payment-page .payment-verification{text-align:center}
    body.public-theme main.payment-page .payment-reminder{position:fixed;top:50%;left:50%;width:min(440px,calc(100% - 2rem));max-height:calc(100dvh - 2rem);margin:0;overflow-y:auto;padding:1.5rem;border:0;border-radius:16px;background:#fff;color:#172b4d;box-shadow:0 24px 70px #0f172a40;transform:translate(-50%,-50%)}
    body.public-theme main.payment-page .payment-reminder::backdrop{background:#0f172a80}
    body.public-theme main.payment-page .payment-reminder h2{margin:0 2rem .65rem 0;color:#10243e!important;font-size:1.2rem}
    body.public-theme main.payment-page .payment-reminder p{margin:0 0 1.25rem;color:#475569;line-height:1.55}
    body.public-theme main.payment-page .payment-reminder-actions{display:flex;justify-content:flex-end;gap:.65rem}
    body.public-theme main.payment-page .payment-reminder-close{position:absolute;top:.7rem;right:.7rem;display:grid;place-items:center;width:36px;height:36px;border:0;border-radius:8px;background:transparent;color:#475569;font-size:1.4rem;cursor:pointer}
    body.public-theme main.payment-page .payment-reminder-close:hover{background:#f1f5f9;color:#10243e}
    body.public-theme main.payment-page .payment-reminder-ok{min-height:42px;padding:.65rem 1rem;border:1px solid #0755c8;border-radius:8px;background:#0755c8;color:#fff;font:inherit;font-weight:800;cursor:pointer}
    body.public-theme main.payment-page .payment-reminder-ok:hover{border-color:#063583;background:#063583}
    body.public-theme main.payment-page .consult-check{display:flex;align-items:flex-start;gap:.6rem;margin:1rem 0;color:#334155!important;line-height:1.5}
    body.public-theme main.payment-page .consult-check span{color:#334155!important}
    body.public-theme main.payment-page .consult-warning{padding:.75rem 1rem;border:1px solid #fdba74!important;border-radius:9px;background:#fff7ed!important;color:#7c2d12!important;line-height:1.55}
    body.public-theme main.payment-page .consult-warning strong{color:#7c2d12!important}
    body.public-theme main.payment-page button.consult-primary{display:flex;width:100%;justify-content:center;align-items:center;gap:.5rem;min-height:48px;padding:.7rem 1rem;border:1px solid #0755c8!important;border-radius:6px;background:#0755c8!important;color:#fff!important;-webkit-text-fill-color:#fff!important;font:inherit;font-weight:800;cursor:pointer}
    body.public-theme main.payment-page button.consult-primary *{background:transparent!important;color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.payment-page button.consult-primary:hover{background:#063583!important;border-color:#063583!important;color:#fff!important;-webkit-text-fill-color:#fff!important}
    body.public-theme main.payment-page .consult-errors{padding:.8rem 1rem .8rem 2rem;border:1px solid #fecaca;border-radius:9px;background:#fef2f2;color:#991b1b}
    body.public-theme main.payment-page .consult-warning{padding:.75rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;line-height:1.55}
    @media(prefers-reduced-motion:reduce){body.public-theme main.payment-page .payment-method-card,body.public-theme main.payment-page .payment-method-reveal{transition:none}}
    @media(max-width:620px){body.public-theme main.payment-page{margin:.65rem auto;padding:.8rem .75rem 2rem}body.public-theme main.payment-page .consult-grid,body.public-theme main.payment-page .payment-methods{grid-template-columns:1fr}body.public-theme main.payment-page .payment-patient{padding:.75rem .85rem}body.public-theme main.payment-page .payment-doctor{gap:.75rem;padding:.85rem}body.public-theme main.payment-page .payment-doctor img{width:66px;height:78px}body.public-theme main.payment-page .payment-doctor-name{font-size:.98rem}body.public-theme main.payment-page .payment-doctor-meta p{font-size:.8rem}}
</style>
@endsection

@section('content')
@php
    $patient = session('doctor_consultation.patient');
    $qrisConfigured = filled($settings?->qris_image_path);
    $bankConfigured = filled($settings?->bank_name) && filled($settings?->account_number) && filled($settings?->account_holder);
    $defaultPaymentMethod = old('payment_method');
    if (
        !in_array($defaultPaymentMethod, ['qris', 'transfer', 'Cash'], true)
        || ($defaultPaymentMethod === 'qris' && !$qrisConfigured)
        || ($defaultPaymentMethod === 'transfer' && !$bankConfigured)
        || ($defaultPaymentMethod === 'Cash' && old('payment_channel') !== 'offline')
    ) {
        $defaultPaymentMethod = null;
    }
@endphp
<main class="payment-page">
    <a class="payment-back" href="{{ route('consultations.doctors') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke daftar dokter</a>
    <div class="payment-patient">
        <div><span class="payment-eyebrow">Pasien konsultasi</span><strong>{{ $patient['name'] }}</strong></div>
    </div>
    <section class="payment-panel payment-doctor">
        @if($doctor->photo_url)<img src="{{ $doctor->photo_url }}" alt="Foto {{ $doctor->display_name }}">@endif
        <div class="payment-doctor-info">
            <span class="payment-doctor-label">Dokter pilihan</span>
            <strong class="payment-doctor-name">{{ $doctor->display_name }}</strong>
            <span class="payment-specialty">{{ $doctor->specialty }}</span>
            <div class="payment-doctor-meta">
                @if($doctor->education)<p><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i><span>{{ $doctor->education }}</span></p>@endif
                @if($doctor->practice_place || $doctor->practice_city)<p><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ collect([$doctor->practice_place, $doctor->practice_city])->filter()->join(', ') }}</span></p>@endif
                <p><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span>STR {{ $doctor->str_number }} · Terverifikasi</span></p>
            </div>
        </div>
    </section>
    <section class="payment-panel" aria-label="Rincian pembayaran">
        <div class="payment-line"><span>Biaya sesi {{ $doctor->session_duration_minutes }} menit</span><strong>Rp {{ number_format($doctor->consultation_price, 0, ',', '.') }}</strong></div>
        <div class="payment-line payment-total"><span>Pembayaranmu</span><strong>Rp {{ number_format($doctor->consultation_price, 0, ',', '.') }}</strong></div>
    </section>
    @if($errors->any())<ul class="consult-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <form method="POST" action="{{ route('consultations.store', $doctor) }}">
            @csrf
            <section class="payment-panel">
                <h2 style="margin:0 0 .75rem;font-size:1.05rem">Pilih jalur pembayaran</h2>
                <p class="payment-muted">Setelah melanjutkan, WhatsApp admin akan terbuka untuk membantu proses pembayaran.</p>
                <div class="payment-methods">
                    <label class="payment-option"><input type="radio" name="payment_channel" value="online" @checked(old('payment_channel') === 'online') required> Online</label>
                    <label class="payment-option"><input type="radio" name="payment_channel" value="offline" @checked(old('payment_channel') === 'offline') required> Offline</label>
                </div>
                <div data-payment-method-section @if(!in_array(old('payment_channel'), ['online', 'offline'], true)) hidden @endif>
                    <h3 style="margin:.75rem 0;font-size:.95rem;color:#243b53">Pilih metode pembayaran</h3>
                    <div class="payment-method-choices">
                    @if($qrisConfigured)
                        <div class="payment-method-card @if($defaultPaymentMethod === 'qris') is-selected @endif" data-payment-card>
                            <label class="payment-option"><input type="radio" name="payment_method" value="qris" @checked($defaultPaymentMethod === 'qris') @disabled(!in_array(old('payment_channel'), ['online', 'offline'], true)) required> QRIS</label>
                            <div class="payment-method-reveal">
                                <div class="payment-method-reveal-inner">
                                    <div class="payment-method-details">
                                        <div class="payment-account-holder">
                                            <span class="payment-detail-label">Atas Nama:</span>
                                            <strong class="payment-detail-value">{{ $settings?->account_holder ?: 'Apotek Medix' }}</strong>
                                        </div>
                                        <img class="qris-preview" src="{{ $settings->qris_image_url }}" alt="QRIS pembayaran konsultasi">
                                        <a class="payment-utility-button payment-qris-download" href="{{ $settings->qris_image_url }}" download="qris-apotek-medix.{{ pathinfo($settings->qris_image_path, PATHINFO_EXTENSION) }}">
                                            <i class="fa-solid fa-download" aria-hidden="true"></i> Simpan QRIS
                                        </a>
                                        <p class="payment-muted payment-verification">Verifikasi pembayaran dilakukan admin melalui WhatsApp.</p>
                                        <p class="payment-muted">Kirim bukti transaksi QRIS langsung di chat WhatsApp admin.</p>
                                        @if($settings?->payment_instructions)<p class="payment-muted">{{ $settings->payment_instructions }}</p>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($bankConfigured)
                        @if($qrisConfigured)<span class="payment-or" aria-hidden="true">atau..</span>@endif
                        <div class="payment-method-card @if($defaultPaymentMethod === 'transfer') is-selected @endif" data-payment-card>
                            <label class="payment-option"><input type="radio" name="payment_method" value="transfer" @checked($defaultPaymentMethod === 'transfer') @disabled(!in_array(old('payment_channel'), ['online', 'offline'], true)) required> Transfer bank</label>
                            <div class="payment-method-reveal">
                                <div class="payment-method-reveal-inner">
                                    <div class="payment-method-details">
                                        <div class="payment-account-holder">
                                            <span class="payment-detail-label">Atas Nama:</span>
                                            <strong class="payment-detail-value">{{ $settings->account_holder }}</strong>
                                        </div>
                                        <strong class="payment-bank-name">{{ $settings->bank_name }}</strong>
                                        <span class="payment-detail-label">Nomor rekening</span>
                                        <strong class="payment-account-number">{{ $settings->account_number }}</strong>
                                        <button class="payment-utility-button payment-copy-button" type="button" data-copy-account="{{ $settings->account_number }}">
                                            <i class="fa-regular fa-copy" aria-hidden="true"></i> Salin nomor rekening
                                        </button>
                                        <span class="payment-copy-status" data-copy-status aria-live="polite"></span>
                                        <p class="payment-muted payment-verification">Verifikasi pembayaran dilakukan admin melalui WhatsApp.</p>
                                        <p class="payment-muted">Kirim bukti transfer langsung di chat WhatsApp admin.</p>
                                        @if($settings?->payment_instructions)<p class="payment-muted">{{ $settings->payment_instructions }}</p>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($qrisConfigured || $bankConfigured)
                        <span class="payment-or" data-cash-or aria-hidden="true" @if(old('payment_channel', 'online') !== 'offline') hidden @endif>atau..</span>
                    @endif
                    <div class="payment-method-card @if($defaultPaymentMethod === 'Cash') is-selected @endif" data-payment-card data-cash-option @if(old('payment_channel', 'online') !== 'offline') hidden @endif>
                        <label class="payment-option"><input type="radio" name="payment_method" value="Cash" @checked($defaultPaymentMethod === 'Cash') @disabled(old('payment_channel') !== 'offline')> Tunai <small>(khusus offline)</small></label>
                        <div class="payment-method-reveal">
                            <div class="payment-method-reveal-inner">
                                <div class="payment-method-details payment-muted">Pembayaran tunai dilakukan langsung di apotek.</div>
                            </div>
                        </div>
                    </div>
                    </div>
                    @unless($qrisConfigured || $bankConfigured)
                        <div class="consult-warning">Metode QRIS dan transfer belum dikonfigurasi. Silakan hubungi admin Apotek Medix.</div>
                    @endunless
                </div>
            </section>
            <div class="consult-warning"><strong>Perhatian:</strong> konsultasi WhatsApp bukan untuk kondisi gawat darurat dan tidak menggantikan pemeriksaan langsung bila diperlukan.</div>
            <label class="consult-check"><input type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required><span>Saya menyatakan informasi yang diberikan benar dan menyetujui data pribadi serta kesehatan pada formulir dikirim kepada dokter melalui WhatsApp setelah kode akses diverifikasi.</span></label>
            <button type="submit" class="consult-primary">Konfirmasi Pembayaran</button>
    </form>
    <dialog class="payment-reminder" id="payment-reminder" aria-labelledby="payment-reminder-title" aria-describedby="payment-reminder-description">
        <button type="button" class="payment-reminder-close" aria-label="Tutup pengingat" data-reminder-close>&times;</button>
        <h2 id="payment-reminder-title">Simpan bukti pembayaran</h2>
        <p id="payment-reminder-description">Jangan lupa simpan bukti pembayaran Anda. Bukti tersebut perlu dikirim melalui WhatsApp.</p>
        <div class="payment-reminder-actions">
            <button type="button" class="payment-reminder-ok" data-reminder-confirm>Oke</button>
        </div>
    </dialog>
</main>
<script>
    const paymentForm = document.querySelector('.payment-page form');
    const paymentReminder = document.getElementById('payment-reminder');
    const paymentSubmitButton = paymentForm?.querySelector('button[type="submit"]');
    const reminderConfirmButton = paymentReminder?.querySelector('[data-reminder-confirm]');
    let paymentReminderConfirmed = false;
    paymentForm?.addEventListener('submit', (event) => {
        if (paymentReminderConfirmed) return;
        event.preventDefault();
        paymentReminder?.showModal();
    });
    paymentReminder?.querySelector('[data-reminder-close]')?.addEventListener('click', () => {
        paymentReminder.close();
    });
    reminderConfirmButton?.addEventListener('click', () => {
        paymentReminderConfirmed = true;
        paymentReminder.close();
        paymentForm?.requestSubmit(paymentSubmitButton);
    });

    const channelInputs = document.querySelectorAll('input[name="payment_channel"]');
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const cashInput = document.querySelector('input[name="payment_method"][value="Cash"]');
    const copyAccountButton = document.querySelector('[data-copy-account]');
    if (copyAccountButton) {
        copyAccountButton.addEventListener('click', async () => {
            const status = document.querySelector('[data-copy-status]');
            const accountNumber = copyAccountButton.dataset.copyAccount || '';
            let copied = false;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(accountNumber);
                    copied = true;
                }
            } catch {
                copied = false;
            }
            if (!copied) {
                let temporaryInput;
                try {
                    temporaryInput = document.createElement('textarea');
                    temporaryInput.value = accountNumber;
                    temporaryInput.setAttribute('readonly', '');
                    temporaryInput.style.position = 'fixed';
                    temporaryInput.style.opacity = '0';
                    document.body.appendChild(temporaryInput);
                    temporaryInput.select();
                    copied = document.execCommand('copy');
                } catch {
                    copied = false;
                } finally {
                    temporaryInput?.remove();
                }
            }
            status.textContent = copied
                ? 'Nomor rekening berhasil disalin.'
                : 'Nomor rekening gagal disalin. Silakan salin secara manual.';
        });
    }
    const updatePaymentChannel = () => {
        const selectedChannel = document.querySelector('input[name="payment_channel"]:checked')?.value;
        const paymentMethodSection = document.querySelector('[data-payment-method-section]');
        if (paymentMethodSection) paymentMethodSection.hidden = !selectedChannel;
        const isOffline = selectedChannel === 'offline';
        const cashOption = document.querySelector('[data-cash-option]');
        if (cashOption) cashOption.hidden = !isOffline;
        const cashSeparator = document.querySelector('[data-cash-or]');
        if (cashSeparator) cashSeparator.hidden = !isOffline;
        paymentMethodInputs.forEach((input) => {
            input.disabled = !selectedChannel || (input === cashInput && !isOffline);
        });
        if (cashInput) {
            if (!isOffline && cashInput.checked) {
                cashInput.checked = false;
                cashInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        paymentMethodInputs.forEach((input) => {
            input.required = !input.disabled;
        });
        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
        document.querySelectorAll('[data-payment-card]').forEach((card) => {
            card.classList.toggle('is-selected', card.querySelector('input[name="payment_method"]') === selectedPaymentMethod);
        });
    };
    const makeRadiosToggleable = (inputs, onChange) => {
        let selectedInput = [...inputs].find((input) => input.checked) || null;
        inputs.forEach((input) => {
            input.addEventListener('click', (event) => {
                if (selectedInput !== input) {
                    selectedInput = input;
                    return;
                }

                event.preventDefault();
                selectedInput = null;
                window.setTimeout(() => {
                    if (selectedInput === null) {
                        input.checked = false;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }, 0);
            });
            input.addEventListener('change', () => {
                selectedInput = [...inputs].find((candidate) => candidate.checked) || null;
                onChange();
            });
        });
    };
    const updatePaymentCards = () => {
        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
        document.querySelectorAll('[data-payment-card]').forEach((card) => {
            card.classList.toggle('is-selected', card.querySelector('input[name="payment_method"]') === selectedPaymentMethod);
        });
    };
    const scrollIntoViewIfNeeded = (element) => {
        if (!element) return;
        const bounds = element.getBoundingClientRect();
        if (bounds.top >= 0 && bounds.bottom <= window.innerHeight) return;
        element.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            block: 'nearest',
        });
    };
    makeRadiosToggleable(channelInputs, () => {
        updatePaymentChannel();
        if (document.querySelector('input[name="payment_channel"]:checked')) {
            window.requestAnimationFrame(() => {
                scrollIntoViewIfNeeded(document.querySelector('[data-payment-method-section] h3'));
            });
        }
    });
    makeRadiosToggleable(paymentMethodInputs, () => {
        updatePaymentCards();
        const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (selectedPaymentMethod) {
            window.setTimeout(() => {
                if (selectedPaymentMethod.checked) {
                    scrollIntoViewIfNeeded(selectedPaymentMethod.closest('[data-payment-card]')?.querySelector('.payment-method-reveal'));
                }
            }, 320);
        }
    });
    updatePaymentChannel();
    updatePaymentCards();
</script>
@endsection
