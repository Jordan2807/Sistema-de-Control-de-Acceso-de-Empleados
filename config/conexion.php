<?php
declare(strict_types=1);

$host = 'localhost';
$baseDatos = 'control_asistencia';
$usuarioDB = 'root';
$passwordDB = '';

$dsn = "mysql:host={$host};dbname={$baseDatos};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $usuarioDB, $passwordDB, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log('Error de conexión a MySQL: ' . $e->getMessage());

    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Verifica la configuración de XAMPP.');
}
