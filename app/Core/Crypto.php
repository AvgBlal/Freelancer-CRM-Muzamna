<?php
/**
 * Encryption Helper
 * AES-256-CBC encryption for sensitive settings (SMTP password, API tokens)
 */

namespace App\Core;

class Crypto
{
    private const METHOD = 'aes-256-cbc';

    private static function getKey(): string
    {
        $configPath = __DIR__ . '/../Config/config.local.php';
        if (!file_exists($configPath)) {
            $configPath = __DIR__ . '/../Config/config.php';
        }
        $config = require $configPath;
        $key = $config['security']['encryption_key'] ?? '';
        if ($key === '') {
            return '';
        }
        return $key;
    }

    public static function encrypt(string $plaintext): string
    {
        if ($plaintext === '') return '';
        $key = self::getKey();
        if ($key === '') return $plaintext;

        $ivLen = openssl_cipher_iv_length(self::METHOD);
        $iv = random_bytes($ivLen);
        $ciphertext = openssl_encrypt($plaintext, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) return $plaintext;

        return 'enc:' . base64_encode($iv . $ciphertext);
    }

    public static function decrypt(string $encrypted): string
    {
        if ($encrypted === '' || strpos($encrypted, 'enc:') !== 0) {
            // Not encrypted (legacy plaintext value) — return as-is
            return $encrypted;
        }

        $key = self::getKey();
        if ($key === '') return $encrypted;

        $data = base64_decode(substr($encrypted, 4));
        if ($data === false) return $encrypted;

        $ivLen = openssl_cipher_iv_length(self::METHOD);
        if (strlen($data) <= $ivLen) return $encrypted;

        $iv = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);
        $decrypted = openssl_decrypt($ciphertext, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted !== false ? $decrypted : $encrypted;
    }
}
