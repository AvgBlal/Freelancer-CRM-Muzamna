<?php
/**
 * Dues Controller
 * Personal money tracking (who owes you)
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\DueRepo;

class DuesController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $dues = DueRepo::getAll($filters, $page);
        $stats = DueRepo::getStats();

        require __DIR__ . '/../Views/dues/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        $csrf = CSRF::field();
        require __DIR__ . '/../Views/dues/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('person_name', __('dues.person_name'))
                  ->max('person_name', 255)
                  ->required('amount', __('common.amount'))
                  ->numeric('amount', __('common.amount'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $id = DueRepo::create($_POST);

        Response::withSuccess(__('dues.created'));
        Response::redirect('/dues/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $due = DueRepo::find($id);
        if (!$due) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        require __DIR__ . '/../Views/dues/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $due = DueRepo::find($id);
        if (!$due) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $csrf = CSRF::field();
        require __DIR__ . '/../Views/dues/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $due = DueRepo::find($id);
        if (!$due) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('person_name', __('dues.person_name'))
                  ->max('person_name', 255)
                  ->required('amount', __('common.amount'))
                  ->numeric('amount', __('common.amount'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        DueRepo::update($id, $_POST);

        Response::withSuccess(__('dues.updated'));
        Response::redirect('/dues/' . $id);
    }

    public function markPaid(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $due = DueRepo::find($id);
        if (!$due) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        DueRepo::markPaid($id);

        Response::withSuccess(__('dues.paid'));
        Response::redirect('/dues/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        DueRepo::delete($id);

        Response::withSuccess(__('dues.deleted'));
        Response::redirect('/dues');
    }
}
