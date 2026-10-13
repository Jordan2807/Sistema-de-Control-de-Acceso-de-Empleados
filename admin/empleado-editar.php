<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

function volverEmpleados(string $mensaje): never
{
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ' . BASE_URL . '/admin/empleados.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id < 1 || !validarTokenCSRF()) {
        http_response_code(400);
        exit('Solicitud inválida. Recarga la página e inténtalo nuevamente.');
    }

    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($cedula === '' || $nombre === '') {
        volverEmpleados('La cédula y el nombre son obligatorios.');
    }

    if (strlen($cedula) > 25 || strlen($nombre) > 150
        || strlen($correo) > 150 || strlen($telefono) > 25) {
        volverEmpleados('Uno de los campos supera la longitud permitida.');
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        volverEmpleados('El correo electrónico no es válido.');
    }

    try {
        $consulta = $pdo->prepare(
            'SELECT id FROM empleados
             WHERE cedula = :cedula AND id <> :id LIMIT 1'
        );
        $consulta->execute([
            ':cedula' => $cedula,
            ':id' => $id
        ]);

        if ($consulta->fetch()) {
            volverEmpleados('Esa cédula ya pertenece a otro empleado.');
        }

        $sql = "UPDATE empleados
                SET cedula = :cedula,
                    nombre = :nombre,
                    correo = :correo,
                    telefono = :telefono
                WHERE id = :id";

        $consulta = $pdo->prepare($sql);
        $consulta->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':correo' => $correo !== '' ? $correo : null,
            ':telefono' => $telefono !== '' ? $telefono : null,
            ':id' => $id
        ]);

        volverEmpleados('Los datos del empleado se actualizaron correctamente.');

    } catch (PDOException $e) {
        error_log('Error al editar empleado: ' . $e->getMessage());

        if ($e->getCode() === '23000') {
            volverEmpleados('La cédula indicada ya está registrada.');
        }

        volverEmpleados('No fue posible actualizar el empleado.');
    }
}

if (!$id || $id < 1) {
    volverEmpleados('El empleado seleccionado no es válido.');
}

$consulta = $pdo->prepare('SELECT * FROM empleados WHERE id = :id');
$consulta->execute([':id' => $id]);
$empleado = $consulta->fetch();

if (!$empleado) {
    volverEmpleados('El empleado solicitado no existe.');
}

$tokenCSRF = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar empleado | Control de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="<?= escapar(BASE_URL) ?>/assets/css/estilos.css"
          rel="stylesheet">
</head>
<body>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h1 class="h4 mb-0">Editar empleado</h1>
                </div>

                <div class="card-body">
                    <form action="<?= escapar(BASE_URL) ?>/admin/empleado-editar.php"
                          method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= escapar($tokenCSRF) ?>">
                        <input type="hidden" name="id"
                               value="<?= (int) $empleado['id'] ?>">

                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input class="form-control" id="cedula" name="cedula"
                                   maxlength="25" required
                                   value="<?= escapar($empleado['cedula']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre completo *
                            </label>
                            <input class="form-control" id="nombre" name="nombre"
                                   maxlength="150" required
                                   value="<?= escapar($empleado['nombre']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control"
                                   id="correo" name="correo" maxlength="150"
                                   value="<?= escapar($empleado['correo'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input class="form-control" id="telefono"
                                   name="telefono" maxlength="25"
                                   value="<?= escapar($empleado['telefono'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Código de acceso</label>
                            <input class="form-control"
                                   value="<?= escapar($empleado['codigo_acceso']) ?>"
                                   disabled>
                            <div class="form-text">
                                El código no cambia al editar los datos.
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">
                            Guardar cambios
                        </button>

                        <a class="btn btn-secondary"
                           href="<?= escapar(BASE_URL) ?>/admin/empleados.php">
                            Cancelar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
