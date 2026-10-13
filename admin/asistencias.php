<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

$tokenCSRF = generarTokenCSRF();

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

$empleadoFiltro = trim($_GET['empleado'] ?? '');
$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

$errores = [];

$validarFecha = static function (string $fecha): bool {
    if ($fecha === '') {
        return true;
    }

    $objetoFecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);

    return $objetoFecha !== false
        && $objetoFecha->format('Y-m-d') === $fecha;
};

if (!$validarFecha($fechaDesde)) {
    $errores[] = 'La fecha inicial no es válida.';
}

if (!$validarFecha($fechaHasta)) {
    $errores[] = 'La fecha final no es válida.';
}

if (
    $fechaDesde !== ''
    && $fechaHasta !== ''
    && $fechaDesde > $fechaHasta
) {
    $errores[] = 'La fecha inicial no puede ser posterior a la fecha final.';
}

$asistencias = [];

if (!$errores) {
    $sql = "SELECT
                a.id,
                a.fecha,
                a.hora_entrada,
                a.hora_salida,
                e.cedula,
                e.nombre
            FROM asistencias a
            INNER JOIN empleados e ON e.id = a.empleado_id
            WHERE 1 = 1";

    $parametros = [];

    if ($empleadoFiltro !== '') {
        $sql .= " AND (
                    e.cedula LIKE :cedula
                    OR e.nombre LIKE :nombre
                  )";

        $parametros[':cedula'] = '%' . $empleadoFiltro . '%';
        $parametros[':nombre'] = '%' . $empleadoFiltro . '%';
    }

    if ($fechaDesde !== '') {
        $sql .= ' AND a.fecha >= :desde';
        $parametros[':desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '') {
        $sql .= ' AND a.fecha <= :hasta';
        $parametros[':hasta'] = $fechaHasta;
    }

    $sql .= ' ORDER BY a.fecha DESC, a.hora_entrada DESC';

    $consulta = $pdo->prepare($sql);
    $consulta->execute($parametros);
    $asistencias = $consulta->fetchAll();
}

function calcularHorasTrabajadas(
    string $entrada,
    ?string $salida
): string {
    if ($salida === null || $salida === '') {
        return 'Pendiente de salida';
    }

    $inicio = strtotime($entrada);
    $fin = strtotime($salida);

    if ($inicio === false || $fin === false || $fin < $inicio) {
        return 'No disponible';
    }

    $segundos = $fin - $inicio;
    $horas = intdiv($segundos, 3600);
    $minutos = intdiv($segundos % 3600, 60);

    return sprintf('%02d h %02d min', $horas, $minutos);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Historial de asistencias | Control de Asistencia</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="<?= escapar(BASE_URL) ?>/assets/css/estilos.css"
        rel="stylesheet"
    >
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand"
           href="<?= escapar(BASE_URL) ?>/admin/index.php">
            Control de Asistencia
        </a>

        <form action="<?= escapar(BASE_URL) ?>/logout.php" method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= escapar($tokenCSRF) ?>">
            <button class="btn btn-danger btn-sm" type="submit">
                Cerrar sesión
            </button>
        </form>
    </div>
</nav>

<main class="container py-4">

    <div class="mb-4">
        <h1 class="h3">Historial de asistencias</h1>
        <p class="text-muted">
            Consulta las entradas, salidas y horas trabajadas.
        </p>
    </div>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-info">
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($errores as $error): ?>
        <div class="alert alert-danger">
            <?= escapar($error) ?>
        </div>
    <?php endforeach; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Filtros de búsqueda</h2>
        </div>

        <div class="card-body">
            <form method="GET"
                  action="<?= escapar(BASE_URL) ?>/admin/asistencias.php">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="empleado" class="form-label">
                            Nombre o cédula
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="empleado"
                            name="empleado"
                            value="<?= escapar($empleadoFiltro) ?>"
                            maxlength="150"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="desde" class="form-label">
                            Fecha desde
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="desde"
                            name="desde"
                            value="<?= escapar($fechaDesde) ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="hasta" class="form-label">
                            Fecha hasta
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="hasta"
                            name="hasta"
                            value="<?= escapar($fechaHasta) ?>"
                        >
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            Buscar
                        </button>
                    </div>
                </div>

                <a class="btn btn-outline-secondary btn-sm mt-3"
                   href="<?= escapar(BASE_URL) ?>/admin/asistencias.php">
                    Limpiar filtros
                </a>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a
            href="<?= escapar(BASE_URL) ?>/admin/asistencias-exportar.php?<?= escapar(http_build_query([
                'empleado' => $empleadoFiltro,
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ])) ?>"
            class="btn btn-success"
        >
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Exportar a Excel (CSV)
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Registros encontrados</h2>
            <span class="badge text-bg-primary">
                <?= count($asistencias) ?>
            </span>
        </div>

        <div class="card-body">
            <?php if ($errores): ?>
                <p class="text-muted">
                    Corrige los filtros para consultar los registros.
                </p>
            <?php elseif (!$asistencias): ?>
                <p class="text-muted mb-0">
                    No se encontraron asistencias con esos criterios.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Cédula</th>
                                <th>Empleado</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Horas trabajadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asistencias as $asistencia): ?>
                                <tr>
                                    <td>
                                        <?= escapar($asistencia['fecha']) ?>
                                    </td>
                                    <td>
                                        <?= escapar($asistencia['cedula']) ?>
                                    </td>
                                    <td>
                                        <?= escapar($asistencia['nombre']) ?>
                                    </td>
                                    <td>
                                        <?= escapar($asistencia['hora_entrada']) ?>
                                    </td>
                                    <td>
                                        <?php if ($asistencia['hora_salida'] !== null): ?>
                                            <?= escapar($asistencia['hora_salida']) ?>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning">
                                                Pendiente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= escapar(
                                            calcularHorasTrabajadas(
                                                $asistencia['hora_entrada'],
                                                $asistencia['hora_salida']
                                            )
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a class="btn btn-secondary mt-3"
       href="<?= escapar(BASE_URL) ?>/admin/index.php">
        Volver al dashboard
    </a>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
