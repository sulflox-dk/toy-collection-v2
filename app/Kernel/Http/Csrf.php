<?php

namespace App\Kernel\Http;

class Csrf
{
    /**
     * Start the session (securely) if not already started, then ensure a token exists.
     */
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            // HARDENED SESSION START 🛡️
            session_start([
                'cookie_lifetime' => 0,    // Session dies when browser closes
                'cookie_path'     => '/',  // Available on entire domain
                'cookie_httponly' => true, // JavaScript cannot access this cookie (XSS protection)
                'cookie_samesite' => 'Lax',// Cookie not sent on cross-site POSTs (CSRF protection)
                'cookie_secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Only allow over HTTPS if SSL is active
            ]);
        }

        if (empty($_SESSION['_csrf_token'])) {
            // Use random_bytes for cryptographically secure token
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
        // ✅ SECURE: Only check POST data or Headers. Never GET.
        // If you added public function post() to Request.php, use $request->post('_token')
        // Otherwise, use $_POST['_token']
        $token = $_POST['_token'] 
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