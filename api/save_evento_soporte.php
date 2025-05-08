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
$soporte = $param['soporteid'] ?? null;
$problema = $param['problema'] ?? null;
$usuario = $param['usuario'] ?? null;
$tiatencion = !empty($param['tiatencion']) ? $param['tiatencion'] : null;
$tfatencion = !empty($param['tfatencion']) ? $param['tfatencion'] : null;
$cicloid = !empty($param['cicloid']) ? $param['cicloid'] : null;

if (empty($soporte)) {
    responder(422, 'Se requiere el parámetro "soporte".');
}

$updsoporte = [];
$soporte = (int)$soporte;

if(!empty($problema)) {
    $updsoporte["problema_id"] = (int)$problema;
}

if(!empty($tiatencion)) {
    $updsoporte["tiempo_inicio_atencion"] = "NOW()";
    $updsoporte["  "] = "NOW()";
}

if(!empty($usuario)) {
    $updsoporte["usuario_modifica"] = $usuario;
}

if(!empty($tfatencion)) {
    $updsoporte["tiempo_fin"] = "NOW()";
    $updsoporte["tiempo_fin_atencion"] = "NOW()";
    $updsoporte["tiempo_transcurrido"] = "TIMEDIFF(NOW(), tiempo_inicio)";
    $updsoporte["tiempo_transcurrido_atencion"] = "TIMEDIFF(NOW(), tiempo_inicio_atencion)";
}

// ✅ Actualizar evento soporte
$soporteid = update_soporte($updsoporte, $soporte);

// ✅ Actualizar ciclo si es necesario
if(!empty($cicloid) && !empty($tfatencion) && !empty($usuario)) {
    $sql = "UPDATE ciclo SET tiempo_fin = NOW(), tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio), usuario_modifica='$usuario' WHERE ciclo_id = $cicloid";
    print_r($sql);
    sc_exec_sql($sql);
}

if ($soporteid !== null) {
    responder(200, 'Evento insertado correctamente.', ['soporte' => $soporteid]);
} else {
    responder(500, 'Error al insertar el Evento.');
}

// ✅ Función para guardar soporteid
function update_soporte($updsoporte, $soporte): ?int {
    if (empty($updsoporte)) {
        return null;
    }

    $set = [];
    foreach ($updsoporte as $campo => $valor) {
        // Detectar si es una función SQL (e.g., NOW(), TIMEDIFF(...))
        $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);


        if (is_numeric($valor) || $esFuncionSQL) {
            $set[] = "$campo = $valor";
        } else {
            $valorSanitizado = addslashes($valor);
            $set[] = "$campo = '$valorSanitizado'";
        }
    }

    $setClause = implode(", ", $set);
    $upd = "UPDATE evento_soporte SET $setClause WHERE evento_soporte_id = $soporte";
    sc_exec_sql($upd);

    return $soporte;
}