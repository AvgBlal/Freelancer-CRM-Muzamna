<?php
/**
 * Front Controller
 * All requests route through here
 */

// Error reporting (safe default: log only, never display in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Timezone
date_default_timezone_set('Africa/Cairo');

// Secure session configuration (MUST be set before session_start)
$configLocalPath_ = __DIR__ . '/app/Config/config.local.php';
$sessionConfig_ = [];
if (file_exists($configLocalPath_)) {
    $localCfg_ = require $configLocalPath_;
    $sessionConfig_ = $localCfg_['session'] ?? [];
}
ini_set('session.cookie_httponly', ($sessionConfig_['httponly'] ?? true) ? '1' : '0');
ini_set('session.cookie_secure', ($sessionConfig_['secure'] ?? false) ? '1' : '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', (string)($sessionConfig_['lifetime'] ?? 7200));
unset($configLocalPath_, $localCfg_, $sessionConfig_);

// Session
session_start();

// Redirect to installer if not yet installed
if (!file_exists(__DIR__ . '/app/Config/config.local.php') && file_exists(__DIR__ . '/install/install.php')) {
    header('Location: /install/install.php');
    exit;
}

// Global error handler
set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
    } else {
        $dir = class_exists('\App\Core\Lang') ? \App\Core\Lang::dir() : 'rtl';
        echo '<div dir="' . $dir . '" style="text-align:center;padding:2rem;font-family:sans-serif;">';
        echo '<h1 style="color:#2563eb;">500</h1><p>' . (function_exists('__') ? __('app.error_title') : 'A system error occurred') . '</p>';
        echo '<p style="color:#6b7280;">' . (function_exists('__') ? __('app.error_message') : 'Please try again later') . '</p>';
        echo '<a href="/dashboard" style="color:#2563eb;">' . (function_exists('__') ? __('app.back_home') : 'Back to Home') . '</a></div>';
    }
    exit;
});

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load core
require_once __DIR__ . '/app/Core/DB.php';
require_once __DIR__ . '/app/Core/Auth.php';
require_once __DIR__ . '/app/Core/CSRF.php';
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Core/Response.php';
require_once __DIR__ . '/app/Core/Validator.php';
require_once __DIR__ . '/app/Core/Lang.php';
require_once __DIR__ . '/app/Core/Crypto.php';

// Initialize translation engine
// Default locale: from session, then from config.local.php, then 'ar'
$defaultLocale = 'ar';
$configLocalPath = __DIR__ . '/app/Config/config.local.php';
if (file_exists($configLocalPath)) {
    $localConfig = require $configLocalPath;
    $defaultLocale = $localConfig['app']['locale'] ?? 'ar';
}
$locale = $_SESSION['locale'] ?? $defaultLocale;
\App\Core\Lang::init($locale);

// Load repositories
require_once __DIR__ . '/app/Repositories/SettingsRepo.php';
require_once __DIR__ . '/app/Repositories/ClientRepo.php';
require_once __DIR__ . '/app/Repositories/ServiceRepo.php';
require_once __DIR__ . '/app/Repositories/ProjectRepo.php';
require_once __DIR__ . '/app/Repositories/TagRepo.php';
require_once __DIR__ . '/app/Repositories/UserRepo.php';
require_once __DIR__ . '/app/Repositories/TaskRepo.php';
require_once __DIR__ . '/app/Repositories/TaskCommentRepo.php';
require_once __DIR__ . '/app/Repositories/TaskTemplateRepo.php';
require_once __DIR__ . '/app/Repositories/DueRepo.php';
require_once __DIR__ . '/app/Repositories/ExpenseRepo.php';
require_once __DIR__ . '/app/Repositories/ReportsRepo.php';
require_once __DIR__ . '/app/Repositories/NotesRepo.php';
require_once __DIR__ . '/app/Repositories/ActivityLogRepo.php';
require_once __DIR__ . '/app/Repositories/SearchRepo.php';
require_once __DIR__ . '/app/Repositories/UnpaidTaskRepo.php';
require_once __DIR__ . '/app/Repositories/SafeItemRepo.php';
require_once __DIR__ . '/app/Repositories/ServiceTypeRepo.php';

