
<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

exigirAdministrador();

// Obtener filtros
$empleadoFiltro = trim($_GET['empleado'] ?? '');
$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

// Validar formato de fechas
$validarFecha = static function (string $fecha): bool {
    if ($fecha === '') {
        return true;
    }

    $objetoFecha = DateTime::createFromFormat('!Y-m-d', $fecha);

    return $objetoFecha !== false
        && $objetoFecha->format('Y-m-d') === $fecha;
};

if (
    !$validarFecha($fechaDesde)
    || !$validarFecha($fechaHasta)
    || (
        $fechaDesde !== ''
        && $fechaHasta !== ''
        && $fechaDesde > $fechaHasta
    )
) {
    http_response_code(400);
    exit('Los filtros de fecha no son válidos.');
}

// Construir consulta
$sql = "
    SELECT
        a.fecha,
        e.cedula,
        e.nombre,
        a.hora_entrada,
        a.hora_salida
    FROM asistencias a
    INNER JOIN empleados e ON e.id = a.empleado_id
    WHERE 1 = 1
";

$parametros = [];

if ($empleadoFiltro !== '') {
    $sql .= " AND (
        e.nombre LIKE :nombre
        OR e.cedula LIKE :cedula
    )";

    $parametros['nombre'] = '%' . $empleadoFiltro . '%';
    $parametros['cedula'] = '%' . $empleadoFiltro . '%';
}

if ($fechaDesde !== '') {
    $sql .= " AND a.fecha >= :desde";
    $parametros['desde'] = $fechaDesde;
}

if ($fechaHasta !== '') {
    $sql .= " AND a.fecha <= :hasta";
    $parametros['hasta'] = $fechaHasta;
}

$sql .= " ORDER BY a.fecha DESC, e.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);

// Evitar que Excel interprete ciertos datos como fórmulas
$protegerCSV = static function ($valor): string {
    $valor = (string) ($valor ?? '');

    if (preg_match('/^[=+\-@\t\r]/', $valor)) {
        return "'" . $valor;
    }

    return $valor;
};

// Preparar descarga
$nombreArchivo = 'historial_asistencias_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo . '"'
);
header('Pragma: no-cache');
header('Expires: 0');

// BOM para facilitar la lectura de tildes en Excel
echo "\xEF\xBB\xBF";

$salida = fopen('php://output', 'w');

// Encabezados del reporte
fputcsv(
    $salida,
    [
        'Fecha',
        'Cedula',
        'Empleado',
        'Hora de entrada',
        'Hora de salida',
        'Horas trabajadas'
    ],
    ';',
    '"',
    ''
);

// Exportar registros
while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $horasTrabajadas = 'Pendiente';

    if ($fila['hora_salida'] !== null) {
        $entrada = strtotime($fila['hora_entrada']);
        $salidaHora = strtotime($fila['hora_salida']);

        if ($entrada !== false && $salidaHora !== false) {
            $diferencia = $salidaHora - $entrada;

            if ($diferencia >= 0) {
                $horas = intdiv($diferencia, 3600);
                $minutos = intdiv($diferencia % 3600, 60);

                $horasTrabajadas = sprintf(
                    '%02d:%02d',
                    $horas,
                    $minutos
                );
            } else {
                $horasTrabajadas = 'No disponible';
            }
        }
    }

    fputcsv(
        $salida,
        [
            $fila['fecha'],
            $protegerCSV($fila['cedula']),
            $protegerCSV($fila['nombre']),
            $fila['hora_entrada'],
            $fila['hora_salida'] ?? 'Pendiente',
            $horasTrabajadas
        ],
        ';',
        '"',
        ''
    );
}

fclose($salida);
exit;

