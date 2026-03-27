<?php
/**
 * Search Repository
 * Unified search across all modules
 */

namespace App\Repositories;

use App\Core\DB;

class SearchRepo
{
    /**
     * Search across all modules and return unified results
     */
    public static function search(string $query, int $limit = 50): array
    {
        $results = [];
        $searchTerm = '%' . $query . '%';

        // Clients
        $clients = DB::fetchAll(
            "SELECT id, name as title, 'client' as module, type as subtitle, created_at
             FROM clients
             WHERE name LIKE :q OR email LIKE :q OR phone LIKE :q OR notes LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $clients);

        // Services
        $services = DB::fetchAll(
            "SELECT id, title, 'service' as module, CONCAT(type, ' - ', status) as subtitle, created_at
             FROM services
             WHERE title LIKE :q OR type LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $services);

        // Projects
        $projects = DB::fetchAll(
            "SELECT p.id, p.title, 'project' as module, CONCAT(p.status, ' - ', COALESCE(c.name, '')) as subtitle, p.created_at
             FROM projects p
             LEFT JOIN clients c ON p.client_id = c.id
             WHERE p.title LIKE :q OR p.description LIKE :q
             ORDER BY p.created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $projects);

        // Tasks
        $tasks = DB::fetchAll(
            "SELECT id, title, 'task' as module, CONCAT(priority, ' - ', status) as subtitle, created_at
             FROM tasks
             WHERE title LIKE :q OR description LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $tasks);

        // Dues
        $dues = DB::fetchAll(
            "SELECT id, person_name as title, 'due' as module, CONCAT(status, ' - ', COALESCE(currency_code, 'EGP')) as subtitle, created_at
             FROM dues
             WHERE person_name LIKE :q OR notes LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $dues);

        // Expenses
        $expenses = DB::fetchAll(
            "SELECT id, title, 'expense' as module, CONCAT(category, ' - ', status) as subtitle, created_at
             FROM expenses
             WHERE title LIKE :q OR notes LIKE :q OR vendor LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $expenses);

        // Notes
        $notes = DB::fetchAll(
            "SELECT id, title, 'note' as module, CONCAT(category, ' - ', priority) as subtitle, created_at
             FROM notes
             WHERE title LIKE :q OR content LIKE :q
             ORDER BY created_at DESC LIMIT :limit",
            ['q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $notes);

        // Sort by most recent
        usort($results, fn($a, $b) => ($b['created_at'] ?? '') <=> ($a['created_at'] ?? ''));

        // Trim to global limit
        return array_slice($results, 0, $limit);
    }

    /**
     * Employee-scoped search: only their tasks, related projects, own notes
     */
    public static function searchForEmployee(string $query, int $userId, int $limit = 50): array
    {
        $results = [];
        $searchTerm = '%' . $query . '%';

        // Tasks assigned to this employee
        $tasks = DB::fetchAll(
            "SELECT id, title, 'task' as module, CONCAT(priority, ' - ', status) as subtitle, created_at
             FROM tasks
             WHERE assigned_to = :uid AND (title LIKE :q OR description LIKE :q)
             ORDER BY created_at DESC LIMIT :limit",
            ['uid' => $userId, 'q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $tasks);

        // Projects that have tasks assigned to this employee
        $projects = DB::fetchAll(
            "SELECT DISTINCT p.id, p.title, 'project' as module, CONCAT(p.status, ' - ', COALESCE(c.name, '')) as subtitle, p.created_at
             FROM projects p
             LEFT JOIN clients c ON p.client_id = c.id
             INNER JOIN tasks t ON t.project_id = p.id AND t.assigned_to = :uid
             WHERE p.title LIKE :q OR p.description LIKE :q
             ORDER BY p.created_at DESC LIMIT :limit",
            ['uid' => $userId, 'q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $projects);

        // Notes created by this employee
        $notes = DB::fetchAll(
            "SELECT id, title, 'note' as module, CONCAT(category, ' - ', priority) as subtitle, created_at
             FROM notes
             WHERE created_by = :uid AND (title LIKE :q OR content LIKE :q)
             ORDER BY created_at DESC LIMIT :limit",
            ['uid' => $userId, 'q' => $searchTerm, 'limit' => $limit]
        );
        $results = array_merge($results, $notes);

        usort($results, fn($a, $b) => ($b['created_at'] ?? '') <=> ($a['created_at'] ?? ''));
        return array_slice($results, 0, $limit);
    }

    /**
     * Get module URL for a search result
     */
    public static function getUrl(string $module, int $id): string
    {
        $routes = [
            'client' => '/clients/',
            'service' => '/services/',
            'project' => '/projects/',
            'task' => '/tasks/',
            'due' => '/dues/',
            'expense' => '/expenses/',
            'note' => '/notes/',
        ];

        return ($routes[$module] ?? '/') . $id;
    }

    /**
     * Get label for a module
     */
    public static function getModuleLabel(string $module): string
    {
        $labels = [
            'client' => __('entity.client'),
            'service' => __('entity.service'),
            'project' => __('entity.project'),
            'task' => __('entity.task'),
            'due' => __('entity.due'),
            'expense' => __('entity.expense'),
            'note' => __('entity.note'),
        ];

        return $labels[$module] ?? $module;
    }

    /**
     * Get badge color class for a module
     */
    public static function getModuleBadge(string $module): string
    {
        $badges = [
            'client' => 'badge-info',
            'service' => 'badge-success',
            'project' => 'badge-warning',
            'task' => 'badge-urgent',
            'due' => 'badge-info',
            'expense' => 'badge-warning',
            'note' => 'badge-secondary',
        ];

        return $badges[$module] ?? 'badge-secondary';
    }
}
