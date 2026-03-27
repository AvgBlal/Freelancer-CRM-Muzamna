<?php
/**
 * FCM CRM — Install Wizard
 * Self-contained browser-based installer (no app dependencies)
 */

session_start();

// Load available languages from config
$languages = [];
$langConfigPath = __DIR__ . '/../app/Config/languages.php';
if (file_exists($langConfigPath)) {
    $languages = require $langConfigPath;
}
if (empty($languages)) {
    $languages = [
        'ar' => ['name' => 'العربية', 'dir' => 'rtl', 'font' => 'Alexandria'],
        'en' => ['name' => 'English', 'dir' => 'ltr', 'font' => 'Inter'],
    ];
}

// Determine installer language from GET, POST, or session
if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
    $_SESSION['install_lang'] = $_GET['lang'];
}
$lang = $_SESSION['install_lang'] ?? 'ar';
if (!isset($languages[$lang])) $lang = 'ar';
$langDir = $languages[$lang]['dir'];
$langIsRtl = $langDir === 'rtl';
$fontFamily = $languages[$lang]['font'];

// Installer-specific translations (self-contained, no app dependency)
$t = [
    'ar' => [
        'install_title' => 'تثبيت نظام إدارة العملاء',
        'install_subtitle' => 'FCM CRM — Freelancer Client Manager',
        'already_installed' => 'النظام مُثبت بالفعل',
        'already_installed_hint' => 'لأسباب أمنية، يُنصح بحذف مجلد',
        'login' => 'تسجيل الدخول',
        'steps' => [
            1 => 'فحص المتطلبات',
            2 => 'قاعدة البيانات',
            3 => 'استيراد البيانات',
            4 => 'إنشاء المدير',
            5 => 'اكتمل التثبيت',
        ],
        // Step 1
        'requirements_title' => 'فحص متطلبات النظام',
        'check_config_writable' => 'مجلد Config قابل للكتابة',
        'check_db_file' => 'ملف database.sql موجود',
        'next' => 'التالي',
        'requirements_fail' => 'يرجى تلبية جميع المتطلبات قبل المتابعة',
        // Step 2
        'db_title' => 'إعدادات قاعدة البيانات',
        'db_host' => 'خادم قاعدة البيانات',
        'db_name' => 'اسم قاعدة البيانات',
        'db_user' => 'اسم المستخدم',
        'db_pass' => 'كلمة المرور',
        'db_test' => 'اختبار الاتصال والمتابعة',
        'db_required' => 'اسم قاعدة البيانات واسم المستخدم مطلوبان',
        'db_fail' => 'فشل الاتصال: ',
        // Step 3
        'import_title' => 'استيراد قاعدة البيانات',
        'import_info' => 'سيتم إنشاء جميع جداول النظام (28 جدول) وإعداد البيانات الافتراضية',
        'import_demo' => 'تثبيت البيانات التجريبية',
        'import_demo_desc' => '3 مستخدمين، 6 عملاء، 10 خدمات، 5 مشاريع، 12 مهمة، وبيانات أخرى',
        'import_btn' => 'استيراد وتثبيت',
        'import_no_file' => 'ملف database.sql غير موجود في مجلد install/',
        'import_error' => 'خطأ في استيراد قاعدة البيانات: ',
        // Step 4
        'admin_title' => 'إنشاء حساب المدير',
        'admin_name' => 'الاسم',
        'admin_name_placeholder' => 'أحمد المدير',
        'admin_email' => 'البريد الإلكتروني',
        'admin_pass' => 'كلمة المرور (6 أحرف على الأقل)',
        'admin_pass_confirm' => 'تأكيد كلمة المرور',
        'admin_btn' => 'إنشاء الحساب وإنهاء التثبيت',
        'admin_required' => 'جميع الحقول مطلوبة',
        'admin_email_invalid' => 'البريد الإلكتروني غير صالح',
        'admin_pass_short' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        'admin_pass_mismatch' => 'كلمتا المرور غير متطابقتين',
        'admin_error' => 'خطأ في إنشاء المستخدم: ',
        // Step 5
        'done_title' => 'تم التثبيت بنجاح!',
        'done_subtitle' => 'النظام جاهز للاستخدام',
        'done_email' => 'البريد:',
        'done_security' => 'تنبيه أمني',
        'done_security_hint' => 'يرجى حذف مجلد <code class="bg-yellow-100 px-2 py-1 rounded">install/</code> فوراً لحماية النظام',
    ],
    'en' => [
        'install_title' => 'Install Client Manager',
        'install_subtitle' => 'FCM CRM — Freelancer Client Manager',
        'already_installed' => 'System Already Installed',
        'already_installed_hint' => 'For security reasons, it is recommended to delete the',
        'login' => 'Login',
        'steps' => [
            1 => 'Requirements',
            2 => 'Database',
            3 => 'Import Data',
            4 => 'Create Admin',
            5 => 'Complete',
        ],
        // Step 1
        'requirements_title' => 'System Requirements Check',
        'check_config_writable' => 'Config directory is writable',
        'check_db_file' => 'database.sql file exists',
        'next' => 'Next',
        'requirements_fail' => 'Please meet all requirements before proceeding',
        // Step 2
        'db_title' => 'Database Configuration',
        'db_host' => 'Database Host',
        'db_name' => 'Database Name',
        'db_user' => 'Username',
        'db_pass' => 'Password',
        'db_test' => 'Test Connection & Continue',
        'db_required' => 'Database name and username are required',
        'db_fail' => 'Connection failed: ',
        // Step 3
        'import_title' => 'Import Database',
        'import_info' => 'All system tables (28 tables) and default data will be created',
        'import_demo' => 'Install demo data',
        'import_demo_desc' => '3 users, 6 clients, 10 services, 5 projects, 12 tasks, and more',
        'import_btn' => 'Import & Install',
        'import_no_file' => 'database.sql file not found in install/ directory',
        'import_error' => 'Database import error: ',
        // Step 4
        'admin_title' => 'Create Admin Account',
        'admin_name' => 'Name',
        'admin_name_placeholder' => 'Admin User',
        'admin_email' => 'Email',
        'admin_pass' => 'Password (minimum 6 characters)',
        'admin_pass_confirm' => 'Confirm Password',
        'admin_btn' => 'Create Account & Finish Installation',
        'admin_required' => 'All fields are required',
        'admin_email_invalid' => 'Invalid email address',
        'admin_pass_short' => 'Password must be at least 6 characters',
        'admin_pass_mismatch' => 'Passwords do not match',
        'admin_error' => 'Error creating user: ',
        // Step 5
        'done_title' => 'Installation Complete!',
        'done_subtitle' => 'The system is ready to use',
        'done_email' => 'Email:',
        'done_security' => 'Security Notice',
        'done_security_hint' => 'Please delete the <code class="bg-yellow-100 px-2 py-1 rounded">install/</code> directory immediately to protect your system',
    ],
];

