<?php
$flash = \App\Core\Response::getFlash();
$user = \App\Core\Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$langDir = \App\Core\Lang::dir();
$langIsRtl = \App\Core\Lang::isRtl();
$fontFamily = \App\Core\Lang::fontFamily();
?>
<!DOCTYPE html>
<html dir="<?= $langDir ?>" lang="<?= \App\Core\Lang::locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/svg+xml" href="/assets/icons/icon.svg">
    <link rel="apple-touch-icon" href="/assets/icons/icon.svg">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?><?= __('app.name') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontFamily) ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['<?= $fontFamily ?>', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                }
            }
        }
    }
    </script>
    <link rel="stylesheet" href="/assets/css/main.css">
<?php $_customCss = \App\Repositories\SettingsRepo::get('custom_css', ''); if ($_customCss !== ''): ?>
    <style><?= str_replace('</style', '<\\/style', $_customCss) ?></style>
<?php endif; ?>
</head>
<body class="bg-gray-100 text-gray-800">
<?php if ($user): ?>
    <!-- Mobile overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 <?= $langIsRtl ? 'right-0' : 'left-0' ?> z-50 h-full w-64 bg-gray-900 text-white transform <?= $langIsRtl ? 'translate-x-full lg:translate-x-0' : '-translate-x-full lg:translate-x-0' ?> transition-transform duration-300 ease-in-out overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center justify-between p-4 border-b border-gray-700">
            <a href="/dashboard" class="flex items-center gap-2 text-white no-underline">
                <i class="fas fa-briefcase text-blue-400 text-xl"></i>
                <span class="font-bold text-lg">FCM</span>
            </a>
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Search -->
        <div class="p-3">
            <form method="GET" action="/search">
                <div class="relative">
                    <i class="fas fa-search absolute top-1/2 <?= $langIsRtl ? 'right-3' : 'left-3' ?> -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" name="q" placeholder="<?= __('nav.search') ?>"
                           class="w-full bg-gray-800 text-white text-sm rounded-lg <?= $langIsRtl ? 'pr-9 pl-3' : 'pl-9 pr-3' ?> py-2 border border-gray-700 focus:border-blue-500 focus:outline-none placeholder-gray-500"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
            </form>
        </div>

        <!-- Navigation -->
        <nav class="px-3 pb-4">
            <?php
            $userRole = $user['role'] ?? 'employee';
            $isEmployee = $userRole === 'employee';
            $isAdmin = $userRole === 'admin';

            if (!function_exists('renderNavItems')) {
            function renderNavItems(array $items, string $currentPath): void {
                foreach ($items as $item) {
                    $isActive = ($currentPath === $item['url'] || str_starts_with($currentPath, $item['url'] . '/'));
                    $activeClass = $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white';
                    echo '<a href="' . $item['url'] . '" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors ' . $activeClass . ' no-underline mb-0.5">';
                    echo '<i class="fas ' . $item['icon'] . ' w-5 text-center"></i>';
                    echo '<span>' . $item['label'] . '</span>';
                    echo '</a>';
                }
            }
            }

            // Employee: dashboard, tasks, projects, notes only
            if ($isEmployee):
            ?>
            <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-2"><?= __('nav.sections.main') ?></p>
            <?php
                renderNavItems([
                    ['url' => '/dashboard', 'icon' => 'fa-home', 'label' => __('nav.dashboard')],
                    ['url' => '/tasks', 'icon' => 'fa-tasks', 'label' => __('nav.tasks')],
                    ['url' => '/tasks/my', 'icon' => 'fa-clipboard-check', 'label' => __('nav.my_tasks')],
                    ['url' => '/projects', 'icon' => 'fa-project-diagram', 'label' => __('nav.projects')],
                    ['url' => '/notes', 'icon' => 'fa-sticky-note', 'label' => __('nav.notes')],
                ], $currentPath);
            ?>
            <?php else: ?>
            <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-2"><?= __('nav.sections.main') ?></p>
            <?php
                renderNavItems([
                    ['url' => '/dashboard', 'icon' => 'fa-home', 'label' => __('nav.dashboard')],
                    ['url' => '/clients', 'icon' => 'fa-users', 'label' => __('nav.clients')],
                    ['url' => '/tags', 'icon' => 'fa-tags', 'label' => __('nav.tags')],
                    ['url' => '/services', 'icon' => 'fa-server', 'label' => __('nav.services')],
                    ['url' => '/service-types', 'icon' => 'fa-layer-group', 'label' => __('nav.service_types')],
                    ['url' => '/projects', 'icon' => 'fa-project-diagram', 'label' => __('nav.projects')],
                    ['url' => '/tasks', 'icon' => 'fa-tasks', 'label' => __('nav.tasks')],
                ], $currentPath);
            ?>

            <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-2 mt-4"><?= __('nav.sections.finance') ?></p>
            <?php renderNavItems([
                    ['url' => '/dues', 'icon' => 'fa-hand-holding-usd', 'label' => __('nav.dues')],
                    ['url' => '/expenses', 'icon' => 'fa-receipt', 'label' => __('nav.expenses')],
                    ['url' => '/unpaid-tasks', 'icon' => 'fa-exclamation-triangle', 'label' => __('nav.unpaid_tasks')],
                    ['url' => '/quotations', 'icon' => 'fa-file-invoice-dollar', 'label' => __('nav.quotations')],
                    ['url' => '/invoices', 'icon' => 'fa-file-invoice', 'label' => __('nav.invoices')],
                    ['url' => '/finance', 'icon' => 'fa-chart-pie', 'label' => __('nav.finance')],
                ], $currentPath); ?>

            <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-2 mt-4"><?= __('nav.sections.tools') ?></p>
            <?php renderNavItems([
                    ['url' => '/safe', 'icon' => 'fa-shield-alt', 'label' => __('nav.safe')],
                    ['url' => '/notes', 'icon' => 'fa-sticky-note', 'label' => __('nav.notes')],
                    ['url' => '/personal', 'icon' => 'fa-user-circle', 'label' => __('nav.personal')],
                    ['url' => '/reports', 'icon' => 'fa-chart-bar', 'label' => __('nav.reports')],
                ], $currentPath); ?>

            <?php if ($isAdmin): ?>
            <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-2 mt-4"><?= __('nav.sections.admin') ?></p>
            <?php renderNavItems([
                    ['url' => '/users', 'icon' => 'fa-user-tie', 'label' => __('nav.users')],
                    ['url' => '/logs', 'icon' => 'fa-history', 'label' => __('nav.logs')],
                    ['url' => '/settings', 'icon' => 'fa-cog', 'label' => __('nav.settings')],
                ], $currentPath); ?>
            <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- User section at bottom -->
        <div class="border-t border-gray-700 p-3 mt-auto">
            <!-- Language switcher -->
            <div class="flex items-center gap-2 px-3 py-2 mb-2">
                <i class="fas fa-globe text-gray-400 w-5 text-center"></i>
                <select onchange="window.location.href='/lang/'+this.value"
                        class="bg-gray-700 text-gray-300 text-xs px-2 py-1 rounded border border-gray-600 outline-none cursor-pointer flex-1">
                    <?php foreach (\App\Core\Lang::available() as $code => $l): ?>
                        <option value="<?= $code ?>" <?= $code === \App\Core\Lang::locale() ? 'selected' : '' ?>><?= $l['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                    <?= mb_substr($user['name'], 0, 1) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate"><?= htmlspecialchars($user['name']) ?></div>
                    <div class="text-xs text-gray-400 truncate"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </div>
            <form method="POST" action="/logout" class="mt-2">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span><?= __('auth.logout') ?></span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main wrapper -->
    <div class="<?= $langIsRtl ? 'lg:mr-64' : 'lg:ml-64' ?> min-h-screen flex flex-col transition-all duration-300">
        <!-- Top bar (mobile) -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-900 text-xl">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800 hidden sm:block"><?= isset($title) ? htmlspecialchars($title) : __('app.name') ?></h1>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Mobile search toggle -->
                    <a href="/search" class="sm:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-search"></i>
                    </a>
                    <!-- User badge (desktop) -->
                    <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($user['name']) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content area -->
        <main class="flex-1 p-4 md:p-6">
<?php else: ?>
    <!-- Guest layout (login page) -->
    <main>
<?php endif; ?>

            <?php if ($flash['error']): ?>
                <div data-flash="error" class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($flash['error']) ?>
                </div>
            <?php endif; ?>
            <?php if ($flash['success']): ?>
                <div data-flash="success" class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($flash['success']) ?>
                </div>
            <?php endif; ?>