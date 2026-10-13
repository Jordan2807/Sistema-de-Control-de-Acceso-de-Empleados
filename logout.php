<?php
declare(strict_types=1);


require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if (!validarTokenCSRF()) {
    http_response_code(403);
    exit('Solicitud inválida. Regresa al sistema e inténtalo nuevamente.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $parametros['path'],
        'domain' => $parametros['domain'],
        'secure' => $parametros['secure'],
        'httponly' => $parametros['httponly'],
        'samesite' => $parametros['samesite'] ?? 'Lax'
    ]);
}

session_destroy();

header('Location: ' . BASE_URL . '/login.php');
exit;
