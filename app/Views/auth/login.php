<!DOCTYPE html>
<html dir="<?= \App\Core\Lang::dir() ?>" lang="<?= \App\Core\Lang::locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('auth.login') ?> - <?= __('app.name') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=<?= urlencode(\App\Core\Lang::fontFamily()) ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
<?php $_customCss = \App\Repositories\SettingsRepo::get('custom_css', ''); if ($_customCss !== ''): ?>
    <style><?= str_replace('</style', '<\\/style', $_customCss) ?></style>
<?php endif; ?>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="text-center mb-3">
                <i class="fas fa-briefcase text-blue-600 text-4xl mb-2"></i>
                <h1 class="login-title" style="margin-bottom: 0;"><?= __('auth.login_title') ?></h1>
                <p class="text-muted" style="font-size:0.85rem;"><?= __('app.name_sub') ?></p>
            </div>

            <?php $flash = \App\Core\Response::getFlash(); ?>
            <?php if ($flash['error']): ?>
                <div class="alert alert-error mb-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($flash['error']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <?= $csrf ?>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope text-gray-400 <?= \App\Core\Lang::isRtl() ? 'ml-1' : 'mr-1' ?>"></i> <?= __('auth.email') ?></label>
                    <input type="email" name="email" class="form-input ltr-input" required autofocus placeholder="admin@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock text-gray-400 <?= \App\Core\Lang::isRtl() ? 'ml-1' : 'mr-1' ?>"></i> <?= __('auth.password') ?></label>
                    <input type="password" name="password" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content: center; padding: 0.75rem;">
                    <i class="fas fa-sign-in-alt"></i>
                    <?= __('auth.login_btn') ?>
                </button>
            </form>

            <?php $_loginHtml = \App\Repositories\SettingsRepo::get('login_custom_html', ''); if ($_loginHtml !== ''): ?>
            <div class="login-custom-message mt-3">
                <?= $_loginHtml ?>
            </div>
            <?php endif; ?>

            <div class="text-center mt-3">
                <select onchange="window.location.href='/lang/'+this.value"
                        class="bg-gray-700 text-gray-300 text-xs px-2 py-1 rounded border border-gray-600 outline-none cursor-pointer">
                    <?php foreach (\App\Core\Lang::available() as $code => $l): ?>
                        <option value="<?= $code ?>" <?= $code === \App\Core\Lang::locale() ? 'selected' : '' ?>><?= $l['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</body>
</html>