// Translation function for installer
$strings = $t[$lang] ?? $t['ar'];
function _t(string $key) {
    global $strings;
    return $strings[$key] ?? $key;
}

// Block access if already installed
$configLocalPath = __DIR__ . '/../app/Config/config.local.php';
if (file_exists($configLocalPath)) {
    ?><!DOCTYPE html>
    <html dir="<?= $langDir ?>" lang="<?= $lang ?>"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _t('already_installed') ?></title><script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontFamily) ?>:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'<?= $fontFamily ?>',sans-serif;}</style></head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="text-6xl mb-4">&#10004;</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= _t('already_installed') ?></h1>
        <p class="text-gray-500 mb-4"><?= _t('already_installed_hint') ?> <code class="bg-gray-100 px-2 py-1 rounded">install/</code></p>
        <a href="/login" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"><?= _t('login') ?></a>
        <div class="mt-4 flex justify-center">
            <select onchange="window.location.href='?lang=' + this.value"
                    class="text-sm px-3 py-1 border rounded-lg text-gray-600 cursor-pointer">
                <?php foreach ($languages as $code => $l): ?>
                    <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= $l['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div></body></html><?php
    exit;
}

// CSRF
if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['_csrf'];

function verifyCsrf(): bool {
    return isset($_POST['_csrf']) && hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf']);
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Step routing
$step = (int)($_GET['step'] ?? $_POST['step'] ?? 1);
$error = '';

// Process POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    switch ($step) {
        case 2: // DB credentials submitted
            $host = trim($_POST['db_host'] ?? 'localhost');
            $name = trim($_POST['db_name'] ?? '');
            $user = trim($_POST['db_user'] ?? '');
            $pass = $_POST['db_pass'] ?? '';

            // Validate database name to prevent SQL injection
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                $error = _t('db_fail') . ' Invalid database name.';
                break;
            }

            if (empty($name) || empty($user)) {
                $error = _t('db_required');
                break;
            }

            try {
                // Test connection without database
                $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                // Test full connection
                $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                $_SESSION['install_db'] = compact('host', 'name', 'user', 'pass');
                unset($_SESSION['_csrf']);
                header('Location: install.php?step=3');
                exit;
            } catch (PDOException $e) {
                error_log('Install DB error: ' . $e->getMessage());
                $error = _t('db_fail');
            }
            break;

        case 3: // Import schema
            $db = $_SESSION['install_db'] ?? null;
            if (!$db) { header('Location: install.php?step=2'); exit; }

            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                // Read and execute schema
                $sqlFile = __DIR__ . '/database.sql';
                if (!file_exists($sqlFile)) {
                    $error = _t('import_no_file');
                    break;
                }

                $sql = file_get_contents($sqlFile);

                // Check if demo data should be skipped
                $withDemo = isset($_POST['with_demo']) && $_POST['with_demo'] === '1';

                if (!$withDemo) {
                    // Remove everything after "-- DEMO DATA" marker
                    $marker = '-- DEMO DATA';
                    $pos = strpos($sql, $marker);
                    if ($pos !== false) {
                        // Keep everything up to the marker line, plus the settings INSERT above it
                        $sql = substr($sql, 0, $pos);
                    }
                }

                // Split by semicolons (not inside strings)
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (empty($stmt) || $stmt === '--') continue;
                    // Skip SET and comment-only statements
                    $clean = trim($stmt);
                    if (strpos($clean, '--') === 0 && strpos($clean, "\n") === false) continue;
                    $pdo->exec($stmt);
                }
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

                $_SESSION['install_demo'] = $withDemo;
                unset($_SESSION['_csrf']);
                header('Location: install.php?step=4');
                exit;
            } catch (PDOException $e) {
                error_log('Install import error: ' . $e->getMessage());
                $error = _t('import_error');
            }
            break;

        case 4: // Create admin user
            $db = $_SESSION['install_db'] ?? null;
            if (!$db) { header('Location: install.php?step=2'); exit; }

            $adminName = trim($_POST['admin_name'] ?? '');
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPass = $_POST['admin_pass'] ?? '';
            $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';

            if (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
                $error = _t('admin_required');
                break;
            }
            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $error = _t('admin_email_invalid');
                break;
            }
            if (strlen($adminPass) < 6) {
                $error = _t('admin_pass_short');
                break;
            }
            if ($adminPass !== $adminPassConfirm) {
                $error = _t('admin_pass_mismatch');
                break;
            }

            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                $hash = password_hash($adminPass, PASSWORD_DEFAULT);

                // Check if email already exists (from demo data)
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$adminEmail]);
                if ($check->fetch()) {
                    // Update existing user
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, password_hash = ?, role = 'admin', is_active = 1 WHERE email = ?");
                    $stmt->execute([$adminName, $hash, $adminEmail]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, 'admin', 1, NOW())");
                    $stmt->execute([$adminName, $adminEmail, $hash]);
                }

                $_SESSION['install_admin_email'] = $adminEmail;
                unset($_SESSION['_csrf']);
                header('Location: install.php?step=5');
                exit;
            } catch (PDOException $e) {
                error_log('Install admin creation error: ' . $e->getMessage());
                $error = _t('admin_error');
            }
            break;
    }
}

