<?php

function formarSqlInsertPreparado(string $tabla, array $datos): array
{
    $columnas = [];
    $marcadores = [];
    $valores = [];

    foreach ($datos as $columna => $valor) {
        $columnas[] = $columna;

        // Si es una función SQL como NOW()
        $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
        if ($esFuncionSQL) {
            $marcadores[] = $valor; // se inserta como está (sin '?')
        } else {
            $marcadores[] = '?'; // marcador para bind
            $valores[] = $valor; // valor para bindParam
        }
    }

    $cols = implode(', ', $columnas);
    $vals = implode(', ', $marcadores);

    $sql = "INSERT INTO $tabla ($cols) VALUES ($vals)";
    return [
        'sql' => $sql,
        'values' => $valores
    ];
}

$tabla = "ingreso";

$env['tipo'] = 1;
$env['con_permiso'] = 3;
$env['tipo_permiso'] = 3;
$env['fecha_mofificacion'] = "now()";
$env['fecha_permiso'] = "2025-06-10 08:40:51";
$env['tiempo'] = "TIMEDIFF(NOW(), tiempo_inicio)";

$rta = formarSqlInsertPreparado($tabla, $env);
echo "<pre>";
print_r($rta);
echo "</pre>";
