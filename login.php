<?php
declare(strict_types=1);


require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';

$totalAdministradores = (int) $pdo
    ->query("SELECT COUNT(*) FROM usuarios")
    ->fetchColumn();

$permitirRegistroInicial = $totalAdministradores === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF()) {
        http_response_code(403);
        exit('Solicitud inválida. Recarga la página e inténtalo nuevamente.');
    }

    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        $error = 'Ingresa tu usuario y contraseña.';
    } else {
        $sql = 'SELECT id, nombre, usuario, password, estado
                FROM usuarios
                WHERE usuario = :usuario
                LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario' => $usuario]);

        $admin = $stmt->fetch();

        if (
            $admin &&
            $admin['estado'] === 'Activo' &&
            password_verify($password, $admin['password'])
        ) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_usuario'] = $admin['usuario'];

            // Renovar el token CSRF después del login.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>

<?php if (($_GET['registro'] ?? '') === 'exitoso'): ?>
    <div class="alert alert-success">
        Administrador creado correctamente.
        Ahora puedes iniciar sesión.
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="<?= escapar(BASE_URL) ?>/assets/css/estilos.css"
          rel="stylesheet">
</head>
<body class="login-page">
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="card login-card border-0 shadow">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="login-icon">CA</div>
                <h1 class="h3 mt-3">Control de Asistencia</h1>
                <p class="text-secondary">
                    Acceso para administradores
                </p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= escapar($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token"
                       value="<?= escapar(generarTokenCSRF()) ?>">

                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario"
                           name="usuario" maxlength="50"
                           autocomplete="username" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Contraseña
                    </label>
                    <input type="password" class="form-control"
                           id="password" name="password"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Iniciar sesión
                </button>

                <?php if ($permitirRegistroInicial): ?>
                    <div class="text-center mt-3">
                        <p class="mb-2">
                            Todavía no hay administradores registrados.
                        </p>

                        <a
                            href="<?= escapar(BASE_URL) ?>/crear-admin-inicial.php"
                            class="btn btn-outline-success w-100"
                        >
                            Crear primer administrador
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="text-center mt-4">
                <a href="<?= escapar(BASE_URL) ?>/index.php">
                    Volver a la pantalla pública
                </a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
