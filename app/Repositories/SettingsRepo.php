<?php
/**
 * Settings Repository
 */

namespace App\Repositories;

use App\Core\DB;

class SettingsRepo
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $result = DB::fetch("SELECT `value` FROM settings WHERE `key` = :key", ['key' => $key]);
        return $result['value'] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        DB::query(
            "INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = :value2",
            ['key' => $key, 'value' => $value, 'value2' => $value]
        );
    }

    public static function getAll(): array
    {
        $results = DB::fetchAll("SELECT `key`, `value` FROM settings");
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, (string) $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');
        return $value === '1' || $value === 'true' || $value === 'on';
    }
}
