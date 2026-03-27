<?php
/**
 * Service Types — loads from DB (service_types table).
 * Manage via /service-types page.
 * Fallback to hardcoded list if table doesn't exist yet.
 */
try {
    $serviceTypeLabels = \App\Repositories\ServiceTypeRepo::getLabelsMap();
} catch (\Throwable $e) {
    $serviceTypeLabels = [
        'hosting' => __('expenses.cat.hosting'),
        'vps' => 'VPS',
        'support' => __('service_types.fallback.support'),
        'domain' => __('service_types.fallback.domain'),
        'maintenance' => __('service_types.fallback.maintenance'),
        'email' => __('service_types.fallback.email'),
        'custom' => __('service_types.fallback.custom'),
    ];
}
