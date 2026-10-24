

<?php $__env->startSection('title', 'Import Produk - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '📥 Import Produk'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .form-card { background:white; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1px solid #f0f0f0; overflow:hidden; }
    .form-card-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:0.6rem; }
    .form-card-header h3 { font-size:0.95rem; font-weight:700; color:#1f2937; margin:0; }
    .header-icon { width:32px; height:32px; background:#e3f2fd; border-radius:0.4rem; display:flex; align-items:center; justify-content:center; color:#1E88E5; font-size:0.9rem; }
    .form-body { padding:1.5rem; }
    .form-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:0.6rem; align-items:center; }
    .btn-save { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.5rem; background:#1E88E5; color:white; border:none; border-radius:0.5rem; font-size:0.9rem; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-save:hover { background:#1565C0; transform:translateY(-1px); }
    .btn-save:disabled { background:#d1d5db; cursor:not-allowed; transform:none; }
    .btn-cancel { display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; background:white; color:#6b7280; border:1px solid #e5e7eb; border-radius:0.5rem; font-size:0.9rem; font-weight:600; text-decoration:none; transition:all 0.2s; }
    .btn-cancel:hover { background:#f9fafb; color:#374151; }
    .upload-zone { border:2px dashed #d1d5db; border-radius:0.6rem; padding:2rem 1rem; text-align:center; background:#fafafa; cursor:pointer; transition:all 0.2s; }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#1E88E5; background:#f0f7ff; }
    .upload-zone .upload-icon { font-size:2.5rem; margin-bottom:0.5rem; }
    .upload-zone p { font-size:0.85rem; color:#6b7280; margin:0 0 0.75rem; }
    .upload-zone small { font-size:0.75rem; color:#9ca3af; display:block; margin-top:0.5rem; }
    .btn-choose { display:inline-flex; align-items:center; gap:0.35rem; padding:0.45rem 1rem; background:white; border:1px solid #d1d5db; border-radius:0.4rem; font-size:0.82rem; font-weight:600; color:#374151; cursor:pointer; transition:all 0.2s; }
    .btn-choose:hover { background:#f3f4f6; border-color:#9ca3af; }
    .info-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:0.75rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; }
    .info-box h3 { color:#1d4ed8; margin:0 0 0.75rem; font-size:0.95rem; font-weight:700; }
    .info-box ol { color:#1e40af; margin:0; padding-left:1.25rem; line-height:1.8; font-size:0.9rem; }
    .template-table { margin-top:1rem; background:#f9fafb; border-radius:0.5rem; padding:1rem; overflow-x:auto; }
    .template-table table { min-width:2200px; border-collapse:collapse; font-size:0.8rem; }
    .template-table thead tr { background:#e5e7eb; }
    .template-table th { padding:0.4rem 0.75rem; text-align:left; border:1px solid #d1d5db; font-weight:700; color:#374151; }
    .template-table td { padding:0.4rem 0.75rem; border:1px solid #d1d5db; color:#6b7280; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:#9ca3af;margin-bottom:1.25rem;">
    <a href="<?php echo e(route('admin.produk.index')); ?>" style="color:#1E88E5;text-decoration:none;font-weight:600;">🛒 Produk Kami</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:#374151;font-weight:600;">Import Produk</span>
</div>

<div class="info-box">
    <h3><i class="fa-solid fa-circle-info"></i> Petunjuk Import</h3>
    <ol>
        <li>Download Template Excel (.xlsx) di bawah</li>
        <li>Isi data produk sesuai kolom yang tersedia</li>
        <li>Isi stok pada kolom <strong>STOK SATUAN</strong> dan harga pada kolom <strong>MODAL</strong> serta harga jual Internal, B2B, dan B2C</li>
        <li><strong>PER STRIP</strong> adalah jumlah strip per satuan kemasan, sedangkan <strong>PER TABLET</strong> adalah jumlah tablet total per satuan kemasan. Harga per strip/tablet dapat diisi 0 jika tidak dijual satuan.</li>
        <li>Upload file <strong>.csv</strong>, <strong>.xls</strong>, atau <strong>.xlsx</strong></li>
        <li>Data yang sudah ada (SKU atau nama produk sama) akan diperbarui, data baru akan ditambahkan</li>
        <li>Struktur file tidak harus persis sama; selama header yang dikenali ada, data tetap bisa dimasukkan</li>
    </ol>
    <p style="color:#059669;margin:0.75rem 0 0;font-size:0.875rem;font-weight:600;">
        ✅ Format baru terdiri dari 19 kolom, termasuk golongan, kemasan, harga per kanal, dan harga per unit
    </p>
</div>


<div class="form-card" style="margin-bottom:1.5rem;">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-file-excel"></i></div>
        <h3>Template File</h3>
    </div>
    <div class="form-body">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <a href="<?php echo e(route('admin.produk.import.template')); ?>" class="btn-save" style="background:#10b981;text-decoration:none;">
                <i class="fa-solid fa-download"></i> Download Template Excel
            </a>
        </div>
        <div class="template-table">
            <table>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>PRINCIPLE</th>
                        <th>NAMA PRODUK</th>
                        <th>LOGO</th>
                        <th>GOLONGAN</th>
                        <th>KOMPOSISI</th>
                        <th>INDIKASI</th>
                        <th>SATUAN</th>
                        <th>KEMASAN</th>
                        <th>SEDIAAN</th>
                        <th>STOK SATUAN</th>
                        <th>PER STRIP</th>
                        <th>PER TABLET</th>
                        <th>MODAL</th>
                        <th>HARGA JUAL INTERNAL</th>
                        <th>HARGA JUAL B2B</th>
                        <th>HARGA JUAL B2C</th>
                        <th>HARGA JUAL PER STRIP</th>
                        <th>HARGA JUAL PER TABLET</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>PT KALBE FARMA</td>
                        <td>Promag Tablet</td>
                        <td>HIJAU</td>
                        <td>Obat Bebas</td>
                        <td>Antasida &amp; Simetikon</td>
                        <td>Membantu meredakan sakit maag</td>
                        <td>BOX</td>
                        <td>10 blister @ 10 tablet</td>
                        <td>TABLET</td>
                        <td>10</td><td>10</td><td>100</td><td>117771</td>
                        <td>123660</td><td>135437</td><td>153102</td><td>16841</td><td>15463</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>PT PFIZER</td>
                        <td>Viagra 100mg</td>
                        <td>MERAH</td>
                        <td>Obat Keras</td>
                        <td>Sildenafil</td>
                        <td>Disfungsi ereksi</td>
                        <td>BOX</td>
                        <td>1 blister @ 4 tablet</td>
                        <td>TABLET</td>
                        <td>1</td><td>1</td><td>4</td><td>200000</td>
                        <td>200000</td><td>205000</td><td>220000</td><td>55000</td><td>0</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>KONIMEX</td>
                        <td>Anakod Sirup</td>
                        <td>BIRU</td>
                        <td>Bebas Terbatas</td>
                        <td>Paracetamol</td>
                        <td>Membantu meredakan batuk dan flu</td>
                        <td>BOTOL</td>
                        <td></td>
                        <td>SIRUP</td>
                        <td>1</td><td>0</td><td>0</td><td>12000</td>
                        <td>12000</td><td>12500</td><td>14000</td><td>0</td><td>0</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>GSK</td>
                        <td>Voltaren Gel</td>
                        <td>MERAH</td>
                        <td>Obat Keras</td>
                        <td>Diclofenac</td>
                        <td>Membantu meredakan nyeri otot</td>
                        <td>TUBE</td>
                        <td></td>
                        <td>KRIM</td>
                        <td>5</td><td>0</td><td>0</td><td>50000</td>
                        <td>49000</td><td>51000</td><td>55000</td><td>0</td><td>0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="form-card">
    <div class="form-card-header">
        <div class="header-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h3>Upload File</h3>
    </div>
    <div class="form-body">
        <?php if($errors->any()): ?>
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:0.5rem;padding:1rem;margin-bottom:1rem;color:#7f1d1d;font-size:0.875rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($errors->first('file')); ?>

            </div>
        <?php endif; ?>
        <div id="dropZone" class="upload-zone" onclick="document.getElementById('fileInput').click()"
             ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <div class="upload-icon">📂</div>
            <p>Klik atau drag & drop file di sini</p>
            <button type="button" class="btn-choose" onclick="event.stopPropagation();document.getElementById('fileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Pilih File
            </button>
            <small>Format: CSV, XLS, XLSX — Maks. 10MB</small>
            <p style="color:#9ca3af;font-size:0.875rem;margin:0.75rem 0 0;" id="fileLabel"></p>
        </div>
        <input type="file" id="fileInput" name="file" accept=".csv,.xls,.xlsx,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" style="display:none;" onchange="updateFileLabel(this)">
    </div>
    <div class="form-footer" style="justify-content:flex-end; flex-wrap:wrap;">
        <form action="<?php echo e(route('admin.produk.import.process')); ?>" method="POST" enctype="multipart/form-data" id="submitForm" style="display:none;">
            <?php echo csrf_field(); ?>
            <input type="file" id="fileInputHidden" name="file" style="display:none;">
        </form>
        <button type="button" class="btn-save" id="submitBtn" onclick="submitForm()" disabled style="opacity:0.5;cursor:not-allowed;">
            <i class="fa-solid fa-cloud-arrow-up"></i> Import Sekarang
        </button>
        <a href="<?php echo e(route('admin.produk.index')); ?>" class="btn-cancel">
            <i class="fa-solid fa-xmark"></i> Batal
        </a>
    </div>
</div>

<script>
function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    const btn   = document.getElementById('submitBtn');
    const zone  = document.getElementById('dropZone');
    if (input.files && input.files[0]) {
        label.textContent = '✅ ' + input.files[0].name;
        label.style.color = '#059669';
        zone.style.borderColor = '#059669';
        zone.style.background  = '#f0fdf4';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor  = 'pointer';
    }
}
function handleDragOver(e) { e.preventDefault(); document.getElementById('dropZone').classList.add('drag-over'); }
function handleDragLeave(e) { document.getElementById('dropZone').classList.remove('drag-over'); }
function handleDrop(e) {
    e.preventDefault();
    const input = document.getElementById('fileInput');
    if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; updateFileLabel(input); }
    document.getElementById('dropZone').classList.remove('drag-over');
}
function submitForm() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput.files.length > 0) {
        const dt = new DataTransfer();
        dt.items.add(fileInput.files[0]);
        document.getElementById('fileInputHidden').files = dt.files;
        document.getElementById('submitForm').submit();
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\admin\produk\import.blade.php ENDPATH**/ ?>