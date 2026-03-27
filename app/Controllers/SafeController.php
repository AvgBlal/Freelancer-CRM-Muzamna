<?php
/**
 * Safe Controller
 * Digital vault for stable plugins, URLs, and files
 * Base class for quotations and invoices controllers
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\SafeItemRepo;
use App\Repositories\ClientRepo;

class SafeController
{
    protected string $type = 'general';
    protected string $typeLabel = '';
    protected string $typeIcon = 'fa-shield-alt';
    protected string $routePrefix = '/safe';
    protected bool $clientRequired = false;

    public function __construct()
    {
        if ($this->typeLabel === '') {
            $this->typeLabel = __('safe.title');
        }
    }

    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'tag' => $_GET['tag'] ?? '',
            'type' => $this->type,
            'client_id' => $_GET['client_id'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 25;

        $items = SafeItemRepo::getAll($filters, $page, $perPage);
        $totalCount = SafeItemRepo::getCount($filters);
        $totalPages = ceil($totalCount / $perPage);
        $stats = SafeItemRepo::getStats($this->type);
        $allTags = SafeItemRepo::getAllTags($this->type);
        $clients = ClientRepo::getAll([], 1, 1000);

        $type = $this->type;
        $typeLabel = $this->typeLabel;
        $typeIcon = $this->typeIcon;
        $routePrefix = $this->routePrefix;
        $clientRequired = $this->clientRequired;

        require __DIR__ . '/../Views/safe/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        $csrf = CSRF::field();
        $clients = ClientRepo::getAll([], 1, 1000);

        $type = $this->type;
        $typeLabel = $this->typeLabel;
        $typeIcon = $this->typeIcon;
        $routePrefix = $this->routePrefix;
        $clientRequired = $this->clientRequired;

        require __DIR__ . '/../Views/safe/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('title', __('common.title'))
                  ->max('title', 255);

        if ($this->clientRequired && empty($_POST['client_id'])) {
            $_SESSION['flash_error'] = __('safe.client_required');
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $user = Auth::user();

        $data = [
            'type' => $this->type,
            'client_id' => $_POST['client_id'] ?? null,
            'title' => $_POST['title'],
            'url' => $_POST['url'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'tags' => $this->normalizeTags($_POST['tags'] ?? ''),
            'file_path' => null,
            'file_original_name' => null,
            'file_size' => null,
            'created_by' => $user['id'] ?? null,
        ];

        $id = SafeItemRepo::create($data);

        // Handle multiple file uploads
        $this->handleMultipleUploads($id);

        Response::withSuccess(__('safe.created'));
        Response::redirect($this->routePrefix . '/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $item = SafeItemRepo::find($id);
        if (!$item) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }
        $this->requireOwnership($item);

        $type = $this->type;
        $typeLabel = $this->typeLabel;
        $typeIcon = $this->typeIcon;
        $routePrefix = $this->routePrefix;

        require __DIR__ . '/../Views/safe/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $item = SafeItemRepo::find($id);
        if (!$item) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }
        $this->requireOwnership($item);

        $csrf = CSRF::field();
        $clients = ClientRepo::getAll([], 1, 1000);

        $type = $this->type;
        $typeLabel = $this->typeLabel;
        $typeIcon = $this->typeIcon;
        $routePrefix = $this->routePrefix;
        $clientRequired = $this->clientRequired;

        require __DIR__ . '/../Views/safe/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $item = SafeItemRepo::find($id);
        if (!$item) {
            http_response_code(404);
            echo '404 - Not Found';
            return;
        }
        $this->requireOwnership($item);

        $validator = new Validator($_POST);
        $validator->required('title', __('common.title'))
                  ->max('title', 255);

        if ($this->clientRequired && empty($_POST['client_id'])) {
            $_SESSION['flash_error'] = __('safe.client_required');
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $data = [
            'title' => $_POST['title'],
            'url' => $_POST['url'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'tags' => $this->normalizeTags($_POST['tags'] ?? ''),
            'client_id' => $_POST['client_id'] ?? null,
        ];

        // Handle removing individual files
        if (!empty($_POST['remove_files'])) {
            foreach ($_POST['remove_files'] as $fileId) {
                $file = SafeItemRepo::findFile((int) $fileId);
                if ($file && $file['safe_item_id'] === $id) {
                    $this->deletePhysicalFile($file['file_path']);
                    SafeItemRepo::deleteFileRecord((int) $fileId);
                }
            }
        }

        // Handle new file uploads
        $this->handleMultipleUploads($id);

        // Legacy single-file removal
        if (!empty($_POST['remove_file'])) {
            $this->deletePhysicalFile($item['file_path']);
            $data['file_path'] = null;
            $data['file_original_name'] = null;
            $data['file_size'] = null;
        }

        SafeItemRepo::update($id, $data);

        Response::withSuccess(__('safe.updated'));
        Response::redirect($this->routePrefix . '/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $item = SafeItemRepo::find($id);
        if ($item) {
            $this->requireOwnership($item);
            $this->deletePhysicalFile($item['file_path']);
            foreach ($item['files'] ?? [] as $file) {
                $this->deletePhysicalFile($file['file_path']);
            }
        }

        SafeItemRepo::delete($id);

        Response::withSuccess(__('safe.deleted'));
        Response::redirect($this->routePrefix);
    }

    public function download(int $id): void
    {
        Auth::requireAuth();

        $item = SafeItemRepo::find($id);
        if (!$item || !$item['file_path']) {
            Response::withError(__('safe.file_not_found'));
            Response::redirect($this->routePrefix);
            return;
        }
        $this->requireOwnership($item);

        $fullPath = realpath(__DIR__ . '/../../' . $item['file_path']);
        $uploadsDir = realpath(__DIR__ . '/../../uploads');
        if (!$fullPath || !$uploadsDir || strpos($fullPath, $uploadsDir) !== 0) {
            Response::withError(__('safe.file_not_on_server'));
            Response::redirect($this->routePrefix . '/' . $id);
            return;
        }

        $downloadName = $this->sanitizeFilename($item['file_original_name'] ?: basename($item['file_path']));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function downloadFile(int $fileId): void
    {
        Auth::requireAuth();

        $file = SafeItemRepo::findFile($fileId);
        if (!$file) {
            Response::withError(__('safe.file_not_found'));
            Response::back();
            return;
        }

        // Check ownership via parent safe item
        $item = SafeItemRepo::find($file['safe_item_id']);
        if ($item) {
            $this->requireOwnership($item);
        }

        $fullPath = realpath(__DIR__ . '/../../' . $file['file_path']);
        $uploadsDir = realpath(__DIR__ . '/../../uploads');
        if (!$fullPath || !$uploadsDir || strpos($fullPath, $uploadsDir) !== 0) {
            Response::withError(__('safe.file_not_on_server'));
            Response::back();
            return;
        }

        $downloadName = $this->sanitizeFilename($file['file_original_name'] ?: basename($file['file_path']));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    protected function handleMultipleUploads(int $itemId): void
    {
        if (!isset($_FILES['files']) || !is_array($_FILES['files']['name'])) {
            return;
        }

        $fileCount = count($_FILES['files']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $name = $_FILES['files']['name'][$i];
            $tmpName = $_FILES['files']['tmp_name'][$i];
            $size = $_FILES['files']['size'][$i];

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['zip', 'gz', 'tar', 'rar', '7z', 'pdf', 'doc', 'docx', 'xls', 'xlsx',
                         'txt', 'md', 'json', 'xml', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'css'];

            if (!in_array($ext, $allowed) || $size > 10 * 1024 * 1024) {
                continue;
            }

            // MIME type validation — block dangerous content types
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpName);
            $dangerousMimes = ['text/html', 'application/javascript', 'image/svg+xml', 'application/x-httpd-php'];
            if (in_array($mimeType, $dangerousMimes)) {
                continue;
            }

            $uploadDir = __DIR__ . '/../../uploads/safe/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($tmpName, $uploadDir . $filename);

            SafeItemRepo::addFile($itemId, [
                'file_path' => 'uploads/safe/' . $filename,
                'file_original_name' => $name,
                'file_size' => $size,
                'sort_order' => $i,
            ]);
        }
    }

    protected function deletePhysicalFile(?string $path): void
    {
        if (!$path) return;
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private function requireOwnership(array $item): void
    {
        if (Auth::isAdmin()) return;
        if (($item['created_by'] ?? null) !== Auth::id()) {
            Response::withError(__('auth.unauthorized'));
            Response::redirect($this->routePrefix);
            exit;
        }
    }

    private function sanitizeFilename(string $name): string
    {
        return str_replace(["\r", "\n", '"', "\0"], ['', '', "'", ''], $name);
    }

    protected function normalizeTags(string $input): ?string
    {
        if (trim($input) === '') return null;
        $tags = array_map('trim', explode(',', $input));
        $tags = array_filter($tags, fn($t) => $t !== '');
        $tags = array_unique($tags);
        return implode(',', $tags) ?: null;
    }
}
