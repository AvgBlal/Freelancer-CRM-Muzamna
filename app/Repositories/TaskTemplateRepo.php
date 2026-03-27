<?php
/**
 * Task Template Repository
 */

namespace App\Repositories;

use App\Core\DB;

class TaskTemplateRepo
{
    /**
     * Get all templates
     */
    public static function getAll(): array
    {
        $sql = "SELECT tt.*, u.name as creator_name
                FROM task_templates tt
                JOIN users u ON tt.created_by = u.id
                WHERE tt.is_active = 1
                ORDER BY tt.category, tt.name";

        return DB::fetchAll($sql);
    }

    /**
     * Get template by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT tt.*, u.name as creator_name
                FROM task_templates tt
                JOIN users u ON tt.created_by = u.id
                WHERE tt.id = :id";

        return DB::fetch($sql, ['id' => $id]);
    }

    /**
     * Get templates by category
     */
    public static function getByCategory(string $category): array
    {
        $sql = "SELECT * FROM task_templates
                WHERE category = :category AND is_active = 1
                ORDER BY name";

        return DB::fetchAll($sql, ['category' => $category]);
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM task_templates
                WHERE is_active = 1 AND category IS NOT NULL
                ORDER BY category";

        $results = DB::fetchAll($sql);
        return array_column($results, 'category');
    }

    /**
     * Create template
     */
    public static function create(array $data): int
    {
        $fields = [
            'created_by' => $data['created_by'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_hours' => $data['default_hours'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'category' => $data['category'] ?? null,
            'is_active' => true,
        ];

        return DB::insert('task_templates', $fields);
    }

    /**
     * Update template
     */
    public static function update(int $id, array $data): void
    {
        $fields = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default_hours' => $data['default_hours'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'category' => $data['category'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        DB::update('task_templates', $fields, 'id = :id', ['id' => $id]);
    }

    /**
     * Delete template (soft delete)
     */
    public static function delete(int $id): void
    {
        DB::update('task_templates', ['is_active' => false], 'id = :id', ['id' => $id]);
    }

    /**
     * Create task from template
     */
    public static function createTaskFromTemplate(int $templateId, array $overrides = []): ?int
    {
        $template = self::find($templateId);
        if (!$template) {
            return null;
        }

        $taskData = [
            'title' => $overrides['title'] ?? $template['name'],
            'description' => $overrides['description'] ?? $template['description'],
            'priority' => $overrides['priority'] ?? $template['priority'],
            'estimated_hours' => $overrides['estimated_hours'] ?? $template['default_hours'],
            'created_by' => $overrides['created_by'],
            'assigned_to' => $overrides['assigned_to'] ?? null,
            'client_id' => $overrides['client_id'] ?? null,
            'project_id' => $overrides['project_id'] ?? null,
            'due_date' => $overrides['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
        ];

        return TaskRepo::create($taskData);
    }
}