// Step 5: Write config and finish
if ($step === 5 && !file_exists($configLocalPath)) {
    $db = $_SESSION['install_db'] ?? null;
    if (!$db) { header('Location: install.php?step=2'); exit; }

    $selectedLocale = $lang;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $safeHost = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $encryptionKey = bin2hex(random_bytes(32));
    $configContent = "<?php\n/**\n * Local Configuration (generated by installer)\n */\n\nreturn [\n    'db' => [\n        'host'     => " . var_export($db['host'], true) . ",\n        'database' => " . var_export($db['name'], true) . ",\n        'username' => " . var_export($db['user'], true) . ",\n        'password' => " . var_export($db['pass'], true) . ",\n        'charset'  => 'utf8mb4',\n    ],\n\n    'app' => [\n        'name'        => 'FCM CRM',\n        'env'         => 'production',\n        'debug'       => false,\n        'url'         => '" . e($scheme . '://' . $safeHost) . "',\n        'timezone'    => 'Africa/Cairo',\n        'locale'      => " . var_export($selectedLocale, true) . ",\n    ],\n\n    'session' => [\n        'lifetime' => 7200,\n        'secure'   => " . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'true' : 'false') . ",\n        'httponly' => true,\n    ],\n\n    'security' => [\n        'csrf_token_name' => 'csrf_token',\n        'encryption_key'  => '" . $encryptionKey . "',\n    ],\n];\n";

    file_put_contents($configLocalPath, $configContent);

    // Clear install session data
    $adminEmail = $_SESSION['install_admin_email'] ?? '';
    unset($_SESSION['install_db'], $_SESSION['install_demo'], $_SESSION['install_admin_email']);

    // Set the selected locale as the session default so user lands in the right language
    $_SESSION['locale'] = $selectedLocale;
}

