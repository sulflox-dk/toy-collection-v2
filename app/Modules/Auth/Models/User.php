<?php
namespace App\Modules\Auth\Models;

use App\Kernel\Database\BaseModel;

class User extends BaseModel
{
    protected static string $table = 'users';

    /**
     * Create a new user with a hashed password.
     */
    public static function register(string $name, string $email, string $password): int
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere('email', $email);
    }
}
