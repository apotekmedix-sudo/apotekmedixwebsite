@extends('layouts.admin')

@section('title', 'Tambah Produk Supplier - Admin Apotek Medix')
@section('page-title', 'Tambah Produk Supplier')

@section('styles')
<style>
    .procurement-page { display:grid; gap:1.25rem; color:#1e293b; }
    .procurement-card { padding:1.2rem; border:1px solid #e2e8f0; border-radius:.85rem; background:#fff; box-shadow:0 4px 14px rgba(15,23,42,.05); }
    .procurement-card h2,.procurement-card h3 { margin:0 0 .45rem; color:#172b4d; }
    .procurement-muted { margin:0 0 1rem; color:#64748b; font-size:.88rem; line-height:1.5; }
    .procurement-notice { padding:.8rem 1rem; border:1px solid #a7f3d0; border-radius:.65rem; background:#ecfdf5; color:#065f46; }
    .procurement-errors { padding:.8rem 1rem .8rem 2rem; border:1px solid #fecaca; border-radius:.65rem; background:#fef2f2; color:#991b1b; }
    .procurement-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; }
    .procurement-field label { display:block; margin-bottom:.3rem; color:#334155; font-size:.8rem; font-weight:700; }
    .procurement-field input,.procurement-field select,.procurement-field textarea { width:100%; min-height:40px; padding:.55rem .7rem; border:1px solid #cbd5e1; border-radius:.5rem; background:#fff; color:#172b4d; font:inherit; font-size:.88rem; }
    .procurement-field textarea { min-height:80px; }
    .procurement-product-search { position:relative; }
    .procurement-product-results { position:absolute; z-index:20; top:100%; left:0; right:0; max-height:260px; overflow:auto; border:1px solid #cbd5e1; border-radius:.5rem; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.12); }
    .procurement-product-results button { display:block; width:100%; padding:.65rem .75rem; border:0; border-bottom:1px solid #e2e8f0; background:#fff; color:#172b4d; text-align:left; cursor:pointer; }
    .procurement-product-results button:hover,.procurement-product-results button:focus { background:#eff6ff; }
    .procurement-product-results small,.procurement-selection-hint { display:block; margin-top:.25rem; color:#64748b; font-size:.78rem; }
    .procurement-selection-hint.is-existing { color:#166534; }
    .procurement-filter-form { display:grid; grid-template-columns:minmax(220px,2fr) minmax(180px,1fr) auto; gap:.6rem; align-items:end; margin:0 0 .9rem; }
    .procurement-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; margin-top:.85rem; }
    .procurement-pagination { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem; margin-top:.9rem; color:#64748b; font-size:.84rem; }
    .procurement-pagination-links { display:flex; gap:.5rem; }
    .procurement-pagination-link { display:inline-flex; align-items:center; justify-content:center; min-height:36px; padding:.45rem .75rem; border:1px solid #bfdbfe; border-radius:.45rem; background:#eff6ff; color:#1d4ed8; font-weight:700; text-decoration:none; }
    .procurement-pagination-link.disabled { border-color:#e2e8f0; background:#f8fafc; color:#94a3b8; }
    .procurement-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; min-height:38px; padding:.55rem .8rem; border:0; border-radius:.5rem; background:#0755c8; color:#fff; font-weight:700; text-decoration:none; cursor:pointer; }
    .procurement-btn.secondary { border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; }
    .procurement-btn.success { background:#15803d; }
    .procurement-table-wrap { overflow:auto; }
    .procurement-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .procurement-table th,.procurement-table td { padding:.7rem; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:middle; }
    .procurement-table th { background:#f8fafc; color:#475569; white-space:nowrap; }
    .procurement-table td { color:#26384f; }
    .procurement-table input[type=number] { width:100px; min-height:36px; padding:.4rem; border:1px solid #cbd5e1; border-radius:.4rem; color:#172b4d; }
    .procurement-toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.8rem; margin-bottom:.8rem; }
    .procurement-toolbar form { display:flex; flex-wrap:wrap; align-items:end; gap:.5rem; }
    .procurement-total { color:#0755c8; font-weight:800; white-space:nowrap; }
    .procurement-badge { display:inline-flex; padding:.25rem .55rem; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:800; }
    .procurement-empty { padding:1.5rem; border:1px dashed #cbd5e1; border-radius:.65rem; color:#64748b; text-align:center; }
    .supplier-management-actions { display:flex; flex-wrap:wrap; gap:.55rem; margin-top:.85rem; }
    .supplier-form-panel { display:none; margin-top:1rem; padding:1rem; border:1px solid #cbd5e1; border-radius:.75rem; background:#fff; }
    .supplier-form-panel.open { display:block; }
    .supplier-form-panel h3 { margin:0 0 .8rem; color:#1f2937; font-size:.95rem; }
    .supplier-form { display:grid; grid-template-columns:minmax(180px,.8fr) minmax(250px,1.4fr) minmax(150px,.7fr) auto; gap:.65rem; align-items:end; }
    .supplier-form label,.supplier-edit-form label { display:block; margin-bottom:.3rem; color:#475569; font-size:.75rem; font-weight:700; }
    .supplier-form input,.supplier-form textarea,.supplier-edit-form input,.supplier-edit-form textarea { width:100%; padding:.65rem .75rem; border:1px solid #cbd5e1; border-radius:.5rem; font:inherit; font-size:.85rem; }
    .supplier-form button,.supplier-edit-actions button { border:0; border-radius:.5rem; padding:.68rem .95rem; background:#1e88e5; color:#fff; font-weight:700; cursor:pointer; white-space:nowrap; }
    .supplier-modal-backdrop { display:none; position:fixed; inset:0; z-index:5000; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.65); }
    .supplier-modal-backdrop.open { display:flex; }
    .supplier-modal { display:flex; flex-direction:column; width:min(760px,100%); max-height:90vh; overflow:hidden; border:1px solid #dbeafe; border-radius:16px; background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.3); }
    .supplier-modal-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.2rem; border-bottom:1px solid #dbeafe; background:linear-gradient(135deg,#eff6ff,#fff); }
    .supplier-modal-head h3 { margin:0; color:#1e3a8a; font-size:1rem; }
    .supplier-modal-head p { margin:.25rem 0 0; color:#64748b; font-size:.78rem; }
    .supplier-modal-close { width:34px; height:34px; border:0; border-radius:50%; background:#dbeafe; color:#1d4ed8; cursor:pointer; }
    .supplier-modal-body { flex:1; min-height:0; overflow-y:auto; padding:1rem 1.2rem; }
    .supplier-manager-search { position:sticky; top:-1rem; z-index:2; display:flex; align-items:center; gap:.55rem; margin:-1rem -1.2rem .85rem; padding:.85rem 1.2rem; border-bottom:1px solid #e2e8f0; background:rgba(255,255,255,.96); }
    .supplier-manager-search i { color:#64748b; }
    .supplier-manager-search input { flex:1; min-width:0; padding:.65rem .8rem; border:1px solid #cbd5e1; border-radius:.55rem; color:#1e293b; font:inherit; font-size:.85rem; }
    .supplier-manager-count { flex:0 0 auto; color:#64748b; font-size:.75rem; }
    .supplier-data-tools { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; margin:0 0 .85rem; padding:.75rem; border:1px solid #e2e8f0; border-radius:.65rem; background:#f8fafc; }
    .supplier-data-tools a,.supplier-data-tools button { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; min-height:36px; padding:.5rem .7rem; border:1px solid #bfdbfe; border-radius:.45rem; background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:700; text-decoration:none; cursor:pointer; }
    .supplier-data-tools button { border-color:#1e88e5; background:#1e88e5; color:#fff; }
    .supplier-data-tools input[type=file] { min-width:160px; max-width:220px; color:#475569; font-size:.72rem; }
    .supplier-import-errors { flex:1 0 100%; margin:0; padding:.65rem .65rem .65rem 1.8rem; border-radius:.45rem; background:#fee2e2; color:#991b1b; font-size:.75rem; }
    .supplier-import-hint { margin:.6rem 0; color:#64748b; font-size:.78rem; }
    .supplier-list { display:grid; gap:.7rem; }
    .supplier-row { padding:.85rem; border:1px solid #e2e8f0; border-radius:10px; background:#fff; }
    .supplier-row-main { display:flex; justify-content:space-between; gap:1rem; align-items:center; }
    .supplier-row-name { margin:0; color:#1e293b; font-size:.88rem; font-weight:800; }
    .supplier-row-address { margin:.25rem 0 0; color:#64748b; font-size:.78rem; line-height:1.45; white-space:pre-line; }
    .supplier-row-phone { margin:.25rem 0 0; color:#475569; font-size:.78rem; }
    .supplier-row-actions,.supplier-row-actions form { display:flex; flex:0 0 auto; gap:.4rem; }
    .supplier-row-actions button { padding:.45rem .65rem; border:1px solid #bfdbfe; border-radius:7px; background:#eff6ff; color:#1d4ed8; font-size:.76rem; font-weight:700; cursor:pointer; }
    .supplier-row-actions .supplier-delete-button { border-color:#fecaca; background:#fef2f2; color:#b91c1c; }
    .supplier-edit-form { display:none; margin-top:.8rem; padding-top:.8rem; border-top:1px solid #e2e8f0; }
    .supplier-edit-form.open { display:block; }
    .supplier-edit-form label { margin:.55rem 0 .25rem; }
    .supplier-edit-actions { display:flex; justify-content:flex-end; gap:.5rem; margin-top:.7rem; }
    .supplier-edit-actions button[type=button] { background:#e2e8f0; color:#334155; }
    .supplier-list-empty,.supplier-search-empty { padding:2rem 1rem; color:#64748b; text-align:center; }
    .supplier-search-empty { display:none; }
    @media(max-width:680px) { .procurement-form-grid { grid-template-columns:1fr; } .procurement-card { padding:.9rem; } }
    @media(max-width:760px) { .supplier-form { grid-template-columns:1fr; } .supplier-form button { width:100%; } }
    @media(max-width:520px) { .supplier-modal-backdrop { padding:0; } .supplier-modal { width:100%; height:100%; max-height:100dvh; border-radius:0; } .supplier-row-main { align-items:flex-start; flex-direction:column; } }
</style>
@endsection

@section('content')
<div class="procurement-page">
    @if(session('status'))<div class="procurement-notice" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<ul class="procurement-errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    <section class="procurement-card">
        <h2>Tambah Supplier</h2>
        <p class="procurement-muted">Tambahkan dan kelola data supplier/PBF di sini. Supplier yang tersimpan akan tersedia saat mengatur harga produk dan membuat pesanan.</p>
        @include('admin.partials.pbf-supplier-management')
    </section>

        <section class="procurement-card">
            <h2>Opsi 1: Kelola manual</h2>
            <p class="procurement-muted">Satu produk katalog dapat memiliki harga berbeda di banyak supplier. Data ini hanya tersedia untuk admin. Cari produk berdasarkan nama atau SKU; sistem hanya menampilkan hasil teratas agar halaman tetap ringan.</p>
            @if($suppliers->isEmpty())
                <p class="procurement-empty">Tambahkan supplier terlebih dahulu di <a href="{{ route('admin.produk-pbf.index') }}">Pesan ke PBF</a>.</p>
            @else
                <form method="POST" action="{{ route('admin.supplier-procurement.offers.store') }}">
                    @csrf
                    <div class="procurement-form-grid">
                        <div class="procurement-field">
                            <label for="offerSupplier">Supplier</label>
                            <select id="offerSupplier" name="supplier_id" required>
                                <option value="">Pilih supplier</option>
                                @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="procurement-field procurement-product-search">
                            <label for="offerMedicineSearch">Cari produk katalog</label>
                            <input id="offerMedicineSearch" type="search" placeholder="Ketik minimal 2 huruf nama atau SKU" autocomplete="off" aria-controls="offerMedicineResults" aria-autocomplete="list">
                            <input id="offerMedicineId" name="medicine_id" type="hidden" value="{{ old('medicine_id') }}" required>
                            <div id="offerMedicineResults" class="procurement-product-results" role="listbox" hidden></div>
                            <small id="offerMedicineHint" class="procurement-selection-hint" aria-live="polite">Belum ada produk dipilih.</small>
                        </div>
                        <div class="procurement-field">
                            <label for="supplierCode">Kode produk supplier</label>
                            <input id="supplierCode" name="supplier_code" value="{{ old('supplier_code') }}" maxlength="100">
                        </div>
                        <div class="procurement-field">
                            <label for="purchasePrice">Harga modal per <span id="offerUnitLabel">unit produk</span></label>
                            <input id="purchasePrice" name="purchase_price" type="number" min="0.01" step="0.01" value="{{ old('purchase_price') }}" required>
                        </div>
                        <div class="procurement-field">
                            <label for="minimumQuantity">Minimum beli (<span id="minimumUnitLabel">unit produk</span>)</label>
                            <input id="minimumQuantity" name="minimum_quantity" type="number" min="1" value="{{ old('minimum_quantity', 1) }}" required>
                        </div>
                    </div>
                    <div class="procurement-actions">
                        <button class="procurement-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan harga supplier</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="procurement-card">
            <div class="procurement-toolbar">
                <div><h2>Opsi 2: Edit lewat Excel</h2><p class="procurement-muted" style="margin:0">Pilih supplier lalu unduh daftar produk katalog. Nama produk dan ID produk sudah terisi; harga supplier yang sudah tercatat ikut ditampilkan. Ubah harga/kode/minimum, lalu unggah file yang sama. Baris dengan harga kosong akan dilewati.</p></div>
            </div>
            <form method="GET" action="{{ route('admin.supplier-procurement.offers.template') }}" class="procurement-actions" style="margin:0 0 1rem">
                <div class="procurement-field">
                    <label for="templateSupplier">Supplier untuk file Excel</label>
                    <select id="templateSupplier" name="supplier_id" required>
                        <option value="">Pilih supplier</option>
                        @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach
                    </select>
                </div>
                <button class="procurement-btn secondary" type="submit"><i class="fa-solid fa-download"></i> Ekspor daftar produk & harga</button>
            </form>
            <form method="POST" action="{{ route('admin.supplier-procurement.offers.import') }}" enctype="multipart/form-data" class="procurement-actions">
                @csrf
                <div class="procurement-field"><label for="offerImport">Pilih file XLSX atau CSV</label><input id="offerImport" type="file" name="file" accept=".xlsx,.csv,.txt" required></div>
                <button class="procurement-btn" type="submit"><i class="fa-solid fa-file-import"></i> Impor perubahan Excel</button>
            </form>
        </section>

        <section class="procurement-card">
            <h2>Daftar harga supplier</h2>
            <form method="GET" action="{{ route('admin.supplier-procurement.index') }}" class="procurement-filter-form">
                <div class="procurement-field">
                    <label for="offerListSearch">Cari produk, SKU, kode supplier</label>
                    <input id="offerListSearch" type="search" name="offer_search" value="{{ request('offer_search') }}" placeholder="Ketik nama, SKU, atau kode supplier">
                </div>
                <div class="procurement-field">
                    <label for="offerListSupplier">Filter supplier</label>
                    <select id="offerListSupplier" name="offer_supplier_id">
                        <option value="">Semua supplier</option>
                        @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('offer_supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach
                    </select>
                </div>
                <button class="procurement-btn secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            </form>
            <div class="procurement-table-wrap">
                <table class="procurement-table">
                    <thead><tr><th>Produk</th><th>Supplier</th><th>Kode supplier</th><th>Harga modal</th><th>Minimum</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td><strong>{{ $offer->medicine?->nama_obat ?? 'Produk dihapus' }}</strong><br><small>{{ $offer->medicine?->sku ?: 'Tanpa SKU' }}</small></td>
                            <td>{{ $offer->supplier?->name ?? 'Supplier dihapus' }}</td>
                            <td>{{ $offer->supplier_code ?: '—' }}</td>
                            <td class="procurement-total">Rp {{ number_format((float) $offer->purchase_price, 0, ',', '.') }}</td>
                            <td>{{ $offer->minimum_quantity }}</td>
                            <td><span class="procurement-badge">{{ $offer->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="procurement-empty">Belum ada harga produk supplier.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($offers instanceof \Illuminate\Contracts\Pagination\Paginator || $offers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                @if($offers->hasPages())
                    <nav class="procurement-pagination" aria-label="Halaman daftar harga supplier">
                        <span>Menampilkan {{ $offers->firstItem() }}–{{ $offers->lastItem() }} dari {{ $offers->total() }} produk</span>
                        <div class="procurement-pagination-links">
                            @if($offers->onFirstPage())
                                <span class="procurement-pagination-link disabled" aria-disabled="true">Sebelumnya</span>
                            @else
                                <a class="procurement-pagination-link" rel="prev" href="{{ $offers->previousPageUrl() }}">Sebelumnya</a>
                            @endif
                            @if($offers->hasMorePages())
                                <a class="procurement-pagination-link" rel="next" href="{{ $offers->nextPageUrl() }}">Next</a>
                            @else
                                <span class="procurement-pagination-link disabled" aria-disabled="true">Next</span>
                            @endif
                        </div>
                    </nav>
                @endif
            @endif
        </section>
</div>
@endsection

@section('scripts')
<script>
    function toggleSupplierForm() {
        document.getElementById('supplierFormPanel').classList.toggle('open');
    }

    function openSupplierManager() {
        document.getElementById('supplierManagerBackdrop').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSupplierManager(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('supplierManagerBackdrop').classList.remove('open');
        document.body.style.overflow = '';
    }

    function toggleSupplierEdit(id) {
        document.getElementById('supplierEdit' + id).classList.toggle('open');
    }

    const supplierManagerSearch = document.getElementById('supplierManagerSearch');
    if (supplierManagerSearch) {
        supplierManagerSearch.addEventListener('input', () => {
            const query = supplierManagerSearch.value.trim().toLocaleLowerCase();
            const rows = Array.from(document.querySelectorAll('.supplier-row'));
            let visibleCount = 0;
            rows.forEach((row) => {
                const matches = row.dataset.supplierSearch.includes(query);
                row.hidden = !matches;
                if (matches) visibleCount++;
            });
            document.getElementById('supplierManagerCount').textContent = `${visibleCount} supplier`;
            const emptyMessage = document.getElementById('supplierSearchEmpty');
            if (emptyMessage) emptyMessage.style.display = visibleCount ? 'none' : 'block';
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSupplierManager();
    });

    const productSearch = document.getElementById('offerMedicineSearch');
    if (productSearch) {
        const supplierSelect = document.getElementById('offerSupplier');
        const productId = document.getElementById('offerMedicineId');
        const results = document.getElementById('offerMedicineResults');
        const hint = document.getElementById('offerMedicineHint');
        const supplierCode = document.getElementById('supplierCode');
        const purchasePrice = document.getElementById('purchasePrice');
        const minimumQuantity = document.getElementById('minimumQuantity');
        const unitLabel = document.getElementById('offerUnitLabel');
        const minimumUnitLabel = document.getElementById('minimumUnitLabel');
        let searchTimer;
        let currentRequest;

        const updateProduct = (medicine) => {
            productId.value = medicine.id;
            productSearch.value = medicine.name + (medicine.sku ? ` · ${medicine.sku}` : '');
            unitLabel.textContent = medicine.unit || 'unit produk';
            minimumUnitLabel.textContent = `jumlah ${medicine.unit || 'unit produk'}`;
            const existing = medicine.offer;
            supplierCode.value = existing?.supplier_code || '';
            purchasePrice.value = existing?.purchase_price || '';
            minimumQuantity.value = existing?.minimum_quantity || 1;
            hint.textContent = existing
                ? `Harga supplier ini sudah ada: Rp ${Number(existing.purchase_price).toLocaleString('id-ID')} per ${medicine.unit}. Nilai dapat diperbarui.`
                : 'Belum ada harga untuk pasangan produk dan supplier ini.';
            hint.classList.toggle('is-existing', Boolean(existing));
            results.hidden = true;
            results.replaceChildren();
        };

        const searchProducts = async (query, selectedId = '') => {
            if (currentRequest) currentRequest.abort();
            currentRequest = new AbortController();
            const params = new URLSearchParams({ q: query });
            if (selectedId) params.set('medicine_id', selectedId);
            if (supplierSelect.value) params.set('supplier_id', supplierSelect.value);
            const response = await fetch(`{{ route('admin.supplier-procurement.medicines.search') }}?${params}`, {
                headers: { 'Accept': 'application/json' },
                signal: currentRequest.signal,
            });
            if (!response.ok) throw new Error('Pencarian produk gagal. Silakan coba lagi.');
            return (await response.json()).data;
        };

        productSearch.addEventListener('input', () => {
            clearTimeout(searchTimer);
            productId.value = '';
            hint.textContent = 'Pilih produk dari hasil pencarian.';
            hint.classList.remove('is-existing');
            supplierCode.value = '';
            purchasePrice.value = '';
            minimumQuantity.value = 1;
            const query = productSearch.value.trim();
            if (query.length < 2) {
                results.hidden = true;
                return;
            }
            searchTimer = setTimeout(async () => {
                try {
                    const medicines = await searchProducts(query);
                    results.replaceChildren();
                    medicines.forEach((medicine) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.setAttribute('role', 'option');
                        button.textContent = medicine.name + (medicine.sku ? ` · ${medicine.sku}` : '');
                        const detail = document.createElement('small');
                        detail.textContent = medicine.offer
                            ? `${medicine.unit} · harga supplier saat ini Rp ${Number(medicine.offer.purchase_price).toLocaleString('id-ID')}`
                            : medicine.unit;
                        button.appendChild(detail);
                        button.addEventListener('click', () => updateProduct(medicine));
                        results.appendChild(button);
                    });
                    if (medicines.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'procurement-empty';
                        empty.textContent = 'Produk tidak ditemukan.';
                        results.appendChild(empty);
                    }
                    results.hidden = false;
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        results.replaceChildren();
                        const message = document.createElement('div');
                        message.className = 'procurement-errors';
                        message.textContent = error.message;
                        results.appendChild(message);
                        results.hidden = false;
                    }
                }
            }, 250);
        });

        supplierSelect.addEventListener('change', async () => {
            if (!productId.value) return;
            try {
                const medicines = await searchProducts('', productId.value);
                if (medicines[0]) updateProduct(medicines[0]);
            } catch (error) {
                hint.textContent = error.message;
                hint.classList.remove('is-existing');
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.procurement-product-search')) results.hidden = true;
        });

        if (productId.value) {
            searchProducts('', productId.value)
                .then((medicines) => {
                    if (medicines[0]) updateProduct(medicines[0]);
                })
                .catch((error) => {
                    hint.textContent = error.message;
                });
        }
    }
</script>
@endsection
