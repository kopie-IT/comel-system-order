<?php

declare(strict_types=1);

// -------------------------------------------------------
// cPanel Deployment: public_html is the web root.
// src/, database/ sit one level above (~/src, ~/database).
// ROOT_PATH points to the parent of public_html (~/),
// which is the cPanel home directory.
// -------------------------------------------------------
define('ROOT_PATH',   dirname(__DIR__));
define('SRC_PATH',    ROOT_PATH . '/src');
define('PUBLIC_PATH', __DIR__);   // always points to the actual web root folder
define('BASE_URL',    '');

// Keep admin session alive until explicit logout (30 days)
ini_set('session.gc_maxlifetime', '2592000');
session_set_cookie_params(['lifetime' => 2592000, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();

// Ensure localhost code changes are reflected immediately.
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
if (function_exists('opcache_invalidate')) {
    $devFiles = array_merge(
        glob(SRC_PATH . '/controllers/*.php') ?: [],
        glob(SRC_PATH . '/controllers/admin/*.php') ?: [],
        glob(SRC_PATH . '/models/*.php') ?: []
    );
    foreach ($devFiles as $devFile) {
        @opcache_invalidate($devFile, true);
    }
}

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/malaysia.php';
require_once SRC_PATH . '/helpers/image.php';

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

// Helper: check superadmin auth
function requireSuperAdmin(): void {
    requireAdmin();
    if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;padding:60px"><h2>403 - Akses Ditolak</h2><p>Hanya Super Admin yang dibenarkan.</p><a href="/admin/dashboard">Kembali</a></div>');
    }
}

// Helper: get app name from settings (cached per request)
function app_name(): string {
    static $name = null;
    if ($name === null) {
        try { $name = (new Setting())->get('app_name') ?: 'Comel Baby Store'; }
        catch (Exception $e) { $name = 'Comel Baby Store'; }
    }
    return $name;
}

// Helper: get base URL from settings, fallback to auto-detect
function base_url(): string {
    static $url = null;
    if ($url === null) {
        try {
            $saved = rtrim(trim((new Setting())->get('base_url') ?: ''), '/');
            if ($saved !== '') {
                $url = $saved;
            } else {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $url    = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            }
        } catch (Exception $e) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $url    = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        }
    }
    return $url;
}

// -------------------------------------------------------
// Router
// -------------------------------------------------------
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';
$uri    = strtolower($uri);   // normalise case — fixes /Admin, /ADMIN, etc.
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

} elseif ($seg0 === 'order' && $seg1 === 'ref' && $seg2 !== '') {
    (new OrderRefController())->show($seg2);

} elseif ($seg0 === 'order' && $seg1 === 'confirmation' && $seg2 !== '') {
    (new OrderController())->confirmation($seg2);

} elseif ($seg0 === 'order' && $seg1 === 'view') {
    $ctrl = new OrderController();
    if ($method === 'POST') { $ctrl->processView(); }
    else { $ctrl->showView(); }

} elseif ($seg0 === 'order' && $seg1 === 'track') {
    $ctrl = new OrderController();
    if ($method === 'POST') { $ctrl->lookupByPhone(); }
    else { $ctrl->lookup(); }

} elseif ($seg0 === 'order' && $seg1 === 'update' && $seg2 !== '') {
    $ctrl = new OrderController();
    if ($method === 'POST') { $ctrl->processUpdate($seg2); }
    else { $ctrl->showUpdate($seg2); }

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
        } elseif ($seg2 === 'restore' && $seg3 !== '' && $method === 'POST') {
            $ctrl->restore((int)$seg3);
        } else {
            $ctrl->index();
        }

    } elseif ($seg1 === 'orders') {
        requireAdmin();
        $ctrl = new AdminOrderController();
        if ($seg2 === 'pending-items') {
            $ctrl->pendingItems();
        } elseif ($seg2 !== '' && $seg3 === 'delete' && $method === 'POST') {
            $ctrl->destroy((int)$seg2);
        } elseif ($seg2 !== '' && $seg3 === 'status' && $method === 'POST') {
            $ctrl->updateStatus((int)$seg2);
        } elseif ($seg2 !== '' && $seg3 === 'courier') {
            $ctrl->courier((int)$seg2);
        } elseif ($seg2 !== '' && $seg3 === 'slip' && $method === 'POST') {
            (new AdminCourierSlipController())->upload((int)$seg2);
        } elseif ($seg2 !== '' && $seg3 === 'notify' && $method === 'POST') {
            $ctrl->notify((int)$seg2);
        } elseif ($seg2 !== '') {
            $ctrl->show((int)$seg2);
        } else {
            $ctrl->index();
        }

    } elseif ($seg1 === 'settings') {
        requireAdmin();
        $ctrl = new AdminSettingsController();
        if ($method === 'POST' && $seg2 === 'ai') { $ctrl->updateAi(); }
        elseif ($method === 'POST' && $seg2 === 'blast') { $ctrl->blast(); }
        elseif ($method === 'POST' && $seg2 === 'test-wawp') { $ctrl->testWawp(); }
        elseif ($method === 'POST' && $seg2 === 'auto-cancel') { $ctrl->updateAutoCancel(); }
        elseif ($method === 'POST') { $ctrl->update(); }
        else { $ctrl->index(); }

    } elseif ($seg1 === 'ai' && $seg2 === 'chat') {
        requireAdmin();
        (new AdminAiController())->chat();

    } elseif ($seg1 === 'ai' && $seg2 === 'models') {
        requireAdmin();
        (new AdminAiController())->models();

    } elseif ($seg1 === 'ai') {
        requireAdmin();
        (new AdminAiPageController())->index();

    } elseif ($seg1 === 'customers') {
        requireAdmin();
        $ctrl = new AdminCustomerController();
        if ($seg2 !== '') { $ctrl->show((int)$seg2); }
        else { $ctrl->index(); }

    } elseif ($seg1 === 'users') {
        requireSuperAdmin();
        $ctrl = new AdminUserController();
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

    } elseif ($seg1 === 'backup') {
        requireAdmin();
        $ctrl = new AdminBackupController();
        if ($seg2 === 'db' && $method === 'POST')    { $ctrl->downloadDb(); }
        elseif ($seg2 === 'files' && $method === 'POST') { $ctrl->downloadFiles(); }
        else { $ctrl->index(); }

    } elseif ($seg1 === 'whatsapp') {
        requireAdmin();
        $ctrl = new AdminWhatsappController();
        if ($seg2 === 'edit' && $seg3 !== '') {
            if ($method === 'POST') { $ctrl->update((int)$seg3); }
            else { $ctrl->edit((int)$seg3); }
        } elseif ($seg2 === 'reset' && $seg3 !== '' && $method === 'POST') {
            $ctrl->reset((int)$seg3);
        } elseif ($seg2 === 'blast' && $method === 'POST') {
            $ctrl->blast();
        } else {
            $ctrl->index();
        }

    } else {
        redirect('/admin/dashboard');
    }

} else {
    http_response_code(404);
    view('errors/404', [], 'public');
}
