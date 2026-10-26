

<?php $__env->startSection('title', 'Promo & Diskon - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', '🏷️ Promo & Diskon'); ?>

<?php $__env->startSection('styles'); ?>
<style>
  /* Tab Navigation Header */
  .promo-tabs-bar {
    display: inline-flex;
    gap: 0.5rem;
    background: #ffffff;
    padding: 0.45rem;
    border-radius: 0.85rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }
  .promo-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 1.25rem;
    border-radius: 0.6rem;
    border: 1.5px solid transparent;
    background: transparent;
    color: #475569;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .promo-tab-btn:hover {
    color: #0F172A;
    background: #f8fafc;
  }
  .promo-tab-btn.active {
    background: #0F172A;
    color: #ffffff;
    border-color: #06B6D4;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.14);
  }
  .promo-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.12rem 0.52rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    background: #e2e8f0;
    color: #334155;
  }
  .promo-tab-btn.active .promo-tab-count {
    background: #06B6D4;
    color: #0F172A;
  }
  .promo-tab-panel {
    display: none;
  }
  .promo-tab-panel.active {
    display: block;
  }

  /* Voucher Tab Styles */
  .dc-grid-layout {
    display: grid;
    grid-template-columns: minmax(320px, 390px) minmax(0, 1fr);
    gap: 1.5rem;
    align-items: start;
  }
  .dc-form-card {
    background: #ffffff;
    border-radius: 0.85rem;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    padding: 1.5rem;
    border-top: 4px solid #0F172A;
  }
  .dc-form-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .dc-form-sub {
    font-size: 0.83rem;
    color: #64748b;
    margin: 0 0 1.25rem;
  }
  .dc-field {
    margin-bottom: 1rem;
  }
  .dc-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.38rem;
  }
  .dc-input, .dc-select {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 0.55rem;
    font-size: 0.9rem;
    color: #0F172A;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
  }
  .dc-input:focus, .dc-select:focus {
    border-color: #06B6D4;
    background: #ffffff;
  }
  .dc-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }
  .dc-quota-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 0.55rem;
    border: 1px solid #e2e8f0;
  }
  .dc-quota-tab {
    padding: 0.55rem 0.65rem;
    border-radius: 0.45rem;
    border: 1.5px solid transparent;
    background: transparent;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    text-align: center;
    transition: all 0.15s;
  }
  .dc-quota-tab.active {
    background: #ffffff;
    border-color: #06B6D4;
    color: #0F172A;
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
  }
  .dc-submit-btn {
    width: 100%;
    padding: 0.78rem 1rem;
    background: #0F172A;
    color: #ffffff;
    border: 1.5px solid #06B6D4;
    border-radius: 0.6rem;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
  }
  .dc-submit-btn:hover {
    background: #1E293B;
    transform: translateY(-1px);
  }
  .dc-code-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    background: #f1f5f9;
    border: 1.5px dashed #0E7490;
    color: #0F172A;
    border-radius: 0.45rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.9rem;
    font-weight: 800;
    letter-spacing: 0.04em;
  }
  .dc-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.22rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
  }
  .dc-badge-active {
    background: #d1fae5;
    color: #065f46;
  }
  .dc-badge-inactive {
    background: #f1f5f9;
    color: #64748b;
  }
  .dc-badge-exhausted {
    background: #fee2e2;
    color: #991b1b;
  }
  .dc-badge-unlimited {
    background: #ecfeff;
    color: #0e7490;
    border: 1px solid #a5f3fc;
  }
  .dc-progress-wrap {
    width: 100%;
    max-width: 190px;
  }
  .dc-progress-bar {
    height: 7px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 4px;
  }
  .dc-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #06B6D4, #10B981);
    border-radius: 999px;
  }
  .dc-progress-fill.full {
    background: #ef4444;
  }
  .dc-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
  }
  .dc-edit-row {
    display: none;
    background: #f8fafc;
  }
  .dc-edit-row.open {
    display: table-row;
  }
  .dc-edit-box {
    padding: 1rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.65rem;
    background: #ffffff;
  }

  @media (max-width: 992px) {
    .dc-grid-layout {
      grid-template-columns: 1fr;
    }
  }
  @media (max-width: 640px) {
    .promo-tabs-bar {
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    .promo-tab-btn {
      justify-content: center;
      padding: 0.65rem 0.5rem;
      font-size: 0.8rem;
    }
  }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $discountCodes = $discountCodes ?? collect();
  $currentTab = $activeTab ?? request('tab', 'channels');
  if (old('code') !== null) {
      $currentTab = 'vouchers';
  }
  $totalCodes = $discountCodes->count();
  $activeCodes = $discountCodes->filter(fn($c) => $c->isAvailable())->count();
  $totalUsed = $discountCodes->sum('used_count');
?>

<!-- 2-TAB NAVIGATION -->
<div class="promo-tabs-bar" role="tablist" aria-label="Pilih Jenis Promo">
  <button
    type="button"
    id="tabBtnChannels"
    role="tab"
    aria-selected="<?php echo e($currentTab === 'channels' ? 'true' : 'false'); ?>"
    class="promo-tab-btn <?php echo e($currentTab === 'channels' ? 'active' : ''); ?>"
    onclick="switchPromoTab('channels')"
  >
    <i class="fa-solid fa-percent"></i>
    <span>Diskon per Kanal</span>
    <span class="promo-tab-count"><?php echo e($medicines->total()); ?></span>
  </button>

  <button
    type="button"
    id="tabBtnVouchers"
    role="tab"
    aria-selected="<?php echo e($currentTab === 'vouchers' ? 'true' : 'false'); ?>"
    class="promo-tab-btn <?php echo e($currentTab === 'vouchers' ? 'active' : ''); ?>"
    onclick="switchPromoTab('vouchers')"
  >
    <i class="fa-solid fa-ticket"></i>
    <span>Kode Diskon Voucher</span>
    <span class="promo-tab-count"><?php echo e($totalCodes); ?></span>
  </button>
</div>

<!-- ========================================================= -->
<!-- TAB 1: DISKON PRODUK PER KANAL                            -->
<!-- ========================================================= -->
<div id="tabPanelChannels" class="promo-tab-panel <?php echo e($currentTab === 'channels' ? 'active' : ''); ?>" role="tabpanel">
  <div class="card">
    <div class="card-title">Atur Diskon per Kanal</div>
    <p style="margin:0 0 1rem;color:#64748b;">Atur persentase promo secara terpisah untuk harga B2B dan B2C. Isi 0 untuk menonaktifkan promo pada kanal tersebut.</p>

    <form method="GET" action="<?php echo e(route('admin.promotions.index')); ?>" style="margin-bottom:1rem;">
      <input type="hidden" name="tab" value="channels">
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Cari nama produk, SKU, indikasi, komposisi, sediaan (sirup/tablet), dosis, atau golongan..." style="flex:1;min-width:220px;padding:.65rem .8rem;border:1px solid #cbd5e1;border-radius:8px;">
        <button class="btn btn-primary" type="submit">Cari</button>
        <?php if($search || request('indikasi') || request('komposisi') || request('sediaan') || request('dosis') || request('golongan')): ?>
          <a class="btn btn-secondary" href="<?php echo e(route('admin.promotions.index', ['tab' => 'channels'])); ?>">Reset</a>
        <?php endif; ?>
      </div>
      <?php echo $__env->make('partials.product-attribute-filters', ['resetUrl' => route('admin.promotions.index', ['tab' => 'channels'])], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Harga B2B<br><small>Diskon %</small></th>
            <th>Harga B2C<br><small>Diskon %</small></th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
              $channelPrices = [
                'b2b' => [
                  'field' => 'promo_b2b_percent',
                  'percent' => $medicine->promo_b2b_percent,
                  'base' => $medicine->getChannelBasePrice('b2b'),
                ],
                'b2c' => [
                  'field' => 'promo_b2c_percent',
                  'percent' => $medicine->promo_b2c_percent,
                  'base' => $medicine->getChannelBasePrice('b2c'),
                ],
              ];
            ?>
            <tr>
              <td>
                <strong><?php echo e($medicine->nama_obat); ?></strong>
                <div style="font-size:.78rem;color:#64748b;"><?php echo e($medicine->sku ?: $medicine->kategori); ?> · <?php echo e($medicine->kelompok === 'PBF' ? 'Produk B2B' : 'Produk B2C'); ?></div>
                <form id="promotion-<?php echo e($medicine->id); ?>" method="POST" action="<?php echo e(route('admin.promotions.update', $medicine)); ?>">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('PUT'); ?>
                  <input type="hidden" name="search" value="<?php echo e($search); ?>">
                </form>
              </td>
              <?php $__currentLoopData = $channelPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel => $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                  <div class="promo-channel-price" data-base-price="<?php echo e($price['base']); ?>" style="min-width:155px;">
                    <div style="margin-bottom:.35rem;color:#64748b;font-size:.76rem;">Harga asli: <span>Rp <?php echo e(number_format($price['base'], 0, ',', '.')); ?></span></div>
                    <label style="display:flex;align-items:center;gap:.35rem;margin-bottom:.4rem;">
                      <input class="promo-percent-input" form="promotion-<?php echo e($medicine->id); ?>" type="number" name="<?php echo e($price['field']); ?>" value="<?php echo e($price['percent']); ?>" min="0" max="100" step="1" required aria-label="Diskon <?php echo e($channel); ?> untuk <?php echo e($medicine->nama_obat); ?>" style="width:72px;padding:.45rem;border:1px solid #cbd5e1;border-radius:7px;">
                      <span>% diskon</span>
                    </label>
                    <strong class="promo-calculated-price" aria-live="polite" style="display:block;color:#047857;font-size:.8rem;">Harga promo: Rp <?php echo e(number_format($medicine->getChannelPromoPrice($channel), 0, ',', '.')); ?></strong>
                  </div>
                </td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <td><button form="promotion-<?php echo e($medicine->id); ?>" class="btn btn-primary btn-sm" type="submit">Simpan</button></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" style="text-align:center;padding:2rem;color:#64748b;">Produk tidak ditemukan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:1rem;"><?php echo e($medicines->links()); ?></div>
  </div>
</div>

<!-- ========================================================= -->
<!-- TAB 2: KODE DISKON VOUCHER (CHECKOUT)                     -->
<!-- ========================================================= -->
<div id="tabPanelVouchers" class="promo-tab-panel <?php echo e($currentTab === 'vouchers' ? 'active' : ''); ?>" role="tabpanel">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total Kode Diskon</div>
      <div class="stat-value"><?php echo e($totalCodes); ?></div>
    </div>
    <div class="stat-card" style="border-top-color:#10B981;">
      <div class="stat-label">Kode Aktif &amp; Tersedia</div>
      <div class="stat-value" style="color:#059669;"><?php echo e($activeCodes); ?></div>
    </div>
    <div class="stat-card" style="border-top-color:#06B6D4;">
      <div class="stat-label">Total Pemakaian User</div>
      <div class="stat-value" style="color:#0E7490;"><?php echo e($totalUsed); ?></div>
    </div>
  </div>

  <div class="dc-grid-layout">
    <!-- FORM TAMBAH KODE DISKON BARU -->
    <div class="dc-form-card">
      <h2 class="dc-form-title"><i class="fa-solid fa-plus-circle" style="color:#0E7490;"></i> Buat Kode Diskon Baru</h2>
      <p class="dc-form-sub">Tambahkan kode promo yang dapat dimasukkan pembeli pada halaman pembayaran/checkout.</p>

      <form action="<?php echo e(route('admin.discount-codes.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="dc-field">
          <label class="dc-label" for="dc_code">Kode Diskon *</label>
          <input type="text" id="dc_code" name="code" class="dc-input" placeholder="Contoh: MEDIX10 atau HEMAT25K" value="<?php echo e(old('code')); ?>" style="text-transform:uppercase;font-weight:700;" required>
        </div>

        <div class="dc-field">
          <label class="dc-label" for="dc_description">Keterangan Promo (Opsional)</label>
          <input type="text" id="dc_description" name="description" class="dc-input" placeholder="Contoh: Diskon Spesial Pelanggan" value="<?php echo e(old('description')); ?>">
        </div>

        <div class="dc-row-2">
          <div class="dc-field">
            <label class="dc-label" for="dc_type">Jenis Potongan *</label>
            <select id="dc_type" name="discount_type" class="dc-select" onchange="onCreateTypeChange()">
              <option value="percent" <?php echo e(old('discount_type', 'percent') === 'percent' ? 'selected' : ''); ?>>Persentase (%)</option>
              <option value="fixed" <?php echo e(old('discount_type') === 'fixed' ? 'selected' : ''); ?>>Nominal (Rp)</option>
            </select>
          </div>

          <div class="dc-field">
            <label class="dc-label" for="dc_value" id="dcValueLabel">Nilai Diskon (%) *</label>
            <input type="number" id="dc_value" name="discount_value" min="1" step="1" class="dc-input" placeholder="Contoh: 10" value="<?php echo e(old('discount_value', 10)); ?>" required>
          </div>
        </div>

        <div class="dc-field">
          <label class="dc-label">Pengaturan Kuota Pemakaian *</label>
          <input type="hidden" id="dc_limit_mode" name="limit_mode" value="<?php echo e(old('limit_mode', 'limited')); ?>">
          <div class="dc-quota-tabs">
            <button type="button" id="tabLimitBtn" class="dc-quota-tab <?php echo e(old('limit_mode', 'limited') === 'limited' ? 'active' : ''); ?>" onclick="setCreateLimitMode('limited')">
              <i class="fa-solid fa-users"></i> Dibatasi User
            </button>
            <button type="button" id="tabUnlimitedBtn" class="dc-quota-tab <?php echo e(old('limit_mode') === 'unlimited' ? 'active' : ''); ?>" onclick="setCreateLimitMode('unlimited')">
              <i class="fa-solid fa-infinity"></i> Tidak Dibatasi
            </button>
          </div>
        </div>

        <div class="dc-field" id="dcUsageLimitWrap" style="<?php echo e(old('limit_mode', 'limited') === 'unlimited' ? 'display:none;' : ''); ?>">
          <label class="dc-label" for="dc_usage_limit">Batas Maksimal User yang Bisa Pakai *</label>
          <input type="number" id="dc_usage_limit" name="usage_limit" min="1" step="1" class="dc-input" placeholder="Contoh: 100" value="<?php echo e(old('usage_limit', 100)); ?>">
          <small style="display:block;margin-top:4px;font-size:0.76rem;color:#64748b;">Misal diisi 100, maka kode otomatis tidak bisa dipakai setelah 100 transaksi.</small>
        </div>

        <div class="dc-field" style="margin-top:0.75rem;">
          <label style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.86rem;font-weight:600;color:#334155;cursor:pointer;">
            <input type="checkbox" name="is_active" value="1" checked style="width:17px;height:17px;accent-color:#0E7490;">
            <span>Langsung aktifkan kode diskon ini</span>
          </label>
        </div>

        <button type="submit" class="dc-submit-btn">
          <i class="fa-solid fa-check"></i> Simpan Kode Diskon
        </button>
      </form>
    </div>

    <!-- TABEL DAFTAR KODE DISKON -->
    <div class="table-container" style="padding:1.25rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
        <div>
          <h2 style="font-size:1.1rem;font-weight:700;color:#0F172A;margin:0;">Daftar Kode Diskon</h2>
          <p style="font-size:0.82rem;color:#64748b;margin:2px 0 0;">Kelola kuota user, ubah batas pemakaian, atau nonaktifkan kode kapan saja.</p>
        </div>
      </div>

      <?php if($discountCodes->isEmpty()): ?>
        <div style="text-align:center;padding:3rem 1rem;color:#64748b;">
          <i class="fa-solid fa-ticket" style="font-size:2.5rem;color:#cbd5e1;margin-bottom:0.75rem;display:block;"></i>
          <p style="margin:0;font-weight:600;">Belum ada kode diskon yang dibuat.</p>
          <p style="margin:4px 0 0;font-size:0.84rem;">Silakan tambahkan kode diskon baru melalui formulir di samping.</p>
        </div>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Potongan</th>
              <th>Kuota &amp; Pemakaian</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $discountCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $exhausted = !$dc->is_unlimited && $dc->usage_limit !== null && $dc->used_count >= $dc->usage_limit;
                $pctUsed = (!$dc->is_unlimited && $dc->usage_limit > 0)
                  ? min(100, round(($dc->used_count / $dc->usage_limit) * 100))
                  : 0;
              ?>
              <tr>
                <td>
                  <div class="dc-code-pill"><?php echo e($dc->code); ?></div>
                  <?php if($dc->description): ?>
                    <div style="font-size:0.78rem;color:#64748b;margin-top:4px;"><?php echo e($dc->description); ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <strong style="font-size:0.95rem;color:#0E7490;"><?php echo e($dc->formatted_value); ?></strong>
                  <div style="font-size:0.74rem;color:#64748b;"><?php echo e($dc->discount_type === 'percent' ? 'Persentase' : 'Nominal Tetap'); ?></div>
                </td>
                <td>
                  <?php if($dc->is_unlimited): ?>
                    <span class="dc-badge dc-badge-unlimited">
                      <i class="fa-solid fa-infinity"></i> Tidak Dibatasi
                    </span>
                    <div style="font-size:0.76rem;color:#475569;margin-top:4px;">
                      Sudah dipakai: <strong><?php echo e($dc->used_count); ?></strong> user
                    </div>
                  <?php else: ?>
                    <div class="dc-progress-wrap">
                      <div style="display:flex;justify-content:space-between;font-size:0.8rem;font-weight:600;color:#1E293B;">
                        <span><?php echo e($dc->used_count); ?> / <?php echo e($dc->usage_limit); ?> user</span>
                        <span style="color:<?php echo e($exhausted ? '#dc2626' : '#059669'); ?>;">
                          Sisa <?php echo e($dc->remainingQuota()); ?>

                        </span>
                      </div>
                      <div class="dc-progress-bar">
                        <div class="dc-progress-fill <?php echo e($exhausted ? 'full' : ''); ?>" style="width: <?php echo e($pctUsed); ?>%;"></div>
                      </div>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if(!$dc->is_active): ?>
                    <span class="dc-badge dc-badge-inactive">Nonaktif</span>
                  <?php elseif($exhausted): ?>
                    <span class="dc-badge dc-badge-exhausted">Kuota Habis</span>
                  <?php else: ?>
                    <span class="dc-badge dc-badge-active">Aktif</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="dc-actions">
                    <button type="button" class="btn btn-sm" style="background:#e0f2fe;color:#0369a1;" onclick="toggleEditRow(<?php echo e($dc->id); ?>)">
                      <i class="fa-solid fa-pen-to-square"></i> Atur
                    </button>

                    <form action="<?php echo e(route('admin.discount-codes.toggle', $dc)); ?>" method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="btn btn-sm" style="background:<?php echo e($dc->is_active ? '#fef3c7' : '#d1fae5'); ?>;color:<?php echo e($dc->is_active ? '#92400e' : '#065f46'); ?>;">
                        <?php echo e($dc->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                      </button>
                    </form>

                    <form action="<?php echo e(route('admin.discount-codes.destroy', $dc)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Hapus kode diskon <?php echo e($dc->code); ?>?');">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              <!-- BARIS EDIT INLINE -->
              <tr id="edit-row-<?php echo e($dc->id); ?>" class="dc-edit-row">
                <td colspan="5">
                  <div class="dc-edit-box">
                    <form action="<?php echo e(route('admin.discount-codes.update', $dc)); ?>" method="POST">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('PUT'); ?>
                      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.75rem;align-items:end;">
                        <div>
                          <label class="dc-label">Kode Diskon</label>
                          <input type="text" name="code" class="dc-input" value="<?php echo e($dc->code); ?>" style="text-transform:uppercase;font-weight:700;" required>
                        </div>
                        <div>
                          <label class="dc-label">Keterangan</label>
                          <input type="text" name="description" class="dc-input" value="<?php echo e($dc->description); ?>" placeholder="Keterangan">
                        </div>
                        <div>
                          <label class="dc-label">Jenis Potongan</label>
                          <select name="discount_type" class="dc-select">
                            <option value="percent" <?php echo e($dc->discount_type === 'percent' ? 'selected' : ''); ?>>Persentase (%)</option>
                            <option value="fixed" <?php echo e($dc->discount_type === 'fixed' ? 'selected' : ''); ?>>Nominal (Rp)</option>
                          </select>
                        </div>
                        <div>
                          <label class="dc-label">Nilai Diskon</label>
                          <input type="number" name="discount_value" min="1" class="dc-input" value="<?php echo e($dc->discount_value); ?>" required>
                        </div>
                        <div>
                          <label class="dc-label">Mode Kuota</label>
                          <select name="limit_mode" class="dc-select" onchange="toggleRowLimitInput(<?php echo e($dc->id); ?>, this.value)">
                            <option value="limited" <?php echo e(!$dc->is_unlimited ? 'selected' : ''); ?>>Dibatasi Kuota User</option>
                            <option value="unlimited" <?php echo e($dc->is_unlimited ? 'selected' : ''); ?>>Tidak Dibatasi (Unlimited)</option>
                          </select>
                        </div>
                        <div id="row-limit-wrap-<?php echo e($dc->id); ?>" style="<?php echo e($dc->is_unlimited ? 'display:none;' : ''); ?>">
                          <label class="dc-label">Batas User</label>
                          <input type="number" name="usage_limit" min="1" class="dc-input" value="<?php echo e($dc->usage_limit ?? 100); ?>">
                        </div>
                      </div>

                      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;margin-top:0.9rem;padding-top:0.75rem;border-top:1px solid #e2e8f0;">
                        <div style="display:flex;gap:1.25rem;flex-wrap:wrap;">
                          <label style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.82rem;font-weight:600;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?php echo e($dc->is_active ? 'checked' : ''); ?>>
                            <span>Status Aktif</span>
                          </label>
                          <label style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.82rem;font-weight:600;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="reset_used" value="1">
                            <span>Reset pemakaian ke 0 (Saat ini: <?php echo e($dc->used_count); ?>)</span>
                          </label>
                        </div>

                        <div style="display:flex;gap:0.5rem;">
                          <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditRow(<?php echo e($dc->id); ?>)">Batal</button>
                          <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function switchPromoTab(tabName) {
    const isVouchers = tabName === 'vouchers';
    const btnChannels = document.getElementById('tabBtnChannels');
    const btnVouchers = document.getElementById('tabBtnVouchers');
    const panelChannels = document.getElementById('tabPanelChannels');
    const panelVouchers = document.getElementById('tabPanelVouchers');

    if (btnChannels && btnVouchers && panelChannels && panelVouchers) {
        btnChannels.classList.toggle('active', !isVouchers);
        btnChannels.setAttribute('aria-selected', !isVouchers ? 'true' : 'false');
        btnVouchers.classList.toggle('active', isVouchers);
        btnVouchers.setAttribute('aria-selected', isVouchers ? 'true' : 'false');

        panelChannels.classList.toggle('active', !isVouchers);
        panelVouchers.classList.toggle('active', isVouchers);

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
    }
}

document.querySelectorAll('.promo-channel-price').forEach((priceBox) => {
    const input = priceBox.querySelector('.promo-percent-input');
    const output = priceBox.querySelector('.promo-calculated-price');
    const basePrice = Number(priceBox.dataset.basePrice || 0);

    const updateCalculatedPrice = () => {
        const percentage = Math.min(100, Math.max(0, Number(input.value) || 0));
        const discountedPrice = Math.round(basePrice * (100 - percentage)) / 100;
        output.textContent = 'Harga promo: ' + new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(discountedPrice);
    };

    input.addEventListener('input', updateCalculatedPrice);
    input.addEventListener('change', updateCalculatedPrice);
});

function onCreateTypeChange() {
    const type = document.getElementById('dc_type').value;
    const lbl = document.getElementById('dcValueLabel');
    const inp = document.getElementById('dc_value');
    if (type === 'percent') {
        lbl.textContent = 'Nilai Diskon (%) *';
        inp.placeholder = 'Contoh: 10';
        inp.max = '100';
    } else {
        lbl.textContent = 'Nominal Potongan (Rp) *';
        inp.placeholder = 'Contoh: 25000';
        inp.removeAttribute('max');
    }
}

function setCreateLimitMode(mode) {
    document.getElementById('dc_limit_mode').value = mode;
    document.getElementById('tabLimitBtn').classList.toggle('active', mode === 'limited');
    document.getElementById('tabUnlimitedBtn').classList.toggle('active', mode === 'unlimited');
    document.getElementById('dcUsageLimitWrap').style.display = mode === 'limited' ? 'block' : 'none';
}

function toggleEditRow(id) {
    const row = document.getElementById('edit-row-' + id);
    if (row) row.classList.toggle('open');
}

function toggleRowLimitInput(id, mode) {
    const wrap = document.getElementById('row-limit-wrap-' + id);
    if (wrap) wrap.style.display = mode === 'limited' ? 'block' : 'none';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/admin/promotions/index.blade.php ENDPATH**/ ?>