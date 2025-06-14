<?php
require_once('../_lib/util/util.php');
require_once('../_lib/util/permiso.php');
require_once('../_lib/util/ingreso.php');
header('Content-Type: application/json');

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

$id = intval($param['id'] ?? null);

// Cuenta con permiso 1:No 2:Si
$con_permiso = intval($param['con_permiso'] ?? 1);

//Tipo 1 Ingreso, 2 Permiso, 3 Salida
$tipo = intval($param['tipo'] ?? 1);

// Tipo de permiso 
//1: Ingreso puntual, 2 Ingreso tarde,
//3: Permiso con retorno, volvere a trabajar, 4: Permiso sin retorno, 5: Permiso refriegerio, 6: Salida
$tipo_permiso = intval($param['tipo_permiso'] ?? 1);

//Codigo
$codigo = $param['codigo'] ?? null;

if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

if (empty($id)) {
    responder(422, 'Se requiere el parámetro "id ingreso".');
}

$param = [];
$param['id'] = $id;
$ingreso = listarTablaSimple($param);
$horaSalida = $ingreso[0]['horario_salida'] ?? null;

if (empty($horaSalida)) {
    responder(422, 'No existe ingreso".');
}

$param = [];
$param['ingreso_id'] = $id;
$param['tipo'] = 'Ingreso';
$permiso = listarTablaSimple($param);
$idpermiso = intval($permiso[0]['id'] ?? 0);
$conpermiso = $permiso[0]['con_permiso'] ?? null;

if (empty($idpermiso) || empty($conpermiso)) {
    responder(422, 'No existe ingreso".');
}

$fecha_permiso =  ($conpermiso == "Si tiene permiso") ? "now()" : $horaSalida;

$saveparam["codigo"] = $codigo;
$saveparam["fecha_permiso"] = $fecha_permiso;
$saveparam["ingreso_id"] = $id;
$saveparam["con_permiso"] = $con_permiso;
$saveparam["tipo"] = $tipo;
$saveparam["tipo_permiso"] = $tipo_permiso;
$saveparam["estado"] = 2;

$tabla = "permiso";

// 💾 Función para guardar ciclo
$insertedId = save_permiso($tabla, $saveparam);

if ($insertedId !== null) {
    responder(200, 'Ingreso permiso correctamente.', ['permiso' => $insertedId]);
} else {
    responder(500, 'Error al ingresar permiso.');
}
