<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

iniciarSesion();
exigirAdministrador();

$mensaje = '';
$error = '';

$adminActual = (int) $_SESSION['admin_id'];

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'La solicitud no es válida. Recarga la página.';
    } else {
        $accion = $_POST['accion'] ?? '';

        try {
            if ($accion === 'crear') {
                $nombre = trim($_POST['nombre'] ?? '');
                $usuario = trim($_POST['usuario'] ?? '');
                $password = $_POST['password'] ?? '';

                if ($nombre === '' || $usuario === '') {
                    throw new RuntimeException(
                        'Completa el nombre y el usuario.'
                    );
                }

                if (
                    strlen($nombre) > 120 ||
                    strlen($usuario) > 50 ||
                    !preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)
                ) {
                    throw new RuntimeException(
                        'El nombre o el usuario no tienen un formato válido.'
                    );
                }

                if (strlen($password) < 8) {
                    throw new RuntimeException(
                        'La contraseña debe tener al menos 8 caracteres.'
                    );
                }

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios
                        (nombre, usuario, password, estado)
                    VALUES
                        (:nombre, :usuario, :password, 'Activo')
                ");

                $stmt->execute([
                    'nombre' => $nombre,
                    'usuario' => $usuario,
                    'password' => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    )
                ]);

                $mensaje = 'Administrador registrado correctamente.';
            } elseif ($accion === 'editar') {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $nombre = trim($_POST['nombre'] ?? '');
                $usuario = trim($_POST['usuario'] ?? '');
                $password = $_POST['password'] ?? '';

                if (!$id || $id < 1) {
                    throw new RuntimeException(
                        'El administrador seleccionado no es válido.'
                    );
                }

                if ($nombre === '' || $usuario === '') {
                    throw new RuntimeException(
                        'El nombre y el usuario son obligatorios.'
                    );
                }

                if (
                    strlen($nombre) > 120 ||
                    strlen($usuario) > 50 ||
                    !preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)
                ) {
                    throw new RuntimeException(
                        'El nombre o el usuario no tienen un formato válido.'
                    );
                }

                $stmt = $pdo->prepare("
                    SELECT id
                    FROM usuarios
                    WHERE id = :id
                ");
                $stmt->execute(['id' => $id]);

                if (!$stmt->fetch()) {
                    throw new RuntimeException(
                        'El administrador no existe.'
                    );
                }

                if ($password !== '' && strlen($password) < 8) {
                    throw new RuntimeException(
                        'La nueva contraseña debe tener al menos 8 caracteres.'
                    );
                }

                if ($password !== '') {
                    $stmt = $pdo->prepare("
                        UPDATE usuarios
                        SET nombre = :nombre,
                            usuario = :usuario,
                            password = :password
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        'nombre' => $nombre,
                        'usuario' => $usuario,
                        'password' => password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        ),
                        'id' => $id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE usuarios
                        SET nombre = :nombre,
                            usuario = :usuario
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        'nombre' => $nombre,
                        'usuario' => $usuario,
                        'id' => $id
                    ]);
                }

                if ($id === $adminActual) {
                    $_SESSION['admin_nombre'] = $nombre;
                    $_SESSION['admin_usuario'] = $usuario;
                }

                $mensaje = 'Administrador actualizado correctamente.';
            } elseif ($accion === 'estado') {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id || $id < 1) {
                    throw new RuntimeException(
                        'El administrador seleccionado no es válido.'
                    );
                }

                if ($id === $adminActual) {
                    throw new RuntimeException(
                        'No puedes desactivar tu propia cuenta.'
                    );
                }

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    SELECT id, estado
                    FROM usuarios
                    WHERE id = :id
                    FOR UPDATE
                ");
                $stmt->execute(['id' => $id]);
                $usuarioSeleccionado = $stmt->fetch();

                if (!$usuarioSeleccionado) {
                    throw new RuntimeException(
                        'El administrador no existe.'
                    );
                }

                if ($usuarioSeleccionado['estado'] === 'Activo') {
                    $totalActivos = (int) $pdo->query("
                        SELECT COUNT(*)
                        FROM usuarios
                        WHERE estado = 'Activo'
                    ")->fetchColumn();

                    if ($totalActivos <= 1) {
                        throw new RuntimeException(
                            'No puedes desactivar al último administrador activo.'
                        );
                    }

                    $nuevoEstado = 'Inactivo';
                } else {
                    $nuevoEstado = 'Activo';
                }

                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET estado = :estado
                    WHERE id = :id
                ");

                $stmt->execute([
                    'estado' => $nuevoEstado,
                    'id' => $id
                ]);

                $pdo->commit();

                $mensaje = 'Estado actualizado correctamente.';

                } elseif ($accion === 'eliminar') {
                    $id = filter_var(
                        $_POST['id'] ?? null,
                        FILTER_VALIDATE_INT
                    );

                    if (!$id || $id < 1) {
                        throw new RuntimeException(
                            'El administrador seleccionado no es válido.'
                        );
                    }

                    if ($id === $adminActual) {
                        throw new RuntimeException(
                            'No puedes eliminar tu propia cuenta.'
                        );
                    }

                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("
                        SELECT id, estado
                        FROM usuarios
                        WHERE id = :id
                        FOR UPDATE
                    ");
                    $stmt->execute(['id' => $id]);
                    $usuarioEliminar = $stmt->fetch();

                    if (!$usuarioEliminar) {
                        throw new RuntimeException(
                            'El administrador no existe.'
                        );
                    }

                    if ($usuarioEliminar['estado'] === 'Activo') {
                        $totalActivos = (int) $pdo->query("
                            SELECT COUNT(*)
                            FROM usuarios
                            WHERE estado = 'Activo'
                        ")->fetchColumn();

                        if ($totalActivos <= 1) {
                            throw new RuntimeException(
                                'No puedes eliminar al último administrador activo.'
                            );
                        }
                    }

                    $stmt = $pdo->prepare("
                        DELETE FROM usuarios
                        WHERE id = :id
                    ");
                    $stmt->execute(['id' => $id]);

                    $pdo->commit();

                    $mensaje = 'Administrador eliminado correctamente.';

                } else {
                    throw new RuntimeException(
                        'La operación solicitada no es válida.'
                    );
                }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($e->getCode() === '23000') {
                $error = 'Ese nombre de usuario ya está registrado.';
            } else {
                $error = 'Ocurrió un error al procesar la operación.';
            }
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Obtener administradores
$stmt = $pdo->query("
    SELECT id, nombre, usuario, estado, fecha_creacion
    FROM usuarios
    ORDER BY id DESC
");
$administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tokenCSRF = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrar usuarios</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success">
    <div class="container">
        <a
            class="navbar-brand"
            href="<?= escapar(BASE_URL) ?>/admin/index.php"
        >
            Control de asistencia
        </a>

        <a
            class="btn btn-outline-light btn-sm"
            href="<?= escapar(BASE_URL) ?>/admin/index.php"
        >
            Volver al panel
        </a>
    </div>
</nav>

<div class="container py-4">

    <h1 class="h3 mb-4">Administración de usuarios</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-success">
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            Registrar administrador
        </div>

        <div class="card-body">
            <form method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapar($tokenCSRF) ?>"
                >
                <input type="hidden" name="accion" value="crear">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            Nombre completo
                        </label>
                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            maxlength="120"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Nombre de usuario
                        </label>
                        <input
                            type="text"
                            name="usuario"
                            class="form-control"
                            maxlength="50"
                            pattern="[a-zA-Z0-9_.-]+"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Contraseña
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </div>

                <button class="btn btn-success mt-3" type="submit">
                    Registrar administrador
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            Administradores registrados
        </div>

        <div class="card-body">
            <?php if (empty($administradores)): ?>
                <p class="text-muted mb-0">
                    No hay administradores registrados.
                </p>
            <?php else: ?>

                <?php foreach ($administradores as $admin): ?>
                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between
                                    align-items-center mb-3">
                            <div>
                                <strong>
                                    <?= escapar($admin['nombre']) ?>
                                </strong>

                                <?php if ((int) $admin['id'] === $adminActual): ?>
                                    <span class="badge text-bg-primary">
                                        Tu cuenta
                                    </span>
                                <?php endif; ?>

                                <?php if ($admin['estado'] === 'Activo'): ?>
                                    <span class="badge text-bg-success">
                                        Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">
                                        Inactivo
                                    </span>
                                <?php endif; ?>

                                <div class="small text-muted">
                                    Registrado:
                                    <?= escapar($admin['fecha_creacion']) ?>
                                </div>
                            </div>
                        </div>

                        <form method="POST" class="mb-3">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapar($tokenCSRF) ?>"
                            >
                            <input type="hidden" name="accion" value="editar">
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $admin['id'] ?>"
                            >

                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Nombre
                                    </label>
                                    <input
                                        type="text"
                                        name="nombre"
                                        class="form-control"
                                        maxlength="120"
                                        required
                                        value="<?= escapar($admin['nombre']) ?>"
                                    >
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">
                                        Usuario
                                    </label>
                                    <input
                                        type="text"
                                        name="usuario"
                                        class="form-control"
                                        maxlength="50"
                                        pattern="[a-zA-Z0-9_.-]+"
                                        required
                                        value="<?= escapar($admin['usuario']) ?>"
                                    >
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">
                                        Nueva contraseña
                                    </label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        minlength="8"
                                        autocomplete="new-password"
                                        placeholder="Dejar vacío para conservarla"
                                    >
                                </div>
                            </div>

                            <button
                                class="btn btn-primary btn-sm mt-3"
                                type="submit"
                            >
                                Guardar cambios
                            </button>
                        </form>

                        <form method="POST">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= escapar($tokenCSRF) ?>"
                            >
                            <input type="hidden" name="accion" value="estado">
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $admin['id'] ?>"
                            >

                            <?php if ((int) $admin['id'] !== $adminActual): ?>
                                <?php if ($admin['estado'] === 'Activo'): ?>
                                    <button
                                        class="btn btn-outline-danger btn-sm"
                                        type="submit"
                                        onclick="return confirm(
                                            '¿Deseas desactivar este administrador?'
                                        )"
                                    >
                                        Desactivar cuenta
                                    </button>
                                <?php else: ?>
                                    <button
                                        class="btn btn-outline-success btn-sm"
                                        type="submit"
                                    >
                                        Activar cuenta
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="small text-muted">
                                    No puedes desactivar tu propia cuenta.
                                </span>
                            <?php endif; ?>
                        </form>
                        <?php if ((int) $admin['id'] !== $adminActual): ?>
                            <form method="POST" class="mt-2"
                                onsubmit="return confirm(
                                    '¿Seguro que deseas eliminar permanentemente este administrador? Esta acción no se puede deshacer.'
                                );">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= escapar($tokenCSRF) ?>"
                                >

                                <input type="hidden" name="accion" value="eliminar">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $admin['id'] ?>"
                                >

                                <button type="submit" class="btn btn-danger btn-sm">
                                    Eliminar administrador
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>