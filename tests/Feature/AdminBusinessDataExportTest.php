<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\PurchaseHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminBusinessDataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_export_contains_template_fields_sku_promotions_and_image_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Medicine::create([
            'sku' => 'SKU-EXPORT-01',
            'nama_obat' => 'Produk Ekspor',
            'kategori' => 'PT Principal',
            'kategori_produk' => 'OBAT',
            'harga' => 15500,
            'harga_modal' => 10000,
            'harga_internal' => 12000,
            'harga_grosir' => 14000,
            'harga_retail' => 16000,
            'stok' => 8,
            'stock_base_units' => 8,
            'deskripsi' => 'Produk untuk tes export',
            'gambar' => 'medicines/produk-export.jpg',
            'gambar_tambahan' => ['medicines/produk-export-2.jpg'],
            'promo_b2b_percent' => 10,
            'promo_b2c_percent' => 15,
            'is_resep' => true,
            'is_grosir' => true,
        ]);
        $originalId = Medicine::where('sku', 'SKU-EXPORT-01')->value('id');

        $response = $this->actingAs($admin)->get(route('admin.produk.export'));

        $response->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
        $content = $response->getContent();
        foreach ([
            'PRINCIPLE',
            'KATEGORI',
            'DESKRIPSI',
            'OBAT',
            'STOK SATUAN',
            'LINK FOTO',
            'HARGA JUAL B2B',
            'HARGA JUAL B2C',
            'HARGA INTERNAL',
            '12000',
            'http://localhost/storage/medicines/produk-export.jpg',
            'LINK FOTO 2',
            'http://localhost/storage/medicines/produk-export-2.jpg',
        ] as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
        foreach (['STOK FISIK UNIT', 'STOK DIRESERVASI UNIT', 'STOK TERSEDIA UNIT', 'STRIP PER KEMASAN', 'TABLET PER STRIP', 'HARGA JUAL PER STRIP', 'HARGA JUAL PER TABLET'] as $removed) {
            $this->assertStringNotContainsString($removed, $content);
        }
        foreach (['LINK FOTO UTAMA', 'ID PRODUK', 'PROMO B2C PERCENT', 'GAMBAR TAMBAHAN', 'HARGA JUAL INTERNAL'] as $unexpected) {
            $this->assertStringNotContainsString($unexpected, $content);
        }
        $this->assertLessThan(strpos($content, 'KATEGORI'), strpos($content, 'GOLONGAN'));
        $this->assertLessThan(strpos($content, 'KOMPOSISI'), strpos($content, 'KATEGORI'));

        $path = tempnam(sys_get_temp_dir(), 'product-export');
        file_put_contents($path, $content);
        $import = new UploadedFile(
            $path,
            'produk.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $this->actingAs($admin)->post(route('admin.produk.import.process'), [
            'file' => $import,
            'mode' => 'bulk_edit',
        ])->assertRedirect();
        @unlink($path);

        $product = Medicine::where('nama_obat', 'Produk Ekspor')->firstOrFail();
        $this->assertSame($originalId, $product->id);
        $this->assertSame(10, $product->promo_b2b_percent);
        $this->assertSame(15, $product->promo_b2c_percent);
        $this->assertTrue($product->is_resep);
        $this->assertTrue($product->is_grosir);
        $this->assertSame('OBAT', $product->kategori_produk);
        $this->assertSame('Produk untuk tes export', $product->deskripsi);
        $this->assertSame(15500.0, (float) $product->harga);
        $this->assertSame('medicines/produk-export.jpg', $product->gambar);
        $this->assertSame(['medicines/produk-export-2.jpg'], $product->gambar_tambahan);
    }

    public function test_product_export_rows_update_by_id_when_sku_or_name_is_edited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Medicine::create([
            'sku' => 'SKU-OLD',
            'nama_obat' => 'Nama Lama',
            'kategori' => 'Principal Lama',
            'harga' => 10000,
            'harga_modal' => 5000,
            'harga_retail' => 11000,
            'stok' => 2,
            'stock_base_units' => 2,
            'deskripsi' => 'Deskripsi lama',
            'gambar' => 'medicines/lama.jpg',
            'gambar_tambahan' => ['medicines/lama-2.jpg'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'edited-products');
        $stream = fopen($path, 'wb');
        fputcsv($stream, [
            'NO', 'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'GOLONGAN', 'KATEGORI', 'KOMPOSISI', 'INDIKASI',
            'SATUAN', 'KEMASAN', 'SEDIAAN', 'STOK SATUAN',
            'MODAL', 'HARGA INTERNAL', 'HARGA JUAL B2B', 'HARGA JUAL B2C',
            'LINK FOTO 1', 'LINK FOTO 2',
        ]);
        fputcsv($stream, [
            $product->id, 'Principal Baru', 'Nama Baru', 'MERAH', 'Obat Bebas', 'ALAT KESEHATAN', 'Komposisi baru',
            'Indikasi baru', 'BOX', '10 strip @ 10 tablet', 'TABLET', '2',
            '6000', '7000', '13000', '14000',
            'http://localhost/storage/medicines/baru.jpg', '',
        ]);
        fclose($stream);

        $upload = new UploadedFile($path, 'produk-edit.csv', 'text/csv', null, true);
        $this->actingAs($admin)
            ->post(route('admin.produk.import.process'), ['file' => $upload])
            ->assertRedirect(route('admin.produk.index'));
        @unlink($path);

        $product->refresh();
        $this->assertSame('SKU-OLD', $product->sku);
        $this->assertSame('Nama Baru', $product->nama_obat);
        $this->assertSame('Principal Baru', $product->kategori);
        $this->assertSame('ALAT KESEHATAN', $product->kategori_produk);
        $this->assertSame(10000.0, (float) $product->harga);
        $this->assertSame(7000.0, (float) $product->harga_internal);
        $this->assertSame(13000.0, (float) $product->harga_grosir);
        $this->assertSame(0.0, (float) $product->price_box);
        $this->assertSame(2, $product->stock_base_units);
        $this->assertSame('medicines/baru.jpg', $product->gambar);
        $this->assertSame(['medicines/lama-2.jpg'], $product->gambar_tambahan);
        $this->assertSame(1, Medicine::count());
    }

    public function test_complete_mode_fills_blank_fields_for_same_name_and_adds_new_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = Medicine::create([
            'sku' => 'KEEP-SKU',
            'nama_obat' => 'Produk Sama',
            'kategori' => 'Principal Terisi',
            'logo' => 'HIJAU',
            'harga' => 10000,
            'harga_modal' => 0,
            'harga_retail' => 0,
            'stok' => 0,
            'stock_base_units' => 0,
            'deskripsi' => 'Deskripsi lama',
        ]);
        $path = tempnam(sys_get_temp_dir(), 'complete-products');
        $headers = [
            'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'GOLONGAN', 'KOMPOSISI', 'INDIKASI',
            'SATUAN', 'KEMASAN', 'SEDIAAN', 'STOK SATUAN',
            'MODAL', 'HARGA JUAL B2B', 'HARGA JUAL B2C',
        ];
        $existingRow = [
            'Principal Baru', 'Produk Sama', 'MERAH', 'Obat Bebas', 'Komposisi diisi',
            'Indikasi diisi', 'BOX', '10 strip @ 10 tablet', 'TABLET', '2',
            '6000', '13000', '14000',
        ];
        $newRow = [
            'Principal Baru', 'Produk Tambahan', 'BIRU', 'Obat Bebas', 'Komposisi baru',
            'Indikasi baru', 'BOX', '', 'TABLET', '5',
            '5000', '8000', '9000',
        ];
        file_put_contents($path, implode("\n", [
            implode(',', $headers),
            implode(',', $existingRow),
            implode(',', $newRow),
        ])."\n");

        $upload = new UploadedFile($path, 'lengkapi.csv', 'text/csv', null, true);
        $this->actingAs($admin)
            ->post(route('admin.produk.import.process'), ['file' => $upload, 'mode' => 'complete'])
            ->assertRedirect(route('admin.produk.index'));
        @unlink($path);

        $existing->refresh();
        $this->assertSame('KEEP-SKU', $existing->sku);
        $this->assertSame('Principal Terisi', $existing->kategori);
        $this->assertSame('HIJAU', $existing->logo);
        $this->assertSame('Obat Bebas', $existing->golongan);
        $this->assertSame('Komposisi diisi', $existing->komposisi);
        $this->assertSame(10000.0, (float) $existing->harga);
        $this->assertSame(6000.0, (float) $existing->harga_modal);
        $this->assertSame(2, $existing->stock_base_units);
        $this->assertSame(2, Medicine::count());
        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Produk Tambahan',
            'kategori' => 'Principal Baru',
        ]);
    }

    public function test_business_backup_includes_product_customer_and_transaction_sheets_only_for_admins(): void
    {
        $this->get(route('admin.business-data.export'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee('Export Produk')
            ->assertSee('Backup Data Bisnis');
        $this->actingAs($admin)
            ->get(route('admin.produk.import'))
            ->assertOk()
            ->assertSee('Tambah / lengkapi produk')
            ->assertSee('Edit massal dari hasil export')
            ->assertSee('Download Export Produk untuk Edit Massal');

        $product = Medicine::create([
            'sku' => 'SKU-BACKUP-01',
            'nama_obat' => 'Produk Backup',
            'kategori' => 'PT Principal',
            'harga' => 15000,
            'stok' => 4,
            'deskripsi' => 'Produk backup',
            'gambar' => 'medicines/backup.jpg',
        ]);
        $customer = Customer::create([
            'identity_key' => str_repeat('a', 64),
            'name' => 'Pelanggan Backup',
            'email' => 'backup@example.test',
            'phone' => '08123456789',
        ]);
        PurchaseHistory::create([
            'customer_id' => $customer->id,
            'buyer_name' => 'Pelanggan Backup',
            'phone' => '08123456789',
            'items' => [['product_id' => $product->id, 'name' => $product->nama_obat, 'quantity' => 2]],
            'total' => 30000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.business-data.export'));

        $response->assertOk();
        $content = $response->getContent();
        foreach ([
            'name="Produk"',
            'name="Pelanggan"',
            'name="Transaksi"',
            'SKU-BACKUP-01',
            'Pelanggan Backup',
            'backup@example.test',
            'Produk Backup',
        ] as $expected) {
            $this->assertStringContainsString($expected, $content);
        }

        $this->assertStringNotContainsString('name="USERS"', strtoupper($content));
        $this->assertStringNotContainsString('name="SESSIONS"', strtoupper($content));

        $path = tempnam(sys_get_temp_dir(), 'business-backup');
        $zipPath = $path.'.zip';
        rename($path, $zipPath);
        file_put_contents($zipPath, $content);
        try {
            $archive = new \PharData($zipPath);
            $workbookXml = file_get_contents($archive['xl/workbook.xml']->getPathname());
            $workbook = simplexml_load_string($workbookXml);
            $this->assertNotFalse($workbook);
            $sheetNames = array_map(
                fn ($sheet) => (string) $sheet['name'],
                $workbook->xpath('//*[local-name()="sheet"]')
            );
            $this->assertContains('Transaksi', $sheetNames);
            $this->assertNotFalse(simplexml_load_string(
                file_get_contents($archive['xl/worksheets/sheet1.xml']->getPathname())
            ));
            $this->assertNotFalse(simplexml_load_string(
                file_get_contents($archive['xl/worksheets/sheet3.xml']->getPathname())
            ));
        } finally {
            unset($archive);
            @unlink($zipPath);
        }
    }

    public function test_transaction_history_uses_compact_pagination_without_oversized_svg_icons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (range(1, 21) as $number) {
            PurchaseHistory::create([
                'buyer_name' => 'Pelanggan ' . $number,
                'buyer_type' => 'umum',
                'items' => [],
                'total' => 1000,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.purchase-history.index'))
            ->assertOk()
            ->assertSee('Menampilkan 1–20 dari 21 transaksi')
            ->assertSee('Navigasi halaman transaksi')
            ->assertSee('Berikutnya')
            ->assertSee('page=2')
            ->assertDontSee('<svg', false);
    }

    public function test_customer_export_separates_customers_by_offline_b2b_and_b2c_sheet(): void
    {
        $this->get(route('admin.customers.export'))->assertRedirect(route('login'));

        $customers = [
            'Offline Sheet Customer' => Customer::create([
                'identity_key' => str_repeat('b', 64),
                'buyer_type' => 'umum',
                'name' => 'Offline Sheet Customer',
                'phone' => '081111111111',
            ]),
            'B2B Sheet Customer' => Customer::create([
                'identity_key' => str_repeat('c', 64),
                'buyer_type' => 'apotik',
                'name' => 'B2B Sheet Customer',
                'phone' => '082222222222',
            ]),
            'B2C Sheet Customer' => Customer::create([
                'identity_key' => str_repeat('d', 64),
                'buyer_type' => 'umum',
                'name' => 'B2C Sheet Customer',
                'phone' => '083333333333',
            ]),
        ];

        foreach ($customers as $name => $customer) {
            PurchaseHistory::create([
                'customer_id' => $customer->id,
                'buyer_type' => $customer->buyer_type,
                'buyer_name' => $name,
                'items' => [],
                'total' => 0,
                'transaction_channel' => $name === 'Offline Sheet Customer' ? 'offline' : 'online',
                'pbf_name' => $name === 'B2B Sheet Customer' ? 'PBF Contoh' : null,
                'payment_status' => 'pending',
            ]);
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.customers.export'));
        $response->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );

        $path = tempnam(sys_get_temp_dir(), 'customer-export');
        $zipPath = $path.'.zip';
        rename($path, $zipPath);
        file_put_contents($zipPath, $response->getContent());
        try {
            $archive = new \PharData($zipPath);
            $workbook = simplexml_load_string(file_get_contents($archive['xl/workbook.xml']->getPathname()));
            $this->assertNotFalse($workbook);
            $sheetNames = array_map(
                fn ($sheet) => (string) $sheet['name'],
                $workbook->xpath('//*[local-name()="sheet"]')
            );
            $this->assertSame(['Customer Offline', 'Customer B2B', 'Customer B2C'], $sheetNames);

            foreach ($sheetNames as $index => $sheetName) {
                $sheetXml = simplexml_load_string(
                    file_get_contents($archive['xl/worksheets/sheet'.($index + 1).'.xml']->getPathname())
                );
                $sheetStrings = array_map(
                    'strval',
                    $sheetXml->xpath('//*[local-name()="c"][@t="inlineStr"]//*[local-name()="t"]')
                );

                foreach ($customers as $customerName => $customer) {
                    $expectedSheet = match ($customerName) {
                        'Offline Sheet Customer' => 'Customer Offline',
                        'B2B Sheet Customer' => 'Customer B2B',
                        default => 'Customer B2C',
                    };
                    $this->assertSame(
                        $sheetName === $expectedSheet,
                        in_array($customerName, $sheetStrings, true),
                        "{$customerName} should only appear in {$expectedSheet}."
                    );
                }
            }
        } finally {
            unset($archive);
            @unlink($zipPath);
        }
    }
}
