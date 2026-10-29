

<?php $__env->startSection('title', ($editing ? 'Edit' : 'Tambah') . ' Customer - Admin Apotek Medix'); ?>
<?php $__env->startSection('page-title', $editing ? 'Edit Customer' : 'Tambah Customer'); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="max-width:900px;">
    <div class="card-title"><?php echo e($editing ? 'Perbarui data customer' : 'Buat data customer'); ?></div>
    <p style="color:#64748b;">Profil ini digunakan untuk menyatukan riwayat pesanan customer. Menghapus profil tidak akan menghapus transaksi.</p>

    <?php if($errors->any()): ?>
        <div role="alert" style="padding:12px 14px;margin-bottom:16px;border-radius:8px;background:#fee2e2;color:#991b1b;">
            <strong>Periksa kembali data yang dimasukkan:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e($editing ? route('admin.customers.update', $customer) : route('admin.customers.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($editing): ?><?php echo method_field('PUT'); ?><?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
            <div>
                <label for="buyer_type">Jenis Customer</label>
                <select id="buyer_type" name="buyer_type" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;">
                    <?php $__currentLoopData = ['umum' => 'Umum / B2C', 'apotik' => 'Apotek / B2B', 'dokter' => 'Dokter / B2B', 'pbf' => 'PBF / B2B']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(old('buyer_type', $customer->buyer_type ?: 'umum') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label for="name">Nama Customer / Pemesan</label><input id="name" name="name" value="<?php echo e(old('name', $customer->name)); ?>" required maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="requester_name">Nama Pemesan</label><input id="requester_name" name="requester_name" value="<?php echo e(old('requester_name', $customer->requester_name)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="email">Email</label><input id="email" name="email" type="email" value="<?php echo e(old('email', $customer->email)); ?>" maxlength="255" autocomplete="email" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="phone">Nomor Telepon</label><input id="phone" name="phone" type="tel" value="<?php echo e(old('phone', $customer->phone)); ?>" maxlength="255" autocomplete="tel" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="outlet_name">Nama Outlet / Apotek</label><input id="outlet_name" name="outlet_name" value="<?php echo e(old('outlet_name', $customer->outlet_name)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="pbf_name">Nama PBF</label><input id="pbf_name" name="pbf_name" value="<?php echo e(old('pbf_name', $customer->pbf_name)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="address">Alamat</label><input id="address" name="address" value="<?php echo e(old('address', $customer->address)); ?>" maxlength="2000" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="kecamatan">Kecamatan</label><input id="kecamatan" name="kecamatan" value="<?php echo e(old('kecamatan', $customer->kecamatan)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="kota">Kota / Kabupaten</label><input id="kota" name="kota" value="<?php echo e(old('kota', $customer->kota)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sia">SIA</label><input id="sia" name="sia" value="<?php echo e(old('sia', $customer->sia)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sipa">SIPA</label><input id="sipa" name="sipa" value="<?php echo e(old('sipa', $customer->sipa)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="no_izin_pbf">Nomor Izin PBF</label><input id="no_izin_pbf" name="no_izin_pbf" value="<?php echo e(old('no_izin_pbf', $customer->no_izin_pbf)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="apj">APJ</label><input id="apj" name="apj" value="<?php echo e(old('apj', $customer->apj)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
            <div><label for="sika">SIKA</label><input id="sika" name="sika" value="<?php echo e(old('sika', $customer->sika)); ?>" maxlength="255" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;"></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit" style="padding:10px 16px;border:0;border-radius:8px;background:#0052cc;color:#fff;font-weight:700;cursor:pointer;"><?php echo e($editing ? 'Simpan Perubahan' : 'Simpan Customer'); ?></button>
            <a href="<?php echo e(route('admin.customers.index')); ?>" style="padding:10px 16px;border:1px solid #cbd5e1;border-radius:8px;color:#334155;text-decoration:none;">Batal</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views\admin\customers\form.blade.php ENDPATH**/ ?>