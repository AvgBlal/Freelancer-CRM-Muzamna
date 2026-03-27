<?php
/**
 * Service Type Repository
 */

namespace App\Repositories;

use App\Core\DB;

class ServiceTypeRepo
{
    public static function getAll(): array
    {
        return DB::fetchAll("SELECT * FROM service_types ORDER BY sort_order, label");
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM service_types WHERE id = :id", ['id' => $id]);
    }

    public static function create(string $slug, string $label): int
    {
        $maxSort = DB::fetch("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_sort FROM service_types");
        return DB::insert('service_types', [
            'slug' => $slug,
            'label' => $label,
            'sort_order' => $maxSort['next_sort'] ?? 1,
        ]);
    }

    public static function update(int $id, string $slug, string $label): void
    {
        DB::update('service_types', ['slug' => $slug, 'label' => $label], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('service_types', 'id = :id', ['id' => $id]);
    }

    /** Count services using this type slug */
    public static function getServiceCount(int $id): int
    {
        $type = self::find($id);
        if (!$type) return 0;
        $row = DB::fetch("SELECT COUNT(*) as cnt FROM services WHERE type = :slug", ['slug' => $type['slug']]);
        return (int) ($row['cnt'] ?? 0);
    }

    /** Get all types as slug => label map (for dropdowns/display) */
    public static function getLabelsMap(): array
    {
        $types = self::getAll();
        $map = [];
        foreach ($types as $t) {
            $map[$t['slug']] = $t['label'];
        }
        return $map;
    }
}
