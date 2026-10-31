<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Apotek Medix</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#1e293b;background:#f8fafc;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0;background:#f8fafc}button,input,select{font:inherit}
        header{background:#fff;border-bottom:1px solid #e2e8f0}.topbar{max-width:1440px;margin:auto;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;gap:16px}
        .brand{display:flex;align-items:center;gap:12px}.logo{width:42px;height:42px;border-radius:12px;background:#fff;object-fit:contain}
        h1{margin:0;font-size:20px;letter-spacing:-.03em}.subtitle{margin:3px 0 0;color:#64748b;font-size:13px}
        .top-actions{display:flex;align-items:center;gap:12px}.user{font-size:13px;color:#475569}.logout{border:1px solid #cbd5e1;background:#fff;color:#334155;padding:9px 12px;border-radius:9px;font-weight:700;cursor:pointer}
        .cashier-order-notification{position:relative;display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#c2410c;text-decoration:none;font-size:13px;font-weight:800;white-space:nowrap}
        .cashier-order-notification[hidden]{display:none}
        .cashier-order-notification-count{display:inline-flex;min-width:20px;height:20px;align-items:center;justify-content:center;padding:0 5px;border-radius:999px;background:#dc2626;color:#fff;font-size:11px;line-height:1;box-shadow:0 0 0 0 #dc262680;animation:cashierNotificationPulse 1.8s infinite}
        @keyframes cashierNotificationPulse{0%{box-shadow:0 0 0 0 #dc262680}70%{box-shadow:0 0 0 7px #dc262600}100%{box-shadow:0 0 0 0 #dc262600}}
        @media(prefers-reduced-motion:reduce){.cashier-order-notification-count{animation:none}}
        .admin-dashboard-link{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #bfdbfe;border-radius:9px;background:#eff6ff;color:#0052cc;text-decoration:none;font-size:13px;font-weight:800;white-space:nowrap}
        .admin-dashboard-link:hover{background:#dbeafe}
        .realtime-clock-badge{display:inline-flex;align-items:center;gap:10px;padding:8px 14px;border-radius:999px;background:#f8fafc;border:1px solid #dbe4f0;color:#0f172a;font-size:13px;font-weight:700;box-shadow:inset 0 1px 2px #0f172a08;white-space:nowrap}
        .realtime-clock-dot{width:8px;height:8px;border-radius:50%;background:#10b981;box-shadow:0 0 0 3px #10b98128;animation:clockPulse 2s infinite}
        .realtime-clock-date{color:#475569;font-weight:600}
        .realtime-clock-sep{color:#cbd5e1}
        .realtime-clock-time{color:#0052cc;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:.02em}
        @keyframes clockPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.55;transform:scale(1.2)}}
        main{max-width:1440px;margin:0 auto;padding:24px}.welcome{display:flex;justify-content:space-between;align-items:end;margin-bottom:20px;gap:12px}
        .welcome h2{margin:0 0 5px;font-size:25px;letter-spacing:-.04em}.welcome p{margin:0;color:#64748b;font-size:14px}
        .refresh{border:0;border-radius:9px;background:#0052cc;color:#fff;padding:10px 14px;font-weight:700;text-decoration:none}
        .notice{padding:12px 15px;border-radius:10px;margin-bottom:16px;background:#dcfce7;color:#166534}.notice.error{background:#fee2e2;color:#991b1b}
        .notice.update{background:#eff6ff;color:#1d4ed8}.notice.queue-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border:1px solid #fbbf24;background:#fffbeb;color:#92400e;font-weight:700;box-shadow:0 4px 14px #92400e12}.notice a{color:inherit;font-weight:800}
        .pending-queue-link{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid #fed7aa;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:800;text-decoration:none;white-space:nowrap}
        .pending-queue-count{display:inline-flex;min-width:22px;height:22px;align-items:center;justify-content:center;padding:0 6px;border-radius:999px;background:#dc2626;color:#fff;font-size:11px}
        .metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:20px}
        .metric,.panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 1px 2px #0f172a08}
        .metric{padding:17px}.metric-label{font-size:12px;color:#64748b;font-weight:700}.metric-value{font-size:26px;font-weight:800;margin-top:7px;letter-spacing:-.04em}
        .metric.pending .metric-value{color:#d97706}.metric.verified .metric-value{color:#0052cc}.metric.printed .metric-value{color:#059669}.metric.cancelled .metric-value{color:#64748b}
        .filters{padding:17px;margin-bottom:16px}.filter-title{font-weight:800;margin:0 0 12px}
        .payment-settings{margin-bottom:20px;padding:17px}
        .payment-settings summary{cursor:pointer;font-weight:800;color:#0f172a}
        .payment-settings-body{padding-top:14px}
        .settings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .settings-grid .wide{grid-column:1/-1}
        .settings-grid label{margin-bottom:6px}
        .settings-grid textarea{width:100%;min-height:84px;border:1px solid #cbd5e1;border-radius:8px;padding:10px;font:inherit;resize:vertical}
        .settings-help{margin:5px 0 0;color:#64748b;font-size:12px}
        .settings-image{display:block;width:min(180px,100%);max-height:180px;margin-top:8px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;background:#fff}
        .qris-image-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:8px}
        .qris-image-preview{display:block;width:min(180px,100%);max-height:180px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;background:#fff}
        .qris-image-preview[hidden]{display:none}
        .qris-crop-button{border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff;color:#0052cc;padding:8px 10px;font-size:12px;font-weight:800;cursor:pointer}
        .qris-crop-error{margin:8px 0 0;color:#b91c1c;font-size:12px;font-weight:700}
        .qris-crop-dialog{width:min(760px,calc(100% - 24px));max-width:none;max-height:calc(100% - 24px);padding:0;border:0;border-radius:16px;overflow:auto;box-shadow:0 24px 70px #0f172a55}
        .qris-crop-dialog::backdrop{background:#0f172acc}
        .qris-crop-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:16px 18px;border-bottom:1px solid #e2e8f0}
        .qris-crop-header h2{margin:0;font-size:17px}
        .qris-crop-header p{margin:5px 0 0;color:#64748b;font-size:12px}
        .qris-crop-close{border:1px solid #cbd5e1;border-radius:8px;background:#fff;padding:7px 10px;cursor:pointer}
        .qris-crop-content{display:grid;grid-template-columns:minmax(0,1fr) 210px;gap:18px;align-items:center;padding:18px}
        .qris-crop-canvas-wrap{width:min(100%,520px);aspect-ratio:1.15;margin:auto;overflow:hidden;border:1px solid #cbd5e1;border-radius:10px;background:#111827;touch-action:none}
        #qrisCropCanvas{display:block;width:100%;height:100%;cursor:crosshair;touch-action:none}
        .qris-crop-tools{display:grid;gap:10px;align-content:center}
        .qris-crop-tools label{margin:0;color:#334155}
        .qris-crop-tools input{padding:0;accent-color:#0052cc}
        .qris-crop-tools p{margin:0;color:#64748b;font-size:12px;line-height:1.5}
        .qris-crop-dimensions{display:grid;grid-template-columns:1fr auto 1fr;gap:7px;align-items:end}
        .qris-crop-dimensions label{min-width:0;margin:0;color:#334155}
        .qris-crop-dimensions input{margin-top:5px;padding:8px;font-size:12px}
        .qris-crop-dimensions>span{padding-bottom:9px;color:#64748b;font-weight:800}
        .qris-crop-preview{display:block;width:min(100%,160px);max-height:160px;aspect-ratio:1.15;border:1px solid #cbd5e1;border-radius:8px;background:#fff;object-fit:contain}
        .qris-crop-footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 18px;border-top:1px solid #e2e8f0}
        .qris-crop-footer button{min-height:40px;padding:9px 14px}
        @media(max-width:620px){.qris-crop-content{grid-template-columns:1fr;padding:12px}.qris-crop-canvas-wrap{width:100%;aspect-ratio:1}.qris-crop-tools{grid-template-columns:1fr auto;align-items:center}.qris-crop-tools p{grid-column:1/-1}.qris-crop-preview{width:80px;height:80px}.qris-crop-footer{flex-direction:column-reverse}.qris-crop-footer button{width:100%}}
        .settings-submit{margin-top:14px}
        .filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;align-items:end}
        label{display:block;font-size:11px;color:#64748b;font-weight:700;margin-bottom:6px}
        input,select{width:100%;min-width:0;border:1px solid #cbd5e1;border-radius:8px;background:#fff;padding:10px;color:#1e293b}
        .primary,.secondary,.danger,.action-link{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:10px 12px;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap}
        .primary{background:#0052cc;color:#fff}.secondary,.action-link{background:#eaf2ff;color:#0052cc}.danger{background:#fff1f2;color:#be123c}
        .clear{display:inline-block;margin-top:12px;font-size:12px;color:#0052cc;text-decoration:none;font-weight:700}
        .status-tabs{display:flex;gap:8px;overflow-x:auto;padding:2px 1px 14px}
        .tab{padding:8px 12px;border-radius:999px;background:#fff;color:#475569;border:1px solid #e2e8f0;text-decoration:none;font-size:12px;font-weight:700;white-space:nowrap}
        .tab.active{background:#0052cc;color:#fff;border-color:#0052cc}
        .table-wrap{overflow-x:auto}.orders{width:100%;border-collapse:collapse;min-width:1040px}
        .cashier-pagination{padding:14px;border-top:1px solid #eef2f7;background:#fff}
        .cashier-pagination nav{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .cashier-pagination nav>div{display:flex;align-items:center;gap:4px}
        .cashier-pagination nav a,.cashier-pagination nav span[aria-current],.cashier-pagination nav span[aria-disabled]{
            display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;
            border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#475569;font-size:12px;text-decoration:none
        }
        .cashier-pagination nav span[aria-current]{background:#0052cc;border-color:#0052cc;color:#fff;font-weight:800}
        .cashier-pagination nav span[aria-disabled]{color:#94a3b8;background:#f8fafc}
        .cashier-pagination nav svg{display:inline-block!important;width:16px!important;height:16px!important;max-width:16px!important;max-height:16px!important}
        .cashier-pagination nav p{margin:0;color:#64748b;font-size:12px}
        .cashier-pagination nav a:hover{border-color:#93c5fd;background:#eff6ff;color:#0052cc}
        .cashier-pagination nav a:focus-visible{outline:3px solid #bfdbfe;outline-offset:2px}
        th,td{padding:14px 12px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:top}
        th{font-size:10px;letter-spacing:.06em;text-transform:uppercase;color:#64748b;background:#f8fafc}
        td{font-size:12px;color:#334155}.order-id{font-weight:800;color:#0f172a}.muted{color:#64748b;font-size:11px;margin-top:4px}.buyer{font-weight:700;color:#1e293b}
        .channel,.status{display:inline-flex;border-radius:999px;padding:5px 8px;font-weight:800;font-size:10px;white-space:nowrap}
        .channel.offline{background:#eff6ff;color:#1d4ed8}.channel.b2b{background:#ecfeff;color:#0e7490}.channel.b2c{background:#f0fdf4;color:#15803d}
        .status.pending{background:#fff7ed;color:#c2410c}.status.verified{background:#eff6ff;color:#1d4ed8}.status.printed{background:#dcfce7;color:#15803d}.status.cancelled{background:#f1f5f9;color:#64748b}
        .items{margin:7px 0 0;padding-left:16px;color:#64748b;line-height:1.6}.amount{font-weight:800;white-space:nowrap;color:#0f172a}
        .payment-proof{margin-top:8px}
        .payment-proof-button{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff;color:#0052cc;font-size:11px;font-weight:800;cursor:pointer}
        .payment-proof-button:hover{background:#dbeafe}
        .payment-proof-dialog{width:min(900px,calc(100% - 24px));height:min(88vh,760px);max-width:none;max-height:none;padding:0;border:0;border-radius:14px;overflow:hidden;background:#fff;color:#0f172a;box-shadow:0 24px 70px #0f172a55}
        .payment-proof-dialog::backdrop{background:#0f172a99}
        .payment-proof-dialog-layout{display:grid;grid-template-rows:auto minmax(0,1fr);height:100%}
        .payment-proof-dialog-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-bottom:1px solid #e2e8f0}
        .payment-proof-dialog-header strong{font-size:14px}
        .payment-proof-dialog-close{border:0;border-radius:8px;background:#f1f5f9;color:#334155;padding:8px 11px;font-weight:800;cursor:pointer}
        .payment-proof-dialog-content{display:grid;place-items:center;min-height:0;padding:12px;background:#f1f5f9}
        .payment-proof-dialog-image{display:block;max-width:100%;max-height:100%;object-fit:contain}
        .payment-proof-dialog-pdf{display:block;width:100%;height:100%;border:0;background:#fff}
        .payment-proof-dialog-image[hidden],.payment-proof-dialog-pdf[hidden]{display:none}
        @media(max-width:620px){.payment-proof-dialog{width:100%;height:100vh;height:100dvh;border-radius:0}.payment-proof-dialog-header{padding:10px 12px}}
        .actions{display:flex;gap:6px;flex-wrap:wrap}.actions form{margin:0}.empty{text-align:center;padding:54px 20px;color:#64748b}
        .hint{font-size:11px;color:#64748b;margin:13px 0 0}
        @media(max-width:1000px){.metrics{grid-template-columns:repeat(3,minmax(0,1fr))}.filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:620px){main{padding:17px 13px}.topbar{padding:13px}.user{display:none}.welcome{align-items:start;flex-direction:column}.welcome h2{font-size:22px}.metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.metric{padding:13px}.metric-value{font-size:22px}.filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid .wide{grid-column:span 2}.settings-grid{grid-template-columns:1fr}.settings-grid .wide{grid-column:auto}.primary{width:100%}}
    </style>
</head>
<body>
<header>
    <div class="topbar">
        <div class="brand"><img class="logo" src="{{ asset('LOGO APOTEK MEDIX.PNG') }}" alt="Logo Apotek Medix"><div><h1>Apotek Medix</h1><p class="subtitle">Dashboard Admin Kasir</p></div></div>
        <div class="realtime-clock-badge" id="cashierRealtimeClockBadge" aria-live="polite" title="Waktu Realtime WIB">
            <span class="realtime-clock-dot" aria-hidden="true"></span>
            <span class="realtime-clock-date" id="cashierRealtimeDate">{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d M Y') }}</span>
            <span class="realtime-clock-sep" aria-hidden="true">•</span>
            <span class="realtime-clock-time" id="cashierRealtimeTime">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
        </div>
        <div class="top-actions">
            <span class="user">{{ auth()->user()->name }} · {{ auth()->user()->isAdmin() ? 'Admin' : 'Kasir' }}</span>
            <a class="cashier-order-notification" id="cashier-order-notification" href="{{ route('cashier.dashboard', ['status' => 'pending']) }}" aria-label="{{ $metrics['pending'] }} pesanan baru menunggu verifikasi" @if($metrics['pending'] === 0) hidden @endif>
                <i class="fa-solid fa-bell" aria-hidden="true"></i>
                <span id="cashier-order-notification-label">Pesanan</span>
                <span class="cashier-order-notification-count" id="cashier-order-notification-count">{{ $metrics['pending'] > 99 ? '99+' : $metrics['pending'] }}</span>
            </a>
            @if(auth()->user()->isAdmin())
                @php
                    $cashierTempoCount = \Illuminate\Support\Facades\Schema::hasTable('purchase_histories')
                        ? \App\Models\PurchaseHistory::whereRaw('LOWER(TRIM(COALESCE(payment_method, ""))) = ?', ['tempo'])
                            ->where(fn ($q) => $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid'))
                            ->where(fn ($q) => $q->whereNull('receipt_status')->orWhere('receipt_status', '!=', 'cancelled'))
                            ->where(fn ($q) => $q->whereNull('approval_status')->orWhere('approval_status', '!=', 'rejected'))
                            ->count()
                        : 0;
                @endphp
                <a class="admin-dashboard-link" href="{{ route('admin.notifications.index') }}" style="background:#fff7ed;border-color:#fed7aa;color:#c2410c;">🔔 Notifikasi Tempo @if($cashierTempoCount > 0)<span style="background:#dc2626;color:#fff;padding:2px 7px;border-radius:999px;font-size:11px;">{{ $cashierTempoCount }}</span>@endif</a>
                <a class="admin-dashboard-link" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard Admin</a>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="logout" type="submit">Keluar</button></form>
            @else
                <form action="{{ route('cashier.logout') }}" method="POST">@csrf<button class="logout" type="submit">Keluar</button></form>
            @endif
        </div>
    </div>
</header>
<main>
    <div class="welcome">
        <div><h2>Antrean transaksi</h2><p>Kelola transaksi offline, B2B, dan B2C dalam satu layar.</p></div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <a class="pending-queue-link" id="pending-queue-link" href="{{ route('cashier.dashboard', ['status' => 'pending']) }}" @if($metrics['pending'] === 0) hidden @endif>
                <span id="pending-queue-label">{{ number_format($metrics['pending']) }} pesanan menunggu verifikasi</span>
                <span class="pending-queue-count" id="pending-queue-count">{{ $metrics['pending'] > 99 ? '99+' : $metrics['pending'] }}</span>
            </a>
            <a class="refresh" href="{{ request()->fullUrl() }}">↻ Perbarui data</a>
        </div>
    </div>
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif
    <div class="notice update queue-alert" id="queue-update" role="status" aria-live="polite" hidden>
        <span id="queue-update-message">Ada perubahan pada antrean transaksi.</span>
        <a id="queue-update-link" href="{{ request()->fullUrl() }}">Muat ulang antrean</a>
    </div>

    <details class="panel payment-settings" id="purchase-payment-settings" @if($errors->any()) open @endif>
        <summary>Pengaturan QRIS &amp; rekening pembayaran</summary>
        <div class="payment-settings-body">
            <p class="subtitle">Pengaturan ini berlaku untuk checkout pembelian B2B dan B2C. Detail pembayaran pada pesanan yang sudah dibuat tetap tersimpan.</p>
            @if($errors->any())
                <div class="notice error" role="alert">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('cashier.payment-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="settings-grid">
                    <div>
                        <label for="qris_image">Gambar QRIS (JPG/PNG/WEBP, maks. 3 MB; dapat dipotong sebelum disimpan)</label>
                        <input id="qris_image" type="file" name="qris_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="qris-image-actions">
                            <button class="qris-crop-button" type="button" id="chooseAndCropQris"><i class="fa-solid fa-crop-simple" aria-hidden="true"></i> Pilih &amp; crop QRIS</button>
                        </div>
                        @if($paymentSettings->qris_image_url)
                            <img class="qris-image-preview" id="qrisImagePreview" src="{{ $paymentSettings->qris_image_url }}" alt="QRIS pembayaran saat ini">
                            <div class="qris-image-actions">
                                <button class="qris-crop-button" type="button" id="cropCurrentQris"><i class="fa-solid fa-crop-simple" aria-hidden="true"></i> Crop ulang gambar ini</button>
                            </div>
                            <label class="settings-help"><input type="checkbox" name="remove_qris" value="1"> Hapus QRIS</label>
                        @else
                            <img class="qris-image-preview" id="qrisImagePreview" alt="Pratinjau QRIS hasil crop" hidden>
                        @endif
                        <p class="settings-help">Pilih gambar untuk mengatur crop. Geser gambar dan atur zoom, lalu konfirmasi sebelum menyimpan.</p>
                        <p class="qris-crop-error" id="qrisCropError" role="alert" hidden></p>
                    </div>
                    <div>
                        <label for="payment_instructions">Instruksi pembayaran</label>
                        <textarea id="payment_instructions" name="payment_instructions" maxlength="1500" placeholder="Contoh: Cantumkan nomor pesanan pada berita transfer.">{{ old('payment_instructions', $paymentSettings->payment_instructions) }}</textarea>
                    </div>
                    <div>
                        <label for="bank_name">Nama bank</label>
                        <input id="bank_name" name="bank_name" maxlength="100" value="{{ old('bank_name', $paymentSettings->bank_name) }}" placeholder="Contoh: BCA">
                    </div>
                    <div>
                        <label for="account_number">Nomor rekening</label>
                        <input id="account_number" name="account_number" maxlength="50" value="{{ old('account_number', $paymentSettings->account_number) }}" placeholder="Masukkan nomor rekening">
                    </div>
                    <div class="wide">
                        <label for="account_holder">Nama pemilik rekening</label>
                        <input id="account_holder" name="account_holder" maxlength="150" value="{{ old('account_holder', $paymentSettings->account_holder) }}" placeholder="Nama sesuai rekening">
                    </div>
                </div>
                <button class="primary settings-submit" type="submit">Simpan pengaturan pembayaran</button>
            </form>
        </div>
    </details>
    <dialog class="qris-crop-dialog" id="qrisCropDialog" aria-labelledby="qrisCropTitle">
        <div class="qris-crop-header">
            <div>
                <h2 id="qrisCropTitle">Atur potongan gambar QRIS</h2>
                <p id="qrisCropFilename">Geser gambar untuk memilih area yang akan disimpan.</p>
            </div>
            <button type="button" class="qris-crop-close" id="qrisCropClose" aria-label="Tutup crop">&times;</button>
        </div>
        <div class="qris-crop-content">
            <div class="qris-crop-canvas-wrap">
                <canvas id="qrisCropCanvas" width="600" height="520" aria-label="Gambar dengan bingkai crop yang dapat digeser dan diubah ukurannya"></canvas>
            </div>
            <div class="qris-crop-tools">
                <label for="qrisCropZoom">Zoom <output id="qrisCropZoomValue">1.0×</output></label>
                <input id="qrisCropZoom" type="range" min="1" max="3" value="1" step="0.01">
                <p>Geser bingkai untuk memindahkan area crop. Tarik sudut atau sisi bingkai untuk mengubah ukuran; pastikan seluruh kode QR tetap terlihat.</p>
                <div class="qris-crop-dimensions" aria-label="Ukuran area crop dalam piksel">
                    <label for="qrisCropWidth">Lebar
                        <input id="qrisCropWidth" type="number" min="1" step="1" inputmode="numeric">
                    </label>
                    <span aria-hidden="true">×</span>
                    <label for="qrisCropHeight">Tinggi
                        <input id="qrisCropHeight" type="number" min="1" step="1" inputmode="numeric">
                    </label>
                </div>
                <img class="qris-crop-preview" id="qrisCropPreview" alt="Pratinjau hasil crop">
            </div>
        </div>
        <div class="qris-crop-footer">
            <button class="secondary" type="button" id="qrisCropCancel">Batal</button>
            <button class="primary" type="button" id="qrisCropConfirm"><i class="fa-solid fa-check" aria-hidden="true"></i> Gunakan hasil crop</button>
        </div>
    </dialog>

    <section class="metrics" aria-label="Ringkasan transaksi">
        <div class="metric"><div class="metric-label">TOTAL TRANSAKSI</div><div class="metric-value">{{ number_format($metrics['total']) }}</div></div>
        <div class="metric pending"><div class="metric-label">MENUNGGU VERIFIKASI</div><div class="metric-value">{{ number_format($metrics['pending']) }}</div></div>
        <div class="metric verified"><div class="metric-label">TERVERIFIKASI</div><div class="metric-value">{{ number_format($metrics['verified']) }}</div></div>
        <div class="metric printed"><div class="metric-label">SUDAH DICETAK</div><div class="metric-value">{{ number_format($metrics['printed']) }}</div></div>
        <div class="metric cancelled"><div class="metric-label">DIBATALKAN</div><div class="metric-value">{{ number_format($metrics['cancelled']) }}</div></div>
    </section>

    <section class="panel filters">
        <p class="filter-title">Filter transaksi</p>
        <form method="GET" action="{{ route('cashier.dashboard') }}">
            <div class="filter-grid">
                <div><label for="order_id">ID PESANAN</label><input id="order_id" name="order_id" type="number" min="1" value="{{ $filters['order_id'] ?? '' }}" placeholder="Contoh: 1024"></div>
                <div><label for="channel">KANAL</label><select id="channel" name="channel"><option value="all">Semua kanal</option><option value="offline" @selected(($filters['channel'] ?? '') === 'offline')>Offline / Kios</option><option value="b2b" @selected(($filters['channel'] ?? '') === 'b2b')>B2B</option><option value="b2c" @selected(($filters['channel'] ?? '') === 'b2c')>B2C</option></select></div>
                <div><label for="payment_method">METODE BAYAR</label><select id="payment_method" name="payment_method"><option value="">Semua metode</option>@foreach($paymentMethods as $method)<option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ $method }}</option>@endforeach</select></div>
                <div><label for="payment_status">STATUS PEMBAYARAN</label><select id="payment_status" name="payment_status"><option value="all">Semua pembayaran</option><option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Menunggu pembayaran</option><option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Lunas</option><option value="cancelled" @selected(($filters['payment_status'] ?? '') === 'cancelled')>Dibatalkan</option></select></div>
                <div><label for="from">DARI TANGGAL</label><input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}"></div>
                <div><label for="to">SAMPAI TANGGAL</label><input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}"></div>
                <div><label for="status">STATUS</label><select id="status" name="status"><option value="all">Semua status</option><option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option><option value="verified" @selected(($filters['status'] ?? '') === 'verified')>Terverifikasi</option><option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Dibatalkan</option></select></div>
                <div><button class="primary" type="submit">Terapkan filter</button></div>
            </div>
            <a class="clear" href="{{ route('cashier.dashboard') }}">Hapus semua filter</a>
        </form>
    </section>

    <nav class="status-tabs" aria-label="Filter status cepat">
        @foreach(['all' => 'Semua', 'pending' => 'Pending', 'verified' => 'Terverifikasi', 'cancelled' => 'Dibatalkan'] as $value => $label)
            <a class="tab {{ ($filters['status'] ?? 'all') === $value ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => $value]) }}">{{ $label }}@if($value === 'pending') <span id="pending-tab-count">· {{ $metrics['pending'] }}</span>@endif</a>
        @endforeach
    </nav>

    <section class="panel table-wrap">
        @if($orders->isEmpty())
            <div class="empty">Tidak ada transaksi yang cocok dengan filter ini.</div>
        @else
            <table class="orders">
                <thead><tr><th>Pesanan</th><th>Kanal</th><th>Pelanggan</th><th>Produk</th><th>Pembayaran</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach($orders as $order)
                    @php
                        $items = is_array($order->items) ? $order->items : [];
                        $channel = $order->transaction_channel === 'offline'
                            ? ['offline', 'Offline']
                            : (in_array($order->buyer_type, ['apotik', 'dokter', 'pbf']) || filled($order->pbf_name)
                                ? ['b2b', 'B2B']
                                : ['b2c', 'B2C']);
                        $receiptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.invoice', now()->addMinutes(30), ['order' => $order->id]);
                    @endphp
                    <tr>
                        <td><div class="order-id">#{{ $order->id }}</div><div class="muted">{{ $order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</div></td>
                        <td><span class="channel {{ $channel[0] }}">{{ $channel[1] }}</span></td>
                        <td><div class="buyer">{{ $order->buyer_name ?: '-' }}</div><div class="muted">{{ $order->phone ?: '-' }}</div></td>
                        <td>
                            <strong>{{ count($items) }} item</strong>
                            <ul class="items">@foreach(array_slice($items, 0, 3) as $item)<li>{{ $item['nama_obat'] ?? $item['name'] ?? 'Produk' }} × {{ $item['quantity'] ?? $item['qty'] ?? 0 }}</li>@endforeach</ul>
                            @if(count($items) > 3)<div class="muted">+{{ count($items) - 3 }} produk lainnya</div>@endif
                        </td>
                        <td>
                            {{ $order->payment_method ?: '-' }}
                            <div class="muted">{{ $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status ?? 'pending') }}@if($order->paid_at) · {{ $order->paid_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB @endif</div>
                            @if($order->isTempoPayment())<div class="muted" style="color:{{ in_array($order->tempo_due_status, ['overdue', 'due_today']) ? '#dc2626' : ($order->tempo_due_status === 'due_soon' ? '#d97706' : '#0369a1') }};font-weight:700;">Jatuh tempo: {{ $order->due_date_formatted }} ({{ $order->tempo_due_label }})</div>@endif
                            @if($order->payment_proof_path)
                                @php
                                    $paymentProofUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.payment-proof', now()->addMinutes(30), ['order' => $order->id]);
                                    $paymentProofExtension = strtolower(pathinfo($order->payment_proof_path, PATHINFO_EXTENSION));
                                @endphp
                                <div class="payment-proof">
                                    <button
                                        type="button"
                                        class="payment-proof-button"
                                        data-proof-url="{{ $paymentProofUrl }}"
                                        data-proof-type="{{ $paymentProofExtension === 'pdf' ? 'pdf' : 'image' }}"
                                        data-order-id="{{ $order->id }}"
                                        aria-label="Lihat bukti pembayaran pesanan #{{ $order->id }}"
                                    ><i class="fa-solid fa-eye" aria-hidden="true"></i> Lihat bukti pembayaran</button>
                                </div>
                            @elseif(in_array(strtolower((string) $order->payment_method), ['qris', 'transfer'], true))
                                <div class="muted" style="margin-top:8px;color:#b91c1c;font-weight:700;">Bukti pembayaran belum tersedia</div>
                            @endif
                        </td>
                        <td class="amount">Rp {{ number_format($order->effective_total, 0, ',', '.') }}</td>
                        <td><span class="status {{ $order->receipt_status ?? 'pending' }}">{{ match($order->receipt_status ?? 'pending') {'verified' => 'Terverifikasi', 'printed' => 'Dicetak', 'cancelled' => 'Dibatalkan', default => 'Pending'} }}</span>
                            @if($order->receipt_status === 'printed')<div class="muted">{{ $order->printed_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</div>@elseif($order->receipt_status === 'verified' && $order->verified_at)<div class="muted">{{ $order->verified_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</div>@endif
                        </td>
                        <td>
                            <div class="actions">
                                @if(($order->receipt_status ?? 'pending') === 'pending')
                                    <a class="action-link" href="{{ $receiptUrl }}" target="_blank" rel="noopener">Review detail</a>
                                    <form action="{{ route('cashier.orders.verify', $order) }}" method="POST" onsubmit="return confirm('Verifikasi transaksi #{{ $order->id }} dan buka struk untuk dicetak?')">@csrf<button class="primary" type="submit">Verifikasi &amp; Cetak</button></form>
                                    <form action="{{ route('cashier.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan pesanan #{{ $order->id }}? Stok akan dikembalikan.')">@csrf<button class="danger" type="submit">Batalkan</button></form>
                                @elseif($order->receipt_status === 'verified')
                                    <a class="action-link" href="{{ route('cashier.orders.receipt', $order) }}" target="_blank" rel="noopener">Buka struk</a>
                                    <form action="{{ route('cashier.orders.printed', $order) }}" method="POST" onsubmit="return confirm('Pastikan invoice sudah berhasil dicetak.')">@csrf<button class="primary" type="submit">Tandai dicetak</button></form>
                                    <form action="{{ route('cashier.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan pesanan #{{ $order->id }}? Stok akan dikembalikan jika sebelumnya sudah dikurangi.')">@csrf<button class="danger" type="submit">Batalkan</button></form>
                                @elseif($order->receipt_status === 'printed')
                                    <a class="action-link" href="{{ $receiptUrl }}" target="_blank" rel="noopener">Cetak ulang</a>
                                    <form action="{{ route('cashier.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan struk transaksi #{{ $order->id }}? Stok akan dikembalikan jika sebelumnya sudah dikurangi. Pengembalian dana tidak dilakukan otomatis.')">@csrf<button class="danger" type="submit">Batalkan struk</button></form>
                                @else
                                    <span class="muted">Tidak ada aksi</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="cashier-pagination">{{ $orders->onEachSide(1)->links() }}</div>
        @endif
        <p class="hint" style="padding:0 15px 12px">Klik “Lihat bukti pembayaran” pada kolom pembayaran untuk membuka foto atau PDF bukti transaksi. Perubahan antrean diperiksa setiap 10 detik. Struk dicetak melalui dialog browser. Tandai “Dicetak” hanya setelah printer menyelesaikan cetak; cetak thermal tanpa dialog membutuhkan konektor printer lokal.</p>
    </section>
</main>
<dialog class="payment-proof-dialog" id="payment-proof-dialog" aria-labelledby="payment-proof-dialog-title">
    <div class="payment-proof-dialog-layout">
        <div class="payment-proof-dialog-header">
            <strong id="payment-proof-dialog-title">Bukti pembayaran</strong>
            <button type="button" class="payment-proof-dialog-close" id="payment-proof-dialog-close">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i> Tutup
            </button>
        </div>
        <div class="payment-proof-dialog-content">
            <img class="payment-proof-dialog-image" id="payment-proof-dialog-image" alt="" hidden>
            <iframe class="payment-proof-dialog-pdf" id="payment-proof-dialog-pdf" title="Bukti pembayaran PDF" hidden></iframe>
        </div>
    </div>
</dialog>
<script>
    (() => {
        const qrisInput = document.getElementById('qris_image');
        const qrisPreview = document.getElementById('qrisImagePreview');
        const chooseAndCropQris = document.getElementById('chooseAndCropQris');
        const cropCurrentQris = document.getElementById('cropCurrentQris');
        const cropDialog = document.getElementById('qrisCropDialog');
        const cropCanvas = document.getElementById('qrisCropCanvas');
        const cropContext = cropCanvas.getContext('2d');
        const cropPreview = document.getElementById('qrisCropPreview');
        const cropZoom = document.getElementById('qrisCropZoom');
        const cropZoomValue = document.getElementById('qrisCropZoomValue');
        const cropWidth = document.getElementById('qrisCropWidth');
        const cropHeight = document.getElementById('qrisCropHeight');
        const cropError = document.getElementById('qrisCropError');
        const cropFilename = document.getElementById('qrisCropFilename');
        let cropSource = null;
        let cropObjectUrl = null;
        let previewObjectUrl = null;
        let cropScale = 1;
        let imageBounds = null;
        let cropRect = null;
        let imageDisplayScale = 1;
        let cropInteraction = null;
        let cropGeneration = 0;

        const showCropError = (message) => {
            cropError.textContent = message;
            cropError.hidden = !message;
        };
        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
        const getSourceCrop = () => {
            if (!cropSource || !imageBounds || !cropRect) return null;
            return {
                x: (cropRect.x - imageBounds.x) / imageDisplayScale,
                y: (cropRect.y - imageBounds.y) / imageDisplayScale,
                width: cropRect.width / imageDisplayScale,
                height: cropRect.height / imageDisplayScale
            };
        };
        const drawQrisCrop = () => {
            if (!cropSource || !cropContext || !imageBounds || !cropRect) return;
            cropContext.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
            cropContext.fillStyle = '#111827';
            cropContext.fillRect(0, 0, cropCanvas.width, cropCanvas.height);
            cropContext.drawImage(cropSource, imageBounds.x, imageBounds.y, imageBounds.width, imageBounds.height);

            const x = cropRect.x;
            const y = cropRect.y;
            const right = x + cropRect.width;
            const bottom = y + cropRect.height;
            cropContext.fillStyle = 'rgba(2, 6, 23, .62)';
            cropContext.fillRect(imageBounds.x, imageBounds.y, imageBounds.width, y - imageBounds.y);
            cropContext.fillRect(imageBounds.x, bottom, imageBounds.width, imageBounds.y + imageBounds.height - bottom);
            cropContext.fillRect(imageBounds.x, y, x - imageBounds.x, cropRect.height);
            cropContext.fillRect(right, y, imageBounds.x + imageBounds.width - right, cropRect.height);

            cropContext.save();
            cropContext.beginPath();
            cropContext.rect(x, y, cropRect.width, cropRect.height);
            cropContext.clip();
            cropContext.strokeStyle = 'rgba(255,255,255,.82)';
            cropContext.lineWidth = 1;
            cropContext.beginPath();
            cropContext.moveTo(x + cropRect.width / 3, y);
            cropContext.lineTo(x + cropRect.width / 3, bottom);
            cropContext.moveTo(x + cropRect.width * 2 / 3, y);
            cropContext.lineTo(x + cropRect.width * 2 / 3, bottom);
            cropContext.moveTo(x, y + cropRect.height / 3);
            cropContext.lineTo(right, y + cropRect.height / 3);
            cropContext.moveTo(x, y + cropRect.height * 2 / 3);
            cropContext.lineTo(right, y + cropRect.height * 2 / 3);
            cropContext.stroke();
            cropContext.restore();

            cropContext.strokeStyle = '#fff';
            cropContext.lineWidth = 2;
            cropContext.strokeRect(x, y, cropRect.width, cropRect.height);
            const handles = [
                [x, y], [x + cropRect.width / 2, y], [right, y],
                [right, y + cropRect.height / 2], [right, bottom],
                [x + cropRect.width / 2, bottom], [x, bottom],
                [x, y + cropRect.height / 2]
            ];
            handles.forEach(([handleX, handleY]) => {
                cropContext.fillStyle = '#fff';
                cropContext.strokeStyle = '#0755c8';
                cropContext.lineWidth = 2;
                cropContext.beginPath();
                cropContext.rect(handleX - 5, handleY - 5, 10, 10);
                cropContext.fill();
                cropContext.stroke();
            });

            const sourceCrop = getSourceCrop();
            if (sourceCrop) {
                const previewCanvas = document.createElement('canvas');
                const previewScale = Math.min(1, 320 / sourceCrop.width, 240 / sourceCrop.height);
                previewCanvas.width = Math.max(1, Math.round(sourceCrop.width * previewScale));
                previewCanvas.height = Math.max(1, Math.round(sourceCrop.height * previewScale));
                previewCanvas.getContext('2d').drawImage(
                    cropSource,
                    sourceCrop.x,
                    sourceCrop.y,
                    sourceCrop.width,
                    sourceCrop.height,
                    0,
                    0,
                    previewCanvas.width,
                    previewCanvas.height
                );
                cropPreview.src = previewCanvas.toDataURL('image/png');
                cropWidth.value = String(Math.max(1, Math.round(sourceCrop.width)));
                cropHeight.value = String(Math.max(1, Math.round(sourceCrop.height)));
            }
            cropZoomValue.value = `${cropScale.toFixed(1)}×`;
        };
        const closeQrisCrop = (clearSelectedFile = false) => {
            cropGeneration++;
            if (cropDialog.open) cropDialog.close();
            cropSource = null;
            imageBounds = null;
            cropRect = null;
            cropInteraction = null;
            if (cropObjectUrl) {
                URL.revokeObjectURL(cropObjectUrl);
                cropObjectUrl = null;
            }
            if (clearSelectedFile) qrisInput.value = '';
        };
        const openQrisCrop = (source, fileName) => {
            showCropError('');
            const image = new Image();
            image.onload = () => {
                cropSource = image;
                imageDisplayScale = Math.min(
                    (cropCanvas.width - 40) / image.width,
                    (cropCanvas.height - 40) / image.height
                );
                imageBounds = {
                    x: (cropCanvas.width - image.width * imageDisplayScale) / 2,
                    y: (cropCanvas.height - image.height * imageDisplayScale) / 2,
                    width: image.width * imageDisplayScale,
                    height: image.height * imageDisplayScale
                };
                const insetX = imageBounds.width * 0.1;
                const insetY = imageBounds.height * 0.1;
                cropRect = {
                    x: imageBounds.x + insetX,
                    y: imageBounds.y + insetY,
                    width: imageBounds.width - insetX * 2,
                    height: imageBounds.height - insetY * 2
                };
                cropScale = 1;
                cropZoom.value = '1';
                cropFilename.textContent = fileName || 'Geser gambar untuk memilih area yang akan disimpan.';
                cropDialog.showModal();
                drawQrisCrop();
            };
            image.onerror = () => {
                showCropError('Gambar QRIS tidak dapat dibuka. Pilih file gambar lain.');
                closeQrisCrop(true);
            };
            image.src = source;
        };

        chooseAndCropQris.addEventListener('click', () => qrisInput.click());
        qrisInput.addEventListener('change', () => {
            const file = qrisInput.files?.[0];
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                showCropError('Gunakan gambar JPG, PNG, atau WEBP.');
                qrisInput.value = '';
                return;
            }
            if (file.size > 20 * 1024 * 1024) {
                showCropError('Ukuran gambar sumber terlalu besar. Pilih gambar maksimal 20 MB.');
                qrisInput.value = '';
                return;
            }
            if (cropObjectUrl) URL.revokeObjectURL(cropObjectUrl);
            cropObjectUrl = URL.createObjectURL(file);
            openQrisCrop(cropObjectUrl, file.name);
        });

        cropCurrentQris?.addEventListener('click', () => {
            const imageUrl = qrisPreview?.getAttribute('src');
            if (!imageUrl || qrisPreview.hidden) {
                showCropError('Gambar QRIS saat ini tidak tersedia.');
                return;
            }
            openQrisCrop(imageUrl, imageUrl.split('/').pop()?.split('?')[0] || 'qris.png');
        });

        const resizeCropAroundCenter = (width, height) => {
            if (!imageBounds || !cropRect) return;
            const centerX = cropRect.x + cropRect.width / 2;
            const centerY = cropRect.y + cropRect.height / 2;
            const nextWidth = clamp(width, 30, imageBounds.width);
            const nextHeight = clamp(height, 30, imageBounds.height);
            cropRect.width = nextWidth;
            cropRect.height = nextHeight;
            cropRect.x = clamp(centerX - nextWidth / 2, imageBounds.x, imageBounds.x + imageBounds.width - nextWidth);
            cropRect.y = clamp(centerY - nextHeight / 2, imageBounds.y, imageBounds.y + imageBounds.height - nextHeight);
            drawQrisCrop();
        };
        cropZoom.addEventListener('input', () => {
            if (!cropRect) return;
            const nextScale = Number(cropZoom.value);
            const sizeFactor = cropScale / nextScale;
            cropScale = nextScale;
            resizeCropAroundCenter(cropRect.width * sizeFactor, cropRect.height * sizeFactor);
        });
        cropWidth.addEventListener('change', () => {
            const requestedWidth = Number(cropWidth.value);
            if (!Number.isFinite(requestedWidth) || requestedWidth <= 0) {
                drawQrisCrop();
                return;
            }
            resizeCropAroundCenter(requestedWidth * imageDisplayScale, cropRect.height);
        });
        cropHeight.addEventListener('change', () => {
            const requestedHeight = Number(cropHeight.value);
            if (!Number.isFinite(requestedHeight) || requestedHeight <= 0) {
                drawQrisCrop();
                return;
            }
            resizeCropAroundCenter(cropRect.width, requestedHeight * imageDisplayScale);
        });
        const canvasPoint = (event) => {
            const bounds = cropCanvas.getBoundingClientRect();
            return {
                x: (event.clientX - bounds.left) * cropCanvas.width / bounds.width,
                y: (event.clientY - bounds.top) * cropCanvas.height / bounds.height
            };
        };
        const getCropHit = (point) => {
            const left = cropRect.x;
            const top = cropRect.y;
            const right = left + cropRect.width;
            const bottom = top + cropRect.height;
            const tolerance = 14;
            const nearLeft = Math.abs(point.x - left) <= tolerance;
            const nearRight = Math.abs(point.x - right) <= tolerance;
            const nearTop = Math.abs(point.y - top) <= tolerance;
            const nearBottom = Math.abs(point.y - bottom) <= tolerance;
            const withinVertical = point.y >= top - tolerance && point.y <= bottom + tolerance;
            const withinHorizontal = point.x >= left - tolerance && point.x <= right + tolerance;
            if (nearLeft && nearTop) return 'nw';
            if (nearRight && nearTop) return 'ne';
            if (nearLeft && nearBottom) return 'sw';
            if (nearRight && nearBottom) return 'se';
            if (nearTop && withinHorizontal) return 'n';
            if (nearBottom && withinHorizontal) return 's';
            if (nearLeft && withinVertical) return 'w';
            if (nearRight && withinVertical) return 'e';
            if (point.x >= left && point.x <= right && point.y >= top && point.y <= bottom) return 'move';
            return 'new';
        };
        const updateCropCursor = (event) => {
            if (!cropRect) return;
            const cursors = {
                nw: 'nwse-resize', se: 'nwse-resize', ne: 'nesw-resize', sw: 'nesw-resize',
                n: 'ns-resize', s: 'ns-resize', e: 'ew-resize', w: 'ew-resize',
                move: 'move', new: 'crosshair'
            };
            cropCanvas.style.cursor = cursors[getCropHit(canvasPoint(event))];
        };
        cropCanvas.addEventListener('pointerdown', (event) => {
            if (!cropRect) return;
            const pointerPoint = canvasPoint(event);
            let point = {
                x: clamp(pointerPoint.x, imageBounds.x, imageBounds.x + imageBounds.width),
                y: clamp(pointerPoint.y, imageBounds.y, imageBounds.y + imageBounds.height)
            };
            const mode = getCropHit(point);
            if (mode === 'new') {
                point = {
                    x: clamp(point.x, imageBounds.x, imageBounds.x + imageBounds.width - 30),
                    y: clamp(point.y, imageBounds.y, imageBounds.y + imageBounds.height - 30)
                };
            }
            cropInteraction = {
                id: event.pointerId,
                mode,
                startX: point.x,
                startY: point.y,
                rect: { ...cropRect }
            };
            cropCanvas.setPointerCapture(event.pointerId);
            updateCropCursor(event);
        });
        cropCanvas.addEventListener('pointermove', (event) => {
            if (!cropInteraction || cropInteraction.id !== event.pointerId) {
                updateCropCursor(event);
                return;
            }
            const point = canvasPoint(event);
            const deltaX = point.x - cropInteraction.startX;
            const deltaY = point.y - cropInteraction.startY;
            const start = cropInteraction.rect;
            if (cropInteraction.mode === 'move') {
                cropRect.x = clamp(start.x + deltaX, imageBounds.x, imageBounds.x + imageBounds.width - start.width);
                cropRect.y = clamp(start.y + deltaY, imageBounds.y, imageBounds.y + imageBounds.height - start.height);
            } else {
                let left = start.x;
                let top = start.y;
                let right = start.x + start.width;
                let bottom = start.y + start.height;
                const edges = cropInteraction.mode;
                if (edges === 'new') {
                    left = clamp(Math.min(cropInteraction.startX, point.x), imageBounds.x, imageBounds.x + imageBounds.width);
                    top = clamp(Math.min(cropInteraction.startY, point.y), imageBounds.y, imageBounds.y + imageBounds.height);
                    right = clamp(Math.max(cropInteraction.startX, point.x), imageBounds.x, imageBounds.x + imageBounds.width);
                    bottom = clamp(Math.max(cropInteraction.startY, point.y), imageBounds.y, imageBounds.y + imageBounds.height);
                } else {
                    if (edges.includes('w')) left = clamp(start.x + deltaX, imageBounds.x, right - 30);
                    if (edges.includes('e')) right = clamp(start.x + start.width + deltaX, left + 30, imageBounds.x + imageBounds.width);
                    if (edges.includes('n')) top = clamp(start.y + deltaY, imageBounds.y, bottom - 30);
                    if (edges.includes('s')) bottom = clamp(start.y + start.height + deltaY, top + 30, imageBounds.y + imageBounds.height);
                }
                if (right - left < 30 || bottom - top < 30) return;
                cropRect = {
                    x: left,
                    y: top,
                    width: right - left,
                    height: bottom - top
                };
            }
            drawQrisCrop();
        });
        cropCanvas.addEventListener('pointerup', () => { cropInteraction = null; });
        cropCanvas.addEventListener('pointercancel', () => { cropInteraction = null; });

        const cancelCrop = () => closeQrisCrop(true);
        document.getElementById('qrisCropClose').addEventListener('click', cancelCrop);
        document.getElementById('qrisCropCancel').addEventListener('click', cancelCrop);
        cropDialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            cancelCrop();
        });
        document.getElementById('qrisCropConfirm').addEventListener('click', () => {
            if (!cropSource || !cropContext) {
                showCropError('Gambar belum siap untuk dipotong.');
                return;
            }
            const sourceCrop = getSourceCrop();
            if (!sourceCrop || sourceCrop.width < 1 || sourceCrop.height < 1) {
                showCropError('Pilih area crop yang lebih besar.');
                return;
            }
            const output = document.createElement('canvas');
            const outputScale = Math.min(1, 4000 / sourceCrop.width, 4000 / sourceCrop.height);
            output.width = Math.max(1, Math.round(sourceCrop.width * outputScale));
            output.height = Math.max(1, Math.round(sourceCrop.height * outputScale));
            const outputContext = output.getContext('2d');
            if (!outputContext) {
                showCropError('Browser tidak mendukung proses crop gambar.');
                return;
            }
            const generation = ++cropGeneration;
            outputContext.imageSmoothingQuality = 'high';
            outputContext.drawImage(
                cropSource,
                sourceCrop.x,
                sourceCrop.y,
                sourceCrop.width,
                sourceCrop.height,
                0,
                0,
                output.width,
                output.height
            );
            output.toBlob((blob) => {
                if (generation !== cropGeneration) return;
                if (!blob) {
                    showCropError('Hasil crop gagal dibuat. Coba pilih gambar lain.');
                    return;
                }
                if (blob.size > 3 * 1024 * 1024) {
                    showCropError('Hasil crop melebihi batas 3 MB. Kurangi zoom atau gunakan gambar sumber lain.');
                    return;
                }
                const croppedFile = new File([blob], 'qris-cropped.png', { type: 'image/png' });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                qrisInput.files = transfer.files;
                const removeQris = document.querySelector('input[name="remove_qris"]');
                if (removeQris) removeQris.checked = false;
                if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = URL.createObjectURL(blob);
                qrisPreview.src = previewObjectUrl;
                qrisPreview.hidden = false;
                if (cropCurrentQris) cropCurrentQris.hidden = false;
                showCropError('');
                closeQrisCrop();
            }, 'image/png');
        });

        const proofDialog = document.getElementById('payment-proof-dialog');
        const proofImage = document.getElementById('payment-proof-dialog-image');
        const proofPdf = document.getElementById('payment-proof-dialog-pdf');
        const closeProofDialog = () => {
            proofDialog.close();
        };
        proofDialog.addEventListener('close', () => {
            proofImage.removeAttribute('src');
            proofPdf.removeAttribute('src');
        });
        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('.payment-proof-button') : null;
            if (button) {
                document.getElementById('payment-proof-dialog-title').textContent = `Bukti pembayaran pesanan #${button.dataset.orderId}`;
                if (button.dataset.proofType === 'pdf') {
                    proofImage.hidden = true;
                    proofPdf.hidden = false;
                    proofPdf.src = button.dataset.proofUrl;
                } else {
                    proofPdf.hidden = true;
                    proofImage.hidden = false;
                    proofImage.src = button.dataset.proofUrl;
                    proofImage.alt = `Bukti pembayaran pesanan #${button.dataset.orderId}`;
                }
                proofDialog.showModal();
                return;
            }
            if (event.target === proofDialog) closeProofDialog();
        });
        document.getElementById('payment-proof-dialog-close').addEventListener('click', closeProofDialog);

        const dateEl = document.getElementById('cashierRealtimeDate');
        const timeEl = document.getElementById('cashierRealtimeTime');
        const updateClock = () => {
            const now = new Date();
            if (dateEl) {
                dateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    weekday: 'long',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }).format(now);
            }
            if (timeEl) {
                timeEl.textContent = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                }).format(now).replace(/\./g, ':') + ' WIB';
            }
        };
        updateClock();
        window.setInterval(updateClock, 1000);

        let previous = {
            pending: @json($metrics['pending']),
            latest: @json($metrics['latest_pending_id']),
        };
        const notice = document.getElementById('queue-update');
        const noticeMessage = document.getElementById('queue-update-message');
        const pendingLink = document.getElementById('pending-queue-link');
        const pendingLabel = document.getElementById('pending-queue-label');
        const pendingCount = document.getElementById('pending-queue-count');
        const pendingTabCount = document.getElementById('pending-tab-count');
        const navNotification = document.getElementById('cashier-order-notification');
        const navNotificationLabel = document.getElementById('cashier-order-notification-label');
        const navNotificationCount = document.getElementById('cashier-order-notification-count');

        const checkQueueUpdates = async () => {
            try {
                const response = await fetch(@json(route('cashier.updates')), {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const current = await response.json();
                if (current.pending !== previous.pending || current.latest_pending_id !== previous.latest) {
                    if (current.latest_pending_id && current.latest_pending_id !== previous.latest && current.pending > previous.pending) {
                        noticeMessage.textContent = `Pesanan baru #${current.latest_pending_id} masuk dan menunggu verifikasi kasir.`;
                    } else {
                        noticeMessage.textContent = 'Antrean transaksi berubah. Muat ulang untuk melihat status terbaru.';
                    }
                    notice.classList.remove('error');
                    notice.classList.add('update');
                    document.getElementById('queue-update-link').href = window.location.href;
                    notice.hidden = false;
                    if (current.latest_pending_id && current.latest_pending_id !== previous.latest && current.pending > 0) {
                        navNotificationLabel.textContent = `Pesanan baru #${current.latest_pending_id}`;
                        navNotification.setAttribute('aria-label', `Pesanan baru nomor ${current.latest_pending_id}; ${current.pending} pesanan menunggu verifikasi`);
                        navNotification.title = `Pesanan baru #${current.latest_pending_id} menunggu verifikasi`;
                    }
                    previous = { pending: current.pending, latest: current.latest_pending_id };
                }
                pendingLink.hidden = current.pending === 0;
                pendingLabel.textContent = `${current.pending.toLocaleString('id-ID')} pesanan menunggu verifikasi`;
                pendingCount.textContent = current.pending > 99 ? '99+' : current.pending;
                pendingTabCount.textContent = `· ${current.pending.toLocaleString('id-ID')}`;
                navNotification.hidden = current.pending === 0;
                navNotificationCount.textContent = current.pending > 99 ? '99+' : current.pending;
                if (current.pending === 0) {
                    navNotificationLabel.textContent = 'Pesanan';
                    navNotification.setAttribute('aria-label', 'Tidak ada pesanan menunggu verifikasi');
                    navNotification.removeAttribute('title');
                }
            } catch (error) {
                console.error('Pembaruan antrean kasir gagal dimuat.', error);
                notice.classList.remove('update');
                notice.classList.add('error');
                noticeMessage.textContent = 'Pembaruan antrean otomatis gagal.';
                document.getElementById('queue-update-link').href = window.location.href;
                notice.hidden = false;
            }
        };
        checkQueueUpdates();
        window.setInterval(checkQueueUpdates, 10000);
    })();
</script>
</body>
</html>
