<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();

function finalizarMarcacion(string $mensaje, string $tipo = 'info'): never
{
    $_SESSION['marcacion_mensaje'] = $mensaje;
    $_SESSION['marcacion_tipo'] = $tipo;

    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if (!validarTokenCSRF()) {
    finalizarMarcacion(
        'La solicitud ha expirado. Recarga la página e inténtalo nuevamente.',
        'warning'
    );
}

$cedula = trim($_POST['cedula'] ?? '');
$codigo = trim($_POST['codigo'] ?? '');

if ($cedula === '' || !preg_match('/^[0-9]{6}$/', $codigo)) {
    finalizarMarcacion(
        'La cédula o el código de acceso no son válidos.',
        'danger'
    );
}

try {
    $pdo->beginTransaction();

    // Verificar los datos y que el empleado esté activo.
    $consulta = $pdo->prepare(
        "SELECT id, nombre
         FROM empleados
         WHERE cedula = :cedula
           AND codigo_acceso = :codigo
           AND estado = 'Activo'
         LIMIT 1"
    );

    $consulta->execute([
        ':cedula' => $cedula,
        ':codigo' => $codigo
    ]);

    $empleado = $consulta->fetch();

    if (!$empleado) {
        $pdo->rollBack();

        finalizarMarcacion(
            'Los datos son incorrectos o el empleado está inactivo.',
            'danger'
        );
    }

    $empleadoId = (int) $empleado['id'];
    $nombre = $empleado['nombre'];

    // Fecha y hora según la zona horaria configurada en app.php.
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    // Bloquear la asistencia existente para evitar cambios simultáneos.
    $consulta = $pdo->prepare(
        "SELECT id, hora_entrada, hora_salida
         FROM asistencias
         WHERE empleado_id = :empleado_id
           AND fecha = :fecha
         LIMIT 1
         FOR UPDATE"
    );

    $consulta->execute([
        ':empleado_id' => $empleadoId,
        ':fecha' => $fecha
    ]);

    $asistencia = $consulta->fetch();

    if (!$asistencia) {
        // Primera marcación del día: entrada.
        $consulta = $pdo->prepare(
            "INSERT INTO asistencias
                (empleado_id, fecha, hora_entrada)
             VALUES
                (:empleado_id, :fecha, :hora)"
        );

        $consulta->execute([
            ':empleado_id' => $empleadoId,
            ':fecha' => $fecha,
            ':hora' => $hora
        ]);

        $pdo->commit();

        finalizarMarcacion(
            "Entrada registrada correctamente para {$nombre} a las {$hora}.",
            'success'
        );
    }

    if ($asistencia['hora_salida'] === null) {
        // Ya existe una entrada: registrar salida.
        $consulta = $pdo->prepare(
            "UPDATE asistencias
             SET hora_salida = :hora
             WHERE id = :id
               AND hora_salida IS NULL"
        );

        $consulta->execute([
            ':hora' => $hora,
            ':id' => $asistencia['id']
        ]);

        if ($consulta->rowCount() !== 1) {
            $pdo->rollBack();

            finalizarMarcacion(
                'No se pudo registrar la salida. Inténtalo nuevamente.',
                'warning'
            );
        }

        $pdo->commit();

        finalizarMarcacion(
            "Salida registrada correctamente para {$nombre} a las {$hora}.",
            'success'
        );
    }

    // Ya tiene entrada y salida registradas.
    $pdo->rollBack();

    finalizarMarcacion(
        "{$nombre}, ya registraste tu entrada y salida de hoy.",
        'warning'
    );

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error al registrar asistencia: ' . $e->getMessage());

    finalizarMarcacion(
        'No fue posible registrar la asistencia. Recarga la página e inténtalo nuevamente.',
        'danger'
    );
}
