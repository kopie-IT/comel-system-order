<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH',  ROOT_PATH . '/src');
define('BASE_URL',  '');

session_start();

require_once SRC_PATH . '/config/database.php';

// Autoload controllers and models
spl_autoload_register(function (string $class): void {
    $paths = [
        SRC_PATH . '/controllers/' . $class . '.php',
        SRC_PATH . '/controllers/admin/' . $class . '.php',
        SRC_PATH . '/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Helper: generate CSRF token
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper: verify CSRF token
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

// Helper: redirect
function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}

// Helper: render view
function view(string $template, array $data = [], string $layout = 'public'): void {
    extract($data);
    $layoutFile = SRC_PATH . '/views/layouts/' . $layout . '.php';
    $viewFile   = SRC_PATH . '/views/' . $template . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(404);
        die('View not found: ' . htmlspecialchars($template));
    }
    require $layoutFile;
}

// Helper: render partial (no layout)
function partial(string $template, array $data = []): void {
    extract($data);
    require SRC_PATH . '/views/' . $template . '.php';
}

// Helper: sanitize output
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Helper: flash messages
function flash(string $key, string $message = ''): string {
    if ($message !== '') {
        $_SESSION['flash'][$key] = $message;
        return '';
    }
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

// Helper: check admin auth
function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        redirect('/admin/login');
    }
}

// -------------------------------------------------------
// Router
// -------------------------------------------------------
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Segment helper
$segments = explode('/', ltrim($uri, '/'));

$seg0 = $segments[0] ?? '';
$seg1 = $segments[1] ?? '';
$seg2 = $segments[2] ?? '';
$seg3 = $segments[3] ?? '';

// -------------------------------------------------------
// Public routes
// -------------------------------------------------------
if ($seg0 === '') {
    (new HomeController())->index();

} elseif ($seg0 === 'category' && $seg1 !== '') {
    (new CategoryController())->show((int)$seg1);

} elseif ($seg0 === 'product' && $seg1 !== '') {
    (new ProductController())->show((int)$seg1);

} elseif ($seg0 === 'cart') {
    $ctrl = new CartController();
    if ($method === 'POST' && $seg1 === 'add')    { $ctrl->add(); }
    elseif ($method === 'POST' && $seg1 === 'update') { $ctrl->update(); }
    elseif ($method === 'POST' && $seg1 === 'remove') { $ctrl->remove(); }
    else { $ctrl->index(); }

} elseif ($seg0 === 'checkout') {
    $ctrl = new CheckoutController();
    if ($method === 'POST') { $ctrl->process(); }
    else { $ctrl->index(); }

} elseif ($seg0 === 'order' && $seg1 === 'confirmation' && $seg2 !== '') {
    (new OrderController())->confirmation($seg2);

// -------------------------------------------------------
// Admin routes
// -------------------------------------------------------
} elseif ($seg0 === 'admin') {

    if ($seg1 === 'login') {
        $ctrl = new AuthController();
        if ($method === 'POST') { $ctrl->login(); }
        else { $ctrl->showLogin(); }

    } elseif ($seg1 === 'logout') {
        (new AuthController())->logout();

    } elseif ($seg1 === 'dashboard' || $seg1 === '') {
        requireAdmin();
        (new DashboardController())->index();

    } elseif ($seg1 === 'categories') {
        requireAdmin();
        $ctrl = new AdminCategoryController();
        if ($seg2 === 'add') {
            if ($method === 'POST') { $ctrl->store(); }
            else { $ctrl->create(); }
        } elseif ($seg2 === 'edit' && $seg3 !== '') {
            if ($method === 'POST') { $ctrl->update((int)$seg3); }
            else { $ctrl->edit((int)$seg3); }
        } elseif ($seg2 === 'delete' && $seg3 !== '' && $method === 'POST') {
            $ctrl->destroy((int)$seg3);
        } else {
            $ctrl->index();
        }

    } elseif ($seg1 === 'products') {
        requireAdmin();
        $ctrl = new AdminProductController();
        if ($seg2 === 'add') {
            if ($method === 'POST') { $ctrl->store(); }
            else { $ctrl->create(); }
        } elseif ($seg2 === 'edit' && $seg3 !== '') {
            if ($method === 'POST') { $ctrl->update((int)$seg3); }
            else { $ctrl->edit((int)$seg3); }
        } elseif ($seg2 === 'delete' && $seg3 !== '' && $method === 'POST') {
            $ctrl->destroy((int)$seg3);
        } else {
            $ctrl->index();
        }

    } elseif ($seg1 === 'orders') {
        requireAdmin();
        $ctrl = new AdminOrderController();
        if ($seg2 !== '' && $seg3 === 'status' && $method === 'POST') {
            $ctrl->updateStatus((int)$seg2);
        } elseif ($seg2 !== '') {
            $ctrl->show((int)$seg2);
        } else {
            $ctrl->index();
        }

    } elseif ($seg1 === 'settings') {
        requireAdmin();
        $ctrl = new AdminSettingsController();
        if ($method === 'POST') { $ctrl->update(); }
        else { $ctrl->index(); }

    } else {
        redirect('/admin/dashboard');
    }

} else {
    http_response_code(404);
    view('errors/404', [], 'public');
}
