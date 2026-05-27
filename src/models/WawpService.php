<?php

/**
 * Change Log
 * -----------------------------------
 * Date: 2026-05-26
 * Developer: AI Assistant
 * Version: v1.4.1
 * Description:
 * - Restored access_token / instance_id in the request BODY (the previous
 *   query-string-only attempt broke message sending because the user's
 *   WAWP server expects auth in the body — same as the original working
 *   behaviour for text).
 * - Image send still uses bracket-notation file[*] keys per WAWP v2 docs.
 * - File upload path now uses multipart/form-data with CURLFile (works on
 *   localhost where the public URL isn't reachable by wawp.net's servers).
 * - Strict HTTPS / JPEG-PNG validation for URL-based image sends.
 */

class WawpService {
    private string $endpoint;
    private string $apiKey;
    private string $senderId;

    public function __construct(string $endpoint, string $apiKey = '', string $senderId = '') {
        $this->endpoint = trim($endpoint);
        $this->apiKey   = trim($apiKey);
        $this->senderId = trim($senderId);
    }

    public function sendTextMessage(string $phone, string $message): array {
        $chatId = $this->formatChatId($phone);
        if ($chatId === '') {
            return ['success' => false, 'error' => 'Nombor telefon tidak sah.'];
        }
        $payload = $this->withAuth([
            'chatId'  => $chatId,
            'message' => $message,
        ]);
        return $this->postJson($this->endpoint, $payload);
    }

    /**
     * Send image via a public HTTPS URL.
     * WAWP fetches the URL from its servers — must be public HTTPS, JPEG or PNG.
     */
    public function sendImageMessage(string $phone, string $imageUrl, string $caption = ''): array {
        $chatId = $this->formatChatId($phone);
        if ($chatId === '') {
            return ['success' => false, 'error' => 'Nombor telefon tidak sah.'];
        }

        if (stripos($imageUrl, 'https://') !== 0) {
            return [
                'success' => false,
                'error'   => 'WAWP hanya boleh ambil imej dari URL HTTPS awam. URL semasa: ' . $imageUrl,
            ];
        }

        $filename = basename(parse_url($imageUrl, PHP_URL_PATH) ?: 'image.jpg');
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeType = $this->extToMime($ext);
        if ($mimeType === '') {
            return [
                'success' => false,
                'error'   => 'WhatsApp hanya menerima imej JPEG/PNG. Sambungan fail: ' . $ext,
            ];
        }

        $imgEndpoint = $this->imageEndpoint();

        // Per WAWP v2 docs: bracket-notation keys for file metadata in the body.
        $payload = $this->withAuth([
            'chatId'         => $chatId,
            'file[url]'      => $imageUrl,
            'file[filename]' => $filename,
            'file[mimetype]' => $mimeType,
        ]);
        if ($caption !== '') {
            $payload['caption'] = $caption;
        }

        return $this->postJson($imgEndpoint, $payload);
    }

    /**
     * Upload image bytes directly via multipart/form-data.
     * Use this when no public HTTPS URL is available (localhost, intranet, etc.).
     * Auto-converts non-JPEG/PNG sources (e.g. WebP) to PNG when GD is loaded.
     */
    public function sendImageFile(string $phone, string $filePath, string $caption = ''): array {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'Fail tidak dijumpai: ' . $filePath];
        }
        $chatId = $this->formatChatId($phone);
        if ($chatId === '') {
            return ['success' => false, 'error' => 'Nombor telefon tidak sah.'];
        }

        $detectedMime = function_exists('mime_content_type')
            ? (mime_content_type($filePath) ?: '')
            : '';
        $bytes    = file_get_contents($filePath);
        $filename = basename($filePath);

        if (!in_array($detectedMime, ['image/jpeg', 'image/png'], true)) {
            $converted = $this->convertToPng($bytes);
            if ($converted === null) {
                return [
                    'success' => false,
                    'error'   => 'WhatsApp hanya menerima JPEG/PNG. Format semasa: ' . ($detectedMime ?: 'tidak diketahui'),
                ];
            }
            $bytes        = $converted;
            $detectedMime = 'image/png';
            $filename     = pathinfo($filename, PATHINFO_FILENAME) . '.png';
        }

        // Materialize bytes to a temp file so cURL can stream it as multipart
        $tmp = tempnam(sys_get_temp_dir(), 'wawp_');
        if ($tmp === false) {
            return ['success' => false, 'error' => 'Gagal cipta fail sementara untuk muat naik.'];
        }
        file_put_contents($tmp, $bytes);

        try {
            $imgEndpoint = $this->imageEndpoint();
            $multipart = $this->withAuth([
                'chatId'         => $chatId,
                'file'           => new CURLFile($tmp, $detectedMime, $filename),
                'file[filename]' => $filename,
                'file[mimetype]' => $detectedMime,
            ]);
            if ($caption !== '') {
                $multipart['caption'] = $caption;
            }

            return $this->postMultipart($imgEndpoint, $multipart);
        } finally {
            @unlink($tmp);
        }
    }

    private function imageEndpoint(): string {
        return preg_replace('/\/send\/text$/', '/send/image', $this->endpoint);
    }

    /**
     * Inject access_token + instance_id into the request payload (body).
     * The user's WAWP server expects auth in the body; do NOT move to query string
     * without confirming server compatibility — doing so previously broke all sends.
     */
    private function withAuth(array $payload): array {
        if ($this->apiKey   !== '') $payload['access_token'] = $this->apiKey;
        if ($this->senderId !== '') $payload['instance_id']  = $this->senderId;
        return $payload;
    }

    private function postJson(string $url, array $body): array {
        if ($url === '') {
            return ['success' => false, 'error' => 'WAWP API endpoint belum dikonfigurasi.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        return $this->execResult($ch);
    }

    private function postMultipart(string $url, array $fields): array {
        if ($url === '') {
            return ['success' => false, 'error' => 'WAWP API endpoint belum dikonfigurasi.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fields, // array → cURL multipart auto
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        return $this->execResult($ch);
    }

    private function execResult($ch): array {
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlErr !== '') {
            return ['success' => false, 'error' => 'CURL error: ' . $curlErr];
        }

        $decoded = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;

        return [
            'success'   => $success,
            'http_code' => $httpCode,
            'response'  => $decoded ?? $response,
            'error'     => $success ? '' : ('HTTP ' . $httpCode . ': ' . substr(json_encode($decoded ?? $response), 0, 500)),
        ];
    }

    private function convertToPng(string $bytes): ?string {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagepng')) {
            return null;
        }
        $img = @imagecreatefromstring($bytes);
        if ($img === false) {
            return null;
        }
        ob_start();
        $ok  = imagepng($img);
        $out = ob_get_clean();
        imagedestroy($img);
        return $ok ? $out : null;
    }

    private function extToMime(string $ext): string {
        $map = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ];
        return $map[$ext] ?? '';
    }

    private function formatChatId(string $phone): string {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') return '';
        if ($digits[0] === '0') {
            $digits = '6' . $digits;
        }
        return $digits . '@c.us';
    }
}
