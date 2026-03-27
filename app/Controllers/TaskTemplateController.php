<?php
/**
 * Task Template Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\TaskTemplateRepo;
use App\Repositories\ClientRepo;
use App\Repositories\ProjectRepo;

class TaskTemplateController
{
    /**
     * List all templates
     */
    public function index(): void
    {
        Auth::requireAuth();

        $templates = TaskTemplateRepo::getAll();
        $categories = TaskTemplateRepo::getCategories();

        require __DIR__ . '/../Views/tasks/templates/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        Auth::requireAuth();

        $csrf = CSRF::field();
        $categories = TaskTemplateRepo::getCategories();

        require __DIR__ . '/../Views/tasks/templates/create.php';
    }

    /**
     * Store new template
     */
    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('name', __('templates.name'))
                  ->max('name', 255);

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $data = [
            'created_by' => $_SESSION['user_id'] ?? 0,
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'default_hours' => $_POST['default_hours'] ?? null,
            'priority' => $_POST['priority'] ?? 'normal',
            'category' => $_POST['category'] ?? null,
        ];

        TaskTemplateRepo::create($data);

        Response::withSuccess(__('templates.created'));
        Response::redirect('/tasks/templates');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        Auth::requireAuth();

        $template = TaskTemplateRepo::find($id);
        if (!$template) {
            http_response_code(404);
            echo '404 - Template not found';
            return;
        }

        $csrf = CSRF::field();
        $categories = TaskTemplateRepo::getCategories();

        require __DIR__ . '/../Views/tasks/templates/edit.php';
    }

    /**
     * Update template
     */
    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('name', __('templates.name'))
                  ->max('name', 255);

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'default_hours' => $_POST['default_hours'] ?? null,
            'priority' => $_POST['priority'] ?? 'normal',
            'category' => $_POST['category'] ?? null,
            'is_active' => isset($_POST['is_active']) ? true : false,
        ];

        TaskTemplateRepo::update($id, $data);

        Response::withSuccess(__('templates.updated'));
        Response::redirect('/tasks/templates');
    }

    /**
     * Delete template (soft delete)
     */
    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        TaskTemplateRepo::delete($id);

        Response::withSuccess(__('templates.deleted'));
        Response::redirect('/tasks/templates');
    }

    /**
     * Use template to create task (show form)
     */
    public function use(int $id): void
    {
        Auth::requireAuth();

        $template = TaskTemplateRepo::find($id);
        if (!$template) {
            http_response_code(404);
            echo '404 - Template not found';
            return;
        }

        $csrf = CSRF::field();
        $employees = \App\Repositories\TaskRepo::getEmployeesForAssignment();
        $clients = ClientRepo::getAll([], 1, 100);
        $projects = ProjectRepo::getAll([], 1, 100);
        $services = \App\Repositories\ServiceRepo::getAll([], 1, 100);

        require __DIR__ . '/../Views/tasks/create.php';
    }

    /**
     * Create task from template
     */
    public function createTask(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $template = TaskTemplateRepo::find($id);
        if (!$template) {
            http_response_code(404);
            echo '404 - Template not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('due_date', __('common.due_date'))
                  ->date('due_date', __('common.due_date'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $overrides = [
            'title' => $_POST['title'] ?? $template['name'],
            'created_by' => $_SESSION['user_id'] ?? 0,
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'client_id' => $_POST['client_id'] ?? null,
            'project_id' => $_POST['project_id'] ?? null,
            'due_date' => $_POST['due_date'],
        ];

        $taskId = TaskTemplateRepo::createTaskFromTemplate($id, $overrides);

        if ($taskId) {
            Response::withSuccess(__('tasks.from_template'));
            Response::redirect('/tasks/' . $taskId);
        } else {
            Response::withError(__('tasks.from_template_fail'));
            Response::back();
        }
    }
}
