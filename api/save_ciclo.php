<?php
header('Content-Type: application/json');

function responder(int $code, string $msn, array $data = []): never {
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'msn'  => $msn,
        'data' => $data
    ]);
    exit;
}

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, 'Método no permitido. Solo se acepta POST.');
}

// ✅ Leer y validar JSON
$input = file_get_contents('php://input');
$param = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responder(400, 'JSON inválido.');
}

// ✅ Validar parámetros requeridos
$costura = $param['costura'] ?? null;
$ciclo   = (int)($param['ciclo'] ?? 0);
$usuario = $param['usuario'] ?? null;
$nombre = $param['nombre'] ?? null;

if (empty($costura)) {
    responder(422, 'Se requiere el parámetro "costura".');
}

if (empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

// ✅ Si hay ciclo, solo actualiza
if ($ciclo > 0) {
    $sql = "UPDATE ciclo 
            SET tiempo_fin = NOW(), 
                tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio),
                usuario_modifica = '" . $usuario . "'
            WHERE ciclo_id = $ciclo";
    sc_exec_sql($sql);
    /**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */
}

// ✅ Si no hay ciclo, insertar uno nuevo

$insert['costua_id'] = (int)$costura;
$insert['usuario_registra'] = "'" . $usuario . "'";
$insert['usuario_nombre'] = "'" . $nombre . "'";

$insertedId = guardar_ciclo($insert);

/**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */


if ($insertedId !== null) {
    responder(200, 'Ciclo insertado correctamente.', ['ciclo' => $insertedId]);
} else {
    responder(500, 'Error al insertar el ciclo.');
}

// ✅ Función para guardar ciclo
function guardar_ciclo($insert): ?int {
    if (empty($insert)) {
        return null;
    }
    
    $columnas = implode(", ", array_keys($insert));
    $valores = implode(", ", $insert);   

    $sql_insert = "INSERT INTO ciclo ($columnas) VALUES ($valores)";
    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}