<?php

namespace App\Kernel\Http;

class Csrf
{
    /**
     * Start the session if not already started, then ensure a token exists.
     */
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Validate the token from the request against the session.
     * Checks both POST body (_token) and X-CSRF-Token header.
     */
    public static function validate(Request $request): bool
    {
        $token = $request->input('_token')
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        return hash_equals(self::token(), (string) $token);
    }

    /**
     * Return a hidden <input> tag for use in forms.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}
