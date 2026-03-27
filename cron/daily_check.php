<?php
/**
 * Daily Cron Script (Enhanced)
 * Run via: php cron/daily_check.php
 * Or via server cron: 0 9 * * * /usr/bin/php /path/to/fcm-native/cron/daily_check.php
 *
 * Checks all modules: services, dues, expenses, tasks, notes
 */

// Ensure CLI only
if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line');
}

// Bootstrap
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/DB.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Repositories/SettingsRepo.php';
require_once __DIR__ . '/../app/Repositories/ServiceRepo.php';
require_once __DIR__ . '/../app/Repositories/DueRepo.php';
require_once __DIR__ . '/../app/Repositories/ExpenseRepo.php';
require_once __DIR__ . '/../app/Repositories/TaskRepo.php';
require_once __DIR__ . '/../app/Repositories/NotesRepo.php';
require_once __DIR__ . '/../app/Repositories/ActivityLogRepo.php';
require_once __DIR__ . '/../app/Services/WhatsAppService.php';
require_once __DIR__ . '/../app/Services/EmailService.php';

use App\Core\DB;
use App\Repositories\SettingsRepo;
use App\Repositories\ServiceRepo;
use App\Repositories\DueRepo;
use App\Repositories\ExpenseRepo;
use App\Repositories\TaskRepo;
use App\Repositories\NotesRepo;
use App\Repositories\ActivityLogRepo;
use App\Services\WhatsAppService;
use App\Services\EmailService;

echo "Starting daily check...\n";

$startTime = time();
$errors = [];
$summary = [];
$reportData = [];

try {
    // 1. Mark expired services
    echo "Marking expired services...\n";
    $expiredCount = ServiceRepo::markExpired();
    $summary[] = "خدمات منتهية: {$expiredCount}";
    echo "  - {$expiredCount} services marked as expired\n";
    if ($expiredCount > 0) {
        ActivityLogRepo::logSystem('auto_expire', 'service', null, null, "تم تحديث {$expiredCount} خدمة كمنتهية");
    }

    // 2. Get reminder days (supports comma-separated multi-days: "60,30,14,7,3,1")
    $reminderDaysStr = SettingsRepo::get('reminder_days', '30');
    $reminderDaysList = array_map('intval', array_filter(explode(',', $reminderDaysStr), 'is_numeric'));
    $maxDays = !empty($reminderDaysList) ? max($reminderDaysList) : 30;

    // 3. Services: recently expired + expiring soon (using max threshold)
    $recentlyExpired = ServiceRepo::getRecentlyExpired();
    $expiringSoon = ServiceRepo::getExpiring($maxDays);
    $summary[] = "منتهية مؤخراً: " . count($recentlyExpired);
    $summary[] = "تنتهي قريباً (خلال {$maxDays} يوم): " . count($expiringSoon);
    echo "  - Recently expired: " . count($recentlyExpired) . "\n";
    echo "  - Expiring soon (within {$maxDays} days): " . count($expiringSoon) . "\n";

    $reportData['services'] = [
        'recentlyExpired' => $recentlyExpired,
        'expiringSoon' => $expiringSoon,
        'reminderDays' => $maxDays,
    ];

    // 4. Overdue dues
    $overdueDues = DueRepo::getOverdue();
    $dueStats = DueRepo::getStats();
    $summary[] = "مستحقات متأخرة: " . count($overdueDues);
    echo "  - Overdue dues: " . count($overdueDues) . "\n";

    $reportData['dues'] = [
        'overdue' => $overdueDues,
        'totalPendingByCurrency' => $dueStats['totalPendingByCurrency'] ?? [],
    ];

    // 5. Overdue expenses
    $overdueExpenses = ExpenseRepo::getOverdue();
    $upcomingExpenses = ExpenseRepo::getUpcoming(7);
    $summary[] = "مصروفات متأخرة: " . count($overdueExpenses);
    echo "  - Overdue expenses: " . count($overdueExpenses) . "\n";

    $reportData['expenses'] = [
        'overdue' => $overdueExpenses,
        'upcoming' => $upcomingExpenses,
    ];

    // 6. Overdue tasks
    $overdueTasks = TaskRepo::getOverdue();
    $summary[] = "مهام متأخرة: " . count($overdueTasks);
    echo "  - Overdue tasks: " . count($overdueTasks) . "\n";

    $reportData['tasks'] = [
        'overdue' => $overdueTasks,
    ];

    // 7. Overdue notes
    $overdueNotes = NotesRepo::getOverdue();
    $upcomingNotes = NotesRepo::getUpcoming(3);
    $summary[] = "تذكيرات متأخرة: " . count($overdueNotes);
    echo "  - Overdue notes: " . count($overdueNotes) . "\n";

    $reportData['notes'] = [
        'overdue' => $overdueNotes,
        'upcoming' => $upcomingNotes,
    ];

    // 8. Send email if enabled
    $emailEnabled = SettingsRepo::getBool('email_enabled');
    $emailTo = SettingsRepo::get('email_to');

    if ($emailEnabled && !empty($emailTo)) {
        echo "Sending email report to {$emailTo}...\n";
        $emailSent = EmailService::sendComprehensiveReport($emailTo, $reportData);
        if ($emailSent) {
            echo "  - Email sent successfully\n";
            $summary[] = "بريد مرسل إلى {$emailTo}";
        } else {
            echo "  - Email failed to send\n";
            $errors[] = "Email failed to send";
        }
    } else {
        echo "  - Email notifications disabled or no recipient configured\n";
    }

    // 9. Send WhatsApp if enabled
    $waEnabled = SettingsRepo::getBool('wa_enabled');

    if ($waEnabled) {
        echo "Sending WhatsApp summary...\n";
        $waSent = WhatsAppService::sendComprehensiveSummary($reportData);
        if ($waSent) {
            echo "  - WhatsApp sent successfully\n";
            $summary[] = "واتساب: تم الإرسال";
        } else {
            echo "  - WhatsApp failed to send\n";
            $errors[] = "WhatsApp failed to send";
        }
    } else {
        echo "  - WhatsApp notifications disabled\n";
    }

    // 10. Log cron run
    $duration = time() - $startTime;
    $status = empty($errors) ? 'success' : 'fail';

    DB::insert('cron_runs', [
        'status' => $status,
        'summary' => implode(" | ", $summary) . " (مدة: {$duration}ث)",
        'error_message' => empty($errors) ? null : implode("; ", $errors),
    ]);

    ActivityLogRepo::logSystem('cron_run', 'system', null, 'daily_check', implode(" | ", $summary));

    echo "\nCompleted in {$duration} seconds\n";
    echo "Status: {$status}\n";

    exit($status === 'fail' ? 1 : 0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";

    DB::insert('cron_runs', [
        'status' => 'fail',
        'summary' => 'Cron failed with exception',
        'error_message' => $e->getMessage(),
    ]);

    exit(1);
}
