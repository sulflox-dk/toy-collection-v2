<?php
namespace App\Modules\Settings\Models;

use App\Kernel\Database\Database;

/**
 * Single-row table for cross-system settings (currently just the
 * collection's default currency). Not a BaseModel-style list — just a
 * plain get/update pair against the one row.
 */
class AppSettings
{
    public const CURRENCIES = ['USD', 'EUR', 'GBP', 'DKK'];

    public static function get(): array
    {
        $row = Database::getInstance()->fetch("SELECT * FROM app_settings WHERE id = 1");
        return $row ?: ['id' => 1, 'currency' => 'USD'];
    }

    public static function currency(): string
    {
        return self::get()['currency'];
    }

    public static function update(array $data): void
    {
        Database::getInstance()->execute(
            "UPDATE app_settings SET currency = ? WHERE id = 1",
            [$data['currency']]
        );
    }
}
