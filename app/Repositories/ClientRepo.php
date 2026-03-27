<?php
/**
 * Client Repository
 */

namespace App\Repositories;

use App\Core\DB;

class ClientRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE :search1 OR c.email LIKE :search2 OR c.phone LIKE :search3)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['tag'])) {
            $where[] = "EXISTS (SELECT 1 FROM client_tags ct JOIN tags t ON ct.tag_id = t.id WHERE ct.client_id = c.id AND t.name = :tag)";
            $params['tag'] = $filters['tag'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT c.*, GROUP_CONCAT(t.name SEPARATOR ', ') as tag_list
                FROM clients c
                LEFT JOIN client_tags ct ON c.id = ct.client_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                WHERE {$whereStr}
                GROUP BY c.id
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM clients WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'name' => $data['name'],
            'type' => $data['type'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'timezone' => $data['timezone'] ?? 'Africa/Cairo',
            'preferred_channel' => $data['preferred_channel'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        return DB::insert('clients', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'name' => $data['name'],
            'type' => $data['type'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'timezone' => $data['timezone'] ?? 'Africa/Cairo',
            'preferred_channel' => $data['preferred_channel'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('clients', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('clients', 'id = :id', ['id' => $id]);
    }

    public static function getContacts(int $clientId): array
    {
        return DB::fetchAll(
            "SELECT * FROM client_contacts WHERE client_id = :client_id ORDER BY created_at",
            ['client_id' => $clientId]
        );
    }

    public static function saveContacts(int $clientId, array $contacts): void
    {
        // Delete existing contacts
        DB::delete('client_contacts', 'client_id = :client_id', ['client_id' => $clientId]);

        // Insert new contacts
        foreach ($contacts as $contact) {
            if (empty($contact['name'])) continue;

            DB::insert('client_contacts', [
                'client_id' => $clientId,
                'name' => $contact['name'],
                'job_title' => $contact['job_title'] ?? null,
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'notes' => $contact['notes'] ?? null,
            ]);
        }
    }

    public static function getTags(int $clientId): array
    {
        return DB::fetchAll(
            "SELECT t.* FROM tags t
             JOIN client_tags ct ON t.id = ct.tag_id
             WHERE ct.client_id = :client_id",
            ['client_id' => $clientId]
        );
    }

    public static function getTagIds(int $clientId): array
    {
        $results = DB::fetchAll(
            "SELECT tag_id FROM client_tags WHERE client_id = :client_id",
            ['client_id' => $clientId]
        );
        return array_column($results, 'tag_id');
    }

    public static function syncTags(int $clientId, array $tagIds): void
    {
        // Delete existing tags
        DB::delete('client_tags', 'client_id = :client_id', ['client_id' => $clientId]);

        // Insert new tags
        foreach ($tagIds as $tagId) {
            if (empty($tagId)) continue;
            DB::query(
                "INSERT IGNORE INTO client_tags (client_id, tag_id) VALUES (:client_id, :tag_id)",
                ['client_id' => $clientId, 'tag_id' => $tagId]
            );
        }
    }

    public static function getServices(int $clientId): array
    {
        return DB::fetchAll(
            "SELECT s.* FROM services s
             JOIN service_clients sc ON s.id = sc.service_id
             WHERE sc.client_id = :client_id
             ORDER BY s.end_date ASC",
            ['client_id' => $clientId]
        );
    }

    public static function getProjects(int $clientId): array
    {
        return DB::fetchAll(
            "SELECT * FROM projects WHERE client_id = :client_id ORDER BY created_at DESC",
            ['client_id' => $clientId]
        );
    }
}
