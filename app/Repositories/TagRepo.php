<?php
/**
 * Tag Repository
 */

namespace App\Repositories;

use App\Core\DB;

class TagRepo
{
    public static function getAll(): array
    {
        return DB::fetchAll("SELECT * FROM tags ORDER BY name");
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM tags WHERE id = :id", ['id' => $id]);
    }

    public static function findOrCreate(string $name): int
    {
        $existing = DB::fetch("SELECT id FROM tags WHERE name = :name", ['name' => $name]);
        if ($existing) {
            return $existing['id'];
        }
        return DB::insert('tags', ['name' => $name]);
    }

    public static function create(string $name): int
    {
        return DB::insert('tags', ['name' => $name]);
    }

    public static function update(int $id, string $name): void
    {
        DB::update('tags', ['name' => $name], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('client_tags', 'tag_id = :id', ['id' => $id]);
        DB::delete('tags', 'id = :id', ['id' => $id]);
    }

    public static function getClientCount(int $id): int
    {
        $row = DB::fetch("SELECT COUNT(*) as cnt FROM client_tags WHERE tag_id = :id", ['id' => $id]);
        return (int) ($row['cnt'] ?? 0);
    }
}
