<?php
namespace App\Modules\Auth\Controllers;

use App\Kernel\Auth\Auth;
use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Modules\Auth\Models\User;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('user_index', [
            'title' => 'Users'
        ]);
    }

    public function list(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = 20;
        $search = trim($request->input('q', ''));
        $role = trim($request->input('role', ''));

        $data = User::getPaginated($page, $perPage, $search, $role);

        $this->renderPartial('user_list', [
            'users' => $data['items'],
            'pagination' => [
                'current' => $page,
                'total'   => $data['totalPages'],
                'count'   => $data['total']
            ]
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $role = trim($request->input('role', 'user'));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['field' => 'email', 'message' => 'A valid email address is required'], 422);
            return;
        }
        if ($password === '') {
            $this->json(['field' => 'password', 'message' => 'Password is required'], 422);
            return;
        }
        if (mb_strlen($password) < 8) {
            $this->json(['field' => 'password', 'message' => 'Password must be at least 8 characters'], 422);
            return;
        }
        if (!in_array($role, ['admin', 'user'], true)) {
            $role = 'user';
        }

        if (User::findByEmail($email)) {
            $this->json(['field' => 'email', 'message' => 'This email is already in use.'], 422);
            return;
        }

        User::register($name, $email, $password, $role);

        $this->json(['success' => true]);
    }

    public function update(Request $request, int $id): void
    {
        $existing = User::find($id);
        if (!$existing) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }

        $name = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $role = trim($request->input('role', 'user'));

        if ($name === '') {
            $this->json(['field' => 'name', 'message' => 'Name is required'], 422);
            return;
        }
        if (mb_strlen($name) > 255) {
            $this->json(['field' => 'name', 'message' => 'Name cannot exceed 255 characters'], 422);
            return;
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['field' => 'email', 'message' => 'A valid email address is required'], 422);
            return;
        }
        if (!in_array($role, ['admin', 'user'], true)) {
            $role = 'user';
        }

        // Check email uniqueness (excluding current user)
        $emailOwner = User::findByEmail($email);
        if ($emailOwner && (int) $emailOwner['id'] !== $id) {
            $this->json(['field' => 'email', 'message' => 'This email is already in use.'], 422);
            return;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ];

        // Only update password if provided
        $password = $request->input('password', '');
        if ($password !== '') {
            if (mb_strlen($password) < 8) {
                $this->json(['field' => 'password', 'message' => 'Password must be at least 8 characters'], 422);
                return;
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        User::update($id, $data);

        // Re-fetch for the updated row HTML
        $updated = User::find($id);

        ob_start();
        $this->renderPartial('user_row', ['u' => $updated]);
        $this->json(['success' => true, 'row_html' => ob_get_clean()]);
    }

    public function destroy(Request $request, int $id): void
    {
        // Prevent deleting yourself
        if ($id === Auth::id()) {
            $this->json(['error' => 'You cannot delete your own account.'], 400);
            return;
        }

        if (!User::find($id)) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }

        User::delete($id);
        $this->json(['success' => true]);
    }
}
