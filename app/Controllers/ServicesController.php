<?php
/**
 * Services Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ServiceRepo;
use App\Repositories\ClientRepo;

class ServicesController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'is_personal' => $_GET['is_personal'] ?? '',
            'search' => $_GET['search'] ?? '',
            'billing_cycle' => $_GET['billing_cycle'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'end_date',
            'sort_dir' => $_GET['sort_dir'] ?? 'asc',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 25;

        $services = ServiceRepo::getAll($filters, $page, $perPage);
        $totalCount = ServiceRepo::getCount($filters);
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        $clients = ClientRepo::getAll([], 1, 1000);
        $overviewStats = ServiceRepo::getOverviewStats();

        require __DIR__ . '/../Views/services/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        $csrf = CSRF::field();
        $clients = ClientRepo::getAll([], 1, 1000);
        require __DIR__ . '/../Views/services/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('title', __('services.service_title'))
                  ->max('title', 255)
                  ->required('type', __('services.service_type'))
                  ->required('end_date', __('services.end_date'))
                  ->date('end_date', __('services.end_date'));

        if (!empty($_POST['price_amount'])) {
            $validator->numeric('price_amount', __('services.price'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $serviceId = ServiceRepo::create($_POST);

        // Link clients
        if (!empty($_POST['client_ids'])) {
            ServiceRepo::syncClients($serviceId, $_POST['client_ids']);
        }

        Response::withSuccess(__('services.created'));
        Response::redirect('/services/' . $serviceId);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $service = ServiceRepo::find($id);
        if (!$service) {
            http_response_code(404);
            echo '404 - Service not found';
            return;
        }

        $clients = ServiceRepo::getClients($id);
        $renewalHistory = ServiceRepo::getRenewalHistory($id);
        $renewalCount = count($renewalHistory);
        $suggestedEndDate = ServiceRepo::calculateNextEndDate(
            $service['end_date'],
            $service['billing_cycle']
        );

        require __DIR__ . '/../Views/services/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $service = ServiceRepo::find($id);
        if (!$service) {
            http_response_code(404);
            echo '404 - Service not found';
            return;
        }

        $csrf = CSRF::field();
        $linkedClients = ServiceRepo::getClientIds($id);
        $allClients = ClientRepo::getAll([], 1, 1000);

        require __DIR__ . '/../Views/services/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $service = ServiceRepo::find($id);
        if (!$service) {
            http_response_code(404);
            echo '404 - Service not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('title', __('services.service_title'))
                  ->max('title', 255)
                  ->required('type', __('services.service_type'))
                  ->required('end_date', __('services.end_date'))
                  ->date('end_date', __('services.end_date'));

        if (!empty($_POST['price_amount'])) {
            $validator->numeric('price_amount', __('services.price'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        ServiceRepo::update($id, $_POST);

        // Update linked clients
        ServiceRepo::syncClients($id, $_POST['client_ids'] ?? []);

        Response::withSuccess(__('services.updated'));
        Response::redirect('/services/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        ServiceRepo::delete($id);

        Response::withSuccess(__('services.deleted'));
        Response::redirect('/services');
    }

    public function linkClients(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        ServiceRepo::syncClients($id, $_POST['client_ids'] ?? []);

        Response::withSuccess(__('services.links_updated'));
        Response::redirect('/services/' . $id);
    }

    public function renew(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $service = ServiceRepo::find($id);
        if (!$service) {
            http_response_code(404);
            echo '404 - Service not found';
            return;
        }

        $newEndDate = $_POST['new_end_date'] ?? '';
        $validator = new Validator($_POST);
        $validator->required('new_end_date', __('services.end_date'))
                  ->date('new_end_date', __('services.end_date'));

        if ($validator->fails()) {
            Response::withError($validator->firstError());
            Response::back();
            return;
        }

        $user = Auth::user();
        ServiceRepo::renew($id, $newEndDate, $user['id'] ?? null, $_POST['renewal_notes'] ?? null);

        Response::withSuccess(__('services.renewed') . ' ' . $newEndDate);
        Response::redirect('/services/' . $id);
    }

    public function changeStatus(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $service = ServiceRepo::find($id);
        if (!$service) {
            http_response_code(404);
            echo '404 - Service not found';
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $allowed = ['active', 'expired', 'paused', 'cancelled'];
        if (!in_array($newStatus, $allowed)) {
            Response::withError(__('common.invalid_status'));
            Response::back();
            return;
        }

        ServiceRepo::updateStatus($id, $newStatus);

        $statusLabel = __('services.status.' . $newStatus);

        Response::withSuccess(__('services.status_changed') . ' ' . $statusLabel);
        Response::redirect('/services/' . $id);
    }
}
