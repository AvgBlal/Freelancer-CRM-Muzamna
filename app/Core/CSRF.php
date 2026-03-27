<?php
/**
 * CSRF Protection
 * Token generation and verification
 */

namespace App\Core;

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const SESSION_KEY = 'csrf_tokens';
    private const MAX_TOKENS = 50;

    public static function init(): void
    {
        Auth::init();
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    public static function generate(): string
    {
        self::init();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][] = $token;

        // Keep only last N tokens
        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_TOKENS) {
            array_shift($_SESSION[self::SESSION_KEY]);
        }

        return $token;
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    public static function validate(?string $token): bool
    {
        self::init();

        if (empty($token)) {
            return false;
        }

        $key = array_search($token, $_SESSION[self::SESSION_KEY] ?? [], true);
        if ($key !== false) {
            // Remove used token
            unset($_SESSION[self::SESSION_KEY][$key]);
            $_SESSION[self::SESSION_KEY] = array_values($_SESSION[self::SESSION_KEY]);
            return true;
        }

        return false;
    }

    public static function verifyRequest(): void
    {
        $token = $_POST[self::TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($token)) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }
}
