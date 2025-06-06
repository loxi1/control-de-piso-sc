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
$evento = $param['evento'] ?? null;
$ciclo = $param['ciclo'] ?? null;
$usuario = $param['usuario'] ?? null;
$nombre = $param['nombre'] ?? null;
$idingreso = intval($param['idingreso'] ?? 0);

if($idingreso <= 0) {
    responder(422, 'Se requiere el parámetro "ingreso".');
}

if (empty($evento)) {
    responder(422, 'Se requiere el parámetro "evento".');
}

if(empty($ciclo)) {
    responder(422, 'Se requiere el parámetro "ciclo".');
}

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

// ✅ Si hay evento, solo actualiza
$sql = "UPDATE evento_normal SET tiempo_fin = NOW(), tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio), usuario_modifica='$usuario' WHERE evento_normal_id = $evento";
sc_exec_sql($sql);

$sql = "UPDATE ciclo SET tiempo_fin = NOW(), tiempo_trascurrido = TIMEDIFF(NOW(), tiempo_inicio), usuario_modifica='$usuario' WHERE ciclo_id = $ciclo";
sc_exec_sql($sql);

if(!empty($nombre) && !empty($costura)) {
    $insertciclo["usuario_nombre"] = $nombre;
    $insertciclo["usuario_registra"] = $usuario;
    $insertciclo['costua_id'] = $costura;
    $insertciclo['ingreso_id'] = $idingreso;

    $sql = "INSERT INTO ciclo (costua_id, ingreso_id, usuario_nombre, usuario_registra) VALUES (".$insertciclo['costua_id'].", $idingreso, '".$insertciclo['usuario_nombre']."', '".$insertciclo['usuario_registra']."')";    
    sc_exec_sql($sql);
}

/**ACTUALIZAR EFICIENCIA, META Y REPROCESO X COSTURA  */

responder(200, 'Evento Actualizado.', ['evento' => $evento]);