<?php
/**
 * Personal Controller
 * Personal dashboard and financial calendar/timeline
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Repositories\DueRepo;
use App\Repositories\ExpenseRepo;
use App\Repositories\NotesRepo;
use App\Repositories\ServiceRepo;

class PersonalController
{
    public function index(): void
    {
        Auth::requireAuth();

        // Personal services
        $personalServices = ServiceRepo::getAll(['is_personal' => '1', 'status' => 'active'], 1, 100);
        $personalServiceCount = ServiceRepo::getCount(['is_personal' => '1']);
        $personalExpiringServices = $this->getPersonalExpiringServices();

        // Dues summary
        $dueStats = DueRepo::getStats();
        $overdueDues = DueRepo::getOverdue();

        // Expense summary
        $expenseStats = ExpenseRepo::getStats();
        $upcomingExpenses = ExpenseRepo::getUpcoming(30);
        $overdueExpenses = ExpenseRepo::getOverdue();

        // Notes
        $pinnedNotes = NotesRepo::getPinned(5);
        $upcomingNotes = NotesRepo::getUpcoming(7);
        $overdueNotes = NotesRepo::getOverdue();
        $noteStats = NotesRepo::getStats();

        // Personal finance summary
        $personalMRR = $this->getPersonalMRR();

        require __DIR__ . '/../Views/personal/index.php';
    }

    public function calendar(): void
    {
        Auth::requireAuth();

        // Gather all upcoming events across modules (next 60 days)
        $events = $this->buildTimeline(60);

        require __DIR__ . '/../Views/personal/calendar.php';
    }

    private function getPersonalExpiringServices(): array
    {
        $sql = "SELECT s.*
                FROM services s
                WHERE s.is_personal = 1
                AND s.status = 'active'
                AND s.end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND s.end_date >= CURDATE()
                ORDER BY s.end_date ASC";

        return DB::fetchAll($sql);
    }

    private function getPersonalMRR(): array
    {
        $sql = "SELECT currency_code, COALESCE(SUM(CASE
                    WHEN billing_cycle = 'monthly' THEN price_amount
                    WHEN billing_cycle = 'yearly' THEN price_amount / 12
                    ELSE 0
                END), 0) as total
                FROM services
                WHERE status = 'active' AND is_personal = 1 AND price_amount IS NOT NULL
                GROUP BY currency_code ORDER BY total DESC";

        return DB::fetchAll($sql);
    }

    /** Build a unified timeline of upcoming events */
    public function buildTimeline(int $days = 60): array
    {
        $events = [];

        // Service expirations
        $services = DB::fetchAll(
            "SELECT id, title, end_date, price_amount, type, is_personal
             FROM services WHERE status = 'active'
             AND end_date >= CURDATE()
             AND end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY end_date ASC",
            ['days' => $days]
        );
        foreach ($services as $s) {
            $events[] = [
                'date' => $s['end_date'],
                'type' => 'service_expiry',
                'icon' => $s['is_personal'] ? 'personal' : 'business',
                'title' => __('personal.timeline.service') . ' ' . $s['title'],
                'amount' => $s['price_amount'],
                'link' => '/services/' . $s['id'],
            ];
        }

        // Expense due dates
        $expenses = DB::fetchAll(
            "SELECT id, title, due_date, amount
             FROM expenses WHERE status = 'pending'
             AND due_date IS NOT NULL
             AND due_date >= CURDATE()
             AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY due_date ASC",
            ['days' => $days]
        );
        foreach ($expenses as $e) {
            $events[] = [
                'date' => $e['due_date'],
                'type' => 'expense_due',
                'icon' => 'expense',
                'title' => __('personal.timeline.expense') . ' ' . $e['title'],
                'amount' => $e['amount'],
                'link' => '/expenses/' . $e['id'],
            ];
        }

        // Due collection dates
        $dues = DB::fetchAll(
            "SELECT id, person_name, due_date, amount, paid_amount
             FROM dues WHERE status IN ('pending', 'partial')
             AND due_date IS NOT NULL
             AND due_date >= CURDATE()
             AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY due_date ASC",
            ['days' => $days]
        );
        foreach ($dues as $d) {
            $remaining = $d['amount'] - ($d['paid_amount'] ?? 0);
            $events[] = [
                'date' => $d['due_date'],
                'type' => 'due_collection',
                'icon' => 'due',
                'title' => __('personal.timeline.due') . ' ' . $d['person_name'],
                'amount' => $remaining,
                'link' => '/dues/' . $d['id'],
            ];
        }

        // Project deadlines
        $projects = DB::fetchAll(
            "SELECT p.id, p.title, p.due_date, c.name as client_name
             FROM projects p
             JOIN clients c ON p.client_id = c.id
             WHERE p.status IN ('idea', 'in_progress')
             AND p.due_date IS NOT NULL
             AND p.due_date >= CURDATE()
             AND p.due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY p.due_date ASC",
            ['days' => $days]
        );
        foreach ($projects as $p) {
            $events[] = [
                'date' => $p['due_date'],
                'type' => 'project_deadline',
                'icon' => 'project',
                'title' => __('personal.timeline.project') . ' ' . $p['title'],
                'amount' => null,
                'link' => '/projects/' . $p['id'],
            ];
        }

        // Task deadlines
        $tasks = DB::fetchAll(
            "SELECT id, title, due_date
             FROM tasks
             WHERE status NOT IN ('completed', 'cancelled')
             AND due_date IS NOT NULL
             AND due_date >= CURDATE()
             AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY due_date ASC",
            ['days' => $days]
        );
        foreach ($tasks as $t) {
            $events[] = [
                'date' => $t['due_date'],
                'type' => 'task_deadline',
                'icon' => 'task',
                'title' => __('personal.timeline.task') . ' ' . $t['title'],
                'amount' => null,
                'link' => '/tasks/' . $t['id'],
            ];
        }

        // Note reminders
        $notes = DB::fetchAll(
            "SELECT id, title, due_date
             FROM notes WHERE status = 'active'
             AND due_date IS NOT NULL
             AND due_date >= CURDATE()
             AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY due_date ASC",
            ['days' => $days]
        );
        foreach ($notes as $n) {
            $events[] = [
                'date' => $n['due_date'],
                'type' => 'note_reminder',
                'icon' => 'note',
                'title' => __('personal.timeline.reminder') . ' ' . $n['title'],
                'amount' => null,
                'link' => '/notes/' . $n['id'],
            ];
        }

        // Sort all events by date
        usort($events, fn($a, $b) => strcmp($a['date'], $b['date']));

        return $events;
    }
}
