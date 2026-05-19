<?php

class AuthController {
    public function showLogin(): void {
        if (!empty($_SESSION['admin_id'])) {
            redirect('/admin/dashboard');
            return;
        }
        view('admin/auth/login', [], 'admin_guest');
    }

    public function login(): void {
        csrf_verify();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $admin = (new Admin())->findByUsername($username);

        if (!$admin || !password_verify($password, $admin['password'])) {
            view('admin/auth/login', ['error' => 'Username atau password tidak sah.'], 'admin_guest');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        redirect('/admin/dashboard');
    }

    public function logout(): void {
        session_destroy();
        redirect('/admin/login');
    }
}
