<?php
/**
 * Clients Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ClientRepo;
use App\Repositories\TagRepo;
use App\Repositories\UnpaidTaskRepo;
use App\Repositories\SafeItemRepo;

class ClientsController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'type' => $_GET['type'] ?? '',
            'tag' => $_GET['tag'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 25;

        $clients = ClientRepo::getAll($filters, $page, $perPage);
        $tags = TagRepo::getAll();

        require __DIR__ . '/../Views/clients/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        $csrf = CSRF::field();
        $tags = TagRepo::getAll();
        require __DIR__ . '/../Views/clients/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('name', __('clients.name'))
                  ->max('name', 190)
                  ->required('type', __('clients.type'))
                  ->in('type', ['individual', 'company']);

        if (!empty($_POST['email'])) {
            $validator->email('email', __('auth.email'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $clientId = ClientRepo::create($_POST);

        // Save contacts
        if (!empty($_POST['contacts'])) {
            ClientRepo::saveContacts($clientId, $_POST['contacts']);
        }

        // Save tags
        if (!empty($_POST['tags'])) {
            ClientRepo::syncTags($clientId, $_POST['tags']);
        }

        Response::withSuccess(__('clients.created'));
        Response::redirect('/clients/' . $clientId);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $client = ClientRepo::find($id);
        if (!$client) {
            http_response_code(404);
            echo '404 - Client not found';
            return;
        }

        $contacts = ClientRepo::getContacts($id);
        $tags = ClientRepo::getTags($id);
        $services = ClientRepo::getServices($id);
        $projects = ClientRepo::getProjects($id);
        $unpaidTasks = UnpaidTaskRepo::getByClient($id);
        $unpaidTaskStats = UnpaidTaskRepo::getStatsByClient($id);
        $quotations = SafeItemRepo::getByClient($id, 'quotation');
        $invoices = SafeItemRepo::getByClient($id, 'invoice');

        require __DIR__ . '/../Views/clients/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $client = ClientRepo::find($id);
        if (!$client) {
            http_response_code(404);
            echo '404 - Client not found';
            return;
        }

        $csrf = CSRF::field();
        $contacts = ClientRepo::getContacts($id);
        $clientTags = ClientRepo::getTagIds($id);
        $allTags = TagRepo::getAll();

        require __DIR__ . '/../Views/clients/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $client = ClientRepo::find($id);
        if (!$client) {
            http_response_code(404);
            echo '404 - Client not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('name', __('clients.name'))
                  ->max('name', 190)
                  ->required('type', __('clients.type'))
                  ->in('type', ['individual', 'company']);

        if (!empty($_POST['email'])) {
            $validator->email('email', __('auth.email'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        ClientRepo::update($id, $_POST);

        // Update contacts
        if (isset($_POST['contacts'])) {
            ClientRepo::saveContacts($id, $_POST['contacts']);
        }

        // Update tags
        ClientRepo::syncTags($id, $_POST['tags'] ?? []);

        Response::withSuccess(__('clients.updated'));
        Response::redirect('/clients/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        ClientRepo::delete($id);

        Response::withSuccess(__('clients.deleted'));
        Response::redirect('/clients');
    }
}