// Steps config
$steps = $strings['steps'];

// Arrow icon direction
$arrowIcon = $langIsRtl ? 'fa-arrow-left' : 'fa-arrow-right';
// Icon margin class
$iconMargin = $langIsRtl ? 'ml-2' : 'mr-2';
$iconMarginSm = $langIsRtl ? 'ml-1' : 'mr-1';
?>
<!DOCTYPE html>
<html dir="<?= $langDir ?>" lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _t('install_title') ?> - FCM CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode($fontFamily) ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: '<?= $fontFamily ?>', sans-serif; background: #f3f4f6; }
        .step-active { background: #2563eb; color: white; }
        .step-done { background: #16a34a; color: white; }
        .step-pending { background: #e5e7eb; color: #6b7280; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-xl">
        <!-- Header -->
        <div class="bg-blue-600 text-white rounded-t-xl px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold"><i class="fas fa-briefcase <?= $iconMargin ?>"></i><?= _t('install_title') ?></h1>
                    <p class="text-blue-200 text-sm mt-1"><?= _t('install_subtitle') ?></p>
                </div>
                <!-- Language Selector -->
                <?php $params = $_GET; unset($params['lang']); ?>
                <div class="flex items-center gap-1 bg-blue-700 rounded-lg px-1">
                    <i class="fas fa-globe text-blue-300 text-sm"></i>
                    <select onchange="var p=new URLSearchParams(window.location.search);p.set('lang',this.value);window.location.search=p.toString()"
                            class="bg-blue-700 text-white text-sm px-2 py-1.5 rounded border-none outline-none cursor-pointer appearance-none">
                        <?php foreach ($languages as $code => $l): ?>
                            <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= $l['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Steps indicator -->
        <div class="flex items-center justify-center gap-2 px-6 py-4 border-b">
            <?php foreach ($steps as $num => $label): ?>
                <?php
                    $class = 'step-pending';
                    if ($num < $step) $class = 'step-done';
                    elseif ($num === $step) $class = 'step-active';
                ?>
                <div class="flex items-center gap-1">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold <?= $class ?>">
                        <?php if ($num < $step): ?><i class="fas fa-check text-xs"></i><?php else: ?><?= $num ?><?php endif; ?>
                    </span>
                    <span class="text-xs hidden sm:inline <?= $num === $step ? 'font-bold text-blue-600' : 'text-gray-400' ?>"><?= $label ?></span>
                </div>
                <?php if ($num < 5): ?><span class="text-gray-300">—</span><?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="p-6">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-circle <?= $iconMarginSm ?>"></i><?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Requirements Check -->
                <?php
                $checks = [
                    'PHP >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
                    'PDO MySQL' => extension_loaded('pdo_mysql'),
                    'Mbstring' => extension_loaded('mbstring'),
                    'JSON' => extension_loaded('json'),
                    _t('check_config_writable') => is_writable(__DIR__ . '/../app/Config/'),
                    _t('check_db_file') => file_exists(__DIR__ . '/database.sql'),
                ];
                $allPass = !in_array(false, $checks, true);
                ?>
                <h2 class="text-lg font-bold mb-4"><i class="fas fa-clipboard-check text-blue-600 <?= $iconMargin ?>"></i><?= _t('requirements_title') ?></h2>
                <div class="space-y-3">
                    <?php foreach ($checks as $label => $pass): ?>
                        <div class="flex items-center justify-between p-3 rounded-lg <?= $pass ? 'bg-green-50' : 'bg-red-50' ?>">
                            <span class="text-sm"><?= $label ?></span>
                            <?php if ($pass): ?>
                                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                            <?php else: ?>
                                <span class="text-red-600"><i class="fas fa-times-circle"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-sm text-gray-400 mt-3">PHP <?= PHP_VERSION ?></div>
                <?php if ($allPass): ?>
                    <a href="install.php?step=2" class="mt-4 block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-bold hover:bg-blue-700">
                        <?= _t('next') ?> <i class="fas <?= $arrowIcon ?> <?= $iconMarginSm ?>"></i>
                    </a>
                <?php else: ?>
                    <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                        <i class="fas fa-exclamation-triangle <?= $iconMarginSm ?>"></i><?= _t('requirements_fail') ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: Database Configuration -->
                <h2 class="text-lg font-bold mb-4"><i class="fas fa-database text-blue-600 <?= $iconMargin ?>"></i><?= _t('db_title') ?></h2>
                <form method="POST" action="install.php">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="step" value="2">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('db_host') ?></label>
                            <input type="text" name="db_host" value="<?= e($_POST['db_host'] ?? 'localhost') ?>" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('db_name') ?></label>
                            <input type="text" name="db_name" value="<?= e($_POST['db_name'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required placeholder="fcm_crm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('db_user') ?></label>
                            <input type="text" name="db_user" value="<?= e($_POST['db_user'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required placeholder="root">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('db_pass') ?></label>
                            <input type="password" name="db_pass" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr">
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700">
                        <i class="fas fa-plug <?= $iconMarginSm ?>"></i><?= _t('db_test') ?>
                    </button>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Import Schema -->
                <h2 class="text-lg font-bold mb-4"><i class="fas fa-file-import text-blue-600 <?= $iconMargin ?>"></i><?= _t('import_title') ?></h2>
                <form method="POST" action="install.php">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="step" value="3">

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-blue-700"><i class="fas fa-info-circle <?= $iconMarginSm ?>"></i><?= _t('import_info') ?></p>
                    </div>

                    <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="with_demo" value="1" checked class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <span class="font-medium text-sm"><?= _t('import_demo') ?></span>
                            <p class="text-xs text-gray-500 mt-1"><?= _t('import_demo_desc') ?></p>
                        </div>
                    </label>

                    <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700">
                        <i class="fas fa-download <?= $iconMarginSm ?>"></i><?= _t('import_btn') ?>
                    </button>
                </form>

            <?php elseif ($step === 4): ?>
                <!-- Step 4: Create Admin User -->
                <h2 class="text-lg font-bold mb-4"><i class="fas fa-user-shield text-blue-600 <?= $iconMargin ?>"></i><?= _t('admin_title') ?></h2>
                <form method="POST" action="install.php">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="step" value="4">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('admin_name') ?></label>
                            <input type="text" name="admin_name" value="<?= e($_POST['admin_name'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 text-sm" required placeholder="<?= _t('admin_name_placeholder') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('admin_email') ?></label>
                            <input type="email" name="admin_email" value="<?= e($_POST['admin_email'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required placeholder="admin@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('admin_pass') ?></label>
                            <input type="password" name="admin_pass" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required minlength="6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?= _t('admin_pass_confirm') ?></label>
                            <input type="password" name="admin_pass_confirm" class="w-full border rounded-lg px-4 py-2 text-sm" dir="ltr" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700">
                        <i class="fas fa-user-plus <?= $iconMarginSm ?>"></i><?= _t('admin_btn') ?>
                    </button>
                </form>

            <?php elseif ($step === 5): ?>
                <!-- Step 5: Complete -->
                <div class="text-center">
                    <div class="text-6xl text-green-500 mb-4"><i class="fas fa-check-circle"></i></div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2"><?= _t('done_title') ?></h2>
                    <p class="text-gray-500 mb-6"><?= _t('done_subtitle') ?></p>

                    <?php if (!empty($adminEmail)): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm" style="text-align: start;">
                            <p><strong><?= _t('done_email') ?></strong> <?= e($adminEmail) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm" style="text-align: start;">
                        <p class="font-bold text-yellow-700"><i class="fas fa-exclamation-triangle <?= $iconMarginSm ?>"></i><?= _t('done_security') ?></p>
                        <p class="text-yellow-600 mt-1"><?= _t('done_security_hint') ?></p>
                    </div>

                    <a href="/login" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700">
                        <i class="fas fa-sign-in-alt <?= $iconMarginSm ?>"></i><?= _t('login') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="border-t px-6 py-3 text-center text-xs text-gray-400">
            FCM CRM &copy; <?= date('Y') ?>
        </div>
    </div>
</body>
</html>
