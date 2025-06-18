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
session_start();
// ✅ Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, 'Método no permitido. Solo se acepta GET.');
}

$codigo = $_SESSION["usr_login"] ?? null;

if (empty($codigo)) {
    responder(422, 'Se requiere el parámetro "codigo".');
}

$existente = "SELECT i.turno_id, t.descripcion AS elturno, i.horario_minimo, i.horario_maximo
FROM ingreso i
JOIN turno t ON t.id = i.turno_id
WHERE i.codigo_operario = '$codigo'
  AND NOW() BETWEEN i.horario_minimo AND i.horario_maximo
ORDER BY i.id DESC
LIMIT 1;";

sc_lookup(rta_existe, $existente);

if (!empty({rta_existe}[0][0])) {
    // ✅ Armar respuesta
    $row = {rta_existe}[0];
    $rta[] = [
            'id' => $row[0],
            'turno'  => htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8')
        ];

    // ✅ Enviar respuesta JSON
    responder(200, 'Turnos obtenidos correctamente.', $rta);
}

$sql = "SELECT
            id,
            turno_id,
            (select descripcion from turno trun where trun.id=turno_id) as elturno,
            -- Hora fin del turno anterior (turno diferente, dia anterior)
            (
                SELECT TIMESTAMP(
                DATE(horarios.ingreso), 
                TIME(tur.hora_fin)
                )
                FROM turno_horario tur
                WHERE tur.turno_id != horarios.turno_id
                AND tur.numero_dia = (
                    CASE 
                        WHEN horarios.numero_dia = 7 AND tur.turno_id = 2 THEN
                            CASE WHEN horarios.numero_dia - 1 = 0 THEN 7 ELSE horarios.numero_dia - 1 END
                        ELSE horarios.numero_dia
                    END
                )
                LIMIT 1
            ) AS horario_minimo,
            
            -- Hora inicio del otro turno del mismo día
            (
                SELECT TIMESTAMP(DATE(horarios.salida), TIME(tur.hora_inicio))
                FROM turno_horario tur
                WHERE tur.turno_id != horarios.turno_id
                AND tur.numero_dia =  (
                    CASE 
                        WHEN horarios.numero_dia = 7 AND tur.turno_id = 2 THEN horarios.numero_dia
                        ELSE CASE WHEN horarios.numero_dia + 1 > 7 THEN 1 ELSE horarios.numero_dia + 1 END
                    END
                )
                LIMIT 1
            ) AS horario_maximo,
                ingreso,
                salida
        FROM (
            SELECT
                id,
                numero_dia,
                turno_id,
                considerar_almuerzo_min,
                TIMESTAMP(CURDATE(), TIME(hora_inicio)) AS ingreso,
                TIMESTAMP(
                DATE_ADD(CURDATE(), INTERVAL DATEDIFF(hora_fin, hora_inicio) DAY),
                TIME(hora_fin)
                ) AS salida,
                    DAYOFWEEK(NOW()) numdia
            FROM turno_horario
                where 
                numero_dia = DAYOFWEEK(NOW())
        ) AS horarios
        WHERE DAYOFWEEK(ingreso) = numero_dia
        HAVING 
            now() BETWEEN horario_minimo AND horario_maximo";

sc_lookup(rta_turno, $sql);


if (!isset({rta_turno}) || !is_array({rta_turno})) {
    responder(500, 'Error al ejecutar la consulta.');
}

if (count({rta_turno}) === 0) {
    responder(404, 'No se encontraron turnos.');
}

// ✅ Armar respuesta
$rta = [];
foreach ({rta_turno} as $row) {
    $rta[] = [
        'id' => $row[1],
        'turno'  => htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8')
    ];
}

// ✅ Enviar respuesta JSON
responder(200, 'Turnos obtenidos correctamente.', $rta);