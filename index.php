<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();

$mensaje = $_SESSION['marcacion_mensaje'] ?? '';
$tipoMensaje = $_SESSION['marcacion_tipo'] ?? 'info';

unset($_SESSION['marcacion_mensaje'], $_SESSION['marcacion_tipo']);

$tokenCSRF = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Marcar asistencia | Control de Asistencia</title>

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

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand"
           href="<?= escapar(BASE_URL) ?>/index.php">
            Control de Asistencia
        </a>

        <a class="btn btn-outline-light btn-sm"
           href="<?= escapar(BASE_URL) ?>/login.php">
            Administración
        </a>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">

            <div class="text-center mb-4">
                <h1 class="h2">Control de asistencia</h1>
                <p class="text-muted">
                    Ingresa tus datos para registrar tu entrada o salida.
                </p>
            </div>

            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= escapar($tipoMensaje) ?> alert-dismissible fade show"
                     role="alert">
                    <?= escapar($mensaje) ?>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h2 class="h5 mb-0">Marcar asistencia</h2>
                </div>

                <div class="card-body p-4">
                    <form action="<?= escapar(BASE_URL) ?>/marcar-asistencia.php"
                          method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= escapar($tokenCSRF) ?>">

                        <div class="mb-3">
                            <label for="cedula" class="form-label">
                                Número de cédula
                            </label>
                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="cedula"
                                name="cedula"
                                maxlength="25"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label for="codigo" class="form-label">
                                Código de acceso
                            </label>
                            <input
                                type="password"
                                class="form-control form-control-lg"
                                id="codigo"
                                name="codigo"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                minlength="6"
                                maxlength="6"
                                autocomplete="off"
                                required
                            >
                            <div class="form-text">
                                Ingresa tu código de seis dígitos.
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-primary btn-lg w-100">
                            Registrar entrada / salida
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-muted mt-3 small">
                Utiliza tu propia cédula y código de acceso.
            </p>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
