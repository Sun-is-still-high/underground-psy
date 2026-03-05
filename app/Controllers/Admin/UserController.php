<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Session;
use Core\Request;

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $search = trim(Request::get('q', ''));
        $role   = Request::get('role', '');

        $where  = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[]       = "(name LIKE :q OR email LIKE :q)";
            $params['q']   = '%' . $search . '%';
        }
        if (in_array($role, ['CLIENT', 'PSYCHOLOGIST', 'ADMIN'])) {
            $where[]         = "role = :role";
            $params['role']  = $role;
        }

        $sql = "SELECT id, name, email, role, is_blocked, blocked_reason, created_at
                FROM users
                WHERE " . implode(' AND ', $where) . "
                ORDER BY created_at DESC";

        $users = Database::query($sql, $params);

        $this->view('admin/users/index', [
            'title'  => 'Пользователи',
            'users'  => $users,
            'search' => $search,
            'role'   => $role,
        ]);
    }

    public function block(int $id): void
    {
        $this->requireAdmin();

        $reason = trim(Request::post('reason', ''));

        Database::execute(
            "UPDATE users SET is_blocked = 1, blocked_reason = :reason WHERE id = :id",
            ['reason' => $reason ?: null, 'id' => $id]
        );

        Session::flash('success', 'Пользователь заблокирован');
        $this->redirect('/admin/users');
    }

    public function unblock(int $id): void
    {
        $this->requireAdmin();

        Database::execute(
            "UPDATE users SET is_blocked = 0, blocked_reason = NULL WHERE id = :id",
            ['id' => $id]
        );

        Session::flash('success', 'Пользователь разблокирован');
        $this->redirect('/admin/users');
    }
}
