@extends('layouts.frontend')

@section('title', 'Ruang File GASS - Apotek Medix')

@section('styles')
<style>
    .gass-room { max-width: 900px; margin: 0 auto; padding: calc(var(--navbar-height, 65px) + 1.5rem) 1rem 3rem; }
    .gass-card { padding: clamp(1.25rem, 4vw, 2.25rem); background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; box-shadow: 0 12px 36px rgba(31, 41, 55, .1); }
    .gass-heading { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .gass-heading img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
    .gass-heading h1 { color: #1565c0; font-size: clamp(1.4rem, 4vw, 2rem); }
    .gass-heading p, .gass-note { color: #64748b; }
    .gass-form { display: grid; gap: .75rem; margin-top: 1.5rem; }
    .gass-form label { font-weight: 700; color: #334155; }
    .gass-form input[type="password"], .gass-form input[type="file"] { width: 100%; padding: .8rem; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; }
    .gass-dropzone { position: relative; display: grid; justify-items: center; gap: .35rem; padding: 1.5rem 1rem; border: 2px dashed #94a3b8; border-radius: 12px; color: #334155; text-align: center; cursor: pointer; }
    .gass-dropzone i { color: #1565c0; font-size: 1.5rem; }
    .gass-dropzone small { color: #64748b; font-weight: 400; }
    .gass-dropzone input[type="file"] { position: absolute; inset: 0; width: 100%; height: 100%; padding: 0; border: 0; opacity: 0; cursor: pointer; }
    .gass-dropzone:focus-within { outline: 3px solid rgba(21, 101, 192, .35); outline-offset: 3px; }
    .gass-dropzone.is-dragging { border-color: #1565c0; background: #eff6ff; }
    .gass-upload-summary { margin: 0; color: #475569; }
    .gass-selected-files { display: grid; gap: .4rem; margin: 0; padding: 0; list-style: none; }
    .gass-selected-file { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .55rem .7rem; border: 1px solid #e2e8f0; border-radius: 8px; }
    .gass-selected-file-name { min-width: 0; overflow-wrap: anywhere; }
    .gass-selected-file-size { display: block; color: #64748b; font-size: .85rem; font-weight: 400; }
    .gass-remove-file { flex: 0 0 auto; padding: .35rem .5rem; border: 0; background: transparent; color: #b91c1c; font-weight: 700; cursor: pointer; }
    .gass-remove-file:hover { color: #991b1b; text-decoration: underline; }
    .gass-button:disabled { opacity: .55; cursor: not-allowed; }
    .gass-error[hidden] { display: none; }
    .gass-search { display: flex; flex-wrap: wrap; gap: .5rem; width: 100%; margin-top: 1.5rem; }
    .gass-search input, .gass-search select { flex: 1 1 180px; min-width: 0; padding: .8rem; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; }
    .gass-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: fit-content; padding: .8rem 1.1rem; border: 0; border-radius: 10px; background: #1565c0; color: #fff; font-weight: 700; text-decoration: none; }
    .gass-button:hover { background: #0d47a1; }
    .gass-button-secondary { background: #475569; }
    .gass-error { color: #b91c1c; }
    .gass-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 2rem; }
    .gass-files { width: 100%; margin-top: 1rem; border-collapse: collapse; table-layout: fixed; }
    .gass-files th, .gass-files td { padding: .8rem .5rem; border-bottom: 1px solid #e2e8f0; text-align: left; overflow-wrap: anywhere; }
    .gass-files th { color: #475569; font-size: .85rem; white-space: nowrap; }
    .gass-files th:nth-child(1) { width: 3rem; }
    .gass-files th:nth-child(2) { width: 31%; }
    .gass-files th:nth-child(3) { width: 17%; }
    .gass-files th:nth-child(4) { width: 6rem; }
    .gass-files th:nth-child(5) { width: 16rem; }
    .gass-file-actions { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .35rem; }
    .gass-file-actions .gass-button { width: 100%; min-width: 0; padding: .65rem .3rem; font-size: .85rem; white-space: nowrap; }
    .gass-share-form, .gass-delete-form { min-width: 0; margin: 0; }
    .gass-button-danger { background: #b91c1c; }
    .gass-button-danger:hover { background: #991b1b; }
    .gass-empty { margin-top: 1rem; padding: 1.25rem; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; text-align: center; }
    .gass-share-dialog { width: min(100% - 2rem, 480px); padding: 1.5rem; border: 1px solid #dbe3ed; border-radius: 12px; box-shadow: 0 20px 60px rgba(15, 23, 42, .25); }
    .gass-share-dialog::backdrop { background: rgba(15, 23, 42, .5); }
    .gass-share-dialog h2 { margin: 0 0 .4rem; color: #1e293b; font-size: 1.25rem; }
    .gass-share-dialog p { margin: 0 0 1rem; color: #64748b; overflow-wrap: anywhere; }
    .gass-share-dialog label { display: block; margin-bottom: .4rem; font-weight: 700; }
    .gass-share-url-row, .gass-share-dialog-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .gass-share-url-row { margin-bottom: 1rem; }
    .gass-share-url-row input { flex: 1 1 220px; min-width: 0; padding: .7rem; border: 1px solid #cbd5e1; border-radius: 8px; }
    .gass-share-dialog-actions .gass-button { flex: 1 1 auto; }
    .gass-share-close { float: right; padding: .2rem .45rem; border: 0; background: transparent; color: #475569; font-size: 1.25rem; cursor: pointer; }
    .gass-share-feedback { min-height: 1.25rem; margin-top: .75rem !important; }
    @media (max-width: 760px) {
        .gass-files { display: block; table-layout: auto; }
        .gass-files thead { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .gass-files tbody { display: block; }
        .gass-files tr { display: grid; grid-template-columns: minmax(0, 1fr); gap: .35rem; padding: .7rem 0; border-bottom: 1px solid #e2e8f0; }
        .gass-files td { display: block; width: auto; padding: .25rem 0; border: 0; }
        .gass-files td:first-child, .gass-files td:nth-child(3), .gass-files td:nth-child(4) { display: none; }
        .gass-files td:nth-child(2) { font-weight: 600; line-height: 1.5; }
        .gass-file-actions { width: 100%; gap: .4rem; }
        .gass-file-actions .gass-button { padding: .65rem .3rem; font-size: .82rem; }
        .gass-heading img { width: 52px; height: 52px; }
    }
</style>
@endsection

@section('content')
<section class="gass-room">
    <div class="gass-card">
        <header class="gass-heading">
            <img src="{{ asset('logo gass.jpeg') }}" alt="Logo GASS">
            <div>
                <h1>Ruang File GASS</h1>
                <p>Unggah dan unduh file bersama.</p>
            </div>
        </header>

        <form class="gass-form" action="{{ route('gass.files.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="files">Pilih satu atau beberapa file</label>
            <label class="gass-dropzone" id="gass-dropzone" for="files">
                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                <span>Tarik file ke sini atau klik untuk memilih</span>
                <small>Pilih maksimal 5 file sekaligus</small>
                <input id="files" name="files[]" type="file" multiple required aria-describedby="gass-file-help">
            </label>
            <p class="gass-note" id="gass-file-help">Semua jenis file diterima. Batas ukuran upload mengikuti konfigurasi server hosting.</p>
            <p class="gass-upload-summary" id="gass-file-summary" role="status" aria-live="polite">Belum ada file dipilih.</p>
            <ul class="gass-selected-files" id="gass-selected-files" aria-label="File yang akan diunggah"></ul>
            <p class="gass-error" id="gass-file-limit-error" role="alert" hidden></p>
            @error('files')
                <span class="gass-error">{{ $message }}</span>
            @enderror
            @error('files.*')
                <span class="gass-error">{{ $message }}</span>
            @enderror
            <button class="gass-button" id="gass-upload-button" type="submit"><i class="fa-solid fa-upload"></i> Unggah File</button>
        </form>

        <form class="gass-search" action="{{ route('gass.room') }}" method="GET" role="search">
            <label class="sr-only" for="gass-file-search">Cari nama file</label>
            <input id="gass-file-search" type="search" name="search" value="{{ $search }}" placeholder="Cari nama file...">
            <label class="sr-only" for="gass-upload-date">Tanggal unggah</label>
            <input id="gass-upload-date" type="date" name="uploaded_on" value="{{ $uploadedOn }}">
            <label class="sr-only" for="gass-file-sort">Urutkan file</label>
            <select id="gass-file-sort" name="sort">
                <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                <option value="name_asc" @selected($sort === 'name_asc')>Nama A-Z</option>
                <option value="size_asc" @selected($sort === 'size_asc')>Ukuran terkecil</option>
                <option value="size_desc" @selected($sort === 'size_desc')>Ukuran terbesar</option>
            </select>
            <button class="gass-button" type="submit"><i class="fa-solid fa-filter"></i> Terapkan</button>
            @if ($search !== '' || $uploadedOn !== '' || $sort !== 'newest')
                <a class="gass-button gass-button-secondary" href="{{ route('gass.room') }}">Hapus filter</a>
            @endif
        </form>

        <div class="gass-toolbar">
            <h2>{{ $search !== '' || $uploadedOn !== '' ? 'Hasil filter' : 'File di ruang ini' }} ({{ $files->count() }})</h2>
            <form action="{{ route('gass.leave') }}" method="POST">
                @csrf
                <button class="gass-button gass-button-secondary" type="submit">Keluar</button>
            </form>
        </div>

        @if ($files->isEmpty())
            <p class="gass-empty">{{ $search !== '' || $uploadedOn !== '' ? 'Tidak ada file yang cocok dengan filter.' : 'Belum ada file. Unggah file pertama ke ruang ini.' }}</p>
        @else
            <table class="gass-files">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th>Nama file</th>
                        <th>Tanggal unggah</th>
                        <th>Ukuran</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $file)
                        @php
                            $fileSize = $file->size_bytes;
                            $fileSizeUnits = ['B', 'KB', 'MB', 'GB', 'TB'];
                            $fileSizeUnit = 0;
                            while ($fileSize >= 1024 && $fileSizeUnit < count($fileSizeUnits) - 1) {
                                $fileSize /= 1024;
                                $fileSizeUnit++;
                            }
                            $uploadedAt = $file->created_at->timezone(config('gass.timezone'));
                        @endphp
                        <tr>
                            <td class="gass-file-number">{{ $loop->iteration }}</td>
                            <td>{{ $file->original_name }}</td>
                            <td>{{ $uploadedAt->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format($fileSize, $fileSizeUnit === 0 ? 0 : 1) }} {{ $fileSizeUnits[$fileSizeUnit] }}</td>
                            <td>
                                <div class="gass-file-actions">
                                    <a class="gass-button" href="{{ route('gass.files.view', $file) }}" target="_blank" rel="noopener">Lihat</a>
                                    <a class="gass-button gass-button-secondary" href="{{ route('gass.files.download', $file) }}">Unduh</a>
                                    <form class="gass-share-form" action="{{ route('gass.files.share', $file) }}" method="POST">
                                        @csrf
                                        <button class="gass-button" type="submit" data-share-trigger data-is-public="{{ $file->is_public ? 'true' : 'false' }}" data-share-url="{{ $file->is_public ? route('gass.files.shared.view', $file->share_token) : '' }}" data-file-name="{{ $file->original_name }}">Share</button>
                                    </form>
                                    <form class="gass-delete-form" action="{{ route('gass.files.destroy', $file) }}" method="POST" onsubmit="return confirm('Hapus file ini? Tindakan ini tidak dapat dikembalikan.');">
                                        @csrf
                                        @method('DELETE')
                                        @if ($search !== '')
                                            <input type="hidden" name="search" value="{{ $search }}">
                                        @endif
                                        @if ($uploadedOn !== '')
                                            <input type="hidden" name="uploaded_on" value="{{ $uploadedOn }}">
                                        @endif
                                        @if ($sort !== 'newest')
                                            <input type="hidden" name="sort" value="{{ $sort }}">
                                        @endif
                                        <button class="gass-button gass-button-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <dialog class="gass-share-dialog" id="gass-share-dialog" aria-labelledby="gass-share-title">
            <button class="gass-share-close" type="button" data-share-close aria-label="Tutup"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            <h2 id="gass-share-title">Bagikan file</h2>
            <p data-share-file-name></p>
            <label for="gass-share-url">Link file</label>
            <div class="gass-share-url-row">
                <input id="gass-share-url" type="url" readonly>
                <button class="gass-button gass-button-secondary" type="button" data-copy-share-link>Salin link</button>
            </div>
            <div class="gass-share-dialog-actions">
                <a class="gass-button" href="#" target="_blank" rel="noopener" data-share-whatsapp><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                <button class="gass-button gass-button-secondary" type="button" data-revoke-share>Jadikan privat</button>
            </div>
            <p class="gass-share-feedback" data-share-feedback role="status" aria-live="polite"></p>
        </dialog>
    </div>
</section>

<script>
    (() => {
        const fileInput = document.getElementById('files');
        const dropzone = document.getElementById('gass-dropzone');
        const fileList = document.getElementById('gass-selected-files');
        const summary = document.getElementById('gass-file-summary');
        const uploadButton = document.getElementById('gass-upload-button');
        const limitError = document.getElementById('gass-file-limit-error');
        const maxFiles = 5;

        if (!fileInput || !dropzone || !fileList || !summary || !uploadButton || !limitError) return;

        let selectedFiles = [];

        const formatSize = (bytes) => {
            if (bytes === 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const size = bytes / (1024 ** unitIndex);

            return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(size)} ${units[unitIndex]}`;
        };

        const syncInput = () => {
            if (typeof DataTransfer === 'undefined') return false;

            const transfer = new DataTransfer();
            selectedFiles.forEach((file) => transfer.items.add(file));
            fileInput.files = transfer.files;

            return true;
        };

        const renderFiles = () => {
            fileList.replaceChildren();
            selectedFiles.forEach((file, index) => {
                const item = document.createElement('li');
                const name = document.createElement('span');
                const size = document.createElement('small');
                const remove = document.createElement('button');

                item.className = 'gass-selected-file';
                name.className = 'gass-selected-file-name';
                name.textContent = file.name;
                size.className = 'gass-selected-file-size';
                size.textContent = formatSize(file.size);
                name.append(size);
                remove.className = 'gass-remove-file';
                remove.type = 'button';
                remove.textContent = 'Hapus';
                remove.setAttribute('aria-label', `Hapus ${file.name}`);
                remove.disabled = typeof DataTransfer === 'undefined';
                remove.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    syncInput();
                    renderFiles();
                });

                item.append(name, remove);
                fileList.append(item);
            });

            const totalSize = selectedFiles.reduce((total, file) => total + file.size, 0);
            summary.textContent = selectedFiles.length
                ? `${selectedFiles.length} file dipilih Â· ${formatSize(totalSize)} total`
                : 'Belum ada file dipilih.';
            uploadButton.disabled = selectedFiles.length === 0;
        };

        const addFiles = (files) => {
            const availableSlots = maxFiles - selectedFiles.length;
            const acceptedFiles = files.slice(0, availableSlots);
            selectedFiles.push(...acceptedFiles);

            if (acceptedFiles.length < files.length) {
                const rejectedCount = files.length - acceptedFiles.length;
                limitError.textContent = `Maksimal ${maxFiles} file. ${rejectedCount} file tambahan tidak ditambahkan.`;
                limitError.hidden = false;
            } else {
                limitError.textContent = '';
                limitError.hidden = true;
            }

            syncInput();
            renderFiles();
        };

        fileInput.addEventListener('change', () => addFiles(Array.from(fileInput.files)));

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragging');
            });
        });

        dropzone.addEventListener('drop', (event) => addFiles(Array.from(event.dataTransfer.files)));
        renderFiles();
    })();
</script>
<script>
    (() => {
        const dialog = document.getElementById('gass-share-dialog');
        const shareUrlInput = document.getElementById('gass-share-url');
        const fileName = dialog?.querySelector('[data-share-file-name]');
        const feedback = dialog?.querySelector('[data-share-feedback]');
        const whatsappLink = dialog?.querySelector('[data-share-whatsapp]');
        const copyButton = dialog?.querySelector('[data-copy-share-link]');
        const revokeButton = dialog?.querySelector('[data-revoke-share]');
        let activeShareForm = null;
        let activeShareButton = null;

        if (!dialog || !shareUrlInput || !feedback || !whatsappLink || !copyButton || !revokeButton) return;

        const updateShareDialog = (button, url) => {
            activeShareButton = button;
            activeShareForm = button.closest('form');
            shareUrlInput.value = url;
            fileName.textContent = button.dataset.fileName || '';
            whatsappLink.href = `https://wa.me/?text=${encodeURIComponent(url)}`;
            feedback.textContent = '';
            dialog.showModal();
        };

        document.querySelectorAll('[data-share-trigger]').forEach((button) => {
            button.closest('form')?.addEventListener('submit', async (event) => {
                event.preventDefault();
                button.disabled = true;
                feedback.textContent = 'Menyiapkan tautan...';

                try {
                    let url = button.dataset.shareUrl;
                    if (button.dataset.isPublic !== 'true') {
                        const response = await fetch(event.currentTarget.action, {
                            method: 'POST',
                            body: new FormData(event.currentTarget),
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const result = await response.json();
                        if (!response.ok || !result.url) throw new Error('Tautan gagal dibuat. Silakan coba lagi.');
                        url = result.url;
                        button.dataset.isPublic = 'true';
                        button.dataset.shareUrl = url;
                    }

                    updateShareDialog(button, url);
                } catch (error) {
                    feedback.textContent = error instanceof Error ? error.message : 'Tautan gagal dibuat.';
                    dialog.showModal();
                } finally {
                    button.disabled = false;
                }
            });
        });

        copyButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(shareUrlInput.value);
                feedback.textContent = 'Link berhasil disalin.';
            } catch {
                shareUrlInput.select();
                document.execCommand('copy');
                feedback.textContent = 'Link siap disalin.';
            }
        });

        revokeButton.addEventListener('click', async () => {
            if (!activeShareForm || !activeShareButton) return;

            revokeButton.disabled = true;
            try {
                const response = await fetch(activeShareForm.action, {
                    method: 'POST',
                    body: new FormData(activeShareForm),
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error();
                activeShareButton.dataset.isPublic = 'false';
                activeShareButton.dataset.shareUrl = '';
                dialog.close();
            } catch {
                feedback.textContent = 'Tautan gagal dinonaktifkan. Silakan coba lagi.';
            } finally {
                revokeButton.disabled = false;
            }
        });

        dialog.querySelector('[data-share-close]')?.addEventListener('click', () => dialog.close());
    })();
</script>
@endsection

