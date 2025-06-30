<?php
/**
 * Utilidades para sincronizacion de fecha en PHP y la base de datos.
 */
function setZonaHoraria(string $zona = 'America/Lima'): void {
    date_default_timezone_set($zona);
}

/**
 * ⚖️ setApiHeaders: Encabezados comunes para todas las respuestas de la API.
 */
function setApiHeaders(): void {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

/**
 * Verifica si existe una sesión activa con 'ingreso_id'.
 * Si no existe, destruye la sesión y responde con un error 401.
 */
function verificarSesionActiva(): void {
    // Iniciar sesión si aún no se ha hecho
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['ingreso_id'])) {
        cerrarSesion();

        http_response_code(401);
        echo json_encode([
            'code' => 401,
            'msn'  => 'Sesión expirada. Por favor, vuelva a iniciar sesión.'
        ]);
        exit;
    }
}

/**
 * Cerrar sesión y destruirla.
 * Utiliza esta función para cerrar la sesión del usuario actual.
 */
function cerrarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

/**
 * Responde con un código HTTP y un mensaje JSON.
 * Utiliza esta función para enviar respuestas estandarizadas desde la API.
 * @param int $code Código de estado HTTP (ej. 200, 404, 500)
 * @param string $msn Mensaje descriptivo de la respuesta
 * @param array $data Datos adicionales a incluir en la respuesta (opcional)
 * 
 * @return never Termina la ejecución del script después de enviar la respuesta
 */
function responder(int $code, string $msn, array $data = []): never {
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'msn'  => $msn,
        'data' => $data
    ]);
    exit;
}

/**
 * Obtiene la URL base del script actual.
 * Utiliza esta función para construir URLs relativas en la aplicación.
 * 
 * @return string URL base del script
 */
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

/**
 * Genera una cadena SQL con condiciones tipo AND a partir de un array con operadores personalizados.
 *
 * Soporta operadores: =, >, <, >=, <=, !=, <>, IS, IS NOT, BETWEEN, IN, LIKE, NOT LIKE
 *
 * Ejemplo:
 * [
 *   'estado_id ='        => 1,
 *   'fecha BETWEEN'      => ['2024-01-01', 'now()'],
 *   'tipo IN'            => [1, 2, 3],
 *   'campo IS'           => null,
 *   'nombre LIKE'        => '%Carlos%',
 *   'codigo NOT LIKE'    => 'Z%',
 * ]
 *
 * @param array $condiciones
 * @return string
 */
function formarCondicionesAvanzadas(array $condiciones): string {
    $partes = [];

    foreach ($condiciones as $clave => $valor) {
        if (!preg_match('/^(\S+)\s+([A-Z\s<>!=]+)$/i', $clave, $match)) {
            continue; // Ignora claves mal formadas
        }

        $columna  = $match[1];
        $operador = strtoupper(trim($match[2]));

        // BETWEEN: valor debe ser array de 2 elementos
        if ($operador === 'BETWEEN' && is_array($valor) && count($valor) === 2) {
            [$ini, $fin] = $valor;
            $ini = isFuncionSQL($ini) ? $ini : "'" . addslashes($ini) . "'";
            $fin = isFuncionSQL($fin) ? $fin : "'" . addslashes($fin) . "'";
            $partes[] = "$columna BETWEEN $ini AND $fin";
        }

        // IN: lista de valores
        elseif ($operador === 'IN' && is_array($valor)) {
            $valores = array_map(fn($v) => is_numeric($v) || isFuncionSQL($v) ? $v : "'" . addslashes($v) . "'", $valor);
            $partes[] = "$columna IN (" . implode(', ', $valores) . ")";
        }

        // IS / IS NOT con NULL
        elseif (in_array($operador, ['IS', 'IS NOT']) && is_null($valor)) {
            $partes[] = "$columna $operador NULL";
        }

        // LIKE y NOT LIKE
        elseif (in_array($operador, ['LIKE', 'NOT LIKE']) && is_string($valor)) {
            $valor = "'" . addslashes($valor) . "'";
            $partes[] = "$columna $operador $valor";
        }

        // Operadores estándar (=, >, <, >=, <=, !=, <>, etc.)
        elseif (is_scalar($valor)) {
            $val = isFuncionSQL($valor) ? $valor : "'" . addslashes($valor) . "'";
            $partes[] = "$columna $operador $val";
        }
    }

    return $partes ? implode(' AND ', $partes) : '';
}

