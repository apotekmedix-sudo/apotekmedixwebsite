

<?php $__env->startSection('title', 'Konsultasi Pasien - Apotek Medix'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .consult-head{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
    .consult-head h1{margin:.2rem 0;color:#10243e;font-size:1.4rem}.consult-muted{color:#52657e;line-height:1.5}
    .consult-head-actions{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap}
    .consult-timer{padding:.6rem .8rem;border-radius:8px;background:#ecfdf5;color:#065f46;font-weight:800}
    .end-session-button{min-height:40px;padding:.55rem .8rem;border:0;border-radius:8px;background:#b91c1c;color:#fff;font:inherit;font-weight:800;cursor:pointer}
    .end-session-button:hover{background:#991b1b}.end-session-button:focus-visible{outline:3px solid #fecaca;outline-offset:2px}
    .patient-card,.consultation-card{margin-bottom:1rem;padding:1rem;border:1px solid #dbe4f0;border-radius:12px;background:#fff}
    .section-heading{margin:0 0 .75rem;color:#10243e;font-size:1rem}
    .patient-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem 1rem;margin:0}
    .patient-details div{min-width:0}.patient-details dt{margin-bottom:.2rem;color:#52657e;font-size:.76rem;font-weight:700}
    .patient-details dd{margin:0;color:#172b4d;font-weight:600;overflow-wrap:anywhere}
    .patient-info{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}
    .patient-info section{min-width:0;padding:.9rem;border:1px solid #dbe4f0;border-radius:10px;background:#fff}
    .patient-info .patient-info-wide{grid-column:1/-1}
    .patient-info h3{margin:0 0 .45rem;color:#10243e;font-size:.88rem}
    .patient-info p{margin:0;color:#334155;line-height:1.6;overflow-wrap:anywhere;white-space:pre-wrap}
    .chat-box{display:flex;flex-direction:column;height:min(60vh,650px);min-height:350px;overflow:hidden;border:1px solid #cbd5e1;border-radius:12px;background:#fff}
    .chat-header{padding:.8rem 1rem;background:#0f766e;color:#fff;font-weight:800}
    .chat-header span{color:#fff}
    .chat-messages{flex:1;overflow:auto;padding:1rem;background:#f8fafc}
    .chat-message{max-width:82%;margin:0 0 .75rem;padding:.65rem .8rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff;white-space:pre-wrap;overflow-wrap:anywhere}
    .chat-message.mine{margin-left:auto;border-color:#bfdbfe;background:#eff6ff}
    .chat-message small{display:block;margin-bottom:.2rem;color:#475569;font-size:.7rem;font-weight:700}
    .chat-compose{display:flex;gap:.5rem;padding:.75rem;border-top:1px solid #e2e8f0}
    .chat-compose textarea{flex:1;min-width:0;min-height:44px;padding:.6rem;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
    .chat-compose button{padding:.6rem .9rem;border:0;border-radius:8px;background:#08734f;color:#fff;font-weight:800}
    .prescription-panel{margin-bottom:1rem;padding:1rem;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff}
    .prescription-panel h2{margin:0 0 .4rem;color:#10243e;font-size:1rem}
    .prescription-panel p{color:#475569;line-height:1.5}
    .prescription-search,.prescription-entry{display:flex;gap:.6rem;align-items:center;margin:.55rem 0}
    .prescription-search input,.prescription-entry input,.prescription-entry textarea{min-width:0;padding:.6rem;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
    .prescription-search input{flex:1}
    .prescription-entry{padding:.7rem;border:1px solid #dbe4f0;border-radius:9px;background:#fff}
    .prescription-entry-info{flex:1;min-width:0}
    .prescription-entry-info small{display:block;color:#52657e}
    .prescription-entry input[type=number]{width:90px}
    .prescription-entry textarea{flex:1}
    .prescription-action{padding:.6rem .8rem;border:0;border-radius:8px;background:#08734f;color:#fff;font-weight:800;cursor:pointer}
    .prescription-action:disabled{opacity:.55;cursor:not-allowed}
    .prescription-remove{padding:.5rem;border:0;background:transparent;color:#b91c1c;cursor:pointer}
    .prescription-results{display:grid;gap:.45rem;margin-top:.7rem}
    .prescription-result{display:flex;justify-content:space-between;align-items:center;gap:.6rem;padding:.65rem;border:1px solid #dbe4f0;border-radius:8px;background:#fff}
    .prescription-result small{display:block;color:#52657e}
    .prescription-confirmed{margin:.65rem 0 0;padding:.7rem;border-radius:8px;background:#dcfce7;color:#166534}
    .session-feedback{margin:0 0 1rem;padding:.75rem 1rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534}
    .session-error{border-color:#fecaca;background:#fef2f2;color:#991b1b}
    @media(max-width:620px){.patient-details,.patient-info{grid-template-columns:1fr}.patient-info .patient-info-wide{grid-column:auto}.consult-head-actions{width:100%;justify-content:space-between}}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<header class="consult-head">
    <div><p class="consult-muted" style="margin:0">Data pasien</p><h1><?php echo e($consultation->patient_name); ?></h1></div>
    <div class="consult-head-actions">
        <div class="consult-timer">Sisa waktu <span id="doctorTimer"><?php echo e(sprintf('%02d:00', $consultation->session_duration_minutes)); ?></span></div>
        <?php if($consultation->isActive()): ?>
            <form method="POST" action="<?php echo e(route('consultations.room.end', $consultation)); ?>" onsubmit="return confirm('Akhiri sesi konsultasi sekarang? Pasien dan dokter tidak dapat mengirim pesan lagi.')">
                <?php echo csrf_field(); ?>
                <button class="end-session-button" type="submit">Akhiri sesi</button>
            </form>
        <?php endif; ?>
    </div>
</header>
<?php if(session('success')): ?><p class="session-feedback" role="status"><?php echo e(session('success')); ?></p><?php endif; ?>
<?php if($errors->has('session')): ?><p class="session-feedback session-error" role="alert"><?php echo e($errors->first('session')); ?></p><?php endif; ?>
<section class="patient-card" aria-labelledby="patientDetailsHeading">
    <h2 class="section-heading" id="patientDetailsHeading">Identitas & kontak</h2>
    <dl class="patient-details">
        <div><dt>Jenis kelamin</dt><dd><?php echo e($consultation->patient_gender); ?></dd></div>
        <div><dt>Tanggal lahir</dt><dd><?php echo e(\Illuminate\Support\Carbon::parse($consultation->patient_birth_date)->format('d-m-Y')); ?></dd></div>
        <div><dt>Nomor ponsel</dt><dd><?php echo e($consultation->patient_phone); ?></dd></div>
        <div><dt>Email</dt><dd><?php echo e($consultation->patient_email); ?></dd></div>
        <?php if($consultation->guardian_name): ?><div><dt>Pendamping pasien</dt><dd><?php echo e($consultation->guardian_name); ?> · <?php echo e($consultation->guardian_relationship); ?></dd></div><?php endif; ?>
    </dl>
</section>
<section class="consultation-card" aria-labelledby="consultationDetailsHeading">
    <h2 class="section-heading" id="consultationDetailsHeading">Informasi konsultasi</h2>
    <div class="patient-info">
        <section class="patient-info-wide"><h3>Keluhan & obat yang diinginkan</h3><p><?php echo e($consultation->complaint); ?></p></section>
        <section><h3>Alergi obat</h3><p><?php echo e($consultation->drug_allergies ?: 'Tidak diisi'); ?></p></section>
        <section><h3>Data fisik</h3><p>Tinggi badan: <?php echo e($consultation->height_cm ? $consultation->height_cm . ' cm' : 'Tidak diisi'); ?><br>Berat badan: <?php echo e($consultation->weight_kg ? $consultation->weight_kg . ' kg' : 'Tidak diisi'); ?></p></section>
        <?php if($consultation->requested_medicine): ?><section class="patient-info-wide"><h3>Obat yang diinginkan</h3><p><?php echo e($consultation->requested_medicine); ?></p></section><?php endif; ?>
    </div>
</section>
<?php if($consultation->payment_channel === 'online'): ?>
<section class="prescription-panel" id="onlinePrescriptionPanel">
    <h2>Resep konsultasi Online</h2>
    <p style="margin:.2rem 0 .8rem">Cari produk B2C yang sesuai, atur jumlah dan petunjuk pemakaian, lalu setujui untuk menerbitkan nomor resep.</p>
    <form id="prescriptionSearchForm" class="prescription-search">
        <input id="prescriptionSearchInput" type="search" minlength="2" maxlength="100" placeholder="Cari nama produk B2C..." required>
        <button class="prescription-action" type="submit">Cari produk</button>
    </form>
    <p id="prescriptionError" class="session-feedback session-error" role="alert" hidden></p>
    <div id="prescriptionResults" class="prescription-results"></div>
    <div id="prescriptionSelectedItems"></div>
    <button id="issuePrescriptionButton" class="prescription-action" type="button" disabled>Setujui & kirim resep</button>
    <div id="prescriptionConfirmed" class="prescription-confirmed" hidden></div>
</section>
<?php else: ?>
<section class="prescription-panel">
    <h2>Konsultasi Offline di apotek</h2>
    <p style="margin:.2rem 0 .8rem">Dokter tidak perlu memilih produk. Di akhir sesi, kirim instruksi agar pasien menemui apoteker untuk mengambil resep dan membayar di apotek.</p>
    <button id="offlinePrescriptionMessage" class="prescription-action" type="button" <?php if(!$consultation->isActive()): echo 'disabled'; endif; ?>>Kirim instruksi pengambilan resep</button>
</section>
<?php endif; ?>
<section class="chat-box">
    <div class="chat-header">Percakapan · <span id="chatState"><?php echo e($consultation->status === 'active' ? 'Sesi aktif' : 'Sesi selesai'); ?></span></div>
    <div id="chatMessages" class="chat-messages" aria-live="polite"></div>
    <p id="chatError" role="alert" style="display:none;margin:0;padding:.6rem .8rem;background:#fef2f2;color:#991b1b;font-size:.85rem"></p>
    <form id="doctorMessageForm" class="chat-compose">
        <textarea id="doctorMessageText" maxlength="2000" placeholder="Tulis balasan..." <?php if(!$consultation->isActive()): echo 'disabled'; endif; ?>></textarea>
        <button type="submit" <?php if(!$consultation->isActive()): echo 'disabled'; endif; ?>>Kirim</button>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(() => {
    const updatesUrl = <?php echo json_encode(route('consultations.room.updates', $consultation), 512) ?>;
    const messageUrl = <?php echo json_encode(route('consultations.room.messages.store', $consultation), 512) ?>;
    <?php if($consultation->payment_channel === 'online'): ?>
    const productsUrl = <?php echo json_encode(route('consultations.room.products', $consultation), 512) ?>;
    const prescriptionUrl = <?php echo json_encode(route('consultations.room.prescriptions.store', $consultation), 512) ?>;
    <?php endif; ?>
    const csrf = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    const messages = document.getElementById('chatMessages');
    let lastId = 0;
    let endsAt = <?php echo json_encode($consultation->ends_at?->toIso8601String(), 15, 512) ?>;
    <?php if($consultation->payment_channel === 'online'): ?>
    const searchForm = document.getElementById('prescriptionSearchForm');
    const searchInput = document.getElementById('prescriptionSearchInput');
    const resultsContainer = document.getElementById('prescriptionResults');
    const selectedContainer = document.getElementById('prescriptionSelectedItems');
    const issueButton = document.getElementById('issuePrescriptionButton');
    const prescriptionError = document.getElementById('prescriptionError');
    let selectedProducts = [];
    const showPrescriptionError = message => {
        prescriptionError.textContent = message;
        prescriptionError.hidden = !message;
    };
    const renderApprovedPrescription = prescription => {
        const confirmed = document.getElementById('prescriptionConfirmed');
        confirmed.replaceChildren();
        const heading = document.createElement('strong');
        heading.textContent = `Nomor resep: ${prescription.number}`;
        const list = document.createElement('ul');
        prescription.items.forEach(item => {
            const row = document.createElement('li');
            row.textContent = `${item.name} · ${item.quantity} ${item.unit_label} · ${item.instructions}`;
            list.append(row);
        });
        confirmed.append(heading, list);
        if (prescription.used) {
            const used = document.createElement('p');
            used.textContent = 'Resep ini sudah digunakan untuk checkout.';
            confirmed.append(used);
        }
        confirmed.hidden = false;
        searchForm.hidden = true;
        resultsContainer.hidden = true;
        selectedContainer.hidden = true;
        issueButton.hidden = true;
    };
    const renderSelectedProducts = () => {
        selectedContainer.replaceChildren();
        selectedProducts.forEach((product, index) => {
            const row = document.createElement('div');
            row.className = 'prescription-entry';
            const info = document.createElement('div');
            info.className = 'prescription-entry-info';
            const name = document.createElement('strong');
            name.textContent = product.name;
            const available = document.createElement('small');
            available.textContent = `Stok tersedia: ${product.available_quantity} ${product.unit_label}`;
            info.append(name, available);
            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = String(product.available_quantity);
            quantity.value = String(product.quantity || 1);
            quantity.setAttribute('aria-label', `Jumlah ${product.name}`);
            quantity.addEventListener('input', () => {
                product.quantity = Number(quantity.value);
                issueButton.disabled = selectedProducts.length === 0
                    || selectedProducts.some(item => !item.instructions?.trim() || item.quantity < 1 || item.quantity > item.available_quantity);
            });
            const instructions = document.createElement('textarea');
            instructions.maxLength = 500;
            instructions.placeholder = 'Petunjuk pemakaian';
            instructions.required = true;
            instructions.value = product.instructions || '';
            instructions.setAttribute('aria-label', `Petunjuk pemakaian ${product.name}`);
            instructions.addEventListener('input', () => {
                product.instructions = instructions.value;
                issueButton.disabled = selectedProducts.length === 0
                    || selectedProducts.some(item => !item.instructions?.trim() || item.quantity < 1 || item.quantity > item.available_quantity);
            });
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'prescription-remove';
            remove.textContent = 'Hapus';
            remove.addEventListener('click', () => {
                selectedProducts.splice(index, 1);
                renderSelectedProducts();
            });
            row.append(info, quantity, instructions, remove);
            selectedContainer.append(row);
        });
        issueButton.disabled = selectedProducts.length === 0
            || selectedProducts.some(item => !item.instructions?.trim() || item.quantity < 1 || item.quantity > item.available_quantity);
    };
    searchForm.addEventListener('submit', async event => {
        event.preventDefault();
        const query = searchInput.value.trim();
        if (query.length < 2) return;
        showPrescriptionError('');
        resultsContainer.textContent = 'Mencari produk...';
        try {
            const response = await fetch(`${productsUrl}?q=${encodeURIComponent(query)}`, {headers:{'Accept':'application/json'}});
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Produk tidak dapat dicari.');
            resultsContainer.replaceChildren();
            if (!result.products.length) {
                resultsContainer.textContent = 'Produk B2C yang tersedia tidak ditemukan.';
                return;
            }
            result.products.forEach(product => {
                const row = document.createElement('div');
                row.className = 'prescription-result';
                const info = document.createElement('div');
                const name = document.createElement('strong');
                name.textContent = product.name;
                const details = document.createElement('small');
                details.textContent = `Rp ${Number(product.price).toLocaleString('id-ID')} · stok ${product.available_quantity} ${product.unit_label}${product.requires_prescription ? ' · Logo K' : ''}`;
                info.append(name, details);
                const add = document.createElement('button');
                add.type = 'button';
                add.className = 'prescription-action';
                add.textContent = 'Pilih';
                add.disabled = selectedProducts.some(item => item.id === product.id);
                add.addEventListener('click', () => {
                    selectedProducts.push({...product, quantity: 1, instructions: ''});
                    renderSelectedProducts();
                    add.disabled = true;
                });
                row.append(info, add);
                resultsContainer.append(row);
            });
        } catch (exception) {
            resultsContainer.replaceChildren();
            showPrescriptionError(exception.message || 'Produk tidak dapat dicari. Coba lagi.');
        }
    });
    issueButton.addEventListener('click', async () => {
        showPrescriptionError('');
        issueButton.disabled = true;
        try {
            const response = await fetch(prescriptionUrl, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body:JSON.stringify({items:selectedProducts.map(item => ({
                    id:item.id,
                    quantity:Number(item.quantity),
                    instructions:item.instructions.trim()
                }))})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Resep tidak dapat diterbitkan.');
            renderApprovedPrescription(result);
        } catch (exception) {
            showPrescriptionError(exception.message || 'Resep tidak dapat diterbitkan. Periksa koneksi lalu coba lagi.');
            renderSelectedProducts();
        }
    });
    <?php endif; ?>
    const addMessage = message => {
        if (message.id <= lastId) return;
        const item = document.createElement('article');
        item.className = `chat-message${message.sender_type === 'doctor' ? ' mine' : ''}`;
        const label = document.createElement('small');
        label.textContent = message.sender_name;
        const text = document.createElement('div');
        text.textContent = message.body;
        item.append(label, text);
        messages.append(item);
        messages.scrollTop = messages.scrollHeight;
        lastId = message.id;
    };
    const poll = async () => {
        const response = await fetch(`${updatesUrl}?after_id=${lastId}`, {headers:{'Accept':'application/json'}});
        if (!response.ok) throw new Error('Status konsultasi tidak dapat diperbarui.');
        const data = await response.json();
        data.messages.forEach(addMessage);
        if (data.prescription) renderApprovedPrescription(data.prescription);
        if (data.ends_at) endsAt = data.ends_at;
        if (data.status === 'completed') document.getElementById('chatState').textContent = 'Sesi selesai';
        updateTimer();
    };
    const updateTimer = () => {
        const seconds = Math.max(0, endsAt ? Math.floor((new Date(endsAt).getTime() - Date.now()) / 1000) : 0);
        document.getElementById('doctorTimer').textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        if (!seconds) {
            document.getElementById('chatState').textContent = 'Sesi selesai';
            document.getElementById('doctorMessageText').disabled = true;
            document.querySelector('#doctorMessageForm button').disabled = true;
        }
    };
    document.getElementById('doctorMessageForm').addEventListener('submit', async event => {
        event.preventDefault();
        const input = document.getElementById('doctorMessageText');
        const button = event.currentTarget.querySelector('button');
        const error = document.getElementById('chatError');
        const body = input.value.trim();
        if (!body) return;
        button.disabled = true;
        error.style.display = 'none';
        try {
            const response = await fetch(messageUrl, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body:JSON.stringify({body})
            });
            if (response.status === 409) throw new Error('Sesi konsultasi telah berakhir.');
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Pesan tidak dapat dikirim.');
            addMessage(result);
            input.value = '';
        } catch (exception) {
            error.textContent = exception.message || 'Pesan tidak dapat dikirim. Periksa koneksi lalu coba lagi.';
            error.style.display = 'block';
        } finally {
            button.disabled = !endsAt || new Date(endsAt).getTime() <= Date.now();
        }
    });
    document.getElementById('offlinePrescriptionMessage')?.addEventListener('click', () => {
        const input = document.getElementById('doctorMessageText');
        input.value = 'Resep telah diberikan. Silakan langsung ke apoteker untuk mengambil obat dan melakukan pembayaran di apotek.';
        document.getElementById('doctorMessageForm').requestSubmit();
    });
    const runPoll = () => poll().catch(error => console.error('Gagal memperbarui sesi konsultasi:', error));
    updateTimer();
    runPoll();
    window.setInterval(runPoll, 3500);
    window.setInterval(updateTimer, 1000);
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.consultation-room', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/doctor/consultation.blade.php ENDPATH**/ ?>