// Load services
require_once __DIR__ . '/app/Services/WhatsAppService.php';
require_once __DIR__ . '/app/Services/EmailService.php';

// Load controllers
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/ClientsController.php';
require_once __DIR__ . '/app/Controllers/ServicesController.php';
require_once __DIR__ . '/app/Controllers/ProjectsController.php';
require_once __DIR__ . '/app/Controllers/SettingsController.php';
require_once __DIR__ . '/app/Controllers/UsersController.php';
require_once __DIR__ . '/app/Controllers/TasksController.php';
require_once __DIR__ . '/app/Controllers/TaskCommentController.php';
require_once __DIR__ . '/app/Controllers/TaskTemplateController.php';
require_once __DIR__ . '/app/Controllers/DuesController.php';
require_once __DIR__ . '/app/Controllers/ExpensesController.php';
require_once __DIR__ . '/app/Controllers/FinanceController.php';
require_once __DIR__ . '/app/Controllers/ReportsController.php';
require_once __DIR__ . '/app/Controllers/NotesController.php';
require_once __DIR__ . '/app/Controllers/PersonalController.php';
require_once __DIR__ . '/app/Controllers/LogsController.php';
require_once __DIR__ . '/app/Controllers/SearchController.php';
require_once __DIR__ . '/app/Controllers/ExportController.php';
require_once __DIR__ . '/app/Controllers/BulkController.php';
require_once __DIR__ . '/app/Controllers/TagsController.php';
require_once __DIR__ . '/app/Controllers/UnpaidTasksController.php';
require_once __DIR__ . '/app/Controllers/SafeController.php';
require_once __DIR__ . '/app/Controllers/ServiceTypesController.php';
require_once __DIR__ . '/app/Controllers/QuotationsController.php';
require_once __DIR__ . '/app/Controllers/InvoicesController.php';

use App\Core\Router;
use App\Core\Auth;

// Initialize router
$router = new Router();

// Auth middleware for protected routes
$authMiddleware = function () {
    $publicRoutes = ['/login', '/lang'];
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $isPublic = in_array($currentPath, $publicRoutes) || str_starts_with($currentPath, '/lang/');
    if (!$isPublic && !Auth::check()) {
        header('Location: /login');
        exit;
    }

    // Force-logout users deactivated while logged in
    if (!$isPublic && Auth::check()) {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $user = \App\Core\DB::fetch("SELECT is_active FROM users WHERE id = :id", ['id' => $userId]);
            if (!$user || empty($user['is_active'])) {
                Auth::logout();
                session_start();
                $_SESSION['flash_error'] = __('auth.account_inactive');
                header('Location: /login');
                exit;
            }
        }
    }

    if ($currentPath === '/login' && Auth::check()) {
        header('Location: /dashboard');
        exit;
    }
};

$router->middleware($authMiddleware);

// Role-based access control middleware
$roleMiddleware = function () {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (!Auth::check()) {
        return;
    }

    $role = Auth::role();

    // Admin sees everything
    if ($role === 'admin') {
        return;
    }

    // Admin-only routes
    $adminOnly = ['/settings', '/users', '/logs'];
    foreach ($adminOnly as $prefix) {
        if ($currentPath === $prefix || str_starts_with($currentPath, $prefix . '/')) {
            \App\Core\Response::withError(__('auth.unauthorized'));
            \App\Core\Response::redirect('/dashboard');
        }
    }

    // Manager sees everything except admin-only routes (already handled above)
    if ($role === 'manager') {
        return;
    }

    // Employee: block everything except allowed routes
    $employeeBlocked = [
        '/clients', '/tags', '/services', '/service-types',
        '/dues', '/expenses', '/unpaid-tasks',
        '/finance', '/reports',
        '/safe', '/quotations', '/invoices',
        '/personal', '/export', '/bulk',
        '/tasks/templates', '/tasks/team-board', '/tasks/create',
        '/projects/create',
    ];

    foreach ($employeeBlocked as $prefix) {
        if ($currentPath === $prefix || str_starts_with($currentPath, $prefix . '/')) {
            \App\Core\Response::withError(__('auth.unauthorized'));
            \App\Core\Response::redirect('/dashboard');
        }
    }
};

