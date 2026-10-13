<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();

$mensaje = '';
$error = '';

// Verificar si ya existe algún administrador.
// No importa si su estado es Activo o Inactivo.
function existeAdministrador(PDO $pdo): bool
{
    return (int) $pdo
        ->query("SELECT COUNT(*) FROM usuarios")
        ->fetchColumn() > 0;
}

// Si ya existe un usuario administrador, bloquear esta página.
if (existeAdministrador($pdo)) {
    http_response_code(403);
    exit(
        'La creación inicial está deshabilitada. ' .
        'Ya existe un administrador.'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'La solicitud expiró o no es válida. Recarga la página.';
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';

        if ($nombre === '' || $usuario === '') {
            $error = 'Completa el nombre y el nombre de usuario.';
        } elseif (
            strlen($nombre) > 120 ||
            strlen($usuario) > 50 ||
            !preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)
        ) {
            $error = 'Revisa el nombre y el formato del usuario.';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($password !== $confirmarPassword) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $bloqueoAdquirido = false;

            try {
                // Evitar dos registros iniciales simultáneos.
                $bloqueoAdquirido = (int) $pdo
                    ->query(
                        "SELECT GET_LOCK(
                            'control_asistencia_admin_inicial',
                            5
                        )"
                    )
                    ->fetchColumn() === 1;

                if (!$bloqueoAdquirido) {
                    throw new RuntimeException(
                        'No se pudo verificar la creación inicial. Intenta de nuevo.'
                    );
                }

                // Comprobar nuevamente bajo el bloqueo.
                if (existeAdministrador($pdo)) {
                    http_response_code(403);
                    exit(
                        'La creación inicial está deshabilitada. ' .
                        'Ya existe un administrador.'
                    );
                }

                $sql = "
                    INSERT INTO usuarios
                        (nombre, usuario, password, estado)
                    VALUES
                        (:nombre, :usuario, :password, 'Activo')
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nombre' => $nombre,
                    'usuario' => $usuario,
                    'password' => password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    )
                ]);

                // No iniciar sesión automáticamente.
                header('Location: login.php?registro=exitoso');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $error = 'Ese nombre de usuario ya está registrado.';
                } else {
                    $error = 'Ocurrió un error al crear el administrador.';
                }
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            } finally {
                if ($bloqueoAdquirido) {
                    $pdo->query(
                        "SELECT RELEASE_LOCK(
                            'control_asistencia_admin_inicial'
                        )"
                    );
                }
            }
        }
    }
}

$tokenCSRF = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear primer administrador</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h4 text-center mb-3">
                        Crear primer administrador
                    </h1>

                    <p class="text-muted text-center">
                        Este formulario solo está disponible
                        mientras no exista ningún administrador.
                    </p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger">
                            <?= escapar($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapar($tokenCSRF) ?>"
                        >

                        <div class="mb-3">
                            <label class="form-label">
                                Nombre completo
                            </label>
                            <input
                                type="text"
                                name="nombre"
                                class="form-control"
                                maxlength="120"
                                required
                                value="<?= escapar($_POST['nombre'] ?? '') ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Nombre de usuario
                            </label>
                            <input
                                type="text"
                                name="usuario"
                                class="form-control"
                                maxlength="50"
                                pattern="[a-zA-Z0-9_.-]+"
                                autocomplete="username"
                                required
                                value="<?= escapar($_POST['usuario'] ?? '') ?>"
                            >
                            <div class="form-text">
                                Usa letras, números, puntos, guiones
                                o guiones bajos.
                            </div>
                        </div>

                        <div class="mb-3">
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

                        <div class="mb-3">
                            <label class="form-label">
                                Confirmar contraseña
                            </label>
                            <input
                                type="password"
                                name="confirmar_password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100"
                        >
                            Crear administrador
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="login.php">
                            Volver al inicio de sesión
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>