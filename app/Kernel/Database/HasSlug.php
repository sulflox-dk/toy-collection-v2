<?php
namespace App\Kernel\Database;

trait HasSlug
{
    // 1. Force the model to implement this method
    abstract public static function getTableName(): string;

    public static function validateUniqueSlug(?string $slug, string $name, int $excludeId = 0): ?string
    {
        $slug = trim($slug ?? '');
        
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        $table = static::getTableName();
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = ? AND id != ?";
        $count = $db->query($sql, [$slug, $excludeId])->fetchColumn();

        if ($count > 0) {
            return null;
        }

        return $slug;
    }
}