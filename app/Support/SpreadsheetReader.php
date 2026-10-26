<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class SpreadsheetReader
{
    public static function read(UploadedFile $file): array
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
            return CsvSpreadsheet::read($file);
        }

        [$sheetXml, $sharedStringsXml] = self::readWorkbookParts($file->getRealPath());

        $sharedStrings = self::parseSharedStrings($sharedStringsXml);
        $rows = self::parseSheetRows($sheetXml, $sharedStrings);
        if ($rows === []) {
            throw new RuntimeException('Header tidak ditemukan dalam file Excel.');
        }

        $headers = array_map(
            fn ($header) => strtolower(trim((string) $header)),
            array_shift($rows)
        );
        $mappedRows = [];
        foreach ($rows as $values) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $values = array_pad($values, count($headers), '');
            $row = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $value = trim((string) ($values[$index] ?? ''));
                    $row[$header] = str_starts_with($value, "'") && preg_match('/^\'[=+\-@\t\r]/', $value)
                        ? substr($value, 1)
                        : $value;
                }
            }
            $mappedRows[] = $row;
        }

        return [$headers, $mappedRows];
    }

    private static function readWorkbookParts(string $path): array
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                throw new RuntimeException('File Excel XLSX tidak dapat dibuka.');
            }

            try {
                $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
                $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml') ?: '';
            } finally {
                $zip->close();
            }
        } else {
            $temporaryPath = tempnam(sys_get_temp_dir(), 'xlsx_');
            if ($temporaryPath === false) {
                throw new RuntimeException('File sementara untuk membaca Excel tidak dapat dibuat.');
            }
            $zipPath = $temporaryPath.'.zip';
            if (! copy($path, $zipPath)) {
                @unlink($temporaryPath);
                throw new RuntimeException('File Excel XLSX tidak dapat disiapkan untuk dibaca.');
            }
            @unlink($temporaryPath);

            try {
                $archive = new \PharData($zipPath);
                if (! isset($archive['xl/worksheets/sheet1.xml'])) {
                    throw new RuntimeException('Sheet pertama tidak ditemukan dalam file Excel.');
                }
                $sheetXml = file_get_contents($archive['xl/worksheets/sheet1.xml']->getPathname());
                $sharedStringsXml = isset($archive['xl/sharedStrings.xml'])
                    ? file_get_contents($archive['xl/sharedStrings.xml']->getPathname())
                    : '';
            } catch (\Exception $exception) {
                if ($exception instanceof RuntimeException) {
                    throw $exception;
                }
                throw new RuntimeException('File Excel XLSX tidak dapat dibuka.', 0, $exception);
            } finally {
                @unlink($zipPath);
            }
        }

        if ($sheetXml === false) {
            throw new RuntimeException('Sheet pertama tidak ditemukan dalam file Excel.');
        }
        if ($sharedStringsXml === false) {
            $sharedStringsXml = '';
        }

        return [$sheetXml, $sharedStringsXml];
    }

    private static function parseSharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $strings = @simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET);
        if ($strings === false) {
            throw new RuntimeException('Daftar teks dalam file Excel tidak dapat dibaca.');
        }

        $values = [];
        foreach ($strings->si as $item) {
            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            if ($text === '') {
                $text = (string) $item->t;
            }
            $values[] = trim($text);
        }

        return $values;
    }

    private static function parseSheetRows(string $xml, array $sharedStrings): array
    {
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $sheet = @simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET);
        if ($sheet === false) {
            throw new RuntimeException('Isi sheet dalam file Excel tidak dapat dibaca.');
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell['r'], $match);
                $column = 0;
                foreach (str_split($match[1] ?? 'A') as $letter) {
                    $column = $column * 26 + ord($letter) - ord('A') + 1;
                }
                $column--;

                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }
                $values[$column] = trim($value);
            }

            if ($values !== []) {
                $lastColumn = max(array_keys($values));
                $values = array_replace(array_fill(0, $lastColumn + 1, ''), $values);
                ksort($values);
                $rows[] = array_values($values);
            }
        }

        return $rows;
    }
}
