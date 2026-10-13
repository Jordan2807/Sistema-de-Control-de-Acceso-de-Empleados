<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

$tokenCSRF = generarTokenCSRF();

$sql = "SELECT id, cedula, nombre, correo, telefono,
               codigo_acceso, estado, fecha_registro
        FROM empleados
        ORDER BY id DESC";

$empleados = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Empleados | Control de Asistencia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="<?= escapar(BASE_URL) ?>/assets/css/estilos.css"
          rel="stylesheet">
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
        <h1 class="h3">Gestión de empleados</h1>
        <p class="text-muted">
            Registra, consulta, edita y administra los empleados.
        </p>
    </div>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= escapar($mensaje) ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Registrar empleado</h2>
        </div>

        <div class="card-body">
            <form action="<?= escapar(BASE_URL) ?>/admin/empleado-guardar.php"
                  method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?= escapar($tokenCSRF) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="cedula" class="form-label">Cédula *</label>
                        <input type="text" class="form-control"
                               id="cedula" name="cedula"
                               maxlength="25" required>
                    </div>

                    <div class="col-md-8">
                        <label for="nombre" class="form-label">
                            Nombre completo *
                        </label>
                        <input type="text" class="form-control"
                               id="nombre" name="nombre"
                               maxlength="150" required>
                    </div>

                    <div class="col-md-6">
                        <label for="correo" class="form-label">Correo</label>
                        <input type="email" class="form-control"
                               id="correo" name="correo" maxlength="150">
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control"
                               id="telefono" name="telefono" maxlength="25">
                    </div>
                </div>

                <button class="btn btn-primary mt-3" type="submit">
                    Registrar empleado
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">
                Empleados registrados (<?= count($empleados) ?>)
            </h2>
        </div>

        <div class="card-body">
            <?php if (!$empleados): ?>
                <p class="text-muted mb-0">
                    Todavía no hay empleados registrados.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?= escapar($empleado['cedula']) ?></td>
                                <td><?= escapar($empleado['nombre']) ?></td>
                                <td><?= escapar($empleado['correo'] ?? '') ?></td>
                                <td><?= escapar($empleado['telefono'] ?? '') ?></td>
                                <td><?= escapar($empleado['codigo_acceso']) ?></td>
                                <td>
                                    <?php if ($empleado['estado'] === 'Activo'): ?>
                                        <span class="badge text-bg-success">
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= escapar($empleado['fecha_registro']) ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-warning btn-sm"
                                           href="<?= escapar(BASE_URL) ?>/admin/empleado-editar.php?id=<?= (int) $empleado['id'] ?>">
                                            Editar
                                        </a>

                                        <form action="<?= escapar(BASE_URL) ?>/admin/empleado-estado.php"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas cambiar el estado de este empleado?');">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= escapar($tokenCSRF) ?>">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $empleado['id'] ?>">
                                            <button class="btn btn-sm <?= $empleado['estado'] === 'Activo' ? 'btn-danger' : 'btn-success' ?>"
                                                    type="submit">
                                                <?= $empleado['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
