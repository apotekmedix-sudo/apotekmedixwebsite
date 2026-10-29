<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\Medicine;
use App\Constants\Companies;
use App\Services\ProductImageDownloader;
use App\Support\HomepageProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminProdukImportController extends Controller
{
    public function showImportForm()
    {
        return view('admin.produk.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'mode' => ['nullable', 'in:complete,bulk_edit'],
        ]);

        set_time_limit(600);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $mode = $request->input('mode', 'legacy');

        if ($extension === 'xlsx') {
            return $this->importXlsx($file, $mode);
        }

        // CSV dan XLS (text-based)
        return $this->importCsv($file, $mode);
    }

    public function downloadTemplate()
    {
        $columns = [
            'NO', 'PRINCIPLE', 'NAMA PRODUK', 'LOGO', 'GOLONGAN', 'KATEGORI', 'KOMPOSISI', 'INDIKASI', 'DESKRIPSI',
            'SATUAN', 'KEMASAN', 'SEDIAAN', 'STOK SATUAN', 'MODAL', 'HARGA INTERNAL',
            'HARGA JUAL B2B', 'HARGA JUAL B2C', 'LINK FOTO 1', 'LINK FOTO 2',
        ];
        $widths = [8, 25, 28, 12, 18, 22, 28, 28, 36, 12, 22, 12, 12, 15, 16, 18, 18, 48, 48];

        $rows = [
            ['1', 'PT KALBE FARMA', 'Promag Tablet', 'HIJAU', 'Obat Bebas', 'obat-resep', 'Antasida & Simetikon', 'Membantu meredakan sakit maag', 'Membantu meredakan rasa tidak nyaman akibat asam lambung.', 'BOX', '10 blister @ 10 tablet', 'TABLET', '10', '117771', '120000', '135437', '153102', '', ''],
            ['2', 'PT PFIZER', 'Viagra 100mg', 'MERAH', 'Obat Keras', 'obat-resep', 'Sildenafil', 'Disfungsi ereksi', 'Obat resep untuk terapi sesuai petunjuk tenaga kesehatan.', 'BOX', '1 blister @ 4 tablet', 'TABLET', '1', '200000', '210000', '205000', '220000', '', ''],
            ['3', 'KONIMEX', 'Anakod Sirup', 'BIRU', 'Bebas Terbatas', 'flu-batuk', 'Paracetamol', 'Membantu meredakan batuk dan flu', 'Sirup untuk membantu meredakan gejala flu dan batuk.', 'BOTOL', '', 'SIRUP', '1', '12000', '13000', '12500', '14000', '', ''],
            ['4', 'GSK', 'Voltaren Gel', 'MERAH', 'Obat Keras', 'obat-resep', 'Diclofenac', 'Membantu meredakan nyeri otot', 'Gel untuk membantu meredakan nyeri otot sesuai petunjuk.', 'TUBE', '', 'KRIM', '5', '50000', '52000', '51000', '55000', '', ''],
        ];

        return \App\Helpers\XlsxWriter::download('template_produk.xlsx', $columns, $rows, $widths);
    }

    // ─── XLSX Parser (pure PHP, no ZipArchive needed) ────────────────────────

    private function importXlsx($file, string $mode)
    {
        $path = $file->getRealPath();

        // XLSX adalah ZIP — coba ZipArchive dulu, fallback ke manual binary extract
        if (class_exists('ZipArchive')) {
            return $this->importXlsxViaZip($path, $mode);
        }

        // Fallback: extract ZIP secara manual menggunakan PharData (built-in PHP)
        return $this->importXlsxViaPhar($path, $mode);
    }

    private function importXlsxViaZip(string $path, string $mode)
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return back()->withErrors(['file' => 'File XLSX tidak dapat dibuka.']);
        }

        $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml') ?: '');
        $sheetXml      = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return back()->withErrors(['file' => 'Sheet tidak ditemukan dalam file XLSX.']);
        }

        return $this->processRows($this->parseSheetXml($sheetXml, $sharedStrings), $mode);
    }

    private function importXlsxViaPhar(string $path, string $mode)
    {
        // Salin ke file .zip sementara agar PharData bisa baca
        $tmpZip = sys_get_temp_dir() . '/' . uniqid('xlsx_') . '.zip';
        copy($path, $tmpZip);

        try {
            $phar = new \PharData($tmpZip);

            $ssContent = '';
            if (isset($phar['xl/sharedStrings.xml'])) {
                $ssContent = file_get_contents($phar['xl/sharedStrings.xml']->getPathname());
            }

            if (!isset($phar['xl/worksheets/sheet1.xml'])) {
                return back()->withErrors(['file' => 'Sheet tidak ditemukan dalam file XLSX.']);
            }

            $sheetXml      = file_get_contents($phar['xl/worksheets/sheet1.xml']->getPathname());
            $sharedStrings = $this->parseSharedStrings($ssContent);

            return $this->processRows($this->parseSheetXml($sheetXml, $sharedStrings), $mode);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca XLSX: ' . $e->getMessage()
                . ' — Coba simpan ulang file sebagai CSV dari Excel.']);
        } finally {
            @unlink($tmpZip);
        }
    }

    private function parseSharedStrings(string $xml): array
    {
        $sharedStrings = [];
        if (empty($xml)) return $sharedStrings;

        // Hapus namespace agar simplexml tidak gagal parse
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $ss  = @simplexml_load_string($xml);
        if (!$ss) return $sharedStrings;

        foreach ($ss->si as $si) {
            $text = '';
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
            if (empty($text)) {
                $text = (string) $si->t;
            }
            $sharedStrings[] = trim($text);
        }

        return $sharedStrings;
    }

    private function parseSheetXml(string $sheetXml, array $sharedStrings): array
    {
        // Hapus namespace agar simplexml tidak gagal parse
        $sheetXml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $sheetXml);
        $sheet    = @simplexml_load_string($sheetXml);
        if (!$sheet) return [];

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];

            foreach ($row->c as $cell) {
                // Hitung index kolom dari atribut r (A=0, B=1, AA=26, dst)
                $cellRef = (string) $cell['r'];
                preg_match('/^([A-Z]+)/', $cellRef, $colMatch);
                $colLetters = $colMatch[1] ?? 'A';
                $colIdx     = 0;
                foreach (str_split($colLetters) as $ch) {
                    $colIdx = $colIdx * 26 + (ord($ch) - ord('A') + 1);
                }
                $colIdx--; // 0-based

                $type  = (string) $cell['t'];
                $value = (string) $cell->v;

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }

                $rowData[$colIdx] = trim($value);
            }

            if (!empty(array_filter($rowData, fn ($value) => $value !== ''))) {
                $rowValues = [];
                for ($column = 0, $lastColumn = max(array_keys($rowData)); $column <= $lastColumn; $column++) {
                    $rowValues[] = $rowData[$column] ?? '';
                }
                $rows[] = $rowValues;
            }
        }

        return $rows;
    }

    // ─── CSV / XLS (text-based) Parser ───────────────────────────────────────

    private function importCsv($file, string $mode)
    {
        $content = file_get_contents($file->getRealPath());

        if (str_starts_with($content, "\xFF\xFE")) {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($content, "\xFE\xFF")) {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_contains($content, "\0")) {
            $encoding = mb_detect_encoding($content, ['UTF-16LE', 'UTF-16BE'], true);
            if ($encoding !== false) {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines   = array_values(array_filter(explode("\n", $content)));

        if (count($lines) < 2) {
            return back()->withErrors(['file' => 'File kosong atau tidak valid.']);
        }

        $rows = [];
        $firstLine = $lines[0];
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',')
            ? ';'
            : (str_contains($firstLine, "\t") ? "\t" : ',');
        foreach ($lines as $line) {
            $row    = str_getcsv($line, $delimiter);
            $row    = array_map(fn($v) => trim((string) $v), $row);
            $rows[] = $row;
        }

        return $this->processRows($rows, $mode);
    }

    // ─── Shared processing logic ──────────────────────────────────────────────

    private function processRows(array $rows, string $mode = 'legacy')
    {
        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File kosong atau tidak valid.']);
        }

        // Cari baris header — baris pertama yang mengandung kolom nama produk yang dikenal
        $headerIdx = 0;
        foreach ($rows as $i => $row) {
            $normalized = array_map(
                fn($h) => $this->normalizeHeader($h),
                $row
            );
            if (in_array('NAMAPRODUK', $normalized) || in_array('NAMA', $normalized) || in_array('PRODUK', $normalized)) {
                $headerIdx = $i;
                break;
            }
        }

        $header = $rows[$headerIdx] ?? [];
        $headerKeys = array_map(fn($h) => $this->resolveHeaderKey($h), $header);
        $headerCount = count($headerKeys);
        $headerKeySet = array_flip($headerKeys);

        $availableKeys = array_filter($headerKeys);
        if (!in_array('NAMA_PRODUK', $availableKeys, true)) {
            return back()->withErrors([
                'file' => 'Header tidak cocok. Kolom nama produk wajib ada (contoh: Nama Produk, Nama, Produk). Ditemukan: ' . implode(', ', array_filter($header)),
            ]);
        }
        if ($mode === 'bulk_edit' && (!isset($headerKeySet['ROW_NO']) || !isset($headerKeySet['LINK_FOTO']))) {
            return back()->withErrors([
                'file' => 'Mode edit massal hanya menerima file Export Produk. Download ulang file export agar kolom NO dan LINK FOTO tersedia.',
            ]);
        }

        $imported      = 0;
        $skipped       = 0;
        $downloadedImages = [];
        $imageImportWarnings = [];
        $validKategori = array_keys(HomepageProductCategories::options());
        $medicineColumns = array_flip(Schema::getColumnListing('medicines'));
        if (isset($headerKeySet['HARGA_INTERNAL']) && !isset($medicineColumns['harga_internal'])) {
            return back()->withErrors([
                'file' => 'Kolom harga internal belum tersedia di database. Jalankan migration terbaru sebelum mengimpor file.',
            ]);
        }

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, $headerIdx + 1) as $row) {
                $row  = array_pad($row, $headerCount, '');
                $row  = array_slice($row, 0, $headerCount);
                $data = [];
                foreach ($headerKeys as $index => $headerKey) {
                    $data[$headerKey] = $row[$index] ?? '';
                }

                $namaProduk = trim((string) ($data['NAMA_PRODUK'] ?? ''));
                if (empty($namaProduk)) {
                    $skipped++;
                    continue;
                }

                $hargaRaw = $data['HARGA'] ?? '0';
                $legacyRetail = $this->parseHarga($this->getValue($data, ['HARGA RETAIL', 'HARGA', 'RETAIL']));
                $hargaModal = $this->parseHarga($this->getValue($data, ['MODAL', 'HARGA MODAL']));
                $hargaB2b = $this->parseHarga($data['HARGA_JUAL_B2B'] ?? '');
                $hasHargaB2c = isset($data['HARGA_JUAL_B2C']) && $data['HARGA_JUAL_B2C'] !== '';
                $hargaB2c = $hasHargaB2c
                    ? $this->parseHarga($data['HARGA_JUAL_B2C'])
                    : $legacyRetail;
                $hargaBox = $hasHargaB2c ? $hargaB2c : $legacyRetail;
                $hasExplicitLegacyPrice = isset($data['HARGA']) && $data['HARGA'] !== '';
                $hargaStored = $hasExplicitLegacyPrice
                    ? $this->parseHarga($data['HARGA'])
                    : ($legacyRetail ?: $this->parseHarga($hargaRaw));
                $stockQuantity = $data['STOK_KEMASAN'] ?? $data['STOK_SATUAN'] ?? $data['STOK'] ?? '0';
                $stok = max(0.0, $this->parseStockQuantity($stockQuantity));

                $katRaw = trim((string) ($data['KATEGORI'] ?? ''));
                $katProduk = $this->resolveProductCategory($katRaw, $validKategori);

                $sku      = trim((string) ($data['SKU'] ?? ''));
                $distributor = trim((string) ($data['DISTRIBUTOR'] ?? ''));
                $brand    = trim((string) $this->getValue($data, ['BRAND', 'MERK']));
                $terjual  = isset($data['TERJUAL']) ? (int) preg_replace('/[^0-9]/', '', (string) $data['TERJUAL']) : 0;
                $gradeRaw = trim((string) ($data['GRADE'] ?? ''));
                $grade    = '';
                if ($gradeRaw !== '') {
                    $grade = strtoupper($gradeRaw);
                    $grade = preg_replace('/^GRADE\s*/i', '', $grade) ?? $grade;
                    $grade = trim($grade);
                }

                $sediaan = trim((string) ($data['SEDIAAN'] ?? $data['SATUAN'] ?? ''));
                $sediaan = $sediaan === '' ? null : strtolower($sediaan);
                if ($sediaan === null && !empty($data['KEMASAN'])) {
                    $oldUnit = strtolower(trim((string) $data['KEMASAN']));
                    $sediaan = in_array($oldUnit, ['fls', 'box', 'tube', 'tablet', 'pcs'], true) ? $oldUnit : null;
                }
                $satuan = trim((string) ($data['SATUAN'] ?? ''));
                $logo = trim((string) ($data['LOGO'] ?? ''));
                $golongan = trim((string) ($data['GOLONGAN'] ?? ''));

                $stripsPerBox = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($data['STRIP_PER_KEMASAN'] ?? $data['PER_STRIP'] ?? '0')));
                $newPackagingColumns = array_key_exists('STRIP_PER_KEMASAN', $data)
                    || array_key_exists('TABLET_PER_STRIP', $data);
                $tabletsPerBox = $newPackagingColumns
                    ? 0
                    : max(0, (int) preg_replace('/[^0-9]/', '', (string) ($data['PER_TABLET'] ?? '0')));
                $explicitTabletsPerStrip = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($data['TABLET_PER_STRIP'] ?? '0')));
                $parsedPackaging = $this->parsePackaging((string) ($data['KEMASAN'] ?? ''));
                if ($stripsPerBox === 0) {
                    $stripsPerBox = $parsedPackaging['strips'];
                }
                $tabletsPerStrip = $explicitTabletsPerStrip > 0
                    ? $explicitTabletsPerStrip
                    : ($stripsPerBox > 0 && $tabletsPerBox > 0 && $tabletsPerBox % $stripsPerBox === 0
                        ? intdiv($tabletsPerBox, $stripsPerBox)
                        : $parsedPackaging['tablets_per_strip']);
                if ($tabletsPerBox === 0 && $stripsPerBox > 0 && $tabletsPerStrip > 0) {
                    $tabletsPerBox = $stripsPerBox * $tabletsPerStrip;
                }
                $packagingEnabled = $stripsPerBox > 0 && $tabletsPerStrip > 0;
                if (array_key_exists('UNIT_PACKAGING_ENABLED', $data)) {
                    $packagingEnabled = $this->parseBoolean($data['UNIT_PACKAGING_ENABLED'], 'UNIT PACKAGING ENABLED');
                }
                $packagingEnabled = $packagingEnabled
                    && Medicine::supportsTabletPackaging($satuan !== '' ? $satuan : null, $sediaan);

                if ($packagingEnabled && ($stripsPerBox < 1 || $tabletsPerStrip < 1)) {
                    throw new \InvalidArgumentException('Kemasan strip/tablet harus diisi jika UNIT PACKAGING ENABLED aktif.');
                }

                if (!$packagingEnabled && (float) $stok !== floor((float) $stok)) {
                    throw new \InvalidArgumentException('Stok pecahan hanya dapat digunakan untuk kemasan tablet/kapsul dengan rasio strip dan tablet.');
                }




                if (!$packagingEnabled && (float) $stok !== floor((float) $stok)) {
                    throw new \InvalidArgumentException('Stok pecahan hanya dapat digunakan untuk kemasan tablet/kapsul dengan rasio strip dan tablet.');
                }

                $hasPackagingColumns = $newPackagingColumns
                    || array_key_exists('PER_STRIP', $data)
                    || array_key_exists('PER_TABLET', $data)
                    || array_key_exists('UNIT_PACKAGING_ENABLED', $data);

                // Kelompok: PBF atau APOTEK — harus diketahui sebelum menentukan $match
                $kelompok = null;
                if (isset($data['KELOMPOK']) && !empty($data['KELOMPOK'])) {
                    $k = strtoupper(trim($data['KELOMPOK']));
                    if (in_array($k, ['PBF', 'APOTEK'])) {
                        $kelompok = $k;
                    }
                }

                $existingProduct = null;
                $productId = trim((string) ($data['PRODUCT_ID'] ?? ''));
                $exportRow = array_key_exists('LINK_FOTO', $data);
                if ($mode === 'complete') {
                    $namedProducts = Medicine::whereRaw('LOWER(nama_obat) = ?', [mb_strtolower($namaProduk)]);
                    if ($kelompok !== null) {
                        $namedProducts->where('kelompok', $kelompok);
                    }
                    $matches = $namedProducts->get();
                    if ($matches->count() > 1) {
                        throw new \InvalidArgumentException("Nama produk '{$namaProduk}' cocok dengan beberapa produk. Gunakan file Export Produk dan mode Edit massal agar diperbarui berdasarkan ID.");
                    }
                    $existingProduct = $matches->first();
                    $match = $existingProduct
                        ? ['id' => $existingProduct->id]
                        : ['nama_obat' => $namaProduk, 'kelompok' => $kelompok];
                } elseif ($mode === 'bulk_edit') {
                    $productId = trim((string) ($data['ROW_NO'] ?? ''));
                    if ($productId === '' || !ctype_digit($productId) || (int) $productId < 1) {
                        throw new \InvalidArgumentException("Baris produk '{$namaProduk}' tidak memiliki ID pada kolom NO.");
                    }
                    $existingProduct = Medicine::find((int) $productId);
                    if (!$existingProduct) {
                        throw new \InvalidArgumentException("Produk dengan ID {$productId} tidak ditemukan; tidak ada produk baru yang dibuat dalam mode edit massal.");
                    }
                    $match = ['id' => (int) $productId];
                } else {
                if ($productId === '' && $exportRow) {
                    $productId = trim((string) ($data['ROW_NO'] ?? ''));
                }
                if ($productId !== '') {
                    if (!ctype_digit($productId) || (int) $productId < 1) {
                        throw new \InvalidArgumentException("ID PRODUK '{$productId}' tidak valid.");
                    }
                    $existingProduct = Medicine::find((int) $productId);
                    if (!$existingProduct) {
                        throw new \InvalidArgumentException("Produk dengan ID {$productId} tidak ditemukan; baris tidak dapat diperlakukan sebagai edit.");
                    }
                    $match = ['id' => (int) $productId];
                // Match key: sertakan kelompok agar PBF dan APOTEK tersimpan sebagai record terpisah
                } elseif (!empty($sku)) {
                    $match = ['sku' => $sku, 'kelompok' => $kelompok];
                } else {
                    $match = ['nama_obat' => $namaProduk, 'kelompok' => $kelompok];
                }
                }

                $deskripsiValue = trim((string) ($data['DESKRIPSI'] ?? ''));

                $attributes = [
                        'sku'              => $sku ?: null,
                        'distributor'      => $distributor ?: null,
                        'nama_obat'        => $namaProduk,
                        'logo'             => $logo ?: null,
                        'golongan'         => $golongan ?: null,
                        'satuan'           => $satuan ?: null,
                        'kemasan'          => trim((string) ($data['KEMASAN'] ?? '')) ?: null,
                        'sediaan'          => $sediaan,
                        'kelompok'         => $kelompok,
                        'kategori'         => $data['PABRIK'] ?? ($data['BRAND'] ?? ''),
                        'brand'            => $brand ?: null,
                        'kategori_produk'  => $katProduk,
                        'harga'            => $hargaStored,
                        'harga_modal'     => $hargaModal,
                        'harga_retail'    => $hargaBox,
                        'price_strip'      => null,
                        'price_tablet'     => null,
                        'stok'             => (int) floor($stok),
                        'terjual'          => $terjual,
                        'grade'            => $grade ?: null,
                        'deskripsi'        => $deskripsiValue,
                        'komposisi'        => ($data['KOMPOSISI'] ?? '') ?: null,
                        'indikasi'         => ($data['INDIKASI'] ?? '') ?: null,
                        'dosis'            => ($data['DOSIS'] ?? '') ?: null,
                    ];
                if (array_key_exists('HARGA_JUAL_B2B', $data) && $data['HARGA_JUAL_B2B'] !== '') {
                    $attributes['harga_grosir'] = $hargaB2b;
                }
                if (isset($data['HARGA_INTERNAL']) && $data['HARGA_INTERNAL'] !== '') {
                    $attributes['harga_internal'] = $this->parseHarga($data['HARGA_INTERNAL']);
                }
                if ($hasPackagingColumns) {
                    $attributes += [
                        'unit_packaging_enabled' => $packagingEnabled,
                        'strips_per_box' => $packagingEnabled ? $stripsPerBox : null,
                        'tablets_per_strip' => $packagingEnabled ? $tabletsPerStrip : null,
                        'price_box' => $packagingEnabled ? $hargaBox : null,
                        'stock_base_units' => (int) round($stok * ($packagingEnabled ? $stripsPerBox * $tabletsPerStrip : 1)),
                    ];
                }
                if (array_key_exists('GAMBAR', $data)) {
                    $attributes['gambar'] = trim((string) $data['GAMBAR']) ?: null;
                }
                if (array_key_exists('GAMBAR_TAMBAHAN', $data)) {
                    $attributes['gambar_tambahan'] = $this->parseImageGallery($data['GAMBAR_TAMBAHAN']);
                }
                if ($exportRow) {
                    if (trim((string) ($data['LINK_FOTO'] ?? '')) !== '') {
                        try {
                            $attributes['gambar'] = $this->parseProductImageLink($data['LINK_FOTO'], $downloadedImages);
                        } catch (\InvalidArgumentException $exception) {
                            $imageImportWarnings[] = "{$namaProduk} (LINK FOTO 1): {$exception->getMessage()}";
                        }
                    }
                }
                if (trim((string) ($data['LINK_FOTO_2'] ?? '')) !== '') {
                    try {
                        $attributes['gambar_tambahan'] = [
                            $this->parseProductImageLink($data['LINK_FOTO_2'], $downloadedImages),
                        ];
                    } catch (\InvalidArgumentException $exception) {
                        $imageImportWarnings[] = "{$namaProduk} (LINK FOTO 2): {$exception->getMessage()}";
                    }
                }
                foreach ([
                    'PROMO_B2B_PERCENT' => 'promo_b2b_percent',
                    'PROMO_B2C_PERCENT' => 'promo_b2c_percent',
                ] as $header => $column) {
                    if (array_key_exists($header, $data)) {
                        $value = trim((string) $data[$header]);
                        if ($value !== '' && (!ctype_digit($value) || (int) $value > 100)) {
                            throw new \InvalidArgumentException("Persentase {$header} harus berupa angka 0 sampai 100.");
                        }
                        $attributes[$column] = $value === '' ? 0 : (int) $value;
                    }
                }
                foreach ([
                    'IS_RESEP' => 'is_resep',
                    'IS_GROSIR' => 'is_grosir',
                ] as $header => $column) {
                    if (array_key_exists($header, $data)) {
                        $attributes[$column] = $this->parseBoolean($data[$header], $header);
                    }
                }

                $existingProduct = $existingProduct ?? Medicine::where($match)->first();
                if ($mode === 'complete' && $existingProduct) {
                    $attributes = $this->onlyFillMissingProductFields($existingProduct, $attributes, $data);
                } elseif ($existingProduct) {
                    $preserveWhenMissing = [
                        'sku' => ['SKU'],
                        'distributor' => ['DISTRIBUTOR'],
                        'brand' => ['BRAND', 'MERK'],
                        'kategori_produk' => ['KATEGORI'],
                        'kelompok' => ['KELOMPOK'],
                        'terjual' => ['TERJUAL'],
                        'grade' => ['GRADE'],
                        'deskripsi' => ['DESKRIPSI'],
                        'harga' => ['HARGA'],
                        'promo_b2b_percent' => ['PROMO_B2B_PERCENT'],
                        'promo_b2c_percent' => ['PROMO_B2C_PERCENT'],
                        'is_resep' => ['IS_RESEP'],
                        'is_grosir' => ['IS_GROSIR'],
                    ];
                    foreach ($preserveWhenMissing as $attribute => $headers) {
                        if (!array_intersect($headers, array_keys($data))) {
                            unset($attributes[$attribute]);
                        }
                    }
                }

                $match = array_intersect_key($match, $medicineColumns);
                $attributes = array_intersect_key($attributes, $medicineColumns);
                if ($existingProduct && !$hasPackagingColumns && array_key_exists('stok', $attributes)) {
                    $attributes['stock_base_units'] = (int) round(
                        $stok * ($existingProduct->usesTabletPackaging() ? $existingProduct->units_per_box : 1)
                    );
                    if ($existingProduct->usesTabletPackaging() && $stok > floor($stok)) {
                        $attributes['stok'] = (int) floor($stok);
                    }
                } elseif (!$existingProduct && !$hasPackagingColumns && $stok > floor($stok)) {
                    throw new \InvalidArgumentException('Jumlah stok harus berupa bilangan bulat untuk produk tanpa konversi kemasan.');
                }
                if ($existingProduct && array_key_exists('stock_base_units', $attributes)
                    && \Illuminate\Support\Facades\Schema::hasTable('purchase_stock_reservations')) {
                    $reservedUnits = (int) \App\Models\PurchaseStockReservation::where('medicine_id', $existingProduct->id)
                        ->sum('quantity_units');
                    if ((int) $attributes['stock_base_units'] < $reservedUnits) {
                        throw new \InvalidArgumentException(
                            "Stok produk '{$namaProduk}' tidak dapat kurang dari {$reservedUnits} unit yang sedang direservasi."
                        );
                    }
                }
                $medicine = Medicine::updateOrCreate($match, $attributes);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($downloadedImages as $imagePath) {
                ImageHelper::deleteProductImage($imagePath);
            }
            return back()->withErrors(['file' => 'Error saat menyimpan data: ' . $e->getMessage()]);
        }

        $message = $mode === 'complete'
            ? "Import selesai: {$imported} produk ditambahkan/dilengkapi, {$skipped} baris dilewati."
            : "Import berhasil: {$imported} produk diperbarui, {$skipped} baris dilewati.";

        $response = redirect()->route('admin.produk.index')->with('success', $message);
        if ($imageImportWarnings !== []) {
            $response->with('warning', $imageImportWarnings);
        }

        return $response;
    }

    private function onlyFillMissingProductFields(Medicine $product, array $attributes, array $data): array
    {
        $sourceHeaders = [
            'sku' => ['SKU'],
            'distributor' => ['DISTRIBUTOR'],
            'nama_obat' => ['NAMA_PRODUK'],
            'logo' => ['LOGO'],
            'golongan' => ['GOLONGAN'],
            'satuan' => ['SATUAN'],
            'kemasan' => ['KEMASAN'],
            'sediaan' => ['SEDIAAN', 'SATUAN'],
            'kelompok' => ['KELOMPOK'],
            'kategori' => ['PABRIK', 'PRINCIPAL'],
            'brand' => ['BRAND'],
            'kategori_produk' => ['KATEGORI'],
            'harga' => ['HARGA', 'HARGA RETAIL', 'HARGA_JUAL_B2C'],
            'harga_modal' => ['MODAL'],
            'harga_internal' => ['HARGA_INTERNAL', 'HARGA JUAL INTERNAL'],
            'gambar_tambahan' => ['LINK_FOTO_2', 'GAMBAR_TAMBAHAN'],
            'harga_grosir' => ['HARGA_JUAL_B2B'],
            'harga_retail' => ['HARGA_JUAL_B2C', 'HARGA RETAIL', 'HARGA'],
            'stok' => ['STOK_KEMASAN', 'STOK_SATUAN', 'STOK'],
            'stock_base_units' => ['STOK_KEMASAN', 'STOK_SATUAN', 'STOK'],
            'strips_per_box' => ['STRIP_PER_KEMASAN', 'PER_STRIP', 'KEMASAN'],
            'tablets_per_strip' => ['TABLET_PER_STRIP', 'PER_TABLET', 'KEMASAN'],
            'unit_packaging_enabled' => ['UNIT_PACKAGING_ENABLED', 'STRIP_PER_KEMASAN', 'TABLET_PER_STRIP', 'PER_STRIP', 'PER_TABLET', 'KEMASAN'],
            'price_box' => ['HARGA_JUAL_B2C', 'HARGA RETAIL', 'HARGA'],
            'price_strip' => ['HARGA_JUAL_PER_STRIP'],
            'price_tablet' => ['HARGA_JUAL_PER_TABLET'],
            'terjual' => ['TERJUAL'],
            'grade' => ['GRADE'],
            'deskripsi' => ['DESKRIPSI'],
            'komposisi' => ['KOMPOSISI'],
            'indikasi' => ['INDIKASI'],
            'gambar' => ['GAMBAR', 'LINK_FOTO'],
            'dosis' => ['DOSIS'],
            'promo_b2b_percent' => ['PROMO_B2B_PERCENT'],
            'promo_b2c_percent' => ['PROMO_B2C_PERCENT'],
            'is_resep' => ['IS_RESEP'],
            'is_grosir' => ['IS_GROSIR'],
        ];

        foreach ($attributes as $attribute => $value) {
            $headers = $sourceHeaders[$attribute] ?? [];
            $hasInput = false;
            foreach ($headers as $header) {
                if (array_key_exists($header, $data) && trim((string) $data[$header]) !== '') {
                    $hasInput = true;
                    break;
                }
            }

            $currentValue = $product->getAttribute($attribute);
            $isMissing = $currentValue === null
                || (is_string($currentValue) && trim($currentValue) === '')
                || (is_array($currentValue) && $currentValue === [])
                || (is_bool($currentValue) && !$currentValue)
                || (is_numeric($currentValue) && (float) $currentValue === 0.0);

            if (!$hasInput || !$isMissing) {
                unset($attributes[$attribute]);
            }
        }

        return $attributes;
    }

    private function resolveProductCategory(string $value, array $validCategories): string
    {
        $legacyCategory = strtoupper(trim($value));
        if (in_array($legacyCategory, Companies::LIST, true)) {
            return $legacyCategory;
        }

        $normalize = static function (string $category): string {
            $category = mb_strtolower(trim($category));
            $category = preg_replace('/[^\p{L}\p{N}&]+/u', ' ', $category) ?? $category;

            return trim(preg_replace('/\s+/', ' ', $category) ?? $category);
        };

        $normalizedValue = $normalize($value);
        foreach (HomepageProductCategories::options() as $key => $label) {
            if (
                in_array($key, $validCategories, true)
                && ($normalizedValue === $normalize($key) || $normalizedValue === $normalize($label))
            ) {
                return $key;
            }
        }

        return 'OBAT';
    }

    private function resolveHeaderKey(string $header): string
    {
        $normalized = $this->normalizeHeader($header);

        $aliases = [
            'SKU' => ['SKU', 'KODEPRODUK', 'KODE', 'PRODUCTCODE'],
            'NAMA_PRODUK' => ['NAMAPRODUK', 'NAMA', 'NAMABARANG', 'PRODUK', 'PRODUCTNAME'],
            'DISTRIBUTOR' => ['DISTRIBUTOR', 'SUPPLIER', 'DISTRIBUTORNAME'],
            'PABRIK' => ['PABRIK', 'PRINCIPLE', 'PRINCIPAL', 'PRODUSEN', 'PRODUCER', 'MANUFACTURER'],
            'BRAND' => ['BRAND', 'MERK', 'MEREK'],
            'HARGA' => ['HARGA', 'RETAIL', 'PRICE'],
            'MODAL' => ['MODAL', 'HARGAMODAL', 'COST'],
            'HARGA_INTERNAL' => ['HARGAINTERNAL', 'HARGAJUALINTERNAL', 'INTERNALPRICE'],
            'HARGA RETAIL' => ['HARGARETAIL', 'RETAILPRICE'],
            'LOGO' => ['LOGO'],
            'GOLONGAN' => ['GOLONGAN', 'CLASSIFICATION'],
            'SATUAN' => ['SATUAN', 'UNIT'],
            'KEMASAN' => ['KEMASAN', 'PACKAGING'],
            'STOK_KEMASAN' => ['STOKKEMASAN', 'STOCKPACKAGE', 'STOCKPACKAGES'],
            'STOK_SATUAN' => ['STOKSATUAN', 'STOCKUNIT'],
            'STRIP_PER_KEMASAN' => ['STRIPPERKEMASAN', 'STRIPPERBOX'],
            'TABLET_PER_STRIP' => ['TABLETPERSTRIP', 'TABLETSPERSTRIP'],
            'PER_STRIP' => ['PERSTRIP', 'STRIPPERBOX', 'STRIPS'],
            'PER_TABLET' => ['PERTABLET', 'TABLETPERBOX', 'TABLETS'],
            'HARGA_JUAL_B2B' => ['HARGAJUALB2B', 'HARGAB2B'],
            'HARGA_JUAL_B2C' => ['HARGAJUALB2C', 'HARGAB2C'],
            'HARGA_JUAL_PER_STRIP' => ['HARGAJUALPERSTRIP', 'HARGAPERSTRIP'],
            'HARGA_JUAL_PER_TABLET' => ['HARGAJUALPERTABLET', 'HARGAPERTABLET'],
            'STOK' => ['STOK', 'STOCK', 'STOCKQTY', 'QTY', 'JUMLAH'],
            'TERJUAL' => ['TERJUAL', 'SALES', 'TERJUALSALES', 'TOTALTERJUAL'],
            'GRADE' => ['GRADE', 'KELAS', 'CLASS'],
            'DESKRIPSI' => ['DESKRIPSI', 'DESCRIPTION'],
            'KOMPOSISI' => ['KOMPOSISI', 'COMPOSITION'],
            'INDIKASI' => ['INDIKASI', 'INDICATION', 'MANFAAT'],
            'DOSIS' => ['DOSIS', 'DOSAGE', 'ATURANPAKAI'],
            'KATEGORI' => ['KATEGORI', 'KATEGORIPRODUK', 'CATEGORY', 'TIPE', 'JENIS'],
            'SEDIAAN' => ['SEDIAAN'],
            'KELOMPOK' => ['KELOMPOK', 'GROUP', 'GRUP'],
            'PROMO_B2B_PERCENT' => ['PROMOB2BPERCENT'],
            'PROMO_B2C_PERCENT' => ['PROMOB2CPERCENT'],
            'IS_RESEP' => ['ISRESEP'],
            'IS_GROSIR' => ['ISGROSIR'],
            'UNIT_PACKAGING_ENABLED' => ['UNITPACKAGINGENABLED'],
            'GAMBAR' => ['GAMBAR', 'IMAGE', 'IMAGEPATH'],
            'GAMBAR_TAMBAHAN' => ['GAMBARTAMBAHAN', 'ADDITIONALIMAGES'],
            'PRODUCT_ID' => ['IDPRODUK', 'PRODUCTID'],
            'ROW_NO' => ['NO'],
            'LINK_FOTO' => ['LINKFOTO', 'LINKFOTO1', 'LINKFOTOUTAMA'],
            'LINK_FOTO_2' => ['LINKFOTO2', 'LINKFOTOKEDUA'],
        ];

        foreach ($aliases as $key => $values) {
            if (in_array($normalized, $values, true)) {
                return $key;
            }
        }

        return $normalized;
    }

    private function normalizeHeader($header): string
    {
        $value = strtoupper(trim(preg_replace('/[^\x20-\x7E]/', '', (string) $header)));
        return preg_replace('/[^A-Z0-9]+/', '', $value);
    }

    private function getValue(array $data, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return (string) $data[$key];
            }
        }

        return '';
    }

    private function parseHarga($value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = str_ireplace(['Rp', ' ', "\u{00A0}"], '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Format harga '{$value}' tidak valid.");
        }

        $parsed = (float) $value;
        if ($parsed < 0 || $parsed > 9999999999.99) {
            throw new \InvalidArgumentException('Harga harus berada dalam rentang Rp 0 hingga Rp 9.999.999.999,99.');
        }

        return $parsed;
    }

    private function parseStockQuantity($value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = str_replace([" ", "\u{00A0}"], '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Jumlah stok '{$value}' tidak valid.");
        }

        $quantity = (float) $value;
        if ($quantity < 0 || $quantity > 10000000) {
            throw new \InvalidArgumentException('Jumlah stok harus berada dalam rentang 0 hingga 10.000.000.');
        }

        return $quantity;
    }

    private function parseBoolean($value, string $field): bool
    {
        $normalized = strtolower(trim((string) $value));
        $values = [
            '1' => true,
            'true' => true,
            'yes' => true,
            'ya' => true,
            '0' => false,
            'false' => false,
            'no' => false,
            'tidak' => false,
            '' => false,
        ];

        if (!array_key_exists($normalized, $values)) {
            throw new \InvalidArgumentException("Nilai {$field} harus berupa ya/tidak atau 1/0.");
        }

        return $values[$normalized];
    }

    private function parseImageGallery($value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $images = json_decode($value, true);
        if (!is_array($images)) {
            throw new \InvalidArgumentException('GAMBAR TAMBAHAN harus berisi daftar path foto dalam format JSON.');
        }

        foreach ($images as $image) {
            if (!is_string($image)) {
                throw new \InvalidArgumentException('Setiap nilai GAMBAR TAMBAHAN harus berupa path teks.');
            }
        }

        return array_values(array_filter(array_map('trim', $images)));
    }

    private function parseProductImageLink($value, array &$downloadedImages): ?string
    {
        $value = $this->normalizeProductImageLink((string) $value);
        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
            $host = strtolower(rtrim((string) parse_url($value, PHP_URL_HOST), '.'));
            $path = parse_url($value, PHP_URL_PATH);
            $relativePath = is_string($path) ? ltrim(rawurldecode($path), '/') : '';
            if (str_starts_with($relativePath, 'storage/')) {
                $relativePath = substr($relativePath, strlen('storage/'));
            }
            $localHosts = array_filter([
                parse_url((string) config('app.url'), PHP_URL_HOST),
                parse_url(url('/'), PHP_URL_HOST),
            ]);
            if (str_starts_with($relativePath, 'medicines/') && in_array($host, $localHosts, true)) {
                return $relativePath;
            }
            if ($scheme !== 'https') {
                throw new \InvalidArgumentException('LINK FOTO eksternal harus menggunakan HTTPS.');
            }

            $imagePath = app(ProductImageDownloader::class)->download($value);
            $downloadedImages[] = $imagePath;

            return $imagePath;
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) ? ltrim(rawurldecode($path), '/') : '';
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        if (!str_starts_with($path, 'medicines/')) {
            throw new \InvalidArgumentException('LINK FOTO harus mengarah ke folder foto produk medicines/.');
        }

        return $path;
    }

    private function normalizeProductImageLink(string $value): string
    {
        $value = trim($value);
        if (str_starts_with(strtolower($value), 'blob:')) {
            throw new \InvalidArgumentException(
                'LINK FOTO berformat blob: hanya tersedia di browser asal dan tidak dapat diunduh oleh server. Gunakan URL gambar HTTPS publik langsung, seperti link ibyteimg.com.'
            );
        }

        if (preg_match('/^=HYPERLINK\(\s*"([^"]+)"/i', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match('/^\[[^\]]*\]\((https?:\/\/.+)\)$/i', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match('/<a\b[^>]*\bhref=["\']([^"\']+)["\'][^>]*>/i', $value, $matches)) {
            $value = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (str_starts_with($value, '<') && str_ends_with($value, '>')) {
                $value = trim(substr($value, 1, -1));
            }
        }

        if (preg_match('~https?://[^\s<>"\]\)]+~i', $value, $matches)) {
            $value = rtrim($matches[0], '.,;:!?');
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match('/^www\./i', $value)) {
            $value = 'https://' . $value;
        }

        return trim($value);
    }

    private function parsePackaging(string $value): array
    {
        if (preg_match('/(\d+)\s*(?:blister|strip|strips?)\s*@\s*(\d+)\s*(?:tab|tablet)/i', $value, $matches)) {
            return ['strips' => (int) $matches[1], 'tablets_per_strip' => (int) $matches[2]];
        }

        return ['strips' => 0, 'tablets_per_strip' => 0];
    }

}
