<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Belanja B2B - Apotek Medix</title>
    <link rel="stylesheet" href="{{ asset('css/medix-public-theme.css') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1rem;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif}
        main{width:min(100%,420px);padding:2rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;box-shadow:0 18px 48px #0f172a18}
        .icon{display:grid;width:56px;height:56px;place-items:center;margin-bottom:1rem;border-radius:16px;background:#eff6ff;color:#0052cc;font-size:1.5rem}
        h1{margin:0 0 .5rem;color:#0f172a;font-size:1.5rem}
        p{color:#64748b;line-height:1.5}
        label{display:block;margin:1.25rem 0 .4rem;font-weight:700}
        input{width:100%;padding:.8rem;border:1px solid #cbd5e1;border-radius:.5rem;font:inherit;letter-spacing:.15em}
        input:focus{outline:3px solid #bfdbfe;border-color:#0052cc}
        button{width:100%;margin-top:1.25rem;padding:.85rem;border:0;border-radius:.5rem;background:#0052cc;color:#fff;font:inherit;font-weight:700;cursor:pointer}
        button:hover{background:#003eaa}
        .request-access-prompt{margin:1rem 0 0;text-align:center;font-size:.9rem}
        .request-access-prompt a{color:#0052cc;text-decoration:none}
        .request-access-prompt a:hover{text-decoration:underline}
        .request-access-dialog{width:min(100% - 2rem,520px);max-height:90vh;overflow:auto;padding:1.5rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;color:#1e293b;box-shadow:0 18px 48px #0f172a30}
        .request-access-dialog::backdrop{background:#0f172a80}
        .request-access-dialog h2{margin:0 2rem .5rem 0;color:#0f172a;font-size:1.25rem}
        .request-access-dialog p{margin:.5rem 0 1rem}
        .request-access-dialog label{margin-top:.85rem}
        .request-access-dialog input{letter-spacing:normal}
        .request-access-dialog .close-dialog{position:absolute;top:1rem;right:1rem;width:auto;margin:0;padding:.35rem .55rem;background:transparent;color:#475569;font-size:1.25rem}
        .request-access-dialog .close-dialog:hover{background:#f1f5f9}
        .error{margin:.75rem 0 0;color:#b91c1c;font-size:.9rem}
        .back{display:block;margin-top:1rem;text-align:center;color:#0052cc;text-decoration:none}
    </style>
</head>
<body class="public-theme public-theme-standalone">
<main>
    <div class="icon" aria-hidden="true">🔒</div>
    <h1>Belanja B2B</h1>
    <p>Masukkan kode aktivasi terbaru dari administrator untuk membuka katalog khusus mitra dan distributor.</p>
    <form action="{{ route('products.pbf.verify') }}" method="POST">
        @csrf
        <label for="kode">Kode akses</label>
        <input id="kode" name="kode" type="password" inputmode="numeric" pattern="[0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" aria-describedby="codeHint" required autofocus>
        <small id="codeHint">Masukkan kode angka 5 digit.</small>
        @error('kode')<p class="error" role="alert">{{ $message }}</p>@enderror
        <button type="submit">Verifikasi &amp; Masuk</button>
    </form>
    <p class="request-access-prompt">Belum memiliki kode akses? <a href="#" id="requestAccessToggle" aria-haspopup="dialog" aria-controls="requestAccessDialog">chat admin.</a></p>
    <a class="back" href="{{ route('home') }}">Kembali ke website</a>
</main>
<dialog class="request-access-dialog" id="requestAccessDialog" aria-labelledby="requestAccessTitle">
    <button type="button" class="close-dialog" id="closeRequestAccess" aria-label="Tutup formulir">&times;</button>
    <h2 id="requestAccessTitle">Permohonan kode akses</h2>
    <p>Isi data berikut untuk mengajukan permohonan kode akses B2B melalui WhatsApp.</p>
    <form id="requestAccessForm">
        <label for="requesterName">Nama apoteker / dokter (wajib)</label>
        <input id="requesterName" name="requester_name" type="text" autocomplete="name" required>

        <label for="outletName">Nama outlet (opsional)</label>
        <input id="outletName" name="outlet_name" type="text" autocomplete="organization">

        <label for="siaNumber">Nomor SIA (opsional)</label>
        <input id="siaNumber" name="sia" type="text">

        <label for="sipaNumber">Nomor SIPA / SIP (opsional)</label>
        <input id="sipaNumber" name="sipa" type="text">

        <label for="strNumber">Nomor STR (opsional)</label>
        <input id="strNumber" name="str" type="text">

        <label for="contactPhone">Nomor telepon (wajib)</label>
        <input id="contactPhone" name="phone" type="tel" autocomplete="tel" required>

        <label for="outletAddress">Alamat outlet (opsional)</label>
        <input id="outletAddress" name="address" type="text" autocomplete="street-address">

        <button type="submit">Chat WhatsApp untuk meminta kode</button>
    </form>
</dialog>
<script>
    const requestAccessToggle = document.getElementById('requestAccessToggle');
    const requestAccessDialog = document.getElementById('requestAccessDialog');
    const requestAccessForm = document.getElementById('requestAccessForm');

    requestAccessToggle.addEventListener('click', (event) => {
        event.preventDefault();
        requestAccessDialog.showModal();
        requestAccessForm.querySelector('input').focus();
    });

    document.getElementById('closeRequestAccess').addEventListener('click', () => {
        requestAccessDialog.close();
    });

    requestAccessForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!requestAccessForm.reportValidity()) return;

        const formData = new FormData(requestAccessForm);
        const message = [
            'Halo Admin Apotek Medix, saya ingin mengajukan permohonan kode akses B2B.',
            '',
            `Nama apoteker/dokter: ${formData.get('requester_name')}`,
            `Nomor telepon: ${formData.get('phone')}`,
            ...[
                ['Nama outlet', formData.get('outlet_name')],
                ['Nomor SIA', formData.get('sia')],
                ['Nomor SIPA / SIP', formData.get('sipa')],
                ['Nomor STR', formData.get('str')],
                ['Alamat outlet', formData.get('address')],
            ]
                .filter(([, value]) => String(value).trim() !== '')
                .map(([label, value]) => `${label}: ${value}`),
            '',
            'Mohon informasi dan bantuan untuk aktivasi kode akses. Terima kasih.'
        ].join('\n');

        window.open(`https://wa.me/{{ config('contacts.customer_service.whatsapp') }}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
        requestAccessDialog.close();
    });
</script>
</body>
</html>
