<?php
require_once('../_lib/util/session_check.php');
header('Content-Type: application/json');

/**
 * Envía una respuesta JSON con el código HTTP y mensaje adecuado.
 */
function responder(int $code, string $msn, array $data = []): never {
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'msn'  => $msn,
        'data' => $data
    ]);
    exit;
}

// Asegurar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido. Solo se acepta POST.');
}

// Leer datos: intenta primero con POST normal
$id = $_POST['id'] ?? null;
$maquina = $_POST['maquina'] ?? null;

// Si vienen vacíos, intenta leer desde JSON
if (empty($id) || empty($maquina)) {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $id = $id ?: ($input['id'] ?? null);
        $maquina = $maquina ?: ($input['maquina'] ?? null);
    }
}

// Validar datos requeridos
if (empty($id)) {
    responder(422, 'Se requiere el parámetro "ID dispositivo".');
}

if (empty($maquina)) {
    responder(422, 'Se requiere el parámetro "Maquina".');
}

// Sanitizar parámetros (solo números y letras, para evitar SQL injection simple)
$id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
$maquina = (int)$maquina;

// Desasociar previamente la MAC en el área 51
$sqlLimpiar = "
    UPDATE maquina
    SET mac = NULL
    WHERE area_ubicacion_id = 51 AND mac = '$id'
";
sc_exec_sql($sqlLimpiar);

// Ejecutar actualización
$sqlAsociar  = "UPDATE maquina SET mac = '$id' WHERE maquina_id = $maquina";
sc_exec_sql($sqlAsociar );

// ✅ Enviar respuesta de éxito
responder(200, 'Máquina actualizada correctamente.', []);
