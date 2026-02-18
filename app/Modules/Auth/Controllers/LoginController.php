<?php
namespace App\Modules\Auth\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Auth\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     * GET /login
     */
    public function showLoginForm(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->withoutLayout()->render('login', [
            'title' => 'Sign In',
            'error' => null,
        ]);
    }

    /**
     * Handle a login attempt.
     * POST /login
     */
    public function login(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');

        if ($email === '' || $password === '') {
            $this->withoutLayout()->render('login', [
                'title' => 'Sign In',
                'error' => 'Please enter your email and password.',
            ]);
            return;
        }

        $user = Auth::attempt($email, $password);

        if (!$user) {
            $this->withoutLayout()->render('login', [
                'title' => 'Sign In',
                'error' => 'Invalid email or password.',
            ]);
            return;
        }

        $intended = $_SESSION['intended_url'] ?? '/';
        unset($_SESSION['intended_url']);
        $this->redirect($intended);
    }

    /**
     * Log the user out.
     * POST /logout
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
