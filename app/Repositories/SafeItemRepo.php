<?php
/**
 * Safe Item Repository
 * Digital vault for stable plugins, URLs, and files
 * Also handles quotations (عروض أسعار) and invoices (فواتير)
 */

namespace App\Repositories;

use App\Core\DB;

class SafeItemRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "si.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "si.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(si.title LIKE :search OR si.notes LIKE :search2 OR si.tags LIKE :search3)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['tag'])) {
            $where[] = "FIND_IN_SET(:tag, si.tags)";
            $params['tag'] = $filters['tag'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT si.*, u.name as creator_name, c.name as client_name,
                (SELECT COUNT(*) FROM safe_item_files sif WHERE sif.safe_item_id = si.id) as file_count
                FROM safe_items si
                LEFT JOIN users u ON u.id = si.created_by
                LEFT JOIN clients c ON c.id = si.client_id
                WHERE {$whereStr}
                ORDER BY si.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function getCount(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE :search OR notes LIKE :search2 OR tags LIKE :search3)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['tag'])) {
            $where[] = "FIND_IN_SET(:tag, tags)";
            $params['tag'] = $filters['tag'];
        }

        $whereStr = implode(' AND ', $where);
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM safe_items WHERE {$whereStr}", $params)['count'] ?? 0);
    }

    public static function find(int $id): ?array
    {
        $item = DB::fetch(
            "SELECT si.*, u.name as creator_name, c.name as client_name
             FROM safe_items si
             LEFT JOIN users u ON u.id = si.created_by
             LEFT JOIN clients c ON c.id = si.client_id
             WHERE si.id = :id",
            ['id' => $id]
        );

        if ($item) {
            $item['files'] = self::getFiles($id);
        }

        return $item;
    }

    public static function create(array $data): int
    {
        $fields = [
            'type' => $data['type'] ?? 'general',
            'client_id' => !empty($data['client_id']) ? (int) $data['client_id'] : null,
            'title' => $data['title'],
            'url' => $data['url'] ?: null,
            'file_path' => $data['file_path'] ?? null,
            'file_original_name' => $data['file_original_name'] ?? null,
            'file_size' => !empty($data['file_size']) ? (int) $data['file_size'] : null,
            'notes' => $data['notes'] ?: null,
            'tags' => $data['tags'] ?: null,
            'created_by' => $data['created_by'] ?: null,
        ];

        return DB::insert('safe_items', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'title' => $data['title'],
            'url' => $data['url'] ?: null,
            'notes' => $data['notes'] ?: null,
            'tags' => $data['tags'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (array_key_exists('client_id', $data)) {
            $fields['client_id'] = !empty($data['client_id']) ? (int) $data['client_id'] : null;
        }

        if (array_key_exists('file_path', $data)) {
            $fields['file_path'] = $data['file_path'] ?: null;
            $fields['file_original_name'] = $data['file_original_name'] ?: null;
            $fields['file_size'] = !empty($data['file_size']) ? (int) $data['file_size'] : null;
        }

        DB::update('safe_items', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('safe_items', 'id = :id', ['id' => $id]);
    }

    // --- Multi-file methods ---

    public static function getFiles(int $itemId): array
    {
        return DB::fetchAll(
            "SELECT * FROM safe_item_files WHERE safe_item_id = :item_id ORDER BY sort_order, created_at",
            ['item_id' => $itemId]
        );
    }

    public static function findFile(int $fileId): ?array
    {
        return DB::fetch("SELECT * FROM safe_item_files WHERE id = :id", ['id' => $fileId]);
    }

    public static function addFile(int $itemId, array $data): int
    {
        return DB::insert('safe_item_files', [
            'safe_item_id' => $itemId,
            'file_path' => $data['file_path'],
            'file_original_name' => $data['file_original_name'],
            'file_size' => $data['file_size'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public static function deleteFileRecord(int $fileId): void
    {
        DB::delete('safe_item_files', 'id = :id', ['id' => $fileId]);
    }

    // --- Client-based queries ---

    public static function getByClient(int $clientId, string $type = ''): array
    {
        $where = "si.client_id = :client_id";
        $params = ['client_id' => $clientId];

        if ($type) {
            $where .= " AND si.type = :type";
            $params['type'] = $type;
        }

        return DB::fetchAll(
            "SELECT si.*, u.name as creator_name,
             (SELECT COUNT(*) FROM safe_item_files sif WHERE sif.safe_item_id = si.id) as file_count
             FROM safe_items si
             LEFT JOIN users u ON u.id = si.created_by
             WHERE {$where}
             ORDER BY si.created_at DESC",
            $params
        );
    }

    // --- Tags ---

    public static function getAllTags(string $type = ''): array
    {
        $where = "tags IS NOT NULL AND tags != ''";
        $params = [];
        if ($type) {
            $where .= " AND type = :type";
            $params['type'] = $type;
        }

        $rows = DB::fetchAll("SELECT tags FROM safe_items WHERE {$where}", $params);
        $allTags = [];
        foreach ($rows as $row) {
            $parts = array_map('trim', explode(',', $row['tags']));
            foreach ($parts as $tag) {
                if ($tag !== '') {
                    $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
                }
            }
        }
        arsort($allTags);
        return $allTags;
    }

    public static function getStats(string $type = ''): array
    {
        $where = '1=1';
        $params = [];
        if ($type) {
            $where = "type = :type";
            $params['type'] = $type;
        }

        $total = (int) (DB::fetch("SELECT COUNT(*) as c FROM safe_items WHERE {$where}", $params)['c'] ?? 0);
        $withFiles = (int) (DB::fetch("SELECT COUNT(*) as c FROM safe_items WHERE {$where} AND file_path IS NOT NULL", $params)['c'] ?? 0);
        $withUrls = (int) (DB::fetch("SELECT COUNT(*) as c FROM safe_items WHERE {$where} AND url IS NOT NULL AND url != ''", $params)['c'] ?? 0);
        return compact('total', 'withFiles', 'withUrls');
    }
}
