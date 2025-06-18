<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/util.php');
require_once('../_lib/util/accesos.php');
require_once('../_lib/util/ingreso.php');
require_once('../_lib/util/eficiencia.php');
require_once('../_lib/util/reproceso.php');

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
// ✅ Validaciones iniciales
$id = intval($param['id'] ?? null);
$con_permiso = intval($param['con_permiso'] ?? 1);  // Cuenta con permiso 1:No 2:Si
$tipo = intval($param['tipo'] ?? 1);    //Tipo 1 Ingreso, 2 Permiso, 3 Salida
$codigo = $param['codigo'] ?? null;

// Tipo de permiso 
//1: Ingreso puntual, 2 Ingreso tarde,
//3: Permiso con retorno, volvere a trabajar, 4: Permiso sin retorno, 5: Permiso refriegerio, 6: Salida
$tipo_permiso = intval($param['tipo_permiso'] ?? 1);

if (!$id) responder(422, 'Se requiere el parámetro "id ingreso".');

if (empty($codigo)) responder(422, 'Se requiere el parámetro "codigo".');

// ✅ Iniciar conexión
$conn = DB::getConnection();

// 🔎 Obtener datos del ingreso
$ingreso = listarTablaSimple("ingreso", ['id' => $id], $conn);
$horaSalida = $ingreso[0]['horario_salida'] ?? null;

if (empty($horaSalida)) {
    responder(422, 'No existe ingreso".');
    DB::closeConnection();
}

// 🔎 Buscar permiso previo tipo Ingreso
$permiso = listarTablaSimple("permiso", ['ingreso_id' => $id, 'tipo' => 'Ingreso'], $conn);
$idpermiso = intval($permiso[0]['id'] ?? 0);
$conpermiso = $permiso[0]['con_permiso'] ?? null;

if (!$idpermiso || !$conpermiso) {
    responder(422, 'No existe ingreso".');
    DB::closeConnection();
}

// ⌛ Fecha de permiso
$fecha_permiso =  ($conpermiso == "Si tiene permiso") ? "now()" : $horaSalida;
$estado = ($tipo == 2) ? 1 : 2;

$idUpPermiso = 0;

//Verificar si existe un tipo(3) Salida o un $tipo_permiso(5) Refrigerio
if($tipo == 3 || $tipo_permiso == 5) {
    $where['ingreso_id'] = $id;
    $where['tipo'] = 3;
    if($tipo_permiso == 5) {
        unset($where['tipo']);
        $where['tipo_permiso'] = 5;
    }
    $existepermiso = listarTablaSimple("permiso", $where, $conn, ['id']);
    $idUpPermiso = intval($existepermiso[0]['id'] ?? 0);
}

if ( !$idUpPermiso) {
    // 💾 Insertar nuevo permiso
    $save = [
        'codigo'            => $codigo,
        'fecha_permiso'     => $fecha_permiso,
        'ingreso_id'        => $id,
        'con_permiso'       => $con_permiso,
        'tipo'              => $tipo,
        'tipo_permiso'      => $tipo_permiso,
        'usuario_creacion'  => $_SESSION["usr_login"],
        'estado'            => $estado
    ];

    // 💾 Función para guardar permiso
    $insertedId = saveTable("permiso", $save, $conn);
    $insertedId = intval($insertedId ?? 0);

    if ( $tipo_permiso == 5 ) {
        updateTable("ingreso", [
            'refrigerio_aplicado' => 2
        ], ['id' => $id], $conn);
        $_SESSION["refrigerio_aplicado"] = 1;
    }
} else {
    $upPermiso['fecha_modificacion'] = "now()";
    if ($conpermiso == "Si tiene permiso") {
        $upPermiso['fecha_permiso'] = $fecha_permiso;
    }
    $insertedId = updateTable("permiso", $upPermiso, ['id' => $idUpPermiso], $conn) ? $idUpPermiso : 0;
}

// ⚙️ Si es salida, actualizar eficiencia y reproceso
$paramh = [];
$paramh['ing.id'] = $id;

if ($tipo === 3) {
    $paramh['salida'] = 1;
}

$tiempo = tiempoXTurnoXColaborador($paramh, $conn);

if ($tiempo >= 0) {
    $efi = calcularEficienciaOnline(['id' => $id, 'tiempo' => $tiempo], $conn);
    $eficiencia = $efi['eficiencia'] ?? 0;

    $reproceso = getCantReproceso(['ingreso_id' => $id], $conn);

    updateTable("ingreso", [
        'eficiencia' => $eficiencia,
        'reproceso'  => $reproceso
    ], ['id' => $id], $conn);
}

DB::closeConnection();

//Borrar las sessiones de usuario
$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600); // ← borra PHPSESSID

responder(200, 'Ingreso permiso correctamente.', ['permiso' => $insertedId]);
