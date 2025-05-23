<?php
require_once(__DIR__ . '/EnvVar.php');
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
            $valorSanitizado = addslashes($valor);
            $valores[] = "'$valorSanitizado'";
        }
    }

    $cols = implode(', ', $columnas);
    $vals = implode(', ', $valores);

    return [
        'cols' => $cols,
        'vals' => $vals
    ];
}

function formarSqlInsert(string $tabla, array $datos): string {
    $datos = formarSqlValues($datos);

    $cols = $datos['cols'] ?? '';
    $vals = $datos['vals'] ?? '';

    if (empty($cols) || empty($vals)) {
        return '';
    }

    return "INSERT INTO $tabla ($cols) VALUES ($vals)";
}

function formarSqlUpdate(string $tabla, array $datos, string $condicion): string {
    return "UPDATE $tabla SET " . formarSqlSet($datos) . " WHERE $condicion";
}

// ✅ Conectar a base Sybase y devolver conexión activa
function conectar_sybase(): ?PDO {
    try {
        $dsn = "dblib:host=" . SERVER_NAME_SY . ":" . PORT_SY . ";dbname=" . DB_NAME_SY;
        $conn = new PDO($dsn, DB_USER_SY, DB_PASSWORD_SY);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        responder(500, 'Error de conexión Sybase: ' . $e->getMessage());
    }
}

// ✅ Conectar a base MySQL y devolver conexión activa
function conectar_mysql(): ?PDO {
    try {
        $dsn = "mysql:host=" . DB_SERVER_MY . ";port=" . DB_PORT_MY . ";dbname=" . DB_NAME_MY . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        $conn = new PDO($dsn, DB_USER_MY, DB_PASSWORD_MY, $options);
        return $conn;
    } catch (PDOException $e) {
        responder(500, 'Error de conexión MySQL: ' . $e->getMessage());
    }
}