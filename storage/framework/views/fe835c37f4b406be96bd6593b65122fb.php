<?php
    $productImages = $medicine?->image_gallery ?? [];
    $imageCount = count($productImages);
?>
<div class="product-gallery-uploader" id="productGalleryUploader" data-existing-count="<?php echo e($imageCount); ?>">
    <div class="product-gallery-heading">
        <div>
            <strong>Galeri Foto Produk</strong>
            <p>Maksimal 2 foto. Foto pertama menjadi foto utama, foto kedua tampil saat kursor diarahkan. Foto boleh dihapus kapan saja.</p>
        </div>
        <span class="product-gallery-count" id="productImageCount"><?php echo e($imageCount); ?> foto</span>
    </div>

    <?php if($productImages): ?>
        <div class="product-gallery-existing" id="productGalleryExisting">
            <?php $__currentLoopData = $productImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="product-gallery-thumb" data-existing-image data-image-path="<?php echo e($path); ?>">
                    <img src="<?php echo e($index === 0 ? $medicine->image_url : url('storage/' . $path)); ?>" alt="Foto produk <?php echo e($index + 1); ?>">
                    <span data-image-label><?php echo e($index === 0 ? 'Utama' : 'Foto ' . ($index + 1)); ?></span>
                    <button type="button" data-remove-existing-image aria-label="Hapus foto produk <?php echo e($index + 1); ?>" title="Hapus foto">&times;</button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div id="productGalleryDeleteInputs"></div>
    <div class="product-gallery-dropzone" id="productGalleryDropzone" role="button" tabindex="0">
        <span class="product-gallery-icon"><i class="fa-solid fa-images"></i></span>
        <strong>Pilih foto produk</strong>
        <span>atau seret dan lepaskan di sini</span>
        <small>JPG, PNG, WEBP · maks. 5 MB per foto · total maksimal 2 foto</small>
        <button type="button" class="product-gallery-pick"><i class="fa-solid fa-arrow-up-from-bracket"></i> Pilih Foto</button>
    </div>
    <input type="file" id="productGalleryInput" name="gambar[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
    <div class="product-gallery-new" id="productGalleryNew"></div>
    <p class="product-gallery-error" id="productGalleryError" role="alert" hidden></p>
    <?php $__errorArgs = ['gambar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <?php $__errorArgs = ['gambar.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="product-crop-modal" id="productCropModal" aria-hidden="true">
    <div class="product-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="productCropTitle">
        <header class="product-crop-header">
            <div>
                <span class="product-crop-eyebrow">EDITOR FOTO PRODUK</span>
                <h2 id="productCropTitle">Atur potongan gambar</h2>
                <p id="productCropFilename"></p>
            </div>
            <button type="button" class="product-crop-close" id="productCropCancel" aria-label="Batalkan foto">&times;</button>
        </header>
        <div class="product-crop-workspace">
            <div class="product-crop-canvas-wrap">
                <canvas id="productCropCanvas" width="480" height="480" aria-label="Area crop foto"></canvas>
                <span class="product-crop-grid"></span>
            </div>
            <aside class="product-crop-tools">
                <span class="product-crop-step" id="productCropStep">Foto 1 dari 1</span>
                <label for="productCropZoom">Zoom <output id="productCropZoomValue">1.0×</output></label>
                <input id="productCropZoom" type="range" min="1" max="3" value="1" step="0.01">
                <p>Geser foto untuk mengatur posisi. Hasil crop berbentuk persegi agar tampil rapi di katalog.</p>
                <div class="product-crop-preview">
                    <span>PRATINJAU</span>
                    <div><canvas id="productCropPreview" width="112" height="112"></canvas></div>
                </div>
            </aside>
        </div>
        <footer class="product-crop-footer">
            <span><i class="fa-solid fa-circle-info"></i> Foto disimpan setelah crop dikonfirmasi.</span>
            <button type="button" class="product-crop-confirm" id="productCropConfirm">Gunakan Foto <i class="fa-solid fa-arrow-right"></i></button>
        </footer>
    </div>
</div>

<style>
    .product-gallery-uploader { padding:1.1rem; border:1px solid #dbeafe; border-radius:1rem; background:linear-gradient(145deg,#f8fbff,#f0f7ff); }
    .product-gallery-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1rem; }
    .product-gallery-heading strong { color:#0f172a; font-size:.95rem; }
    .product-gallery-heading p { max-width:620px; margin:.3rem 0 0; color:#64748b; font-size:.8rem; line-height:1.5; }
    .product-gallery-count { flex:none; padding:.38rem .7rem; border:1px solid #bfdbfe; border-radius:999px; background:white; color:#1d4ed8; font-size:.75rem; font-weight:800; }
    .product-gallery-existing,.product-gallery-new { display:flex; flex-wrap:wrap; gap:.75rem; margin-bottom:.85rem; }
    .product-gallery-thumb { position:relative; width:96px; height:96px; overflow:hidden; border:1px solid #dbeafe; border-radius:.8rem; background:white; box-shadow:0 4px 14px rgba(15,23,42,.08); }
    .product-gallery-thumb img { width:100%; height:100%; object-fit:cover; }
    .product-gallery-thumb span { position:absolute; right:.3rem; bottom:.3rem; left:.3rem; overflow:hidden; padding:.25rem .35rem; border-radius:.45rem; background:rgba(15,23,42,.76); color:white; font-size:.63rem; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .product-gallery-thumb button { position:absolute; top:.3rem; right:.3rem; width:24px; height:24px; border:0; border-radius:50%; background:#ef4444; color:white; cursor:pointer; }
    .product-gallery-thumb.pending-delete { opacity:.48; }
    .product-gallery-thumb.pending-delete::after { position:absolute; inset:0; display:grid; place-items:center; background:#7f1d1d66; color:#fff; content:'Dihapus saat disimpan'; font-size:.65rem; font-weight:800; text-align:center; }
    .product-gallery-thumb.pending-delete button { z-index:1; background:#2563eb; }
    .product-gallery-dropzone { display:flex; min-height:170px; flex-direction:column; align-items:center; justify-content:center; gap:.35rem; padding:1.2rem; border:1.5px dashed #93c5fd; border-radius:.85rem; background:rgba(255,255,255,.82); color:#64748b; cursor:pointer; text-align:center; transition:border-color .2s,background .2s,transform .2s; }
    .product-gallery-dropzone[hidden] { display:none; }
    .product-gallery-dropzone:hover,.product-gallery-dropzone.drag-over { transform:translateY(-1px); border-color:#2563eb; background:white; }
    .product-gallery-icon { display:grid; width:42px; height:42px; place-items:center; margin-bottom:.2rem; border-radius:14px; background:linear-gradient(135deg,#dbeafe,#cffafe); color:#2563eb; font-size:1.15rem; }
    .product-gallery-dropzone strong { color:#1e293b; font-size:.9rem; }
    .product-gallery-dropzone>span:not(.product-gallery-icon) { font-size:.78rem; }
    .product-gallery-dropzone small { margin:.25rem 0; color:#94a3b8; font-size:.7rem; }
    .product-gallery-pick { margin-top:.25rem; padding:.55rem .9rem; border:0; border-radius:.6rem; background:#1d4ed8; color:white; font-size:.78rem; font-weight:800; cursor:pointer; }
    .product-gallery-error { margin:.65rem 0 0; color:#b91c1c; font-size:.8rem; font-weight:700; }
    .product-crop-modal { position:fixed; z-index:10000; inset:0; display:none; align-items:flex-start; justify-content:center; overflow-y:auto; padding:1rem; background:rgba(2,6,23,.76); backdrop-filter:blur(10px); }
    .product-crop-modal.open { display:flex; }
    .product-crop-dialog { position:relative; display:flex; width:min(900px,100%); max-height:calc(100vh - 2rem); flex-direction:column; overflow:hidden; margin:auto; border:1px solid rgba(255,255,255,.5); border-radius:1.25rem; background:#fff; box-shadow:0 30px 100px rgba(0,0,0,.35); }
    @supports (height:100dvh) {
        .product-crop-modal { min-height:100dvh; }
        .product-crop-dialog { max-height:calc(100dvh - 2rem); }
    }
    .product-crop-header { display:flex; flex-shrink:0; justify-content:space-between; align-items:flex-start; padding:1.2rem 1.4rem; border-bottom:1px solid #e2e8f0; }
    .product-crop-eyebrow { color:#2563eb; font-size:.65rem; font-weight:900; letter-spacing:.16em; }
    .product-crop-header h2 { margin:.3rem 0; color:#0f172a; font-size:1.15rem; }
    .product-crop-header p { margin:0; color:#64748b; font-size:.78rem; }
    .product-crop-close { width:36px; height:36px; border:1px solid #e2e8f0; border-radius:50%; background:#fff; color:#475569; font-size:1.5rem; cursor:pointer; }
    .product-crop-workspace { display:grid; min-height:0; flex:1 1 auto; grid-template-columns:minmax(0,1.3fr) minmax(210px,.7fr); gap:1.2rem; overflow:auto; padding:1.35rem; background:#f8fafc; }
    .product-crop-canvas-wrap { position:relative; width:min(100%,480px,calc(100vh - 300px)); aspect-ratio:1; justify-self:center; overflow:hidden; border:1px solid #cbd5e1; border-radius:1rem; background:#0f172a; box-shadow:0 12px 32px rgba(15,23,42,.16); cursor:grab; touch-action:none; }
    @supports (height:100dvh) { .product-crop-canvas-wrap { width:min(100%,480px,calc(100dvh - 300px)); } }
    .product-crop-canvas-wrap.dragging { cursor:grabbing; }
    #productCropCanvas { display:block; width:100%; height:100%; }
    .product-crop-grid { position:absolute; inset:0; pointer-events:none; background:linear-gradient(90deg,transparent 33.1%,rgba(255,255,255,.26) 33.3%,transparent 33.6%,transparent 66.4%,rgba(255,255,255,.26) 66.7%,transparent 66.9%),linear-gradient(0deg,transparent 33.1%,rgba(255,255,255,.26) 33.3%,transparent 33.6%,transparent 66.4%,rgba(255,255,255,.26) 66.7%,transparent 66.9%); }
    .product-crop-tools { display:flex; flex-direction:column; align-items:stretch; gap:.85rem; padding:.25rem; }
    .product-crop-step { align-self:flex-start; padding:.35rem .6rem; border-radius:999px; background:#dbeafe; color:#1d4ed8; font-size:.7rem; font-weight:900; }
    .product-crop-tools label { display:flex; justify-content:space-between; color:#334155; font-size:.8rem; font-weight:800; }
    .product-crop-tools output { color:#2563eb; }
    #productCropZoom { width:100%; accent-color:#2563eb; }
    .product-crop-tools p { margin:0; color:#64748b; font-size:.76rem; line-height:1.6; }
    .product-crop-preview { display:flex; flex-direction:column; align-items:flex-start; gap:.45rem; margin-top:auto; color:#94a3b8; font-size:.62rem; font-weight:900; letter-spacing:.12em; }
    .product-crop-preview>div { padding:.35rem; border:1px solid #e2e8f0; border-radius:.8rem; background:white; box-shadow:0 5px 16px rgba(15,23,42,.08); }
    #productCropPreview { display:block; width:112px; height:112px; border-radius:.5rem; }
    .product-crop-footer { position:relative; z-index:2; display:flex; flex-shrink:0; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.4rem; border-top:1px solid #e2e8f0; background:#fff; box-shadow:0 -8px 18px rgba(15,23,42,.05); }
    .product-crop-footer>span { color:#64748b; font-size:.73rem; }
    .product-crop-confirm { position:relative; z-index:3; flex:none; padding:.7rem 1rem; border:0; border-radius:.7rem; background:linear-gradient(135deg,#2563eb,#0891b2); color:white; font-size:.8rem; font-weight:900; cursor:pointer; pointer-events:auto; box-shadow:0 6px 16px rgba(37,99,235,.22); }
    @media(max-width:650px) { .product-crop-workspace { grid-template-columns:1fr; padding:.85rem; } .product-crop-canvas-wrap { width:min(100%,380px,calc(100vh - 400px)); } .product-crop-tools { display:grid; grid-template-columns:1fr; gap:.55rem; } .product-crop-preview { display:none; } .product-crop-footer { align-items:stretch; flex-direction:column; padding:.75rem 1rem; } .product-crop-confirm { width:100%; min-height:44px; } .product-gallery-heading { flex-direction:column; } }
    @supports (height:100dvh) { @media(max-width:650px) { .product-crop-canvas-wrap { width:min(100%,380px,calc(100dvh - 400px)); } } }
</style>

<script>
(() => {
    const input = document.getElementById('productGalleryInput');
    const dropzone = document.getElementById('productGalleryDropzone');
    const gallery = document.getElementById('productGalleryNew');
    const existingGallery = document.getElementById('productGalleryExisting');
    const deleteInputs = document.getElementById('productGalleryDeleteInputs');
    const count = document.getElementById('productImageCount');
    const error = document.getElementById('productGalleryError');
    const modal = document.getElementById('productCropModal');
    const canvas = document.getElementById('productCropCanvas');
    const previewCanvas = document.getElementById('productCropPreview');
    const zoomInput = document.getElementById('productCropZoom');
    const zoomValue = document.getElementById('productCropZoomValue');
    const ctx = canvas.getContext('2d');
    const previewCtx = previewCanvas.getContext('2d');
    const existingCount = Number(document.getElementById('productGalleryUploader').dataset.existingCount || 0);
    const removedImages = new Set();
    let processed = Array.from(input.files || []);
    let queue = [];
    let current = null;
    let sourceImage = null;
    let zoom = 1;
    let panX = 0;
    let panY = 0;
    let pointer = null;

    const showError = (message) => {
        error.textContent = message;
        error.hidden = !message;
    };

    const remainingExistingCount = () => existingCount - removedImages.size;

    const updateCount = () => {
        const total = remainingExistingCount() + processed.length;
        count.textContent = `${total} foto`;
        dropzone.hidden = total >= 2;
        if (existingGallery) {
            let imageIndex = 0;
            existingGallery.querySelectorAll('[data-existing-image]').forEach(card => {
                if (card.classList.contains('pending-delete')) return;
                const label = card.querySelector('[data-image-label]');
                label.textContent = imageIndex++ === 0 ? 'Utama' : 'Foto 2';
            });
        }
    };

    const drawCrop = () => {
        if (!sourceImage) return;
        const scale = Math.max(canvas.width / sourceImage.width, canvas.height / sourceImage.height) * zoom;
        const width = sourceImage.width * scale;
        const height = sourceImage.height * scale;
        const maxX = Math.max(0, (width - canvas.width) / 2);
        const maxY = Math.max(0, (height - canvas.height) / 2);
        panX = Math.max(-maxX, Math.min(maxX, panX));
        panY = Math.max(-maxY, Math.min(maxY, panY));
        const x = (canvas.width - width) / 2 + panX;
        const y = (canvas.height - height) / 2 + panY;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(sourceImage, x, y, width, height);
        previewCtx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
        previewCtx.drawImage(canvas, 0, 0, previewCanvas.width, previewCanvas.height);
        zoomValue.value = `${zoom.toFixed(1)}×`;
    };

    const renderGallery = () => {
        gallery.replaceChildren();
        processed.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'product-gallery-thumb';
            const image = document.createElement('img');
            const previewUrl = URL.createObjectURL(file);
            image.src = previewUrl;
            image.addEventListener('load', () => URL.revokeObjectURL(previewUrl), { once: true });
            image.alt = `Foto baru ${index + 1}`;
            const label = document.createElement('span');
            label.textContent = remainingExistingCount() + index === 0 ? 'Utama' : 'Foto 2';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', `Hapus foto baru ${index + 1}`);
            remove.innerHTML = '&times;';
            remove.addEventListener('click', () => {
                processed.splice(index, 1);
                syncFiles();
            });
            card.append(image, label, remove);
            gallery.append(card);
        });
        updateCount();
    };

    const syncFiles = () => {
        const transfer = new DataTransfer();
        processed.forEach(file => transfer.items.add(file));
        input.files = transfer.files;
        renderGallery();
        showError('');
    };

    const openNextCrop = () => {
        if (!queue.length) {
            current = null;
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            syncFiles();
            return;
        }
        current = queue.shift();
        sourceImage = new Image();
        sourceImage.onload = () => {
            zoom = 1;
            panX = 0;
            panY = 0;
            zoomInput.value = '1';
            document.getElementById('productCropFilename').textContent = current.name;
            document.getElementById('productCropStep').textContent = `Foto ${processed.length + 1} dari ${processed.length + queue.length + 1}`;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            drawCrop();
        };
        sourceImage.onerror = () => {
            showError(`Tidak dapat membuka file ${current.name}.`);
            openNextCrop();
        };
        sourceImage.src = URL.createObjectURL(current);
    };

    const queueFiles = (files) => {
        const selected = Array.from(files || []);
        if (!selected.length) return;
        if (selected.some(file => !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
            showError('Gunakan file JPG, PNG, atau WEBP.');
            input.value = '';
            return;
        }
        if (selected.some(file => file.size > 5 * 1024 * 1024)) {
            showError('Ukuran setiap foto maksimal 5 MB.');
            input.value = '';
            return;
        }
        if (remainingExistingCount() + processed.length + selected.length > 2) {
            showError('Galeri produk maksimal 2 foto. Hapus foto lama atau pilih lebih sedikit foto baru.');
            input.value = '';
            return;
        }
        queue = selected;
        openNextCrop();
    };

    input.addEventListener('change', () => {
        const selected = Array.from(input.files || []);
        input.value = '';
        queueFiles(selected);
    });
    existingGallery?.querySelectorAll('[data-remove-existing-image]').forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('[data-existing-image]');
            const path = card.dataset.imagePath;
            if (removedImages.has(path)) {
                removedImages.delete(path);
                Array.from(deleteInputs.querySelectorAll('[data-delete-path]'))
                    .find(input => input.dataset.deletePath === path)
                    ?.remove();
                card.classList.remove('pending-delete');
                button.innerHTML = '&times;';
                button.setAttribute('aria-label', 'Hapus foto produk');
                button.title = 'Hapus foto';
            } else {
                removedImages.add(path);
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'hapus_gambar[]';
                hidden.value = path;
                hidden.dataset.deletePath = path;
                deleteInputs.append(hidden);
                card.classList.add('pending-delete');
                button.textContent = '↶';
                button.setAttribute('aria-label', 'Batalkan penghapusan foto');
                button.title = 'Batalkan penghapusan';
            }
            updateCount();
            renderGallery();
            showError('');
        });
    });
    dropzone.addEventListener('click', () => input.click());
    dropzone.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });
    dropzone.addEventListener('dragover', event => {
        event.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', event => {
        event.preventDefault();
        dropzone.classList.remove('drag-over');
        queueFiles(event.dataTransfer.files);
    });
    zoomInput.addEventListener('input', () => {
        zoom = Number(zoomInput.value);
        drawCrop();
    });
    document.querySelector('.product-crop-canvas-wrap').addEventListener('pointerdown', event => {
        pointer = { x: event.clientX, y: event.clientY };
        event.currentTarget.classList.add('dragging');
        event.currentTarget.setPointerCapture(event.pointerId);
    });
    document.querySelector('.product-crop-canvas-wrap').addEventListener('pointermove', event => {
        if (!pointer) return;
        const rect = canvas.getBoundingClientRect();
        panX += (event.clientX - pointer.x) * canvas.width / rect.width;
        panY += (event.clientY - pointer.y) * canvas.height / rect.height;
        pointer = { x: event.clientX, y: event.clientY };
        drawCrop();
    });
    document.querySelector('.product-crop-canvas-wrap').addEventListener('pointerup', event => {
        pointer = null;
        event.currentTarget.classList.remove('dragging');
    });
    document.getElementById('productCropCancel').addEventListener('click', () => {
        queue = [];
        current = null;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        syncFiles();
    });
    document.getElementById('productCropConfirm').addEventListener('click', () => {
        canvas.toBlob(blob => {
            if (!blob) {
                showError('Gagal memproses crop foto. Coba pilih gambar lain.');
                return;
            }
            const safeName = current.name.replace(/\.[^.]+$/, '') + '-crop.jpg';
            processed.push(new File([blob], safeName, { type: 'image/jpeg', lastModified: Date.now() }));
            openNextCrop();
        }, 'image/jpeg', 0.92);
    });

    document.getElementById('productGalleryUploader').closest('form').addEventListener('submit', event => {
        if (remainingExistingCount() + processed.length > 2) {
            event.preventDefault();
            showError('Galeri produk maksimal 2 foto.');
            dropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    updateCount();
})();
</script>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/partials/product-image-uploader.blade.php ENDPATH**/ ?>