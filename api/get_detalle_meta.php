<?php
require_once('../_lib/util/funciones.php');
header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$op = $_GET['op'] ?? null;
$linea = $_GET['linea'] ?? null;
$usuario = $_GET['usuario'] ?? null;
$empresa = "COFACO";
$compania = "02";

if(empty($op)) {
    responder(422, 'Se requiere el parámetro "op".');
}

if(empty($linea)) {
    responder(422, 'Se requiere el parámetro "linea".');
}

if(empty($usuario)) {
    responder(422, 'Se requiere el parámetro "usuario".');
}

try {
    //Conexion a la base de datos
    $conn = conectar_sybase();

    $sql = "SELECT SUM(cant) AS total
        FROM (
            SELECT COUNT(*) as cant FROM ordencortetallasmov
            WHERE ccmpn=:ccmpn AND nnope=:nnope AND cod_equipo=:cod_equipo
            AND fechamodifica >= CAST(GETDATE() AS DATE)
            AND fechamodifica < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa=:empresa
            UNION ALL
            SELECT COUNT(*) as cant FROM ordencortetallasmov 
            WHERE ccmpn=:ccmpn AND nnope=:nnope AND cod_equipo=:cod_equipo
            AND fechSalCostExt2 >= CAST(GETDATE() AS DATE)
            AND fechSalCostExt2 < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa=:empresa
        ) AS total_prendas";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nnope', $op);
    $stmt->bindParam(':cod_equipo', $linea);
    $stmt->bindParam(':ccmpn', $compania);
    $stmt->bindParam(':empresa', $empresa); // doble uso en tu SQL

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $canttimbradas = $result['total'] ?? 0;

    $canttimbradas = (int)$canttimbradas;
    if($canttimbradas == 0) {
        responder(200, 'No hay prendas timbradas.');
    }
    
    //Meta del día cambiar: AND fecha >= CAST(GETDATE() AS DATE) AND fecha < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
    $area = "SALIDA DE COSTURA";
    $sqs = "SELECT TOP 1 cantmeta
        FROM meta_linea_areas
        WHERE ccmpn=:ccmpn
            AND nnope=:nnope
            AND fecinicio >= GETDATE()
            AND GETDATE() < fecfin
            AND linea=:linea
            AND empresa=:empresa
            AND area=:area";

    $stmr = $conn->prepare($sqs);
    $stmr->bindParam(':nnope', $op);
    $stmr->bindParam(':linea', $linea);
    $stmr->bindParam(':ccmpn', $compania);
    $stmr->bindParam(':area', $area);
    $stmr->bindParam(':empresa', $empresa); // doble uso en tu SQL

    $stmr->execute();
    $resultado = $stmr->fetch(PDO::FETCH_ASSOC);
    $meta = $resultado['cantmeta'] ?? 0;

    $meta = (int) $meta;
    if($meta == 0) {
        responder(200, 'No se registro la meta.');
    }

    $rta = ($meta > 0) ? ($canttimbradas * 100) / $meta : 0;

    $respuesta = [
        'meta' => $rta
    ];

    responder(200, 'Consulta exitosa.', $respuesta);

} catch (PDOException $e) {
    responder(500, 'Error al consultar la base de datos: ' . $e->getMessage());
} finally {
    $conn = null; // ✅ Cierre de conexión
}