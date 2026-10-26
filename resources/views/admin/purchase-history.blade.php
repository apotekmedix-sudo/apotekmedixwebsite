@extends('layouts.admin')

@section('title', 'Riwayat Pembelian - Admin Apotek Medix')
@section('page-title', '🧾 Riwayat Pembelian')

@section('content')

{{-- Total Omset --}}
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card" style="border-top-color:#8b5cf6;">
        <div class="stat-label">Total Omzet dari Semua Pembelian</div>
        <div class="stat-value" style="color:#8b5cf6;">{{ 'Rp ' . number_format($total_omzet, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Tabel Riwayat Pembelian --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:1.3rem;font-weight:700;color:#1e293b;">Semua Transaksi</div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <a href="{{ route('admin.purchase-history.export') }}" class="btn btn-primary btn-sm" title="Download sebagai Excel/CSV">
                <i class="fa-solid fa-download"></i> Download
            </a>
            <form id="deleteAllForm" method="POST" action="{{ route('admin.purchase-history.destroyAll') }}" onsubmit="return confirm('Yakin ingin menghapus SEMUA riwayat pembelian?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus Semua</button>
            </form>
        </div>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Pembeli</th>
                    <th>Jenis</th>
                    <th>Saluran</th>
                    <th>Pembayaran</th>
                    <th>Status Kasir</th>
                    <th>Total</th>
                    <th style="width: 15%;">Items</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="font-size:0.82rem;color:#6b7280;">{{ $order->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') }} WIB</td>
                        <td>
                            <strong>{{ $order->buyer_name ?: ($order->outlet_name ?: '-') }}</strong>
                            @if($order->requester_name && $order->requester_name !== $order->buyer_name)
                                <div style="font-size:0.75rem;color:#64748b;">{{ $order->requester_name }}</div>
                            @endif
                        </td>
                        <td>
                            @if(in_array($order->buyer_type, ['pbf', 'apotik', 'dokter'], true))
                                <span class="badge badge-primary">B2B ({{ strtoupper($order->buyer_type) }})</span>
                            @else
                                <span class="badge badge-secondary">Umum (B2C)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $order->transaction_channel === 'offline' ? 'badge-primary' : 'badge-secondary' }}">
                                {{ $order->transaction_channel === 'offline' ? 'Offline / Kios' : 'Online' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $paymentLabel = $order->payment_method;
                                if ($paymentLabel === 'Tempo' && !empty($order->payment_term_days)) {
                                    $paymentLabel .= ' (' . $order->payment_term_days . ' hari)';
                                }
                            @endphp
                            @if($order->payment_method)
                                <span style="background:#eff6ff;color:#1d4ed8;padding:0.2rem 0.55rem;border-radius:20px;font-size:0.78rem;font-weight:600;">{{ $paymentLabel }}</span>
                                @if($order->isTempoPayment())
                                    <div style="margin-top:0.3rem;font-size:0.74rem;font-weight:700;color:{{ in_array($order->tempo_due_status, ['overdue', 'due_today']) ? '#dc2626' : ($order->tempo_due_status === 'due_soon' ? '#d97706' : '#0369a1') }};">
                                        Jatuh Tempo: {{ $order->due_date_formatted }}<br>
                                        <span>({{ $order->tempo_due_label }})</span>
                                    </div>
                                @endif
                            @else
                                <span style="color:#d1d5db;font-size:0.8rem;font-style:italic;">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $isCancelled = $order->receipt_status === 'cancelled' || $order->payment_status === 'cancelled' || $order->approval_status === 'rejected';
                                $isPrinted = !$isCancelled && $order->receipt_status === 'printed';
                                $isVerified = !$isCancelled && !$isPrinted && ($order->receipt_status === 'verified' || $order->payment_status === 'paid' || $order->approval_status === 'approved');

                                if ($isCancelled) {
                                    $statusLabel = 'Dibatalkan';
                                    $statusColor = '#dc2626';
                                    $statusBg = '#fee2e2';
                                } elseif ($isPrinted) {
                                    $statusLabel = 'Selesai (Struk Dicetak)';
                                    $statusColor = '#166534';
                                    $statusBg = '#dcfce7';
                                } elseif ($isVerified) {
                                    $statusLabel = 'Diverifikasi Kasir';
                                    $statusColor = '#166534';
                                    $statusBg = '#dcfce7';
                                } else {
                                    $statusLabel = 'Menunggu Kasir';
                                    $statusColor = '#92400e';
                                    $statusBg = '#fef3c7';
                                }
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:0.3rem;align-items:flex-start;">
                                <span style="display:inline-block;padding:0.25rem 0.65rem;border-radius:999px;font-size:0.75rem;font-weight:700;color:{{ $statusColor }};background:{{ $statusBg }};">
                                    {{ $statusLabel }}
                                </span>
                                @if($order->cashier)
                                    <small style="color:#64748b;font-size:0.72rem;">Kasir: {{ $order->cashier->name }}</small>
                                @endif
                            </div>
                        </td>
                        <td style="font-weight: 700;">{{ 'Rp ' . number_format($order->effective_total, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $itemsArray = is_string($order->items) ? json_decode($order->items, true) : ($order->items ?? []);
                                $itemsArray = is_array($itemsArray) ? $itemsArray : [];
                            @endphp
                            @if(count($itemsArray) > 0)
                                <ul style="padding-left: 15px; margin: 0; font-size: 0.8rem; line-height: 1.5;">
                                    @foreach(array_slice($itemsArray, 0, 3) as $item)
                                        @php
                                            $potongan = (int) ($item['potongan'] ?? 0);
                                        @endphp
                                        <li>
                                            {{ $item['nama_obat'] ?? $item['name'] ?? 'N/A' }}
                                            <span style="color:#6b7280;font-weight:500;">(×{{ $item['quantity'] ?? $item['qty'] ?? 0 }} {{ strtolower($item['unit_label'] ?? 'box') }})</span>
                                            @if($potongan > 0)
                                                <span style="display:inline-block;margin-left:0.3rem;padding:0.15rem 0.45rem;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:0.72rem;font-weight:700;">Potongan Rp {{ number_format($potongan, 0, ',', '.') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if(count($itemsArray) > 3)
                                        <li style="color:#6b7280; margin-top: 0.3rem;"><em>+{{ count($itemsArray) - 3 }} item lainnya</em></li>
                                    @endif
                                </ul>
                            @else
                                <span style="color:#9ca3af; font-style: italic;">Tidak ada data</span>
                            @endif
                        </td>
                        <td style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                            <button class="btn btn-secondary btn-sm" data-order-id="{{ $order->id }}" onclick="fetchAndShowDetailModal({{ $order->id }})">
                                <i class="fa-solid fa-eye"></i> Lihat
                            </button>
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.purchase-history.purchase-order', $order->id) }}" target="_blank" rel="noopener">
                                <i class="fa-solid fa-file-signature"></i> Surat Pesanan
                            </a>
                            <form method="POST" action="{{ route('admin.purchase-history.destroy', $order->id) }}" onsubmit="return confirmDeleteOne(event, '{{ addslashes($order->buyer_name) }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;color:#6b7280;padding:2rem;">
                            Belum ada riwayat pembelian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    <div class="pagination-container" style="margin-top: 1.5rem;">
        {{ $orders->links() }}
    </div>
</div>

{{-- Modal Detil --}}
<div id="detail-modal" class="modal-backdrop" style="display:none;" onclick="closeDetailModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h5 class="modal-title">Detail Pembelian</h5>
            <button type="button" class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="detail-content">
                {{-- Content will be injected by JS --}}
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1040;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #fff;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: slide-down 0.3s ease-out;
    overflow: hidden;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}
.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}
.modal-close {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    overflow-x: hidden;
}
.detail-grid {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 0.75rem 1.5rem;
}
.detail-label {
    font-weight: 600;
    color: #4b5563;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.items-table th, .items-table td {
    padding: 0.5rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.items-table th {
    background-color: #f9fafb;
}

@keyframes slide-down {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
@endsection

@section('scripts')
<script>
    const ordersData = {
        @foreach($orders as $order)
            {{ $order->id }}: @json($order),
        @endforeach
    };

    function fetchAndShowDetailModal(orderId) {
        const order = ordersData[orderId];
        if (!order) {
            alert('Data order tidak ditemukan');
            return;
        }
        showDetailModal(order);
    }

    function showDetailModal(order) {
        const modal = document.getElementById('detail-modal');
        const content = document.getElementById('detail-content');

        let items = order.items || [];
        if (typeof items === 'string') {
            try {
                items = JSON.parse(items);
            } catch (e) {
                items = [];
            }
        }

        const itemsHtml = (items && Array.isArray(items) && items.length > 0)
            ? items.map((item) => {
                const namaObat = item.nama_obat || item.name || 'N/A';
                const qty = item.quantity || item.qty || 0;
                const harga = item.harga || item.price || 0;
                const subtotal = qty * harga;
                const potongan = Number(item.potongan || 0);
                const potonganHtml = potongan > 0
                    ? `<span style="display:inline-block;margin-top:0.25rem;padding:0.18rem 0.5rem;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:0.72rem;font-weight:700;">Potongan Rp ${Number(potongan).toLocaleString('id-ID')}</span>`
                    : '<span style="color:#9ca3af;font-size:0.8rem;">Tidak ada potongan</span>';

                return `
                    <tr>
                        <td>${namaObat}</td>
                        <td>${qty}</td>
                        <td>Rp ${Number(harga).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(subtotal).toLocaleString('id-ID')}</td>
                        <td>${potonganHtml}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="5" style="text-align:center;color:#9ca3af;">Tidak ada data item</td></tr>';

        const isCancelled = order.receipt_status === 'cancelled' || order.payment_status === 'cancelled' || order.approval_status === 'rejected';
        const isPrinted = !isCancelled && order.receipt_status === 'printed';
        const isVerified = !isCancelled && !isPrinted && (order.receipt_status === 'verified' || order.payment_status === 'paid' || order.approval_status === 'approved');
        const statusText = isCancelled ? 'Dibatalkan' : (isPrinted ? 'Selesai (Struk Dicetak)' : (isVerified ? 'Diverifikasi Kasir' : 'Menunggu Kasir'));
        const statusColor = isCancelled ? '#dc2626' : ((isPrinted || isVerified) ? '#166534' : '#92400e');
        const statusBg = isCancelled ? '#fee2e2' : ((isPrinted || isVerified) ? '#dcfce7' : '#fef3c7');

        let detailHtml = `
            <div class="detail-grid">
                <div class="detail-label">Nama Pembeli / Outlet</div>
                <div>: <strong>${order.buyer_name || order.outlet_name || '-'}</strong></div>

                ${order.requester_name ? `<div class="detail-label">Apoteker / Dokter</div><div>: ${order.requester_name}</div>` : ''}

                <div class="detail-label">Jenis</div>
                <div>: ${['pbf', 'apotik', 'dokter'].includes(order.buyer_type) ? 'B2B (' + String(order.buyer_type).toUpperCase() + ')' : 'Umum (B2C)'}</div>

                <div class="detail-label">Kontak</div>
                <div>: ${order.phone || '-'}</div>

                <div class="detail-label">Alamat</div>
                <div>: ${order.address || '-'}${order.kecamatan ? ', ' + order.kecamatan : ''}${order.kota ? ', ' + order.kota : ''}</div>

                <div class="detail-label">Waktu Pesan</div>
                <div>: ${order.created_at_wib || '-'}</div>

                <div class="detail-label">Waktu Diverifikasi</div>
                <div>: ${order.verified_at_wib || '-'}</div>

                <div class="detail-label">Waktu Dibayar</div>
                <div>: ${order.paid_at_wib || '-'}</div>

                <div class="detail-label">Waktu Dicetak</div>
                <div>: ${order.printed_at_wib || '-'}</div>

                ${order.due_date_formatted ? `<div class="detail-label">Jatuh Tempo</div><div>: <strong style="color:#dc2626;">${order.due_date_formatted} (${order.tempo_due_label || '-'})</strong></div>` : ''}
        `;

        if (order.sia || order.sipa || ['pbf', 'apotik', 'dokter'].includes(order.buyer_type)) {
            detailHtml += `
                <div class="detail-label">SIA</div>
                <div>: ${order.sia || '-'}</div>
                <div class="detail-label">SIPA</div>
                <div>: ${order.sipa || '-'}</div>
            `;
        }

        detailHtml += `
            </div>
            <h6 style="margin-top: 1.5rem; margin-bottom: 0.5rem; font-weight: 600;">Daftar Item</h6>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Nama Obat</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Potongan</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            <div style="margin-top: 1rem; padding: 0.75rem 0.9rem; background: #f8fafc; border-radius: 8px;">
                <div style="font-weight: 700; font-size: 1.05rem;">Status Kasir: <span style="display:inline-block;padding:0.25rem 0.6rem;border-radius:999px;font-size:0.8rem;font-weight:700;color:${statusColor};background:${statusBg};">${statusText}</span></div>
                <div style="margin-top: 0.35rem; font-weight: 700; font-size: 1.05rem;">Total Efektif: Rp ${Number(order.effective_total || 0).toLocaleString('id-ID')}</div>
            </div>
            ${order.payment_method ? `<div style="margin-top:0.75rem;padding:0.6rem 0.9rem;background:#eff6ff;border-radius:8px;font-size:0.9rem;color:#1d4ed8;"><i class="fa-solid fa-credit-card" style="margin-right:0.4rem;"></i> <strong>Metode Pembayaran:</strong> ${order.payment_method === 'Tempo' && order.payment_term_days ? `${order.payment_method} (${order.payment_term_days} hari)` : order.payment_method}</div>` : ''}
        `;

        content.innerHTML = detailHtml;
        modal.style.display = 'flex';
    }

    function closeDetailModal() {
        const modal = document.getElementById('detail-modal');
        modal.style.display = 'none';
    }

    function confirmDeleteOne(e, buyerName) {
        if (!confirm('Hapus riwayat pembelian untuk "' + buyerName + '" ?')) {
            e.preventDefault();
            return false;
        }
        return true;
    }
</script>
@endsection
