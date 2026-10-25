<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CsvSpreadsheet
{
    public static function read(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        if ($content === false || $content === '') {
            throw new RuntimeException('File CSV kosong atau tidak dapat dibaca.');
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $firstLine = strtok($content, "\n") ?: '';
        $delimiter = count(str_getcsv($firstLine, ';')) > count(str_getcsv($firstLine, ',')) ? ';' : ',';
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new RuntimeException('File CSV tidak dapat diproses.');
        }

        fwrite($stream, $content);
        rewind($stream);
        $headers = fgetcsv($stream, 0, $delimiter, '"', '\\');
        if ($headers === false) {
            fclose($stream);
            throw new RuntimeException('Header CSV tidak ditemukan.');
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
        $rows = [];
        while (($values = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            if ($values === [null] || count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
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
            $rows[] = $row;
        }
        fclose($stream);

        return [$headers, $rows];
    }

    public static function download(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers);

            foreach ($rows as $row) {
                fputcsv($stream, array_map([self::class, 'spreadsheetSafeValue'], $row));
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private static function spreadsheetSafeValue(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
