<?php

class AdminUserController {

    public function index(): void {
        $users = (new Admin())->all();
        view('admin/users/index', ['users' => $users], 'admin');
    }

    public function create(): void {
        view('admin/users/form', ['user' => null, 'editing' => false], 'admin');
    }

    public function store(): void {
        csrf_verify();
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'admin';

        $errors = [];
        if ($username === '')  $errors[] = 'Username diperlukan.';
        if (strlen($password) < 6) $errors[] = 'Password minimum 6 aksara.';
        if (!in_array($role, ['superadmin', 'admin'])) $errors[] = 'Role tidak sah.';

        $model = new Admin();
        if ($model->usernameExists($username)) $errors[] = 'Username sudah digunakan.';

        if (!empty($errors)) {
            view('admin/users/form', ['user' => null, 'editing' => false, 'errors' => $errors, 'old' => $_POST], 'admin');
            return;
        }

        $model->create($username, $password, $role);
        flash('success', 'Pengguna berjaya ditambah.');
        redirect('/admin/users');
    }

    public function edit(int $id): void {
        $user = (new Admin())->findById($id);
        if (!$user) { redirect('/admin/users'); return; }
        view('admin/users/form', ['user' => $user, 'editing' => true], 'admin');
    }

    public function update(int $id): void {
        csrf_verify();

        // Prevent editing own role/username to avoid lockout
        $currentAdminId = (int)($_SESSION['admin_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'admin';

        $errors = [];
        if ($username === '') $errors[] = 'Username diperlukan.';
        if (!in_array($role, ['superadmin', 'admin'])) $errors[] = 'Role tidak sah.';
        if ($password !== '' && strlen($password) < 6) $errors[] = 'Password minimum 6 aksara.';

        $model = new Admin();
        if ($model->usernameExists($username, $id)) $errors[] = 'Username sudah digunakan.';

        // Prevent removing superadmin role from self
        if ($id === $currentAdminId && $role !== 'superadmin') {
            $errors[] = 'Anda tidak boleh menukar role anda sendiri.';
        }

        if (!empty($errors)) {
            $user = $model->findById($id);
            view('admin/users/form', ['user' => $user, 'editing' => true, 'errors' => $errors, 'old' => $_POST], 'admin');
            return;
        }

        $model->update($id, $username, $role, $password);
        flash('success', 'Pengguna berjaya dikemaskini.');
        redirect('/admin/users');
    }

    public function destroy(int $id): void {
        csrf_verify();
        $currentAdminId = (int)($_SESSION['admin_id'] ?? 0);
        if ($id === $currentAdminId) {
            flash('error', 'Anda tidak boleh memadam akaun anda sendiri.');
            redirect('/admin/users');
            return;
        }
        (new Admin())->delete($id);
        flash('success', 'Pengguna berjaya dipadam.');
        redirect('/admin/users');
    }
}
