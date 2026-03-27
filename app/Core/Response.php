<?php
/**
 * Response Helpers
 */

namespace App\Core;

class Response
{
    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $serverHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($refererHost && $refererHost !== $serverHost) {
            $referer = '/';
        }
        self::redirect($referer);
    }

    public static function withError(string $message): void
    {
        $_SESSION['flash_error'] = $message;
    }

    public static function withSuccess(string $message): void
    {
        $_SESSION['flash_success'] = $message;
    }

    public static function getFlash(): array
    {
        $flash = [
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ];
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        return $flash;
    }
}
