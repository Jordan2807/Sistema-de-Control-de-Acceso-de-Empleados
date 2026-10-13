<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

$tokenCSRF = generarTokenCSRF();

$fechaHoy = date('Y-m-d');

$totalEmpleados = (int) $pdo
    ->query('SELECT COUNT(*) FROM empleados')
    ->fetchColumn();

$empleadosActivos = (int) $pdo
    ->query("SELECT COUNT(*) FROM empleados WHERE estado = 'Activo'")
    ->fetchColumn();

$empleadosInactivos = $totalEmpleados - $empleadosActivos;

$consulta = $pdo->prepare(
    'SELECT COUNT(DISTINCT empleado_id)
     FROM asistencias
     WHERE fecha = :fecha'
);
$consulta->execute([':fecha' => $fechaHoy]);
$empleadosConEntradaHoy = (int) $consulta->fetchColumn();

$consulta = $pdo->prepare(
    'SELECT COUNT(*)
     FROM asistencias
     WHERE fecha = :fecha
       AND hora_salida IS NULL'
);
$consulta->execute([':fecha' => $fechaHoy]);
$empleadosDentro = (int) $consulta->fetchColumn();

$consulta = $pdo->prepare(
    'SELECT COUNT(*)
     FROM asistencias
     WHERE fecha = :fecha'
);
$consulta->execute([':fecha' => $fechaHoy]);
$registrosHoy = (int) $consulta->fetchColumn();

$consulta = $pdo->prepare(
    "SELECT COUNT(*)
     FROM empleados e
     WHERE e.estado = 'Activo'
       AND NOT EXISTS (
           SELECT 1
           FROM asistencias a
           WHERE a.empleado_id = e.id
             AND a.fecha = :fecha
       )"
);
$consulta->execute([':fecha' => $fechaHoy]);
$empleadosSinMarcar = (int) $consulta->fetchColumn();

$consulta = $pdo->prepare(
    "SELECT a.fecha, a.hora_entrada, a.hora_salida,
            e.nombre, e.cedula
     FROM asistencias a
     INNER JOIN empleados e ON e.id = a.empleado_id
     WHERE a.fecha = :fecha
     ORDER BY a.hora_entrada DESC
     LIMIT 10"
);
$consulta->execute([':fecha' => $fechaHoy]);
$ultimasAsistencias = $consulta->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Control de Asistencia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="<?= escapar(BASE_URL) ?>/assets/css/estilos.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= escapar(BASE_URL) ?>/admin/index.php">
            Control de Asistencia
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white d-none d-md-inline">
                <?= escapar($_SESSION['admin_nombre'] ?? 'Administrador') ?>
            </span>

            <form action="<?= escapar(BASE_URL) ?>/logout.php" method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= escapar($tokenCSRF) ?>">
                <button class="btn btn-danger btn-sm" type="submit">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">

    <div class="mb-4">
        <h1 class="h3">Dashboard administrativo</h1>
        <p class="text-muted mb-0">
            Resumen de asistencia del <?= escapar($fechaHoy) ?>.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total de empleados</p>
                    <h2 class="display-6"><?= $totalEmpleados ?></h2>
                    <span class="small text-muted">
                        Todos los estados
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Empleados activos</p>
                    <h2 class="display-6 text-success">
                        <?= $empleadosActivos ?>
                    </h2>
                    <span class="small text-muted">
                        Inactivos: <?= $empleadosInactivos ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Con asistencia hoy</p>
                    <h2 class="display-6 text-primary">
                        <?= $empleadosConEntradaHoy ?>
                    </h2>
                    <span class="small text-muted">
                        Empleados con registro de entrada
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Dentro según el sistema</p>
                    <h2 class="display-6 text-warning">
                        <?= $empleadosDentro ?>
                    </h2>
                    <span class="small text-muted">
                        Entrada registrada y salida pendiente
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Registros de hoy</p>
                    <h2 class="display-6"><?= $registrosHoy ?></h2>
                    <span class="small text-muted">
                        Asistencias creadas hoy
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Activos sin marcar hoy</p>
                    <h2 class="display-6"><?= $empleadosSinMarcar ?></h2>
                    <span class="small text-muted">
                        Todavía sin registro de hoy
                    </span>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Últimas asistencias de hoy</h2>
        </div>

        <div class="card-body">
            <?php if (!$ultimasAsistencias): ?>
                <p class="text-muted mb-0">
                    Todavía no hay asistencias registradas hoy.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Empleado</th>
                                <th>Cédula</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Estado de asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimasAsistencias as $asistencia): ?>
                                <tr>
                                    <td><?= escapar($asistencia['nombre']) ?></td>
                                    <td><?= escapar($asistencia['cedula']) ?></td>
                                    <td><?= escapar($asistencia['hora_entrada']) ?></td>
                                    <td>
                                        <?= $asistencia['hora_salida'] !== null
                                            ? escapar($asistencia['hora_salida'])
                                            : '<span class="badge text-bg-warning">Pendiente</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($asistencia['hora_salida'] === null): ?>
                                            <span class="badge text-bg-warning">
                                                Sin salida
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">
                                                Completada
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">Accesos rápidos</h2>
        </div>

        <div class="card-body d-flex flex-wrap gap-2">
            <a class="btn btn-primary"
               href="<?= escapar(BASE_URL) ?>/admin/empleados.php">
                Gestionar empleados
            </a>

            <a class="btn btn-outline-primary"
               href="<?= escapar(BASE_URL) ?>/admin/asistencias.php">
                Historial de asistencias
            </a>

            <a class="btn btn-outline-secondary"
               href="<?= escapar(BASE_URL) ?>/index.php"
               target="_blank" rel="noopener">
                Abrir pantalla de marcación
            </a>

            <a
                href="<?= escapar(BASE_URL) ?>/admin/usuarios.php"
                class="btn btn-outline-success">
                Administrar usuarios
            </a>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
