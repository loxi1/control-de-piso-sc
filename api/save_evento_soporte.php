<?php
require_once('../_lib/util/session_check.php');
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
$tipo = $param['tipo'] ?? 0;
$idingreso = intval($param['idingreso'] ?? 0);

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
    $updsoporte["tiempo_inicio_atencion_mec"] = "NOW()";
    $updsoporte["tiempo_fin_aceptacion"] = "NOW()";
    $updsoporte["estado"] = "4";
}

if(!empty($usuario)) {
    $updsoporte["usuario_modifica"] = $usuario;
}

if(!empty($tfatencion)) {
    $updsoporte["tiempo_fin"] = "NOW()";
    $updsoporte["tiempo_fin_atencion"] = "NOW()";
    $updsoporte["tiempo_transcurrido"] = "TIMEDIFF(NOW(), tiempo_inicio)";
    $updsoporte["tiempo_transcurrido_atencion"] = "TIMEDIFF(NOW(), tiempo_inicio_atencion)";
    $updsoporte["tiempo_fin_atencion_mec"] = "NOW()";
    $updsoporte["estado"] = "6";

    
    $costura = !empty($param['costuraid']) ? $param['costuraid'] : null;
    $nombre = !empty($param['nombre']) ? $param['nombre'] : null;
    
    if(!empty($nombre) && !empty($costura)) {
        $insertciclo["usuario_nombre"] = $nombre;
        $insertciclo["usuario_registra"] = $usuario;
        $insertciclo['costua_id'] = $costura;
    }

    /**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */
}

// ✅ Actualizar evento soporte
$soporteid = update_soporte($updsoporte, $soporte);

// ✅ Actualizar ciclo si es necesario
if(!empty($cicloid) && !empty($tfatencion) && !empty($usuario) && !empty($tipo)) {
    $settiempo = (int)$tipo === 51 ? "'00:45:00'" : "tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio)";
    $sql = "UPDATE ciclo SET tiempo_fin = NOW(), tiempo_trascurrido = $settiempo, usuario_modifica='$usuario' WHERE ciclo_id = $cicloid";
    sc_exec_sql($sql);
}

if(!empty($insertciclo)) {
    $sql = "INSERT INTO ciclo (costua_id, ingreso_id, tiempo_inicio, usuario_nombre, usuario_registra) VALUES (".$insertciclo['costua_id'].",$idingreso, NOW(), '".$insertciclo['usuario_nombre']."', '".$insertciclo['usuario_registra']."')";    
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