/**
 * Detecta si un valor es una función SQL como NOW(), UUID(), etc.
 */
function isFuncionSQL($valor): bool {
    return is_string($valor) && preg_match('/^\s*[A-Z_]+\s*\(.*\)\s*$/i', $valor);
}

/**
 * Formar una consulta SQL SET para UPDATE.
 * Genera una cadena de texto con las asignaciones de columnas y valores.
 * @param array $datos Array asociativo con columnas y valores
 * 
 * @return string Consulta SQL SET formateada
 */
function formarSqlSet(array $datos): string {
    $set = [];

    foreach ($datos as $columna => $valor) {
        // Si es NULL explícito
        if (is_null($valor)) {
            $set[] = "$columna = NULL";
            continue;
        }

        // Si es una función SQL como NOW(), UUID(), etc.
        $esFuncionSQL = isFuncionSQL($valor);
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

/**
 * Genera una consulta SQL tipo: INSERT INTO tabla (col1, col2) VALUES ('valor1', 'valor2')
 * Y devuelve las columnas y valores formateados.
 * @param array $datos Array asociativo con columnas y valores
 * 
 * @return array Array con 'cols' y 'vals' formateados
 */
function formarSqlValues(array $datos): array {
    $columnas = [];
    $valores = [];

    foreach ($datos as $columna => $valor) {
        $columnas[] = $columna;

        $esFuncionSQL = isFuncionSQL($valor);
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

/**
 * Retorna una consulta SQL tipo: INSERT INTO tabla (col1, col2) VALUES (?, ?, NOW())
 * Y devuelve también los valores a enlazar (en el mismo orden).
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores
 * 
 * @return string Consulta SQL formateada
 */
function formarSqlInsert(string $tabla, array $datos): string {
    $datos = formarSqlValues($datos);

    $cols = $datos['cols'] ?? '';
    $vals = $datos['vals'] ?? '';

    if (empty($cols) || empty($vals)) {
        return '';
    }

    return "INSERT INTO $tabla ($cols) VALUES ($vals)";
}

/**
 * Genera una consulta SQL tipo: UPDATE tabla SET col1=?, col2=? WHERE condicion
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores
 * @param string $condicion Condición para el WHERE
 * 
 * @return string Consulta SQL formateada
 */
function formarSqlUpdate(string $tabla, array $datos, string $condicion): string {
    return "UPDATE $tabla SET " . formarSqlSet($datos) . " WHERE $condicion";
}

/**
 * Genera SQL tipo: INSERT INTO tabla (col1, col2) VALUES (?, ?, NOW())
 * Y devuelve también los valores a enlazar (en el mismo orden)
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores
 * 
 * @return array Array con 'sql' y 'values' para bind
 */
function formarSqlInsertPreparado(string $tabla, array $datos): array {
    $columnas = [];
    $marcadores = [];
    $valores = [];

    foreach ($datos as $columna => $valor) {
        $columnas[] = $columna;

        // Si es una función SQL como NOW()
        $esFuncionSQL = isFuncionSQL($valor);
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
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores
 * @param array $where Array asociativo con condiciones para el WHERE
 * 
 * @return array Array con 'sql' y 'values' para bind
 */
function formarSqlUpdatePreparado(string $tabla, array $datos, array $where): array {
    $setPartes = [];
    $wherePartes = [];
    $valores = [];

    // 🛠️ Armado de SET
    foreach ($datos as $columna => $valor) {
        $esFuncionSQL = isFuncionSQL($valor);
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

/**
 * Inserta a la DB registros de una tabla 
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return int|0 Retorna el ID del último registro insertado o 0 en caso de error
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

/**
 * Actualizar registros de una tabla
 * @param string $tabla Nombre de la tabla
 * @param array $datos Array asociativo con columnas y valores a actualizar
 * @param array $whe Array asociativo con condiciones para el WHERE
 * @param PDO $conn Conexión a la base de datos
 * 
 * @return bool|null Retorna true si se actualizó correctamente, 
 *                  false si hubo error o null si no se pudo preparar la consulta. 
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
 * @param string $tabla Nombre de la tabla a consultar
 * @param array $param Array asociativo con condiciones para el WHERE
 * @param PDO $conn Conexión a la base de datos
 * @param array $campos Array de campos a seleccionar (por defecto ['*'])
 * 
 * @return array|null Retorna un array con los registros encontrados o null en caso de error.
*/
function listarTablaSimple(string $tabla, array $param, PDO $conn, array $campos = ['*']): ?array {
    if (!$conn || count($param)<1) return [];

    $whereData = blindValueWhereCondiciones($param); // Validar y sanitizar los parámetros

    $condiciones = $whereData['condiciones'] ?? [];
    $bindings = $whereData['bindings'] ?? [];

    if (empty($condiciones) || empty($bindings)) return null;

    try {
        //Mostrar permiso
        $sql = "SELECT " . implode(', ', $campos) . "
                FROM $tabla
                WHERE ".implode(" AND ", $condiciones);

        $stmt = $conn->prepare($sql);
        foreach ($bindings as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error ingreso: " . $e->getMessage());
        return null;
    }
}

/**
 * Devolver un array blindValue con condiciones
 * array (where)
 * Esta función genera condiciones SQL y bindings para parámetros.
 * Utiliza un alias para evitar conflictos con nombres de columnas que contienen puntos (.) lo cambia por guión (_).
 * @param array $param Array asociativo con columnas y valores
 * 
 * @return array Array con 'condiciones' y 'bindings' *
 */
function blindValueWhereCondiciones(array $param): array {
    $condiciones = [];
    $bindings = [];

    foreach ($param as $columna => $valor) {
        if (!isset($valor)) continue;

        // Extraer operador si existe
        preg_match('/(.+?)\s*(=|!=|<>|<|>|<=|>=|LIKE)$/i', $columna, $matches);

        if ($matches) {
            $colBase = $matches[1];
            $operador = $matches[2];
        } else {
            $colBase = $columna;
            $operador = '=';
        }

        $aliasReal = str_replace(['.', ' '], '_', $colBase);
        $condiciones[] = "$colBase $operador :$aliasReal";
        $bindings[$aliasReal] = $valor;
    }

    return [
        'condiciones' => $condiciones,
        'bindings' => $bindings
    ];
}


/**
 * Listar una tabla con condiciones avanzadas.
 * Permite condiciones complejas con operadores personalizados.
 * @param string $tabla Nombre de la tabla a consultar
 * @param array $whereData Array con condiciones y bindings para el WHERE
 * @param ?string $condFijas Condiciones fijas adicionales para el WHERE
 * @param PDO $conn Conexión a la base de datos
 * @param array $campos Array de campos a seleccionar (por defecto ['*'])
 * @param ?string $groupBy Agrupamiento de resultados (opcional)
 * @param ?string $having Condición HAVING (opcional)
 * @param ?string $orderBy Ordenamiento de resultados (opcional)
 * @param ?string $limit Límite de resultados (opcional)
 * 
 * @return array|null Retorna un array con los registros encontrados o null en caso de error.
 * @example
 * listarTablaAvanzada('ingreso', ['codigo' => '36104'], ['fecha BETWEEN' => ['2024-01-01', 'NOW()']], $conn);
 * SELECT * FROM ingreso
 * WHERE 1=1 AND codigo = :codigo AND fecha BETWEEN '2024-01-01' AND NOW()
 *  
 * */
function listarTablaAvanzada(
    string $tabla,
    array $param,
    ?array $paramFijas,
    PDO $conn,
    array $campos = ['*'],
    ?string $groupBy = null,
    ?string $having = null,
    ?string $orderBy = null,
    ?string $limit = null
): ?array {
    if (!$conn) return null;

    $whereData = blindValueWhereCondiciones($param);
    $condiciones = $whereData['condiciones'] ?? [];
    $bindings    = $whereData['bindings'] ?? [];
    // Validar condiciones fijas
    $condAvanzadas = $paramFijas ? formarCondicionesAvanzadas($paramFijas) : '';

    if (empty($condiciones)) return [];

    // Construcción base del SQL
    $sql = "SELECT " . implode(', ', $campos) . " FROM $tabla WHERE 1=1";

    if (!empty($condiciones)) {
        $sql .= " AND " . implode(' AND ', $condiciones);
    }

    if (!empty($condAvanzadas)) {
        $sql .= " AND $condAvanzadas";
    }

    if ($groupBy) {
        $sql .= " GROUP BY $groupBy";
    }

    if ($having) {
        $sql .= " HAVING $having";
    }

    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }

    if ($limit) {
        $sql .= " LIMIT $limit";
    }

    try {
        $stmt = $conn->prepare($sql);
        foreach ($bindings as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("❌ Error en listarTablaAvanzada (con HAVING): " . $e->getMessage());
        return null;
    }
}
