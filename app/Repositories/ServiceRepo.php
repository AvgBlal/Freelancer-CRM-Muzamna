<?php
/**
 * Service Repository
 */

namespace App\Repositories;

use App\Core\DB;

class ServiceRepo
{
    private static array $allowedSortColumns = [
        'title' => 's.title',
        'type' => 's.type',
        'end_date' => 's.end_date',
        'price' => 's.price_amount',
        'status' => 's.status',
        'created_at' => 's.created_at',
    ];

    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "s.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = "s.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM service_clients sc2 WHERE sc2.service_id = s.id AND sc2.client_id = :client_id)";
            $params['client_id'] = $filters['client_id'];
        }

        if (isset($filters['is_personal']) && $filters['is_personal'] !== '') {
            $where[] = "s.is_personal = :is_personal";
            $params['is_personal'] = (int) $filters['is_personal'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(s.title LIKE :search1 OR s.notes_sensitive LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['billing_cycle'])) {
            $where[] = "s.billing_cycle = :billing_cycle";
            $params['billing_cycle'] = $filters['billing_cycle'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        // Sorting
        $sortBy = self::$allowedSortColumns[$filters['sort_by'] ?? ''] ?? 's.end_date';
        $sortDir = (strtolower($filters['sort_dir'] ?? '') === 'desc') ? 'DESC' : 'ASC';

        $sql = "SELECT s.*, GROUP_CONCAT(c.name SEPARATOR ', ') as client_names
                FROM services s
                LEFT JOIN service_clients sc ON s.id = sc.service_id
                LEFT JOIN clients c ON sc.client_id = c.id
                WHERE {$whereStr}
                GROUP BY s.id
                ORDER BY {$sortBy} {$sortDir}
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function getCount(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }
        if (isset($filters['is_personal']) && $filters['is_personal'] !== '') {
            $where[] = "is_personal = :is_personal";
            $params['is_personal'] = (int) $filters['is_personal'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(title LIKE :search1 OR notes_sensitive LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM services WHERE {$whereStr}", $params)['count'] ?? 0);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM services WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'title' => $data['title'],
            'type' => $data['type'],
            'status' => $data['status'] ?? 'active',
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'],
            'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
            'price_amount' => $data['price_amount'] ?: null,
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'currency_custom' => $data['currency_custom'] ?? null,
            'billing_cycle' => $data['billing_cycle'] ?? null,
            'notes_sensitive' => $data['notes_sensitive'] ?? null,
            'is_personal' => !empty($data['is_personal']) ? 1 : 0,
        ];

        return DB::insert('services', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'title' => $data['title'],
            'type' => $data['type'],
            'status' => $data['status'] ?? 'active',
            'start_date' => $data['start_date'] ?: null,
            'end_date' => $data['end_date'],
            'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
            'price_amount' => $data['price_amount'] ?: null,
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'currency_custom' => $data['currency_custom'] ?? null,
            'billing_cycle' => $data['billing_cycle'] ?? null,
            'notes_sensitive' => $data['notes_sensitive'] ?? null,
            'is_personal' => !empty($data['is_personal']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('services', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('services', 'id = :id', ['id' => $id]);
    }

    public static function getClients(int $serviceId): array
    {
        return DB::fetchAll(
            "SELECT c.* FROM clients c
             JOIN service_clients sc ON c.id = sc.client_id
             WHERE sc.service_id = :service_id",
            ['service_id' => $serviceId]
        );
    }

    public static function getClientIds(int $serviceId): array
    {
        $results = DB::fetchAll(
            "SELECT client_id FROM service_clients WHERE service_id = :service_id",
            ['service_id' => $serviceId]
        );
        return array_column($results, 'client_id');
    }

    public static function syncClients(int $serviceId, array $clientIds): void
    {
        // Delete existing links
        DB::delete('service_clients', 'service_id = :service_id', ['service_id' => $serviceId]);

        // Insert new links
        foreach ($clientIds as $clientId) {
            if (empty($clientId)) continue;
            DB::query(
                "INSERT INTO service_clients (service_id, client_id) VALUES (:service_id, :client_id)",
                ['service_id' => $serviceId, 'client_id' => $clientId]
            );
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        DB::update('services', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function markExpired(): int
    {
        $sql = "UPDATE services
                SET status = 'expired', updated_at = NOW()
                WHERE status IN ('active', 'paused')
                AND end_date < CURDATE()";

        $stmt = DB::query($sql);
        return $stmt->rowCount();
    }

    public static function getExpiring(int $days): array
    {
        $sql = "SELECT s.*, GROUP_CONCAT(c.name SEPARATOR ', ') as client_names
                FROM services s
                LEFT JOIN service_clients sc ON s.id = sc.service_id
                LEFT JOIN clients c ON sc.client_id = c.id
                WHERE s.status = 'active'
                AND s.end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                AND s.end_date >= CURDATE()
                GROUP BY s.id
                ORDER BY s.end_date ASC";

        return DB::fetchAll($sql, ['days' => $days]);
    }

    /** Monthly Recurring Revenue from active services, grouped by currency */
    public static function getMRR(): array
    {
        $sql = "SELECT currency_code, COALESCE(SUM(CASE
                    WHEN billing_cycle = 'monthly' THEN price_amount
                    WHEN billing_cycle = 'yearly' THEN price_amount / 12
                    ELSE 0
                END), 0) as total
                FROM services
                WHERE status = 'active' AND price_amount IS NOT NULL
                GROUP BY currency_code ORDER BY total DESC";

        return DB::fetchAll($sql);
    }

    /** Revenue by service type */
    public static function getRevenueByType(): array
    {
        $sql = "SELECT type,
                    COUNT(*) as count,
                    COALESCE(SUM(price_amount), 0) as total_value,
                    COALESCE(SUM(CASE
                        WHEN billing_cycle = 'monthly' THEN price_amount
                        WHEN billing_cycle = 'yearly' THEN price_amount / 12
                        ELSE 0
                    END), 0) as monthly_value
                FROM services
                WHERE status = 'active' AND price_amount IS NOT NULL
                GROUP BY type
                ORDER BY monthly_value DESC";

        return DB::fetchAll($sql);
    }

    /** Revenue by client */
    public static function getRevenueByClient(): array
    {
        $sql = "SELECT c.id, c.name,
                    COUNT(DISTINCT s.id) as service_count,
                    COALESCE(SUM(s.price_amount), 0) as total_value,
                    COALESCE(SUM(CASE
                        WHEN s.billing_cycle = 'monthly' THEN s.price_amount
                        WHEN s.billing_cycle = 'yearly' THEN s.price_amount / 12
                        ELSE 0
                    END), 0) as monthly_value
                FROM clients c
                JOIN service_clients sc ON c.id = sc.client_id
                JOIN services s ON sc.service_id = s.id
                WHERE s.status = 'active' AND s.price_amount IS NOT NULL AND s.is_personal = 0
                GROUP BY c.id, c.name
                ORDER BY monthly_value DESC";

        return DB::fetchAll($sql);
    }

    public static function getRecentlyExpired(): array
    {
        $sql = "SELECT s.*, GROUP_CONCAT(c.name SEPARATOR ', ') as client_names
                FROM services s
                LEFT JOIN service_clients sc ON s.id = sc.service_id
                LEFT JOIN clients c ON sc.client_id = c.id
                WHERE s.status = 'expired'
                AND s.end_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY s.id
                ORDER BY s.end_date DESC";

        return DB::fetchAll($sql);
    }

    /** Service overview stats for the services page */
    public static function getOverviewStats(): array
    {
        $active = DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'active'")['count'] ?? 0;
        $expired = DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'expired'")['count'] ?? 0;
        $paused = DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'paused'")['count'] ?? 0;

        $totalValueByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(price_amount), 0) as total FROM services
             WHERE status = 'active' AND price_amount IS NOT NULL
             GROUP BY currency_code ORDER BY total DESC"
        );

        $expiringCount = DB::fetch(
            "SELECT COUNT(*) as count FROM services WHERE status = 'active'
             AND end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND end_date >= CURDATE()"
        )['count'] ?? 0;

        return compact('active', 'expired', 'paused', 'totalValueByCurrency', 'expiringCount');
    }

    /** Renew a service: extend end_date, log renewal, reactivate */
    public static function renew(int $id, string $newEndDate, ?int $renewedBy = null, ?string $notes = null): void
    {
        $service = self::find($id);
        if (!$service) return;

        // Log the renewal
        DB::insert('service_renewals', [
            'service_id' => $id,
            'old_end_date' => $service['end_date'],
            'new_end_date' => $newEndDate,
            'renewed_by' => $renewedBy,
            'notes' => $notes ?: null,
        ]);

        // Update the service
        DB::update('services', [
            'end_date' => $newEndDate,
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    /** Calculate next renewal date based on billing cycle */
    public static function calculateNextEndDate(string $currentEndDate, ?string $billingCycle): string
    {
        $date = new \DateTime($currentEndDate);

        switch ($billingCycle) {
            case 'monthly':
                $date->modify('+1 month');
                break;
            case 'yearly':
                $date->modify('+1 year');
                break;
            default:
                $date->modify('+1 month');
                break;
        }

        return $date->format('Y-m-d');
    }

    /** Get renewal history for a service */
    public static function getRenewalHistory(int $serviceId): array
    {
        $sql = "SELECT sr.*, u.name as renewed_by_name
                FROM service_renewals sr
                LEFT JOIN users u ON sr.renewed_by = u.id
                WHERE sr.service_id = :service_id
                ORDER BY sr.created_at DESC";

        return DB::fetchAll($sql, ['service_id' => $serviceId]);
    }

    /** Get total renewals count for a service */
    public static function getRenewalCount(int $serviceId): int
    {
        return (int) (DB::fetch(
            "SELECT COUNT(*) as count FROM service_renewals WHERE service_id = :service_id",
            ['service_id' => $serviceId]
        )['count'] ?? 0);
    }

    /** Services by type breakdown */
    public static function getCountByType(): array
    {
        $sql = "SELECT type, status, COUNT(*) as count
                FROM services
                GROUP BY type, status
                ORDER BY type, status";

        return DB::fetchAll($sql);
    }
}
