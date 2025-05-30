<?php
require_once('../_lib/util/funciones.php');
//date_default_timezone_set('America/New_York');
$datetime = new DateTime();
$timezone = $datetime->getTimezone()->getName();
echo "La zona horaria actual es: " . $timezone;
$dias_semana = ["domingo", "lunes", "martes", "miercoles", "jueves", "viernes", "sabado"];
// Enter your code here, enjoy!
$fechas = [];
$sql_campos = [];
echo "<pre>";
for ($i = -3; $i <= 3; $i++) {
    $fecha = date('Y-m-d', strtotime("$i day"));
    $indice_dia = ($i + 4) % 7; // Alinea el índice con el array de nombres
    $dia_nombre = $dias_semana[$indice_dia];

    $fechas[$dia_nombre] = $fecha;
    print_r("$dia_nombre ($indice_dia) " . date("w", strtotime($fecha)) . " $fecha \n \r");

    // Construir campo SQL
    $sql_campos[] = "CONCAT_WS(' ', '$dia_nombre', DAYOFWEEK('$fecha'), '$fecha') AS `$dia_nombre`";
}
// Generar SQL
$sql = "SELECT " . implode(", ", $sql_campos);
print_r("\n$sql\n\n");

// Ejecutar consulta (esto depende de tu entorno, ejemplo con ScriptCase):
sc_lookup(rs_data_sybase, $sql);

// Obtener resultados
$info = {rs_data_sybase}[0];

foreach ($dias_semana as $dia => $fecha) {
    print_r($info[$dia] . "\n");
}
echo "</pre>";