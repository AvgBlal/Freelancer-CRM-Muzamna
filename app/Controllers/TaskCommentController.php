<?php
/**
 * Task Comment Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Repositories\TaskCommentRepo;
use App\Repositories\TaskRepo;

class TaskCommentController
{
    /**
     * Store new comment
     */
    public function store(int $taskId): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $task = TaskRepo::find($taskId);
        if (!$task) {
            http_response_code(404);
            echo '404 - Task not found';
            return;
        }

        // Employees can only comment on their assigned tasks
        if (Auth::isEmployee() && (int) ($task['assigned_to'] ?? 0) !== Auth::id()) {
            Response::withError(__('comments.unauthorized'));
            Response::redirect('/tasks');
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            Response::withError(__('comments.empty'));
            Response::back();
            return;
        }

        // Parse mentions
        $mentions = TaskCommentRepo::parseMentions($message);

        TaskCommentRepo::create([
            'task_id' => $taskId,
            'user_id' => $_SESSION['user_id'] ?? 0,
            'message' => $message,
            'mentions' => $mentions,
        ]);

        // Log time if provided
        $hours = (float) ($_POST['hours'] ?? 0);
        if ($hours > 0 && $hours <= 24) {
            TaskRepo::logTime($taskId, $hours);
            TaskCommentRepo::logActivity($taskId, $_SESSION['user_id'], __('comments.log.time') . ' ' . $hours . ' ' . __('tasks.log.time_unit'));
        }

        Response::withSuccess(__('comments.created'));
        Response::redirect('/tasks/' . $taskId);
    }

    /**
     * Delete comment
     */
    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        TaskCommentRepo::delete($id);

        Response::withSuccess(__('comments.deleted'));
        Response::back();
    }

    /**
     * Get comments via AJAX (for real-time updates)
     */
    public function getComments(int $taskId): void
    {
        Auth::requireAuth();

        $comments = TaskCommentRepo::getByTask($taskId);

        header('Content-Type: application/json');
        echo json_encode(['comments' => $comments]);
    }
}
