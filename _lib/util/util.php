<?php
date_default_timezone_set('America/Lima');

function responder(int $code, string $msn, array $data = []): never {
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'msn'  => $msn,
        'data' => $data
    ]);
    exit;
}

function getUrl(): string {
    $base_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
    $script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // subir 2 niveles
    return rtrim(rtrim($base_url.$script_dir,'/'), '/');
}

function getRequestUri(): string {
    $host = str_replace(' ', '', $_SERVER['HTTP_HOST']); // eliminar espacios internos
    $script_dir = dirname(dirname($_SERVER['REQUEST_URI'])); // subir 2 niveles
    return rtrim($host.$script_dir, '/');
}

function apiGet(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);

    if ($err || empty($response)) {
        return null;
    }

    return json_decode($response, true);
}

// ✅ Función auxiliar para extraer datos
function extraerDato(array $respuesta, string $clave): float {
    return (is_array($respuesta) && ($respuesta['code'] ?? 0) === 200 && isset($respuesta['data'][$clave]))
        ? floatval($respuesta['data'][$clave])
        : 0.0;
}

function formarSqlSet(array $datos): string {
    $set = [];

    foreach ($datos as $columna => $valor) {
        // Si es NULL explícito
        if (is_null($valor)) {
            $set[] = "$columna = NULL";
            continue;
        }

        // Si es una función SQL como NOW(), UUID(), etc.
        $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
        if ($esFuncionSQL || is_numeric($valor)) {
            $set[] = "$columna = $valor";
        } else {
            // Escapar comillas simples y otros caracteres
            $valorSanitizado = addslashes($valor);
            $set[] = "$columna = '$valorSanitizado'";
        }
    }

    return implode(', ', $set);
}

function formarSqlValues(array $datos): array {
    $columnas = [];
    $valores = [];

    foreach ($datos as $columna => $valor) {
        $columnas[] = $columna;

        $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
        if (is_numeric($valor) || $esFuncionSQL) {
            $valores[] = $valor;
        } else {
            $valores[] = "'$valor'";
        }
    }

    $cols = implode(', ', $columnas);
    $vals = implode(', ', $valores);

    return [
        'cols' => $cols,
        'vals' => $vals
    ];
}

//Sql Insert
function formarSqlInsert(string $tabla, array $datos): string {
    $datos = formarSqlValues($datos);

    $cols = $datos['cols'] ?? '';
    $vals = $datos['vals'] ?? '';

    if (empty($cols) || empty($vals)) {
        return '';
    }

    return "INSERT INTO $tabla ($cols) VALUES ($vals)";
}

//Sql update
function formarSqlUpdate(string $tabla, array $datos, string $condicion): string {
    return "UPDATE $tabla SET " . formarSqlSet($datos) . " WHERE $condicion";
}

/**
 * Genera SQL tipo: INSERT INTO tabla (col1, col2) VALUES (?, ?, NOW())
 * Y devuelve también los valores a enlazar (en el mismo orden)
 */
function formarSqlInsertPreparado(string $tabla, array $datos): array {
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

/**
 * Gerera SQL tipo: UPDATE TABLE SET col1=?, col2=? where id=?
 */
function formarSqlUpdatePreparado(string $tabla, array $datos, array $where): array {
    $setPartes = [];
    $wherePartes = [];
    $valores = [];

    // 🛠️ Armado de SET
    foreach ($datos as $columna => $valor) {
        $esFuncionSQL = is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
        if ($esFuncionSQL) {
            $setPartes[] = "$columna = $valor";
        } else {
            $setPartes[] = "$columna = ?";
            $valores[] = $valor;
        }
    }

    // 🛠️ Armado de WHERE
    foreach ($where as $columna => $valor) {
        $wherePartes[] = "$columna = ?";
        $valores[] = $valor;
    }

    $sql = "UPDATE $tabla SET " . implode(', ', $setPartes) . " WHERE " . implode(' AND ', $wherePartes);

    return [
        'sql' => $sql,
        'values' => $valores
    ];
}

//Inserta a la DB registros de una tabla 
/**
 * Parametros
 * string (tabla)
 * array campos (datos)
 */
function saveTable(string $tabla, array $datos, PDO $conn): ?int {
    $rta = formarSqlInsertPreparado($tabla, $datos);
    $sql_insert = $rta['sql'] ?? null;
    $valores = $rta['values'] ?? null;

    if (empty($sql_insert)) {
        return 0;
    }

    if (!$conn) {
        return 0;
    }

    try {
        $stmt_mysql = $conn->prepare($sql_insert);
        $stmt_mysql->execute($valores);

        $id = (int)$conn->lastInsertId();
        return $id;

    } catch (Exception $e) {
        error_log("❌ Exception en $tabla: " . $e->getMessage());
        return 0;
    }
}

//Actualizar registros de una tabla
/**
 * Parametros
 * string (tabla)
 * array campos (datos)
 * array campos (where)
 * 
 */
function updateTable(string $tabla, array $datos, array $whe, PDO $conn): ?bool {
    $rta = formarSqlUpdatePreparado($tabla, $datos, $whe);
    $sql_insert = $rta['sql'] ?? null;
    $valores = $rta['values'] ?? null;

    if (empty($sql_insert) || empty($valores)) {
        return false;
    }

    if (!$conn) {
        error_log("❌ SQL UPDATE inválido o sin valores");
        return false;
    }

    try {
        $stmt = $conn->prepare($sql_insert);
        $ok = $stmt->execute($valores);

        if (!$ok) {
            error_log("❌ Error al ejecutar UPDATE en $tabla");
        }

        return $ok;

    } catch (Exception $e) {
        error_log("❌ Exception en $tabla: " . $e->getMessage());
        return false;
    }
}

/**
 * Listar una sola tabla
 * array (where)
*/
function listarTablaSimple(string $tabla, array $param, PDO $conn, array $campos = ['*']): ?array {
    if (!$conn || count($param)<1) return [];

    $where = [];
    foreach ($param as $k => $v) {
        if (!empty($v)) $where[$k] = "$k = :$k";
    }

    if (empty($where)) return [];

    try {
        //Mostrar permiso
        $sql = "SELECT " . implode(', ', $campos) . "
                FROM $tabla
                WHERE ".implode(" AND ", $where);

        $stmt = $conn->prepare($sql);
        foreach ($where as $k => $v) {
            $stmt->bindValue(":$k", $param[$k]);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}