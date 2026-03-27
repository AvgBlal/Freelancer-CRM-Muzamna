<?php
/**
 * Bulk Operations Controller
 * Handles bulk status changes and bulk delete for various resources
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Repositories\ServiceRepo;
use App\Repositories\TaskRepo;
use App\Repositories\ProjectRepo;
use App\Repositories\ClientRepo;
use App\Repositories\DueRepo;
use App\Repositories\ExpenseRepo;
use App\Repositories\NotesRepo;
use App\Repositories\SafeItemRepo;
use App\Repositories\UnpaidTaskRepo;

class BulkController
{
    /**
     * Bulk update service statuses
     */
    public function services(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $ids = $_POST['ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';

        if (empty($ids) || !is_array($ids)) {
            Response::withError(__('common.no_items_selected'));
            Response::redirect('/services');
            return;
        }

        $allowedStatuses = ['active', 'paused', 'cancelled'];
        $count = 0;

        if (in_array($action, $allowedStatuses)) {
            foreach ($ids as $id) {
                ServiceRepo::updateStatus((int) $id, $action);
                $count++;
            }

            $statusLabel = __('services.status.' . $action);

            Response::withSuccess(__('bulk.updated', ['count' => $count]) . ' ' . $statusLabel);
        } elseif ($action === 'delete') {
            foreach ($ids as $id) {
                ServiceRepo::delete((int) $id);
                $count++;
            }
            Response::withSuccess(__('bulk.deleted', ['count' => $count]));
        } else {
            Response::withError(__('common.invalid_action'));
        }

        Response::redirect('/services');
    }

    /**
     * Bulk update task statuses
     */
    public function tasks(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $ids = $_POST['ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';

        if (empty($ids) || !is_array($ids)) {
            Response::withError(__('common.no_items_selected'));
            Response::redirect('/tasks');
            return;
        }

        $allowedStatuses = ['in_progress', 'completed', 'on_hold', 'cancelled'];
        $count = 0;

        if (in_array($action, $allowedStatuses)) {
            foreach ($ids as $id) {
                TaskRepo::updateStatus((int) $id, $action, '');
                $count++;
            }

            $statusLabel = __('tasks.status.' . $action);

            Response::withSuccess(__('bulk.tasks_updated', ['count' => $count]) . ' ' . $statusLabel);
        } elseif ($action === 'delete') {
            foreach ($ids as $id) {
                TaskRepo::delete((int) $id);
                $count++;
            }
            Response::withSuccess(__('bulk.tasks_deleted', ['count' => $count]));
        } else {
            Response::withError(__('common.invalid_action'));
        }

        Response::redirect('/tasks');
    }

    /**
     * Bulk delete projects
     */
    public function projects(): void
    {
        $this->bulkDelete('projects', ProjectRepo::class, 'bulk.projects_deleted');
    }

    /**
     * Bulk delete clients
     */
    public function clients(): void
    {
        $this->bulkDelete('clients', ClientRepo::class, 'bulk.clients_deleted');
    }

    /**
     * Bulk delete dues
     */
    public function dues(): void
    {
        $this->bulkDelete('dues', DueRepo::class, 'bulk.dues_deleted');
    }

    /**
     * Bulk delete expenses
     */
    public function expenses(): void
    {
        $this->bulkDelete('expenses', ExpenseRepo::class, 'bulk.expenses_deleted');
    }

    /**
     * Bulk delete notes
     */
    public function notes(): void
    {
        $this->bulkDelete('notes', NotesRepo::class, 'bulk.notes_deleted');
    }

    /**
     * Bulk delete safe items
     */
    public function safe(): void
    {
        $this->bulkDelete('safe', SafeItemRepo::class, 'bulk.safe_deleted');
    }

    /**
     * Bulk delete unpaid tasks
     */
    public function unpaidTasks(): void
    {
        $this->bulkDelete('unpaid-tasks', UnpaidTaskRepo::class, 'bulk.deleted');
    }

    private function bulkDelete(string $redirect, string $repoClass, string $messageKey): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $ids = $_POST['ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';

        if (empty($ids) || !is_array($ids)) {
            Response::withError(__('common.no_items_selected'));
            Response::redirect('/' . $redirect);
            return;
        }

        if ($action === 'delete') {
            $count = 0;
            foreach ($ids as $id) {
                $repoClass::delete((int) $id);
                $count++;
            }
            Response::withSuccess(__($messageKey, ['count' => $count]));
        } else {
            Response::withError(__('common.invalid_action'));
        }

        Response::redirect('/' . $redirect);
    }
}