$router->middleware($roleMiddleware);

// Language switch route
$router->get('/lang/{code}', function ($code) {
    $available = \App\Core\Lang::available();
    if (isset($available[$code])) {
        $_SESSION['locale'] = $code;
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $serverHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($refererHost && $refererHost !== $serverHost) {
        $referer = '/dashboard';
    }
    header('Location: ' . $referer);
    exit;
});

// Auth routes
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->post('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
$router->get('/', [\App\Controllers\DashboardController::class, 'index']);

// Clients
$router->get('/clients', [\App\Controllers\ClientsController::class, 'index']);
$router->get('/clients/create', [\App\Controllers\ClientsController::class, 'create']);
$router->post('/clients', [\App\Controllers\ClientsController::class, 'store']);
$router->get('/clients/{id}', [\App\Controllers\ClientsController::class, 'show']);
$router->get('/clients/{id}/edit', [\App\Controllers\ClientsController::class, 'edit']);
$router->post('/clients/{id}', [\App\Controllers\ClientsController::class, 'update']);
$router->post('/clients/{id}/delete', [\App\Controllers\ClientsController::class, 'delete']);

// Tags
$router->get('/tags', [\App\Controllers\TagsController::class, 'index']);
$router->post('/tags', [\App\Controllers\TagsController::class, 'store']);
$router->post('/tags/{id}', [\App\Controllers\TagsController::class, 'update']);
$router->post('/tags/{id}/delete', [\App\Controllers\TagsController::class, 'delete']);

// Service Types
$router->get('/service-types', [\App\Controllers\ServiceTypesController::class, 'index']);
$router->post('/service-types', [\App\Controllers\ServiceTypesController::class, 'store']);
$router->post('/service-types/{id}', [\App\Controllers\ServiceTypesController::class, 'update']);
$router->post('/service-types/{id}/delete', [\App\Controllers\ServiceTypesController::class, 'delete']);

// Services
$router->get('/services', [\App\Controllers\ServicesController::class, 'index']);
$router->get('/services/create', [\App\Controllers\ServicesController::class, 'create']);
$router->post('/services', [\App\Controllers\ServicesController::class, 'store']);
$router->get('/services/{id}', [\App\Controllers\ServicesController::class, 'show']);
$router->get('/services/{id}/edit', [\App\Controllers\ServicesController::class, 'edit']);
$router->post('/services/{id}', [\App\Controllers\ServicesController::class, 'update']);
$router->post('/services/{id}/delete', [\App\Controllers\ServicesController::class, 'delete']);
$router->post('/services/{id}/link-clients', [\App\Controllers\ServicesController::class, 'linkClients']);
$router->post('/services/{id}/change-status', [\App\Controllers\ServicesController::class, 'changeStatus']);
$router->post('/services/{id}/renew', [\App\Controllers\ServicesController::class, 'renew']);

// Projects
$router->get('/projects', [\App\Controllers\ProjectsController::class, 'index']);
$router->get('/projects/create', [\App\Controllers\ProjectsController::class, 'create']);
$router->post('/projects', [\App\Controllers\ProjectsController::class, 'store']);
$router->get('/projects/{id}', [\App\Controllers\ProjectsController::class, 'show']);
$router->get('/projects/{id}/edit', [\App\Controllers\ProjectsController::class, 'edit']);
$router->post('/projects/{id}', [\App\Controllers\ProjectsController::class, 'update']);
$router->post('/projects/{id}/delete', [\App\Controllers\ProjectsController::class, 'delete']);

// Users (Employees)
$router->get('/users', [\App\Controllers\UsersController::class, 'index']);
$router->get('/users/create', [\App\Controllers\UsersController::class, 'create']);
$router->post('/users', [\App\Controllers\UsersController::class, 'store']);
$router->get('/users/{id}', [\App\Controllers\UsersController::class, 'show']);
$router->get('/users/{id}/edit', [\App\Controllers\UsersController::class, 'edit']);
$router->post('/users/{id}', [\App\Controllers\UsersController::class, 'update']);
$router->post('/users/{id}/delete', [\App\Controllers\UsersController::class, 'delete']);
$router->post('/users/{id}/toggle-active', [\App\Controllers\UsersController::class, 'toggleActive']);

// Tasks
$router->get('/tasks', [\App\Controllers\TasksController::class, 'index']);
$router->get('/tasks/my', [\App\Controllers\TasksController::class, 'myTasks']);
$router->get('/tasks/team-board', [\App\Controllers\TasksController::class, 'teamBoard']);
$router->get('/tasks/create', [\App\Controllers\TasksController::class, 'create']);
$router->post('/tasks', [\App\Controllers\TasksController::class, 'store']);
$router->get('/tasks/{id}', [\App\Controllers\TasksController::class, 'show']);
$router->get('/tasks/{id}/edit', [\App\Controllers\TasksController::class, 'edit']);
$router->post('/tasks/{id}', [\App\Controllers\TasksController::class, 'update']);
$router->post('/tasks/{id}/delete', [\App\Controllers\TasksController::class, 'delete']);
$router->post('/tasks/{id}/status', [\App\Controllers\TasksController::class, 'updateStatus']);
$router->post('/tasks/{id}/progress', [\App\Controllers\TasksController::class, 'updateProgress']);
$router->post('/tasks/{id}/log-time', [\App\Controllers\TasksController::class, 'logTime']);

// Task Comments
$router->post('/tasks/{id}/comments', [\App\Controllers\TaskCommentController::class, 'store']);

// Task Templates
$router->get('/tasks/templates', [\App\Controllers\TaskTemplateController::class, 'index']);
$router->get('/tasks/templates/create', [\App\Controllers\TaskTemplateController::class, 'create']);
$router->post('/tasks/templates', [\App\Controllers\TaskTemplateController::class, 'store']);
$router->get('/tasks/templates/{id}/edit', [\App\Controllers\TaskTemplateController::class, 'edit']);
$router->post('/tasks/templates/{id}', [\App\Controllers\TaskTemplateController::class, 'update']);
$router->post('/tasks/templates/{id}/delete', [\App\Controllers\TaskTemplateController::class, 'delete']);
$router->get('/tasks/templates/{id}/use', [\App\Controllers\TaskTemplateController::class, 'use']);
$router->post('/tasks/templates/{id}/create-task', [\App\Controllers\TaskTemplateController::class, 'createTask']);

// Dues (Personal Money Tracker)
$router->get('/dues', [\App\Controllers\DuesController::class, 'index']);
$router->get('/dues/create', [\App\Controllers\DuesController::class, 'create']);
$router->post('/dues', [\App\Controllers\DuesController::class, 'store']);
$router->get('/dues/{id}', [\App\Controllers\DuesController::class, 'show']);
$router->get('/dues/{id}/edit', [\App\Controllers\DuesController::class, 'edit']);
$router->post('/dues/{id}', [\App\Controllers\DuesController::class, 'update']);
$router->post('/dues/{id}/mark-paid', [\App\Controllers\DuesController::class, 'markPaid']);
$router->post('/dues/{id}/delete', [\App\Controllers\DuesController::class, 'delete']);

// Expenses (Money You Owe)
$router->get('/expenses', [\App\Controllers\ExpensesController::class, 'index']);
$router->get('/expenses/create', [\App\Controllers\ExpensesController::class, 'create']);
$router->post('/expenses', [\App\Controllers\ExpensesController::class, 'store']);
$router->get('/expenses/{id}', [\App\Controllers\ExpensesController::class, 'show']);
$router->get('/expenses/{id}/edit', [\App\Controllers\ExpensesController::class, 'edit']);
$router->post('/expenses/{id}', [\App\Controllers\ExpensesController::class, 'update']);
$router->post('/expenses/{id}/mark-paid', [\App\Controllers\ExpensesController::class, 'markPaid']);
$router->post('/expenses/{id}/delete', [\App\Controllers\ExpensesController::class, 'delete']);

// Unpaid Tasks (Unquoted work per client)
$router->get('/unpaid-tasks', [\App\Controllers\UnpaidTasksController::class, 'index']);
$router->get('/unpaid-tasks/create', [\App\Controllers\UnpaidTasksController::class, 'create']);
$router->post('/unpaid-tasks', [\App\Controllers\UnpaidTasksController::class, 'store']);
$router->get('/unpaid-tasks/{id}', [\App\Controllers\UnpaidTasksController::class, 'show']);
$router->get('/unpaid-tasks/{id}/edit', [\App\Controllers\UnpaidTasksController::class, 'edit']);
$router->post('/unpaid-tasks/{id}', [\App\Controllers\UnpaidTasksController::class, 'update']);
$router->post('/unpaid-tasks/{id}/delete', [\App\Controllers\UnpaidTasksController::class, 'delete']);
$router->post('/unpaid-tasks/{id}/change-status', [\App\Controllers\UnpaidTasksController::class, 'updateStatus']);

// Finance (Statistics)
$router->get('/finance', [\App\Controllers\FinanceController::class, 'index']);

// Reports (Analytics Hub)
$router->get('/reports', [\App\Controllers\ReportsController::class, 'index']);

// Notes (Personal Notes & Reminders)
$router->get('/notes', [\App\Controllers\NotesController::class, 'index']);
$router->get('/notes/create', [\App\Controllers\NotesController::class, 'create']);
$router->post('/notes', [\App\Controllers\NotesController::class, 'store']);
$router->get('/notes/{id}', [\App\Controllers\NotesController::class, 'show']);
$router->get('/notes/{id}/edit', [\App\Controllers\NotesController::class, 'edit']);
$router->post('/notes/{id}', [\App\Controllers\NotesController::class, 'update']);
$router->post('/notes/{id}/delete', [\App\Controllers\NotesController::class, 'delete']);
$router->post('/notes/{id}/toggle-pin', [\App\Controllers\NotesController::class, 'togglePin']);
$router->post('/notes/{id}/archive', [\App\Controllers\NotesController::class, 'archive']);
$router->post('/notes/{id}/restore', [\App\Controllers\NotesController::class, 'restore']);

// Safe (Digital Vault / المخزن الآمن)
$router->get('/safe', [\App\Controllers\SafeController::class, 'index']);
$router->get('/safe/create', [\App\Controllers\SafeController::class, 'create']);
$router->post('/safe', [\App\Controllers\SafeController::class, 'store']);
$router->get('/safe/{id}', [\App\Controllers\SafeController::class, 'show']);
$router->get('/safe/{id}/edit', [\App\Controllers\SafeController::class, 'edit']);
$router->get('/safe/{id}/download', [\App\Controllers\SafeController::class, 'download']);
$router->post('/safe/{id}', [\App\Controllers\SafeController::class, 'update']);
$router->post('/safe/{id}/delete', [\App\Controllers\SafeController::class, 'delete']);

// Safe file download (shared across all types)
$router->get('/safe/files/{id}/download', [\App\Controllers\SafeController::class, 'downloadFile']);

// Quotations (عروض الأسعار)
$router->get('/quotations', [\App\Controllers\QuotationsController::class, 'index']);
$router->get('/quotations/create', [\App\Controllers\QuotationsController::class, 'create']);
$router->post('/quotations', [\App\Controllers\QuotationsController::class, 'store']);
$router->get('/quotations/{id}', [\App\Controllers\QuotationsController::class, 'show']);
$router->get('/quotations/{id}/edit', [\App\Controllers\QuotationsController::class, 'edit']);
$router->post('/quotations/{id}', [\App\Controllers\QuotationsController::class, 'update']);
$router->post('/quotations/{id}/delete', [\App\Controllers\QuotationsController::class, 'delete']);

// Invoices (فواتير العملاء)
$router->get('/invoices', [\App\Controllers\InvoicesController::class, 'index']);
$router->get('/invoices/create', [\App\Controllers\InvoicesController::class, 'create']);
$router->post('/invoices', [\App\Controllers\InvoicesController::class, 'store']);
$router->get('/invoices/{id}', [\App\Controllers\InvoicesController::class, 'show']);
$router->get('/invoices/{id}/edit', [\App\Controllers\InvoicesController::class, 'edit']);
$router->post('/invoices/{id}', [\App\Controllers\InvoicesController::class, 'update']);
$router->post('/invoices/{id}/delete', [\App\Controllers\InvoicesController::class, 'delete']);

// Personal (Dashboard & Calendar)
$router->get('/personal', [\App\Controllers\PersonalController::class, 'index']);
$router->get('/personal/calendar', [\App\Controllers\PersonalController::class, 'calendar']);

// Search
$router->get('/search', [\App\Controllers\SearchController::class, 'index']);

// Export (CSV)
$router->get('/export/clients', [\App\Controllers\ExportController::class, 'clients']);
$router->get('/export/services', [\App\Controllers\ExportController::class, 'services']);
$router->get('/export/dues', [\App\Controllers\ExportController::class, 'dues']);
$router->get('/export/expenses', [\App\Controllers\ExportController::class, 'expenses']);
$router->get('/export/tasks', [\App\Controllers\ExportController::class, 'tasks']);

// Export (PDF - print view)
$router->get('/export/clients/pdf', [\App\Controllers\ExportController::class, 'clientsPdf']);
$router->get('/export/services/pdf', [\App\Controllers\ExportController::class, 'servicesPdf']);
$router->get('/export/dues/pdf', [\App\Controllers\ExportController::class, 'duesPdf']);
$router->get('/export/expenses/pdf', [\App\Controllers\ExportController::class, 'expensesPdf']);
$router->get('/export/tasks/pdf', [\App\Controllers\ExportController::class, 'tasksPdf']);

// Bulk Operations
$router->post('/bulk/services', [\App\Controllers\BulkController::class, 'services']);
$router->post('/bulk/tasks', [\App\Controllers\BulkController::class, 'tasks']);
$router->post('/bulk/projects', [\App\Controllers\BulkController::class, 'projects']);
$router->post('/bulk/clients', [\App\Controllers\BulkController::class, 'clients']);
$router->post('/bulk/dues', [\App\Controllers\BulkController::class, 'dues']);
$router->post('/bulk/expenses', [\App\Controllers\BulkController::class, 'expenses']);
$router->post('/bulk/notes', [\App\Controllers\BulkController::class, 'notes']);
$router->post('/bulk/safe', [\App\Controllers\BulkController::class, 'safe']);
$router->post('/bulk/unpaid-tasks', [\App\Controllers\BulkController::class, 'unpaidTasks']);

// Logs (Admin only)
$router->get('/logs', [\App\Controllers\LogsController::class, 'activity']);
$router->get('/logs/notifications', [\App\Controllers\LogsController::class, 'notifications']);
$router->get('/logs/cron', [\App\Controllers\LogsController::class, 'cron']);

// Settings
$router->get('/settings', [\App\Controllers\SettingsController::class, 'index']);
$router->post('/settings', [\App\Controllers\SettingsController::class, 'update']);
$router->post('/settings/email-test', [\App\Controllers\SettingsController::class, 'testEmail']);
$router->post('/settings/whatsapp-test', [\App\Controllers\SettingsController::class, 'testWhatsApp']);

// Dispatch
$router->dispatch();
