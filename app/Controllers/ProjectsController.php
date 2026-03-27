<?php
/**
 * Projects Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ProjectRepo;
use App\Repositories\ClientRepo;

class ProjectsController
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 25;

        // Employees only see projects that have tasks assigned to them
        if (Auth::isEmployee()) {
            $projects = ProjectRepo::getByEmployee(Auth::id(), $filters, $page, $perPage);
            $clients = [];
        } else {
            $projects = ProjectRepo::getAll($filters, $page, $perPage);
            $clients = ClientRepo::getAll([], 1, 1000);
        }

        require __DIR__ . '/../Views/projects/index.php';
    }

    public function create(): void
    {
        Auth::requireRole('admin', 'manager');
        $csrf = CSRF::field();
        $clients = ClientRepo::getAll([], 1, 1000);
        require __DIR__ . '/../Views/projects/create.php';
    }

    public function store(): void
    {
        Auth::requireRole('admin', 'manager');
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('client_id', __('common.client'))
                  ->required('title', __('projects.project_title'))
                  ->max('title', 255);

        if (!empty($_POST['due_date'])) {
            $validator->date('due_date', __('projects.delivery_date'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $projectId = ProjectRepo::create($_POST);

        // Save todos
        if (!empty($_POST['todos'])) {
            ProjectRepo::saveTodos($projectId, $_POST['todos']);
        }

        Response::withSuccess(__('projects.created'));
        Response::redirect('/projects/' . $projectId);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $project = ProjectRepo::find($id);
        if (!$project) {
            http_response_code(404);
            echo '404 - Project not found';
            return;
        }

        // Employees can only view projects that have tasks assigned to them
        if (Auth::isEmployee() && !ProjectRepo::hasEmployeeTasks($id, Auth::id())) {
            Response::withError(__('projects.unauthorized'));
            Response::redirect('/projects');
            return;
        }

        $todos = ProjectRepo::getTodos($id);
        $client = ClientRepo::find($project['client_id']);

        require __DIR__ . '/../Views/projects/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireRole('admin', 'manager');

        $project = ProjectRepo::find($id);
        if (!$project) {
            http_response_code(404);
            echo '404 - Project not found';
            return;
        }

        $csrf = CSRF::field();
        $todos = ProjectRepo::getTodos($id);
        $clients = ClientRepo::getAll([], 1, 1000);

        require __DIR__ . '/../Views/projects/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireRole('admin', 'manager');
        CSRF::verifyRequest();

        $project = ProjectRepo::find($id);
        if (!$project) {
            http_response_code(404);
            echo '404 - Project not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('client_id', __('common.client'))
                  ->required('title', __('projects.project_title'))
                  ->max('title', 255);

        if (!empty($_POST['due_date'])) {
            $validator->date('due_date', __('projects.delivery_date'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        ProjectRepo::update($id, $_POST);

        // Update todos
        if (isset($_POST['todos'])) {
            ProjectRepo::saveTodos($id, $_POST['todos']);
        }

        // Recalculate progress from todos, then apply manual override if set
        ProjectRepo::recalculateProgress($id);
        if (isset($_POST['progress'])) {
            $manualProgress = max(0, min(100, (int) $_POST['progress']));
            ProjectRepo::updateProgress($id, $manualProgress);
        }

        Response::withSuccess(__('projects.updated'));
        Response::redirect('/projects/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireRole('admin', 'manager');
        CSRF::verifyRequest();

        ProjectRepo::delete($id);

        Response::withSuccess(__('projects.deleted'));
        Response::redirect('/projects');
    }
}
