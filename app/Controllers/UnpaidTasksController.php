<?php
/**
 * Unpaid Tasks Controller
 * Track unquoted emergency work done for clients
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\UnpaidTaskRepo;
use App\Repositories\ClientRepo;
use App\Repositories\UserRepo;

class UnpaidTasksController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $tasks = UnpaidTaskRepo::getAll($filters, $page);
        $clients = ClientRepo::getAll();

        require __DIR__ . '/../Views/unpaid_tasks/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();

        $csrf = CSRF::field();
        $clients = ClientRepo::getAll();
        $users = UserRepo::getAll();
        $selectedClientId = $_GET['client_id'] ?? '';

        require __DIR__ . '/../Views/unpaid_tasks/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('title', __('unpaid.task_title'))
                  ->max('title', 255)
                  ->required('client_id', __('common.client'))
                  ->required('hours', __('unpaid.hours'))
                  ->required('total_cost', __('unpaid.cost'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $attachment = $this->handleUpload();
        if ($attachment) {
            $_POST['attachment'] = $attachment;
        }

        $id = UnpaidTaskRepo::create($_POST);

        Response::withSuccess(__('unpaid.created'));
        Response::redirect('/unpaid-tasks/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $task = UnpaidTaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        require __DIR__ . '/../Views/unpaid_tasks/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $task = UnpaidTaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $csrf = CSRF::field();
        $clients = ClientRepo::getAll();
        $users = UserRepo::getAll();

        require __DIR__ . '/../Views/unpaid_tasks/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $task = UnpaidTaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('title', __('unpaid.task_title'))
                  ->max('title', 255)
                  ->required('client_id', __('common.client'))
                  ->required('hours', __('unpaid.hours'))
                  ->required('total_cost', __('unpaid.cost'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $attachment = $this->handleUpload();
        if ($attachment) {
            $this->deleteAttachment($task['attachment']);
            $_POST['attachment'] = $attachment;
        }

        if (!empty($_POST['remove_attachment']) && empty($attachment)) {
            $this->deleteAttachment($task['attachment']);
            $_POST['attachment'] = '';
            // Clear attachment in DB
            \App\Core\DB::query(
                "UPDATE unpaid_tasks SET attachment = NULL WHERE id = :id",
                ['id' => $id]
            );
        }

        UnpaidTaskRepo::update($id, $_POST);

        Response::withSuccess(__('unpaid.updated'));
        Response::redirect('/unpaid-tasks/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $task = UnpaidTaskRepo::find($id);
        if ($task) {
            $this->deleteAttachment($task['attachment']);
        }

        $clientId = $task['client_id'] ?? null;
        UnpaidTaskRepo::delete($id);

        Response::withSuccess(__('unpaid.deleted'));
        Response::redirect('/unpaid-tasks');
    }

    public function updateStatus(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $status = $_POST['status'] ?? '';
        $validStatuses = ['pending', 'quoted', 'invoiced', 'paid', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            Response::withError(__('common.invalid_status'));
            Response::back();
            return;
        }

        UnpaidTaskRepo::updateStatus($id, $status);

        Response::withSuccess(__('unpaid.status_updated'));
        Response::back();
    }

    private function handleUpload(): ?string
    {
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['attachment'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt'];

        if (!in_array($ext, $allowed)) {
            return null;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // MIME type validation — block dangerous content types
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $dangerousMimes = ['text/html', 'application/javascript', 'image/svg+xml', 'application/x-httpd-php'];
        if (in_array($mimeType, $dangerousMimes)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../uploads/unpaid_tasks/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        return 'uploads/unpaid_tasks/' . $filename;
    }

    private function deleteAttachment(?string $path): void
    {
        if (!$path) return;
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
