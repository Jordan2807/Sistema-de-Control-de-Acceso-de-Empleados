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

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['mensaje'] = 'El empleado seleccionado no es válido.';
    header('Location: ' . BASE_URL . '/admin/empleados.php');
    exit;
}

try {
    $consulta = $pdo->prepare(
        'SELECT estado FROM empleados WHERE id = :id'
    );
    $consulta->execute([':id' => $id]);
    $empleado = $consulta->fetch();

    if (!$empleado) {
        $_SESSION['mensaje'] = 'El empleado no existe.';
    } else {
        $nuevoEstado = $empleado['estado'] === 'Activo'
            ? 'Inactivo'
            : 'Activo';

        $consulta = $pdo->prepare(
            'UPDATE empleados SET estado = :estado WHERE id = :id'
        );

        $consulta->execute([
            ':estado' => $nuevoEstado,
            ':id' => $id
        ]);

        $_SESSION['mensaje'] =
            'El empleado ahora está ' . strtolower($nuevoEstado) . '.';
    }

} catch (PDOException $e) {
    error_log('Error al cambiar estado del empleado: ' . $e->getMessage());
    $_SESSION['mensaje'] = 'No fue posible cambiar el estado del empleado.';
}

header('Location: ' . BASE_URL . '/admin/empleados.php');
exit;
