<?php
/**
 * Expenses Controller
 * Track money you owe / business costs
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ExpenseRepo;

class ExpensesController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $expenses = ExpenseRepo::getAll($filters, $page);
        $stats = ExpenseRepo::getStats();

        require __DIR__ . '/../Views/expenses/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        $csrf = CSRF::field();
        require __DIR__ . '/../Views/expenses/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('title', __('expenses.expense_title'))
                  ->max('title', 255)
                  ->required('amount', __('common.amount'))
                  ->numeric('amount', __('common.amount'))
                  ->required('category', __('expenses.category'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $id = ExpenseRepo::create($_POST);

        Response::withSuccess(__('expenses.created'));
        Response::redirect('/expenses/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $expense = ExpenseRepo::find($id);
        if (!$expense) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        require __DIR__ . '/../Views/expenses/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $expense = ExpenseRepo::find($id);
        if (!$expense) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $csrf = CSRF::field();
        require __DIR__ . '/../Views/expenses/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $expense = ExpenseRepo::find($id);
        if (!$expense) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('title', __('expenses.expense_title'))
                  ->max('title', 255)
                  ->required('amount', __('common.amount'))
                  ->numeric('amount', __('common.amount'))
                  ->required('category', __('expenses.category'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        ExpenseRepo::update($id, $_POST);

        Response::withSuccess(__('expenses.updated'));
        Response::redirect('/expenses/' . $id);
    }

    public function markPaid(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $expense = ExpenseRepo::find($id);
        if (!$expense) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        ExpenseRepo::markPaid($id);

        Response::withSuccess(__('expenses.paid'));
        Response::redirect('/expenses/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        ExpenseRepo::delete($id);

        Response::withSuccess(__('expenses.deleted'));
        Response::redirect('/expenses');
    }
}
