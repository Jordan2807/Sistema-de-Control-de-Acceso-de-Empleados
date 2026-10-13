<?php
declare(strict_types=1);

function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/'
    ]);

    session_start();
}

function generarTokenCSRF(): string
{
    iniciarSesion();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarTokenCSRF(): bool
{
    iniciarSesion();

    $tokenRecibido = $_POST['csrf_token'] ?? '';
    $tokenGuardado = $_SESSION['csrf_token'] ?? '';

    return is_string($tokenRecibido)
        && is_string($tokenGuardado)
        && $tokenRecibido !== ''
        && hash_equals($tokenGuardado, $tokenRecibido);
}

function escapar(mixed $valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function exigirAdministrador(): void
{
    iniciarSesion();

    if (empty($_SESSION['admin_id'])) {
        require_once __DIR__ . '/../config/app.php';

        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
