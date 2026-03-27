<?php
/**
 * Tasks Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\TaskRepo;
use App\Repositories\TaskCommentRepo;
use App\Repositories\ClientRepo;
use App\Repositories\ProjectRepo;
use App\Repositories\ServiceRepo;

class TasksController
{
    /**
     * List all tasks (manager view) — employees see only their assigned tasks
     */
    public function index(): void
    {
        Auth::requireAuth();

        $assignedTo = $_GET['assigned_to'] ?? '';
        $projectFilter = $_GET['project_filter'] ?? '';

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'assigned_to' => $assignedTo === 'none' ? '' : $assignedTo,
            'unassigned_user' => $assignedTo === 'none' ? '1' : '',
            'unassigned_project' => $projectFilter === 'none' ? '1' : '',
            'client_id' => $_GET['client_id'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Employees only see tasks assigned to them
        if (Auth::isEmployee()) {
            $filters['assigned_to'] = Auth::id();
            $filters['unassigned_user'] = '';
        }

        $page = (int) ($_GET['page'] ?? 1);
        $tasks = TaskRepo::getAll($filters, $page, 25);
        $employees = TaskRepo::getEmployeesForAssignment();
        $clients = Auth::isEmployee() ? [] : ClientRepo::getAll([], 1, 100);

        require __DIR__ . '/../Views/tasks/index.php';
    }

    /**
     * My tasks (employee view)
     */
    public function myTasks(): void
    {
        Auth::requireAuth();

        $userId = $_SESSION['user_id'] ?? 0;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
        ];

        $tasks = TaskRepo::getByAssignedTo($userId, $filters);
        $dashboardData = TaskRepo::getDashboardTasks($userId);

        require __DIR__ . '/../Views/tasks/my_tasks.php';
    }

    /**
     * Team board view
     */
    public function teamBoard(): void
    {
        Auth::requireAuth();

        $teamWorkload = TaskRepo::getTeamWorkload();
        $tasks = TaskRepo::getAll(['status' => $_GET['status'] ?? ''], 1, 100);
        $employees = TaskRepo::getEmployeesForAssignment();

        require __DIR__ . '/../Views/tasks/team_board.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        Auth::requireAuth();

        $csrf = CSRF::field();
        $employees = TaskRepo::getEmployeesForAssignment();
        $clients = ClientRepo::getAll([], 1, 100);
        $projects = ProjectRepo::getAll([], 1, 100);
        $services = ServiceRepo::getAll([], 1, 100);
        $templates = \App\Repositories\TaskTemplateRepo::getAll();

        require __DIR__ . '/../Views/tasks/create.php';
    }

    /**
     * Store new task
     */
    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('title', __('tasks.task_title'))
                  ->max('title', 255)
                  ->required('due_date', __('common.due_date'))
                  ->date('due_date', __('common.due_date'));

        if (!empty($_POST['estimated_hours'])) {
            $validator->numeric('estimated_hours', __('tasks.estimated_hours'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'priority' => $_POST['priority'] ?? 'normal',
            'created_by' => $_SESSION['user_id'] ?? 0,
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'client_id' => $_POST['client_id'] ?? null,
            'project_id' => $_POST['project_id'] ?? null,
            'service_id' => $_POST['service_id'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'due_date' => $_POST['due_date'],
            'estimated_hours' => $_POST['estimated_hours'] ?? null,
        ];

        $taskId = TaskRepo::create($data);

        // Log activity
        TaskCommentRepo::logActivity($taskId, $_SESSION['user_id'], __('tasks.log.created'));

        // Log assignment if assigned
        if (!empty($data['assigned_to'])) {
            $assigneeName = '';
            foreach (TaskRepo::getEmployeesForAssignment() as $emp) {
                if ($emp['id'] == $data['assigned_to']) {
                    $assigneeName = $emp['name'];
                    break;
                }
            }
            TaskCommentRepo::logActivity($taskId, $_SESSION['user_id'], __('tasks.log.assigned') . ' ' . $assigneeName);
        }

        Response::withSuccess(__('tasks.created'));
        Response::redirect('/tasks/' . $taskId);
    }

    /**
     * Show task detail — employees can only see tasks assigned to them
     */
    public function show(int $id): void
    {
        Auth::requireAuth();

        $task = TaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Task not found';
            return;
        }

        if (Auth::isEmployee() && (int) ($task['assigned_to'] ?? 0) !== Auth::id()) {
            Response::withError(__('tasks.unauthorized'));
            Response::redirect('/tasks');
            return;
        }

        $comments = TaskCommentRepo::getByTask($id);
        $employees = TaskRepo::getEmployeesForAssignment();

        require __DIR__ . '/../Views/tasks/show.php';
    }

    /**
     * Show edit form — admin/manager only
     */
    public function edit(int $id): void
    {
        Auth::requireRole('admin', 'manager');

        $task = TaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Task not found';
            return;
        }

        $csrf = CSRF::field();
        $employees = TaskRepo::getEmployeesForAssignment();
        $clients = ClientRepo::getAll([], 1, 100);
        $projects = ProjectRepo::getAll([], 1, 100);
        $services = ServiceRepo::getAll([], 1, 100);

        require __DIR__ . '/../Views/tasks/edit.php';
    }

    /**
     * Update task — admin/manager only
     */
    public function update(int $id): void
    {
        Auth::requireRole('admin', 'manager');
        CSRF::verifyRequest();

        $task = TaskRepo::find($id);
        if (!$task) {
            http_response_code(404);
            echo '404 - Task not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('title', __('tasks.task_title'))
                  ->max('title', 255)
                  ->required('due_date', __('common.due_date'))
                  ->date('due_date', __('common.due_date'));

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'priority' => $_POST['priority'] ?? 'normal',
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'client_id' => $_POST['client_id'] ?? null,
            'project_id' => $_POST['project_id'] ?? null,
            'service_id' => $_POST['service_id'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'due_date' => $_POST['due_date'],
            'estimated_hours' => $_POST['estimated_hours'] ?? null,
        ];

        // Check if assignee changed
        $oldAssignee = $task['assigned_to'];
        $newAssignee = $data['assigned_to'];

        TaskRepo::update($id, $data);

        // Log reassignment
        if ($oldAssignee != $newAssignee && !empty($newAssignee)) {
            $assigneeName = '';
            foreach (TaskRepo::getEmployeesForAssignment() as $emp) {
                if ($emp['id'] == $newAssignee) {
                    $assigneeName = $emp['name'];
                    break;
                }
            }
            TaskCommentRepo::logActivity($id, $_SESSION['user_id'], __('tasks.log.reassigned') . ' ' . $assigneeName);
        }

        Response::withSuccess(__('tasks.updated'));
        Response::redirect('/tasks/' . $id);
    }

    /**
     * Delete task — admin/manager only
     */
    public function delete(int $id): void
    {
        Auth::requireRole('admin', 'manager');
        CSRF::verifyRequest();

        TaskRepo::delete($id);

        Response::withSuccess(__('tasks.deleted'));
        Response::redirect('/tasks');
    }

    /**
     * Update task status — employees can only update their assigned tasks
     */
    public function updateStatus(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        if (Auth::isEmployee()) {
            $task = TaskRepo::find($id);
            if (!$task || (int) ($task['assigned_to'] ?? 0) !== Auth::id()) {
                Response::withError(__('tasks.unauthorized_update'));
                Response::redirect('/tasks');
                return;
            }
        }

        $status = $_POST['status'] ?? '';
        $comment = $_POST['comment'] ?? '';

        $validStatuses = ['draft', 'assigned', 'in_progress', 'in_review', 'revision_needed', 'completed', 'on_hold', 'blocked', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            Response::withError(__('common.invalid_status'));
            Response::back();
            return;
        }

        TaskRepo::updateStatus($id, $status, $comment);

        // Recalculate project progress if task belongs to a project
        $task = $task ?? TaskRepo::find($id);
        if (!empty($task['project_id'])) {
            ProjectRepo::recalculateProgressFromTasks((int) $task['project_id']);
        }

        $statusLabel = __('tasks.status.' . $status);

        $logMessage = __('tasks.log.status_change') . ' ' . $statusLabel;
        if (!empty($comment)) {
            $logMessage .= ' - ' . $comment;
        }

        TaskCommentRepo::logActivity($id, $_SESSION['user_id'], $logMessage);

        // If there's a user comment, add it too
        if (!empty($comment)) {
            TaskCommentRepo::create([
                'task_id' => $id,
                'user_id' => $_SESSION['user_id'],
                'message' => $comment,
            ]);
        }

        // Log time if provided
        $hours = (float) ($_POST['hours'] ?? 0);
        if ($hours > 0 && $hours <= 24) {
            TaskRepo::logTime($id, $hours);
            TaskCommentRepo::logActivity($id, $_SESSION['user_id'], __('tasks.log.time') . ' ' . $hours . ' ' . __('tasks.log.time_unit'));
        }

        Response::withSuccess(__('tasks.status_updated'));
        Response::redirect('/tasks/' . $id);
    }

    /**
     * Update task progress — employees can only update their assigned tasks
     */
    public function updateProgress(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        if (Auth::isEmployee()) {
            $task = TaskRepo::find($id);
            if (!$task || (int) ($task['assigned_to'] ?? 0) !== Auth::id()) {
                Response::withError(__('tasks.unauthorized_update'));
                Response::redirect('/tasks');
                return;
            }
        }

        $progress = (int) ($_POST['progress'] ?? 0);
        $progress = max(0, min(100, $progress));

        TaskRepo::updateProgress($id, $progress);

        // Recalculate project progress if task belongs to a project
        $task = $task ?? TaskRepo::find($id);
        if (!empty($task['project_id'])) {
            ProjectRepo::recalculateProgressFromTasks((int) $task['project_id']);
        }

        TaskCommentRepo::logActivity($id, $_SESSION['user_id'], __('tasks.log.progress') . ' ' . $progress . '%');

        Response::withSuccess(__('tasks.progress_updated'));
        Response::back();
    }

    /**
     * Log time spent — employees can only log time on their assigned tasks
     */
    public function logTime(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        if (Auth::isEmployee()) {
            $task = TaskRepo::find($id);
            if (!$task || (int) ($task['assigned_to'] ?? 0) !== Auth::id()) {
                Response::withError(__('tasks.unauthorized_update'));
                Response::redirect('/tasks');
                return;
            }
        }

        $hours = (float) ($_POST['hours'] ?? 0);
        if ($hours <= 0 || $hours > 24) {
            Response::withError(__('tasks.time_range_error'));
            Response::back();
            return;
        }

        TaskRepo::logTime($id, $hours);

        $description = $_POST['description'] ?? __('tasks.log.time');
        TaskCommentRepo::logActivity($id, $_SESSION['user_id'], $description . ' ' . $hours . ' ' . __('tasks.log.time_unit'));

        Response::withSuccess(__('tasks.time_logged'));
        Response::back();
    }
}
