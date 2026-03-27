<?php $title = __('settings.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-cog text-blue-500"></i> <?= __('settings.title') ?></h2>
    </div>

    <form method="POST" action="/settings">
        <?= $csrf ?>

        <h3 class="mb-2"><i class="fas fa-sliders-h text-gray-400"></i> <?= __("settings.general") ?></h3>

        <div class="form-group">
            <label class="form-label"><?= __("settings.default_currency") ?></label>
            <?php $currencyFieldName = 'default_currency'; $currencySelected = $settings['default_currency'] ?? 'EGP'; ?>
            <?php require __DIR__ . '/../partials/currency_select.php'; ?>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("settings.reminder_days") ?></label>
            <?php
            $reminderDaysStr = $settings['reminder_days'] ?? '30';
            $activeDays = array_map('intval', array_filter(explode(',', $reminderDaysStr), 'is_numeric'));
            $commonDays = [60, 30, 14, 7, 3, 1];
            ?>
            <div class="flex gap-2" style="flex-wrap: wrap; margin-bottom: 0.5rem;">
                <?php foreach ($commonDays as $d): ?>
                <label class="checkbox-label" style="padding: 0.4rem 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer; user-select: none;">
                    <input type="checkbox" class="reminder-day-check" value="<?= $d ?>" <?= in_array($d, $activeDays) ? 'checked' : '' ?>>
                    <?= $d ?> <?= __('common.day') ?>
                </label>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="reminder_days" id="reminder_days_hidden" value="<?= htmlspecialchars($reminderDaysStr) ?>">
            <small class="form-hint"><?= __('settings.reminder_hint') ?></small>
        </div>

        <hr class="my-3">

        <h3 class="mb-2"><i class="fas fa-envelope text-gray-400"></i> <?= __("settings.email_section") ?></h3>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="email_enabled" value="1" <?= ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                <?= __('settings.email_enable') ?>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("settings.email_to") ?></label>
            <input type="email" name="email_to" class="form-input ltr-input" value="<?= htmlspecialchars($settings['email_to'] ?? '') ?>" placeholder="admin@example.com">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("settings.email_method") ?></label>
            <select name="email_driver" class="form-select" id="email-driver-select" onchange="toggleSmtpFields()">
                <option value="mail" <?= ($settings['email_driver'] ?? 'mail') === 'mail' ? 'selected' : '' ?>>mail() — <?= __('settings.email_mail') ?></option>
                <option value="smtp" <?= ($settings['email_driver'] ?? 'mail') === 'smtp' ? 'selected' : '' ?>>SMTP — <?= __('settings.email_smtp') ?></option>
            </select>
        </div>

        <div id="smtp-fields" style="<?= ($settings['email_driver'] ?? 'mail') !== 'smtp' ? 'display:none;' : '' ?>">
            <div class="card" style="background: #f9fafb; margin-bottom: 1rem;">
                <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem;"><i class="fas fa-server text-gray-400"></i> <?= __("settings.smtp_settings") ?></h4>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-input ltr-input" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port</label>
                        <input type="number" name="smtp_port" class="form-input ltr-input" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" placeholder="587">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= __("settings.smtp_encryption") ?></label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (Port 587)</option>
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL/TLS (Port 465)</option>
                            <option value="" <?= ($settings['smtp_encryption'] ?? 'tls') === '' ? 'selected' : '' ?>><?= __("settings.smtp_no_encryption") ?> (Port 25)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username / Email</label>
                        <input type="text" name="smtp_username" class="form-input ltr-input" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>" placeholder="user@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="smtp_password" class="form-input ltr-input" value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>" placeholder="App password or SMTP password">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __("settings.email_from_name") ?></label>
                <input type="text" name="email_from_name" class="form-input" value="<?= htmlspecialchars($settings['email_from_name'] ?? __('app.name')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?= __("settings.email_from_addr") ?></label>
                <input type="email" name="email_from_address" class="form-input ltr-input" value="<?= htmlspecialchars($settings['email_from_address'] ?? '') ?>" placeholder="noreply@example.com">
                <small class="form-hint"><?= __('settings.email_from_hint') ?></small>
            </div>
        </div>

        <hr class="my-3">

        <h3 class="mb-2"><i class="fab fa-whatsapp text-green-500"></i> <?= __('settings.whatsapp_section') ?></h3>

        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="wa_enabled" value="1" <?= ($settings['wa_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                <?= __('settings.whatsapp_enable') ?>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">Whatspie API URL</label>
            <input type="url" name="wa_api_url" class="form-input ltr-input" value="<?= htmlspecialchars($settings['wa_api_url'] ?? 'https://api.whatspie.com/messages/send-text') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">API Token</label>
            <input type="password" name="wa_token" class="form-input ltr-input" value="<?= htmlspecialchars($settings['wa_token'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Device ID (optional)</label>
            <input type="text" name="wa_device_id" class="form-input ltr-input" value="<?= htmlspecialchars($settings['wa_device_id'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("settings.wa_recipients") ?></label>
            <textarea name="wa_recipients" class="form-textarea ltr-input" placeholder="201234567890,966501234567"><?= htmlspecialchars($settings['wa_recipients'] ?? '') ?></textarea>
            <small class="form-hint"><?= __('settings.wa_recipients_hint') ?></small>
        </div>

        <hr class="my-3">

        <h3 class="mb-2"><i class="fas fa-palette text-gray-400"></i> <?= __('settings.custom_css_section') ?></h3>

        <div class="form-group">
            <label class="form-label"><?= __('settings.custom_css_label') ?></label>
            <textarea name="custom_css" class="form-textarea ltr-input" rows="6"
                      placeholder="body { background: #f0f0f0; }"
                      style="font-family: monospace; font-size: 0.85rem;"
            ><?= htmlspecialchars($settings['custom_css'] ?? '') ?></textarea>
            <small class="form-hint"><?= __('settings.custom_css_hint') ?></small>
        </div>

        <hr class="my-3">

        <h3 class="mb-2"><i class="fas fa-sign-in-alt text-gray-400"></i> <?= __('settings.login_html_section') ?></h3>

        <div class="form-group">
            <label class="form-label"><?= __('settings.login_html_label') ?></label>
            <textarea name="login_custom_html" class="form-textarea" rows="5"
                      placeholder="<div>Demo: admin@example.com / password</div>"
            ><?= htmlspecialchars($settings['login_custom_html'] ?? '') ?></textarea>
            <small class="form-hint"><?= __('settings.login_html_hint') ?></small>
        </div>

        <div class="flex gap-2 mb-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('settings.save') ?></button>
        </div>
    </form>

    <hr class="my-3">

    <h3 class="mb-2"><i class="fas fa-vial text-gray-400"></i> <?= __("settings.test_section") ?></h3>
    <div class="flex gap-2">
        <form method="POST" action="/settings/email-test">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-envelope"></i> <?= __("settings.test_email") ?></button>
        </form>
        <form method="POST" action="/settings/whatsapp-test">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit" class="btn btn-secondary"><i class="fab fa-whatsapp"></i> <?= __("settings.test_whatsapp") ?></button>
        </form>
    </div>
</div>

<script>
function toggleSmtpFields() {
    var driver = document.getElementById('email-driver-select').value;
    document.getElementById('smtp-fields').style.display = driver === 'smtp' ? '' : 'none';
}

// Sync reminder day checkboxes with hidden field
function syncReminderDays() {
    var checks = document.querySelectorAll('.reminder-day-check');
    var vals = [];
    checks.forEach(function(c) { if (c.checked) vals.push(parseInt(c.value)); });
    vals.sort(function(a, b) { return b - a; });
    document.getElementById('reminder_days_hidden').value = vals.length > 0 ? vals.join(',') : '30';
}
document.querySelectorAll('.reminder-day-check').forEach(function(c) {
    c.addEventListener('change', syncReminderDays);
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
