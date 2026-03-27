<?php
/**
 * Users Controller (Employee Management)
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\UserRepo;

class UsersController
{
    /**
     * List all users (employees)
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $filters = [
            'role' => $_GET['role'] ?? '',
            'department' => $_GET['department'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $page = (int) ($_GET['page'] ?? 1);
        $users = UserRepo::getAll($filters, $page, 25);
        $departments = UserRepo::getDepartments();

        require __DIR__ . '/../Views/users/index.php';
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        Auth::requireRole('admin');

        $csrf = CSRF::field();
        $departments = UserRepo::getDepartments();

        require __DIR__ . '/../Views/users/create.php';
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        Auth::requireRole('admin');
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('name', __('clients.name'))
                  ->max('name', 255)
                  ->required('email', __('auth.email'))
                  ->email('email', __('auth.email'))
                  ->required('password', __('auth.password'))
                  ->min('password', 6, __('auth.password'));

        // Check email uniqueness
        if (!empty($_POST['email']) && UserRepo::findByEmail($_POST['email'])) {
            $validator->addError(__('users.email_exists'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        UserRepo::create([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => $_POST['role'] ?? 'employee',
            'department' => $_POST['department'] ?? null,
            'max_tasks_capacity' => $_POST['max_tasks_capacity'] ?? 5,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);

        Response::withSuccess(__('users.created'));
        Response::redirect('/users');
    }

    /**
     * Show user details
     */
    public function show(int $id): void
    {
        Auth::requireRole('admin');

        $showUser = UserRepo::find($id);
        if (!$showUser) {
            http_response_code(404);
            echo '404 - User not found';
            return;
        }

        // Get user's tasks
        $tasks = \App\Repositories\TaskRepo::getByAssignedTo($id);

        require __DIR__ . '/../Views/users/show.php';
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        Auth::requireRole('admin');

        $editUser = UserRepo::find($id);
        if (!$editUser) {
            http_response_code(404);
            echo '404 - User not found';
            return;
        }

        $csrf = CSRF::field();
        $departments = UserRepo::getDepartments();

        require __DIR__ . '/../Views/users/edit.php';
    }

    /**
     * Update user
     */
    public function update(int $id): void
    {
        Auth::requireRole('admin');
        CSRF::verifyRequest();

        $user = UserRepo::find($id);
        if (!$user) {
            http_response_code(404);
            echo '404 - User not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('name', __('clients.name'))
                  ->max('name', 255)
                  ->required('email', __('auth.email'))
                  ->email('email', __('auth.email'));

        // Check email uniqueness if changed
        if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
            if (UserRepo::findByEmail($_POST['email'])) {
                $validator->addError(__('users.email_exists'));
            }
        }

        // Validate password if provided
        if (!empty($_POST['password']) && strlen($_POST['password']) < 6) {
            $validator->addError(__('users.password_min'));
        }

        if ($validator->fails()) {
            $_SESSION['flash_error'] = $validator->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        UserRepo::update($id, [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'] ?? null,
            'role' => $_POST['role'] ?? 'employee',
            'department' => $_POST['department'] ?? null,
            'max_tasks_capacity' => $_POST['max_tasks_capacity'] ?? 5,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);

        Response::withSuccess(__('users.updated'));
        Response::redirect('/users/' . $id);
    }

    /**
     * Delete user
     */
    public function delete(int $id): void
    {
        Auth::requireRole('admin');
        CSRF::verifyRequest();

        UserRepo::delete($id);

        Response::withSuccess(__('users.deleted'));
        Response::redirect('/users');
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(int $id): void
    {
        Auth::requireRole('admin');
        CSRF::verifyRequest();

        UserRepo::toggleActive($id);

        Response::withSuccess(__('users.toggled'));
        Response::back();
    }
}
