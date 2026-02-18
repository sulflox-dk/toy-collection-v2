<?php
namespace App\Modules\Auth\Models;

use App\Kernel\Database\BaseModel;

class User extends BaseModel
{
    protected static string $table = 'users';

    /**
     * Create a new user with a hashed password.
     */
    public static function register(string $name, string $email, string $password, string $role = 'user'): int
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere('email', $email);
    }

    /**
     * Get paginated user list with search and role filtering.
     */
    public static function getPaginated(int $page = 1, int $perPage = 20, string $search = '', string $role = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = "SELECT id, name, email, role, created_at, updated_at FROM " . static::$table;

        if ($search !== '') {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($role !== '') {
            $where[] = "role = ?";
            $params[] = $role;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY name ASC";

        $countSql = "SELECT COUNT(*) FROM ({$sql}) as sub";
        $total = (int) static::db()->query($countSql, $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $items = static::db()->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }
}
