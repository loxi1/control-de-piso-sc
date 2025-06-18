<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/util.php');
require_once('../_lib/util/accesos.php');
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

// ✅ Iniciar conexión
$conn = DB::getConnection();

// 🔎 Buscar permiso (Refrigerio y permiso con retorno) para actualizar el estado y fecha de ingreso [Util]
$permiso = listarTablaSimple("permiso", ['ingreso_id' => $id, 'tipo' => 'Permiso'], $conn);

$idPermiso = intval($permiso[0]['id'] ?? 0);
if($idPermiso) {
    $fechaCreacion = $permiso[0]['fecha_creacion'] ?? null;
    $fechaPermiso = "now()";
    if($permiso[0]['tipo_permiso'] == 'Refrigerio' && !empty($fechaCreacion)) {    
        // 🔎 Buscar ingreso obtener turno y dia de la semana [Util]
        $ingreso = listarTablaSimple("ingreso", ['id' => $id], $conn);
        $turno = intval($ingreso[0]['turno_id'] ?? 0);
        $dia = intval($ingreso[0]['dia_de_la_semana'] ?? 0);
        if ( $turno && $dia ) {
            // 🔎 Buscar la cantidad de minutos que dura su refrigerio [Util]
            $turnoAll = listarTablaSimple("turno_horario", ['numero_dia'=>$dia, 'turno_id'=>$turno], $conn);
            $totalMinutos = intval($turnoAll[0]['considerar_almuerzo_min'] ?? 0);
            if($totalMinutos) {
                $datetime = new DateTime($fechaCreacion);
                $datetime->add(new DateInterval('PT' . $totalMinutos . 'M'));
                $fechaPermiso = $datetime->format('Y-m-d H:i:s');
            }
        }
    }
    updateTable("permiso", ["fecha_permiso"=>$fechaPermiso,"estado"=>2,"fecha_modificacion"=>"now()"], ["id"=>$idPermiso], $conn);
}

if($tipo == 1) {
    // Si es ingreso, no se requiere fecha_permiso
    if(!empty($fecha_permiso)) {
        $vafs = $fecha_permiso;
    }

    //Validar si ya existe un permiso para esa fecha con el ingreso_id y para el mismo operario
    $sqlexiste = "select id from permiso where codigo='$codigo' and ingreso_id=$id and tipo=$tipo";
    sc_lookup(rs_existe, $sqlexiste);

    if (!empty({rs_existe}[0][0])) {
        $sqlupdate = "UPDATE permiso SET fecha_modificacion=now(), con_permiso=$con_permiso, fecha_permiso='$vafs', tipo_permiso=$tipo_permiso WHERE id=".{rs_existe}[0][0];
        sc_exec_sql($sqlupdate);
        responder(200, 'Ya existe un permiso para esa fecha y operario.',['permiso' => {rs_existe}[0][0]]);
    }
}
//ID permiso
$insertedId = guardar_permiso(formarSqlInsert("permiso", [
	"codigo" => $codigo,
	"fecha_permiso" => $vafs,
	"ingreso_id" => $id,
	"con_permiso" => $con_permiso,
	"tipo" => $tipo,
    "usuario_creacion" => $codigo,
	"tipo_permiso" => $tipo_permiso
]));

DB::closeConnection();

if ($insertedId !== null) {
    responder(200, 'Ingreso permiso correctamente.', ['permiso' => $insertedId]);
} else {
    responder(500, 'Error al ingresar permiso.');
}

// 💾 Función para guardar ciclo
function guardar_permiso($sql_insert): ?int {
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