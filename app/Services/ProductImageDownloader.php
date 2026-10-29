<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use Illuminate\Http\UploadedFile;

class ProductImageDownloader
{
    private const MAX_BYTES = 5242880;
    private const MAX_REDIRECTS = 5;

    public function download(string $url): string
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Unduhan LINK FOTO membutuhkan ekstensi cURL pada server.');
        }

        $redirectCount = 0;
        $status = 0;
        $contents = '';
        $downloadedBytes = 0;
        do {
            [$host, $port, $resolveEntry] = $this->resolvePublicHttpsHost($url);
            $redirectLocation = null;
            $responseStatus = 0;
            $curlError = 0;
            $curlErrorMessage = '';
            $downloaded = false;

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $contents = '';
                $downloadedBytes = 0;
                $redirectLocation = null;
                $responseStatus = 0;
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
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ApotekMedixProductImporter/1.0)',
                    CURLOPT_HTTPHEADER => ['Accept: image/jpeg,image/png,image/webp,image/gif,*/*;q=0.8'],
                    CURLOPT_PROXY => '',
                    CURLOPT_RESOLVE => [$resolveEntry],
                    CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$redirectLocation, &$responseStatus): int {
                        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
                            $responseStatus = (int) $matches[1];
                        } elseif (stripos($header, 'Location:') === 0) {
                            $redirectLocation = trim(substr($header, strlen('Location:')));
                        }

                        return strlen($header);
                    },
                    CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$contents, &$downloadedBytes, &$responseStatus): int {
                        if ($responseStatus >= 300 && $responseStatus < 400) {
                            return strlen($chunk);
                        }

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
                $curlErrorMessage = curl_error($curl);
                curl_close($curl);

                if ($downloadedBytes > self::MAX_BYTES || ($downloaded !== false && $curlError === 0 && ($status === 200 || ($status >= 300 && $status < 400)))) {
                    break;
                }

                if ($attempt < 3 && ($curlError !== 0 || $status === 429 || $status >= 500)) {
                    usleep(250000 * $attempt);
                    continue;
                }

                break;
            }

            if ($downloadedBytes > self::MAX_BYTES) {
                throw new \InvalidArgumentException('Ukuran foto dari LINK FOTO melebihi batas 5 MB.');
            }
            if ($downloaded === false || $curlError !== 0) {
                throw new \InvalidArgumentException(
                    "Unduhan LINK FOTO gagal (cURL {$curlError}: {$curlErrorMessage}). Periksa koneksi server atau pastikan host dapat diakses."
                );
            }

            if ($status >= 300 && $status < 400) {
                if ($redirectLocation === null || ++$redirectCount > self::MAX_REDIRECTS) {
                    throw new \InvalidArgumentException('LINK FOTO terlalu banyak dialihkan atau tidak memiliki tujuan redirect yang valid.');
                }
                $url = $this->resolveRedirectUrl($url, $redirectLocation);
                continue;
            }

            break;
        } while (true);

        if ($status !== 200) {
            throw new \InvalidArgumentException(
                "Server LINK FOTO mengembalikan HTTP {$status}. Pastikan tautan aktif dan dapat diakses publik."
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

    private function resolvePublicHttpsHost(string $url): array
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
            throw new \InvalidArgumentException('LINK FOTO dan tujuan redirect harus berupa URL HTTPS publik yang valid.');
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

        return [$host, $port, "{$host}:{$port}:{$resolveAddress}"];
    }

    private function resolveRedirectUrl(string $currentUrl, string $location): string
    {
        $location = trim($location);
        if ($location === '') {
            throw new \InvalidArgumentException('Tujuan redirect LINK FOTO kosong.');
        }

        $locationParts = parse_url($location);
        if (isset($locationParts['scheme'])) {
            return $location;
        }

        $currentParts = parse_url($currentUrl);
        if (!isset($currentParts['scheme'], $currentParts['host'])) {
            throw new \InvalidArgumentException('URL LINK FOTO tidak dapat digunakan untuk mengikuti redirect.');
        }
        $authority = $currentParts['scheme'] . '://' . $currentParts['host'];
        if (isset($currentParts['port'])) {
            $authority .= ':' . $currentParts['port'];
        }

        if (str_starts_with($location, '//')) {
            return $currentParts['scheme'] . ':' . $location;
        }
        if (str_starts_with($location, '/')) {
            return $authority . $location;
        }

        $currentPath = (string) ($currentParts['path'] ?? '/');
        $directory = substr($currentPath, 0, (int) strrpos($currentPath, '/') + 1);

        return $authority . $directory . $location;
    }
}
