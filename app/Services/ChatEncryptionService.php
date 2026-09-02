<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;


class ChatEncryptionService{
    protected static function getRoomKey(int $roomId){

        $masterKey = config('app.key');

        if (str_starts_with($masterKey, 'base64:')) {
            $masterKey = base64_decode(substr($masterKey, 7));
        }

        return hash_hmac('sha256', "room_key_salt_{$roomId}", $masterKey, true);
    }

    public static function encrypt(string $value, int $roomId){
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

    public static function decrypt(string $payload, int $roomId): ?string
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
}
