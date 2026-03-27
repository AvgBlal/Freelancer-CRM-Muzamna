<?php
/**
 * Finance Controller
 * Financial statistics and overview
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Repositories\DueRepo;
use App\Repositories\ExpenseRepo;
use App\Repositories\ServiceRepo;

class FinanceController
{
    public function index(): void
    {
        Auth::requireAuth();

        // Revenue (from dues collected)
        $dueStats = DueRepo::getStats();

        // Expenses
        $expenseStats = ExpenseRepo::getStats();

        // Service MRR
        $mrr = ServiceRepo::getMRR();

        // Revenue by service type
        $revenueByType = ServiceRepo::getRevenueByType();

        // Revenue by client
        $revenueByClient = ServiceRepo::getRevenueByClient();

        // Expense totals by category
        $expensesByCategory = ExpenseRepo::getTotalsByCategory();

        // Monthly income (dues paid)
        $monthlyIncome = $this->getMonthlyIncome(12);

        // Monthly expenses
        $monthlyExpenses = ExpenseRepo::getMonthlyTotals(12);

        // Combine for profit/loss
        $monthlyProfitLoss = $this->calculateMonthlyProfitLoss($monthlyIncome, $monthlyExpenses);

        // Totals by currency
        $totalActiveServicesValueByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(price_amount), 0) as total FROM services
             WHERE status = 'active' AND price_amount IS NOT NULL
             GROUP BY currency_code ORDER BY total DESC"
        );

        $totalPaidDuesByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(paid_amount), 0) as total FROM dues WHERE status = 'paid'
             GROUP BY currency_code ORDER BY total DESC"
        );

        $totalPaidExpensesByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(amount), 0) as total FROM expenses WHERE status = 'paid'
             GROUP BY currency_code ORDER BY total DESC"
        );

        // Net profit/loss per currency
        $netByCurrency = [];
        foreach ($totalPaidDuesByCurrency as $row) {
            $netByCurrency[$row['currency_code']] = ($netByCurrency[$row['currency_code']] ?? 0) + (float)$row['total'];
        }
        foreach ($totalPaidExpensesByCurrency as $row) {
            $netByCurrency[$row['currency_code']] = ($netByCurrency[$row['currency_code']] ?? 0) - (float)$row['total'];
        }
        $netProfitByCurrency = [];
        foreach ($netByCurrency as $currency => $total) {
            $netProfitByCurrency[] = ['currency_code' => $currency, 'total' => $total];
        }

        require __DIR__ . '/../Views/finance/index.php';
    }

    private function getMonthlyIncome(int $months): array
    {
        $sql = "SELECT
                    DATE_FORMAT(paid_at, '%Y-%m') as month,
                    SUM(paid_amount) as total
                FROM dues
                WHERE status = 'paid'
                AND paid_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
                ORDER BY month ASC";

        return DB::fetchAll($sql, ['months' => $months]);
    }

    private function calculateMonthlyProfitLoss(array $income, array $expenses): array
    {
        $incomeMap = [];
        foreach ($income as $row) {
            $incomeMap[$row['month']] = (float) $row['total'];
        }

        $expenseMap = [];
        foreach ($expenses as $row) {
            $expenseMap[$row['month']] = (float) $row['total'];
        }

        // Get all months
        $allMonths = array_unique(array_merge(array_keys($incomeMap), array_keys($expenseMap)));
        sort($allMonths);

        $result = [];
        foreach ($allMonths as $month) {
            $inc = $incomeMap[$month] ?? 0;
            $exp = $expenseMap[$month] ?? 0;
            $result[] = [
                'month' => $month,
                'income' => $inc,
                'expenses' => $exp,
                'profit' => $inc - $exp,
            ];
        }

        return $result;
    }
}
