<?php
/**
 * Authentication Manager
 * Session-based auth for admin users
 */

namespace App\Core;

class Auth
{
    private const SESSION_KEY = 'user_id';
    private const SESSION_REGENERATED = 'session_regenerated';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../Config/config.php';
            $session = $config['session'] ?? [];

            ini_set('session.cookie_httponly', $session['httponly'] ?? true ? '1' : '0');
            ini_set('session.cookie_secure', $session['secure'] ?? true ? '1' : '0');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', $session['lifetime'] ?? 7200);

            session_start();
        }
    }

    public static function check(): bool
    {
        self::init();
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_KEY];
        return DB::fetch("SELECT id, name, email, role FROM users WHERE id = :id", ['id' => $userId]);
    }

    public static function login(string $email, string $password): string
    {
        self::init();

        $user = DB::fetch(
            "SELECT id, password_hash, is_active FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'failed';
        }

        if (empty($user['is_active'])) {
            return 'inactive';
        }

        // Regenerate session ID on login
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $user['id'];
        $_SESSION[self::SESSION_REGENERATED] = time();

        return 'success';
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];
        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Response::redirect('/login');
            exit;
        }
    }

    public static function guestOnly(): void
    {
        if (self::check()) {
            Response::redirect('/dashboard');
            exit;
        }
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isManager(): bool
    {
        return self::role() === 'manager';
    }

    public static function isEmployee(): bool
    {
        return self::role() === 'employee';
    }

    /**
     * Check if user has one of the given roles
     */
    public static function hasRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    /**
     * Require one of the given roles, redirect to dashboard with error if not authorized
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!self::hasRole(...$roles)) {
            Response::withError(__('auth.unauthorized'));
            Response::redirect('/dashboard');
            exit;
        }
    }
}
