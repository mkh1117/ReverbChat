<?php

namespace App\Services;

class ChatEncryptionService
{
    /**
     * تولید کلید ۳۲ بایتی منحصر‌به‌فرد برای هر روم
     */
    protected static function getRoomKey(int|string $roomId): string
    {
        $masterKey = config('app.key');

        if (str_starts_with($masterKey, 'base64:')) {
            $masterKey = base64_decode(substr($masterKey, 7));
        }

        return hash_hmac('sha256', "room_key_salt_{$roomId}", $masterKey, true);
    }

    /**
     * رمزنگاری پیام‌های متنی متداول (AES-256-GCM)
     */
    public static function encrypt(string $value, int|string $roomId): string
    {
        $key = static::getRoomKey($roomId);
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        $tag = "";

        $encrypted = openssl_encrypt($value, 'aes-256-gcm', $key, 0, $iv, $tag);
        $payload = json_encode([
            'iv' => base64_encode($iv),
            'value' => $encrypted,
            'tag' => base64_encode($tag)
        ]);

        return base64_encode($payload);
    }

    /**
     * رمزگشایی پیام‌های متنی متداول
     */
    public static function decrypt(string $payload, int|string $roomId): ?string
    {
        try {
            $key = static::getRoomKey($roomId);
            $decoded = json_decode(base64_decode($payload), true);

            if (!isset($decoded['iv'], $decoded['value'], $decoded['tag'])) {
                return $payload;
            }

            $iv = base64_decode($decoded['iv']);
            $encrypted = $decoded['value'];
            $tag = base64_decode($decoded['tag']);

            $decrypted = openssl_decrypt($encrypted, 'aes-256-gcm', $key, 0, $iv, $tag);

            return $decrypted !== false ? $decrypted : '[خطا در تایید اصالت پیام]';
        } catch (\Throwable $e) {
            return $payload;
        }
    }

    /**
     * رمزنگاری استریمی فایل با Libsodium Secretstream
     */
    public static function encryptFileStream(string $sourcePath, string $destPath, int|string $roomId): bool
    {
        $key = static::getRoomKey($roomId);

        $srcStream = fopen($sourcePath, 'rb');
        $destStream = fopen($destPath, 'wb');

        if (!$srcStream || !$destStream) {
            return false;
        }

        $fileSize = filesize($sourcePath);
        $readBytes = 0;


        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        fwrite($destStream, $header);

        $chunkSize = 1024 * 1024; // 1 MB

        while ($readBytes < $fileSize && !feof($srcStream)) {
            $chunk = fread($srcStream, $chunkSize);
            if ($chunk === '' || $chunk === false) {
                break;
            }

            $readBytes += strlen($chunk);

            $tag = ($readBytes >= $fileSize)
                ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_PUSH;

            $encryptedChunk = sodium_crypto_secretstream_xchacha20poly1305_push($state, $chunk, '', $tag);
            fwrite($destStream, $encryptedChunk);
        }

        fclose($srcStream);
        fclose($destStream);

        return true;
    }

    /**
     * رمزگشایی و استریم فایل به مرورگر با Libsodium Secretstream
     */
    public static function decryptFileStreamResponse(string $filePath, int|string $roomId, string $mimeType, string $originalName)
{
    if (!file_exists($filePath)) {
        abort(404);
    }

    $key = static::getRoomKey($roomId);

    $cacheKey = 'decrypted_' . md5($filePath . $roomId);
    $tempDir = storage_path('app/temp_decrypted');
    @mkdir($tempDir, 0755, true);
    $tempPath = $tempDir . '/' . $cacheKey;


    if (!file_exists($tempPath)) {
        $srcStream = fopen($filePath, 'rb');
        $destStream = fopen($tempPath, 'wb');

        if (!$srcStream || !$destStream) {
            if ($srcStream) fclose($srcStream);
            if ($destStream) fclose($destStream);
            abort(500, 'خطا در باز کردن فایل.');
        }

        $header = fread($srcStream, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
        if (strlen($header) < SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES) {
            fclose($srcStream);
            fclose($destStream);
            @unlink($tempPath);
            abort(500, 'فایل معتبر نیست.');
        }

        $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
        $chunkSize = (1024 * 1024) + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;

        while (!feof($srcStream)) {
            $encryptedChunk = fread($srcStream, $chunkSize);
            if ($encryptedChunk === '' || $encryptedChunk === false) {
                break;
            }

            $res = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $encryptedChunk);
            if ($res === false) {
                fclose($srcStream);
                fclose($destStream);
                @unlink($tempPath);
                abort(500, 'خطا در رمزکشایی.');
            }

            [$decryptedChunk, $tag] = $res;
            fwrite($destStream, $decryptedChunk);

            if ($tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL) {
                break;
            }
        }

        fclose($srcStream);
        fclose($destStream);
    }

    $disposition = request()->boolean('download')
    ? 'attachment'
    : 'inline';

return response()->file(
    $tempPath,
    [
        'Content-Type' => $mimeType,
        'Content-Disposition' => $disposition . '; filename="' . $originalName . '"',
        'Cache-Control' => 'private, max-age=3600',
    ]
);
}
}
