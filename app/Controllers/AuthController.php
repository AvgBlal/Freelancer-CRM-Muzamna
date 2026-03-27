<?php
/**
 * Authentication Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;

class AuthController
{
    public function showLogin(): void
    {
        Auth::guestOnly();
        $csrf = CSRF::field();
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        Auth::guestOnly();
        CSRF::verifyRequest();

        // Rate limiting: max 5 attempts per 5 minutes
        $maxAttempts = 5;
        $lockoutSeconds = 300;
        $now = time();

        if (!empty($_SESSION['login_lockout']) && $_SESSION['login_lockout'] > $now) {
            $remaining = (int) ceil(($_SESSION['login_lockout'] - $now) / 60);
            $_SESSION['flash_error'] = __('auth.too_many_attempts');
            Response::back();
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('email', __('auth.email'))
                  ->email('email', __('auth.email'));
        $validator->required('password', __('auth.password'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            Response::back();
            return;
        }

        $result = Auth::login($_POST['email'], $_POST['password']);

        if ($result === 'success') {
            unset($_SESSION['login_attempts'], $_SESSION['login_lockout']);
            Response::redirect('/dashboard');
        } elseif ($result === 'inactive') {
            $_SESSION['flash_error'] = __('auth.account_inactive');
            Response::back();
        } else {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= $maxAttempts) {
                $_SESSION['login_lockout'] = $now + $lockoutSeconds;
                $_SESSION['login_attempts'] = 0;
            }
            $_SESSION['flash_error'] = __('auth.invalid_creds');
            Response::back();
        }
    }

    public function logout(): void
    {
        CSRF::verifyRequest();
        Auth::logout();
        Response::redirect('/login');
    }
}
