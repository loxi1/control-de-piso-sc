<?php
session_start();
$ruta_util = sc_url_library("prj", "mantenimiento_control_piso", "php/util.php");
$ruta_acce = sc_url_library("prj", "mantenimiento_control_piso", "php/accesos.php");
$ruta_ingr = sc_url_library("prj", "mantenimiento_control_piso", "php/ingreso.php");

require_once($ruta_util);
require_once($ruta_acce);
require_once($ruta_ingr);

setApiHeaders();

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$codigo = $_SESSION["usr_login"] ?? null;
$empresa = $_SESSION['empresa_id'] ?? null;

if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

// ✅ Iniciar conexión
$conn = DB::getConnection();

$whereData = blindValueWhereCondiciones(['i.codigo_operario' => $codigo]);
$rta_existe = validarIngresoHorario($whereData, $conn);

$sqTurno['empresa_id'] = $empresa;

if (!empty($validarIngreso['turno_id'])) {
    if($validarIngreso['estado'] == 1) {
        // ✅ Armar respuesta        
        $rta[] = [
                'id' => $validarIngreso['turno_id'],
                'turno'  => htmlspecialchars($validarIngreso['elturno'], ENT_QUOTES, 'UTF-8')
            ];

        // ✅ Enviar respuesta JSON
        responder(200, 'Turnos obtenidos correctamente.', $rta);
    }
    $sqTurno['turno_id !='] = $validarIngreso['turno_id'];
}

print_r($sqlTurno);
$whereData = blindValueWhereCondiciones($sqlTurno);
if (empty($whereData)) {
    responder(422, 'No se encontraron datos para el turno proporcionado.');
}

$allTurno = getTurno($whereData, $conn);

if (!isset($allTurno) || !is_array($allTurno)) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count($allTurno) === 0) {
    responder(404, 'No se encontraron turnos.');
}

// ✅ Armar respuesta
$rta = [];
foreach ($allTurno as $row) {
    $rta[] = [
        'id' => $row['turno_id'],
        'turno'  => htmlspecialchars($row['nombre_turno'], ENT_QUOTES, 'UTF-8')
    ];    
}

// ✅ Enviar respuesta JSON
responder(200, 'Turnos obtenidos correctamente.', $rta);