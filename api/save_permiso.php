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

$codigo = $param['codigo'] ?? null;
$id = intval($param['id'] ?? null);

// Cuenta con permiso 1:No 2:Si
$con_permiso = intval($param['con_permiso'] ?? 1);

//Tipo 1 Ingreso, 2 Permiso, 3 Salida
$tipo = intval($param['tipo'] ?? 1);

// Tipo de permiso 
//1: Ingreso puntual, 2 Ingreso tarde,
//3: Permiso con retorno, volvere a trabajar, 4: Permiso sin retorno, 5: Permiso refriegerio, 6: Salida
$tipo_permiso = intval($param['tipo_permiso'] ?? 1);

//Fecha de permiso
$fecha_permiso = $param['fecha_permiso'] ?? null;

if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

if (empty($id)) {
    responder(422, 'Se requiere el parámetro "id ingreso".');
}

$vafs = "now()";

if($tipo == 1) {
    // Si es ingreso, no se requiere fecha_permiso
    if(!empty($fecha_permiso)) {
        $vafs = "'$fecha_permiso'";
    }
    //Validar si ya existe un permiso para esa fecha con el ingreso_id y para el mismo operario
    $sqlexiste = "select id from permiso where codigo='$codigo' and ingreso_id=$id and tipo=$tipo";
    sc_lookup(rs_existe, $sqlexiste);

    if (!empty({rs_existe}[0][0])) {
        $sqlupdate = "UPDATE permiso SET fecha_modificacion=now(), con_permiso=$con_permiso, fecha_permiso=$vafs, tipo_permiso=$tipo_permiso WHERE id=".{rs_existe}[0][0];
        sc_exec_sql($sqlupdate);
        responder(200, 'Ya existe un permiso para esa fecha y operario.',['permiso' => {rs_existe}[0][0]]);
    }
}

$cols = "codigo, fecha_permiso, ingreso_id, con_permiso, tipo, tipo_permiso";

$vals = "'$codigo',$vafs, $id, $con_permiso, $tipo, $tipo_permiso";
$tabla = "permiso";

$sql = "INSERT INTO $tabla ($cols) VALUES ($vals)";

$insertedId = guardar_ingreso($sql);

if ($insertedId !== null) {
    responder(200, 'Ingreso permiso correctamente.', ['permiso' => $insertedId]);
} else {
    responder(500, 'Error al ingresar permiso.');
}

// 💾 Función para guardar ciclo
function guardar_ingreso($sql_insert): ?int {
    if (empty($sql_insert)) {
        return null;
    }

    sc_exec_sql($sql_insert);

    $sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (isset({rs_id[0][0]})) {
        return (int){rs_id[0][0]};
    }
    return null;
}