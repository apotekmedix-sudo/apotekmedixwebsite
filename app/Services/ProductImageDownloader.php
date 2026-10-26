<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use Illuminate\Http\UploadedFile;

class ProductImageDownloader
{
    private const MAX_BYTES = 5242880;

    public function download(string $url): string
    {
        $parts = parse_url($url);
        $rawHost = strtolower((string) ($parts['host'] ?? ''));
        $host = trim($rawHost, '[]');
        $port = (int) ($parts['port'] ?? 443);
        if (
            strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || $host === ''
            || str_ends_with($rawHost, '.')
            || isset($parts['user'])
            || isset($parts['pass'])
            || $port !== 443
        ) {
            throw new \InvalidArgumentException('LINK FOTO eksternal harus berupa URL HTTPS publik yang valid.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : array_values(array_filter(array_map(
                static fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null,
                dns_get_record($host, DNS_A | DNS_AAAA) ?: []
            )));
        if ($ips === []) {
            throw new \InvalidArgumentException('Host LINK FOTO tidak dapat ditemukan.');
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \InvalidArgumentException('LINK FOTO harus menggunakan host internet publik.');
            }
        }

        $resolvedIp = $ips[0];
        $resolveAddress = str_contains($resolvedIp, ':') ? '[' . $resolvedIp . ']' : $resolvedIp;
        $resolveEntry = "{$host}:{$port}:{$resolveAddress}";
        $contents = '';
        $downloadedBytes = 0;
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Unduhan LINK FOTO membutuhkan ekstensi cURL pada server.');
        }
        $curl = curl_init($url);
        if ($curl === false) {
            throw new \RuntimeException('Tidak dapat memulai unduhan LINK FOTO.');
        }

        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROXY => '',
            CURLOPT_RESOLVE => [$resolveEntry],
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$contents, &$downloadedBytes): int {
                $chunkLength = strlen($chunk);
                $downloadedBytes += $chunkLength;
                if ($downloadedBytes > self::MAX_BYTES) {
                    return 0;
                }
                $contents .= $chunk;

                return $chunkLength;
            },
        ]);

        $downloaded = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = curl_errno($curl);
        curl_close($curl);
        if ($downloaded === false || $curlError !== 0 || $status !== 200) {
            throw new \InvalidArgumentException(
                $downloadedBytes > self::MAX_BYTES
                    ? 'Ukuran foto dari LINK FOTO melebihi batas 5 MB.'
                    : 'Foto dari LINK FOTO gagal diunduh. Gunakan link langsung ke file gambar yang dapat diakses publik.'
            );
        }

        $imageInfo = @getimagesizefromstring($contents);
        $mimeExtensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? '') : '';
        if (
            !isset($mimeExtensions[$mime])
            || (int) ($imageInfo[0] ?? 0) > 8000
            || (int) ($imageInfo[1] ?? 0) > 8000
            || ((int) ($imageInfo[0] ?? 0) * (int) ($imageInfo[1] ?? 0)) > 40000000
        ) {
            throw new \InvalidArgumentException('LINK FOTO harus berupa gambar JPEG, PNG, WEBP, atau GIF dengan ukuran gambar yang wajar.');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'product-image-');
        if ($tempPath === false) {
            throw new \RuntimeException('Foto dari LINK FOTO gagal disiapkan untuk disimpan.');
        }
        if (file_put_contents($tempPath, $contents) === false) {
            unlink($tempPath);
            throw new \RuntimeException('Foto dari LINK FOTO gagal disiapkan untuk disimpan.');
        }

        try {
            return ImageHelper::storeProductImage(new UploadedFile(
                $tempPath,
                'product-image.' . $mimeExtensions[$mime],
                $mime,
                null,
                true
            ));
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
