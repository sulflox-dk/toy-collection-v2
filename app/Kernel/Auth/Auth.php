<?php
namespace App\Kernel\Auth;

use App\Kernel\Database\Database;

class Auth
{
    private static ?array $cachedUser = null;

    /**
     * Log in a user by storing their ID in the session.
     * Regenerates session ID to prevent session fixation.
     */
    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        static::$cachedUser = null;
    }

    /**
     * Log out the current user.
     */
    public static function logout(): void
    {
        static::$cachedUser = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if there is an authenticated user.
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get the authenticated user's ID, or null.
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get the full authenticated user record, or null.
     * Cached per-request to avoid repeated DB queries.
     */
    public static function user(): ?array
    {
        if (!static::check()) {
            return null;
        }

        if (static::$cachedUser === null) {
            $db = Database::getInstance();
            static::$cachedUser = $db->query(
                "SELECT id, name, email, created_at FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            )->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        return static::$cachedUser;
    }

    /**
     * Attempt to authenticate with email and password.
     * Returns the user array on success, null on failure.
     */
    public static function attempt(string $email, string $password): ?array
    {
        $db = Database::getInstance();
        $user = $db->query(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        // Rehash if algorithm has been upgraded
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $db->query(
                "UPDATE users SET password = ? WHERE id = ?",
                [password_hash($password, PASSWORD_DEFAULT), $user['id']]
            );
        }

        static::login($user['id']);

        return $user;
    }
}
