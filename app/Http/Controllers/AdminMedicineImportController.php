<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Constants\Companies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMedicineImportController extends Controller
{
    private array $companies = Companies::LIST;

    public function showImportForm()
    {
        return view('admin.medicines.import', [
            'categories' => $this->companies
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048'],
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            return back()->withErrors(['file' => 'Format harus CSV / XLS / XLSX']);
        }

        if (in_array($ext, ['xls', 'xlsx'])) {
            return $this->importExcel($file);
        }

        return $this->importCsv($file);
    }

    public function downloadTemplate()
    {
        $columns = ['KELOMPOK', 'PABRIK', 'NAMA PRODUK', 'SEDIAAN', 'RETAIL', 'STOK', 'KOMPOSISI', 'INDIKASI', 'GOLONGAN', 'KATEGORI'];
        $widths  = [12, 18, 30, 10, 12, 8, 25, 30, 18, 22];

        $rows = [
            ['PBF',    'KIMIA FARMA', 'Paracetamol 500mg',   'fls', '5000',   '100', 'Paracetamol 500 mg', 'Demam & nyeri',          'BEBAS', 'OBAT'],
            ['APOTEK', 'BERNOFARM',   'Aspirin 80mg',        'box', '12000',  '80',  'Aspirin 80 mg',      'Nyeri & demam',          'KERAS', 'OBAT'],
            ['PBF',    'OMRON',       'Tensimeter Digital',  '',    '350000', '20',  '-',                  'Mengukur tekanan darah', 'BEBAS', 'ALAT KESEHATAN'],
        ];

        return \App\Helpers\XlsxWriter::download('template_medicines.xlsx', $columns, $rows, $widths);
    }

    /**
     * =========================
     * CSV IMPORT
     * =========================
     */
    private function importCsv($file)
    {
        $content = file_get_contents($file->getRealPath());

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines   = array_values(array_filter(explode("\n", $content)));

        if (count($lines) < 2) {
            return back()->withErrors(['file' => 'File kosong']);
        }

        // AUTO DETECT DELIMITER
        $delimiter = str_contains($lines[0], ';') ? ';' : ',';

        $header = array_map(
            fn($h) => strtoupper(trim($h)),
            str_getcsv($lines[0], $delimiter)
        );

        // Normalisasi alias: RETAIL → HARGA
        $header = array_map(fn($h) => $h === 'RETAIL' ? 'HARGA' : $h, $header);

        $required = ['PABRIK', 'NAMA PRODUK'];

        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            return back()->withErrors([
                'file' => 'Header kurang: ' . implode(', ', $missing)
            ]);
        }

        return $this->processRows($header, array_slice($lines, 1), $delimiter);
    }

    /**
     * =========================
     * EXCEL IMPORT
     * =========================
     */
    private function importExcel($file)
    {
        $content = file_get_contents($file->getRealPath());

        // XML-based Excel (.xls)
        if (strpos($content, '<Workbook') !== false) {
            return $this->importExcelXml($content);
        }

        // Modern XLSX (ZIP/PK format) — parse via ZipArchive
        if (strpos($content, 'PK') === 0) {
            return $this->importXlsx($file->getRealPath());
        }

        return back()->withErrors([
            'file' => 'Format Excel tidak dikenali'
        ]);
    }

    /**
     * =========================
     * XLSX PARSER (modern .xlsx via ZipArchive)
     * =========================
     */
    private function importXlsx(string $path)
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return back()->withErrors(['file' => 'File XLSX tidak bisa dibuka']);
        }

        // Baca shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            libxml_use_internal_errors(true);
            $ssXml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $ssXml);
            $ss    = simplexml_load_string($ssXml);
            if ($ss) {
                foreach ($ss->si as $si) {
                    // Gabungkan semua node <t> (untuk rich text)
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    if (empty($text)) {
                        $text = (string)$si->t;
                    }
                    $sharedStrings[] = trim($text);
                }
            }
        }

        // Baca sheet pertama
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return back()->withErrors(['file' => 'Sheet tidak ditemukan di XLSX']);
        }

        libxml_use_internal_errors(true);
        $sheetXml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $sheetXml);
        $sheet    = simplexml_load_string($sheetXml);

        if (!$sheet) {
            return back()->withErrors(['file' => 'File XLSX rusak']);
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $r          = [];
            $lastColIdx = -1;

            foreach ($row->c as $cell) {
                // Hitung index kolom dari atribut r (contoh: A1, B2, AA3)
                $cellRef = (string)$cell['r'];
                preg_match('/^([A-Z]+)/', $cellRef, $colMatch);
                $colLetters = $colMatch[1] ?? 'A';
                $colIdx     = 0;
                foreach (str_split($colLetters) as $ch) {
                    $colIdx = $colIdx * 26 + (ord($ch) - ord('A') + 1);
                }
                $colIdx--; // 0-based

                // Isi kolom yang dilewati (sparse cells)
                while ($lastColIdx < $colIdx - 1) {
                    $r[] = '';
                    $lastColIdx++;
                }

                $type  = (string)$cell['t'];
                $value = (string)$cell->v;

                if ($type === 's') {
                    // shared string
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string)$cell->is->t;
                }

                $r[]        = trim($value);
                $lastColIdx = $colIdx;
            }

            $rows[] = $r;
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'Data kosong']);
        }

        $header = array_map(fn($h) => strtoupper(trim($h)), $rows[0]);

        return $this->processArrayRows($header, array_slice($rows, 1));
    }

    /**
     * =========================
     * EXCEL XML PARSER
     * =========================
     */
    private function importExcelXml($content)
    {
        libxml_use_internal_errors(true);

        $content = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $content);

        $xml = simplexml_load_string($content);

        if (!$xml) {
            return back()->withErrors(['file' => 'File rusak']);
        }

        $rows = [];

        foreach ($xml->Worksheet[0]->Table->Row as $row) {
            $r = [];
            foreach ($row->Cell as $cell) {
                $r[] = trim((string)$cell->Data);
            }
            $rows[] = $r;
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'Data kosong']);
        }

        $header = array_map(fn($h) => strtoupper(trim($h)), $rows[0]);

        return $this->processArrayRows($header, array_slice($rows, 1));
    }

    /**
     * =========================
     * PROCESS CSV ROW
     * =========================
     */
    private function processRows($header, $lines, $delimiter)
    {
        $rows = [];

        foreach ($lines as $line) {
            $rows[] = str_getcsv($line, $delimiter);
        }

        return $this->processArrayRows($header, $rows);
    }

    /**
     * =========================
     * CORE IMPORT
     * =========================
     */
    private function processArrayRows($header, $rows)
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        if (count($rows) > 5000) {
            return back()->withErrors([
                'file' => 'Maksimal 5000 baris'
            ]);
        }

        // Normalisasi alias kolom: HARGA / RETAIL → HARGA
        $header = array_map(function($h) {
            if ($h === 'RETAIL') return 'HARGA';
            return $h;
        }, $header);

        // Cek kolom minimum yang dibutuhkan
        $required = ['PABRIK', 'NAMA PRODUK'];
        $missing  = array_diff($required, $header);

        if (!empty($missing)) {
            return back()->withErrors([
                'file' => 'Header kurang: ' . implode(', ', $missing)
            ]);
        }

        DB::beginTransaction();

        try {

            foreach ($rows as $i => $row) {

                // Lewati baris kosong
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue;
                }

                // Sesuaikan jumlah kolom dengan header
                $padded = array_pad(array_slice($row, 0, count($header)), count($header), '');
                $data   = array_combine($header, $padded);
                $data   = array_map('trim', $data);

                if (empty($data['NAMA PRODUK'])) {
                    $skipped++;
                    continue;
                }

                // Harga: cek kolom HARGA (sudah dinormalisasi dari RETAIL)
                $harga = $this->parseHarga($data['HARGA'] ?? '0');

                // Stok
                $stok = (int) preg_replace('/[^0-9]/', '', $data['STOK'] ?? '0');

                // Terjual
                $terjual = (int) preg_replace('/[^0-9]/', '', $data['TERJUAL'] ?? '0');

                // Grade
                $gradeRaw = trim((string) ($data['GRADE'] ?? ''));
                $grade = null;
                if ($gradeRaw !== '') {
                    $grade = strtoupper($gradeRaw);
                    $grade = preg_replace('/^GRADE\s*/i', '', $grade) ?? $grade;
                    $grade = trim($grade);
                }

                // SKU
                $sku = !empty($data['SKU']) ? $data['SKU'] : null;

                // GOLONGAN / is_resep — opsional
                $golongan = strtoupper($data['GOLONGAN'] ?? '');
                $isResep  = $golongan === 'KERAS';

                // Sediaan
                $sediaan = null;
                if (!empty($data['SEDIAAN'])) {
                    $sediaan = strtolower($data['SEDIAAN']);
                }

                // Kelompok: PBF atau APOTEK
                $kelompok = null;
                if (!empty($data['KELOMPOK'])) {
                    $k = strtoupper($data['KELOMPOK']);
                    if (in_array($k, ['PBF', 'APOTEK'])) {
                        $kelompok = $k;
                    }
                }

                // Kategori produk (OBAT, ALAT KESEHATAN, SKINCARE & KOSMETIK, dll)
                $kategoriProduk = !empty($data['KATEGORI']) ? $data['KATEGORI'] : null;

                // Deskripsi — gabungkan DESKRIPSI + KOMPOSISI + INDIKASI yang tersedia
                $parts = array_filter(array_map('trim', [
                    $data['DESKRIPSI']  ?? '',
                    $data['KOMPOSISI']  ?? '',
                    $data['INDIKASI']   ?? '',
                ]));
                $deskripsi = !empty($parts) ? implode(' | ', $parts) : $data['NAMA PRODUK'];

                $updateData = [
                    'sku'            => $sku,
                    'kelompok'       => $kelompok,
                    'kategori'       => $data['PABRIK'],
                    'kategori_produk'=> $kategoriProduk,
                    'brand'          => $data['PABRIK'],
                    'sediaan'        => $sediaan,
                    'harga'          => $harga,
                    'stok'           => $stok,
                    'terjual'        => $terjual,
                    'grade'          => $grade,
                    'deskripsi'      => $deskripsi,
                    'komposisi'      => !empty($data['KOMPOSISI']) ? $data['KOMPOSISI'] : null,
                    'indikasi'       => !empty($data['INDIKASI'])  ? $data['INDIKASI']  : null,
                    'is_resep'       => $isResep,
                    'is_grosir'      => false,
                ];

                // Pastikan deskripsi tidak pernah kosong/null (kolom NOT NULL di DB)
                if (empty(trim($updateData['deskripsi'] ?? ''))) {
                    $updateData['deskripsi'] = $data['NAMA PRODUK'];
                }

                Medicine::updateOrCreate(
                    [
                        'nama_obat' => $data['NAMA PRODUK'],
                        'kelompok'  => $kelompok ?? '',
                    ],
                    $updateData
                );

                $imported++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'file' => 'Error: ' . $e->getMessage()
            ]);
        }

        $msg = "Import berhasil: {$imported}";
        if ($skipped) $msg .= " | Skip: {$skipped}";

        return redirect()->route('admin.medicines.index')
            ->with('success', $msg);
    }

    /**
     * =========================
     * PARSE HARGA (INDONESIA FORMAT)
     * =========================
     */
    private function parseHarga($value)
    {
        if (!$value) return 0;

        $value = str_replace(['Rp', 'rp', ' '], '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace('.', '', $value);
        }

        return (float) $value;
    }

    /**
     * =========================
     * VALIDATION (opsional, tidak memblokir baris)
     * =========================
     */
    private function validateRow($data, $line)
    {
        $err = [];

        if (empty($data['PABRIK']))
            $err[] = "Baris {$line}: PABRIK kosong";

        return $err;
    }
}