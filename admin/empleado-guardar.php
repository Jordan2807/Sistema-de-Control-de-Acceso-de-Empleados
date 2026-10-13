<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/empleados.php');
    exit;
}

if (!validarTokenCSRF()) {
    http_response_code(403);
    exit('Solicitud inválida. Recarga la página e inténtalo nuevamente.');
}

$cedula = trim($_POST['cedula'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

function volverConMensaje(string $mensaje): never
{
    iniciarSesion();
    $_SESSION['mensaje'] = $mensaje;

    header('Location: ' . BASE_URL . '/admin/empleados.php');
    exit;
}

if ($cedula === '' || $nombre === '') {
    volverConMensaje('La cédula y el nombre son obligatorios.');
}

if (strlen($cedula) > 25 || strlen($nombre) > 150) {
    volverConMensaje('La cédula o el nombre superan la longitud permitida.');
}

if (strlen($correo) > 150 || strlen($telefono) > 25) {
    volverConMensaje('El correo o el teléfono superan la longitud permitida.');
}

if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverConMensaje('El correo electrónico no tiene un formato válido.');
}

try {
    $consulta = $pdo->prepare(
        'SELECT id FROM empleados WHERE cedula = :cedula LIMIT 1'
    );
    $consulta->execute([':cedula' => $cedula]);

    if ($consulta->fetch()) {
        volverConMensaje('Ya existe un empleado registrado con esa cédula.');
    }

    // Generar un código aleatorio de seis dígitos.
    do {
        $codigo = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $consulta = $pdo->prepare(
            'SELECT id FROM empleados WHERE codigo_acceso = :codigo LIMIT 1'
        );
        $consulta->execute([':codigo' => $codigo]);
        $codigoExiste = (bool) $consulta->fetch();

    } while ($codigoExiste);

    $sql = "INSERT INTO empleados
            (cedula, nombre, correo, telefono, codigo_acceso, estado)
            VALUES
            (:cedula, :nombre, :correo, :telefono, :codigo, 'Activo')";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        ':cedula' => $cedula,
        ':nombre' => $nombre,
        ':correo' => $correo !== '' ? $correo : null,
        ':telefono' => $telefono !== '' ? $telefono : null,
        ':codigo' => $codigo
    ]);

    volverConMensaje(
        'Empleado registrado correctamente. Código de acceso: ' . $codigo
    );

} catch (PDOException $e) {
    error_log('Error al registrar empleado: ' . $e->getMessage());

    if ($e->getCode() === '23000') {
        volverConMensaje(
            'La cédula o el código de acceso ya están registrados. Inténtalo nuevamente.'
        );
    }

    volverConMensaje('No fue posible registrar el empleado.');
}
