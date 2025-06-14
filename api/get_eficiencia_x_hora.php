<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/accesos.php');
require_once('../_lib/util/util.php');
require_once('../_lib/util/eficiencia.php');
require_once('../_lib/util/ingreso.php');

header('Content-Type: application/json');

// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$idingreso = intval($_SESSION['ingreso_id'] ?? 0);
if (!$idingreso) {
    responder(422, 'Se requiere parámetro "ingreso"');
}

$conn = DB::getConnection();

// ⌛ Tiempo transcurrido
$tiempo = tiempoXTurnoXColaborador(['ing.id' => $idingreso], $conn);
// error_log("Ingreso: $tiempo"); // solo si estás depurando

if ($tiempo <= 0) {
    responder(200, 'Sin ciclos registrados.', ['eficiencia' => 0, 'cantidad' => 0]);
}

// Obtener eficiencia ⚙️
$efi = calcularEficienciaOnline(['id' => $idingreso, 'tiempo' => $tiempo], $conn);
$eficiencia = $efi['eficiencia'] ?? 0;
$cantidad = intval($efi['cantidad'] ?? 0);

DB::closeConnection();

// ✅ Enviar respuesta JSON
responder(200, 'Eficiencia obtenida correctamente.', [
    'eficiencia' => $eficiencia,
    'cantidad'   => $cantidad
]);