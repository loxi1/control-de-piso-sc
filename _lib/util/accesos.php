<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/loader/env_loader.php');

class DB {
    private static ?PDO $conn = null;

    /**
     * Obtiene una conexión a la base de datos MySQL.
     * en base a los parámetros de configuración del entorno.
     * @param string $db Nombre de la base de datos a conectar (por defecto 'bd_mes').
     * @return PDO Objeto de conexión a la base de datos.
     * 
     * @return PDO
     */
    public static function getConnection(string $db = 'bd_mes'): PDO {
        if (self::$conn === null) {
            $conf = EnvConfig::getMySQL();
            //$dsn = "mysql:host={$conf['host']};port={$conf['port']};dbname={$db};charset=utf8mb4";
            $dsn = "mysql:host={$conf['hostname']};port={$conf['port']};dbname={$db};charset=utf8mb4";

            try {
                /*self::$conn = new PDO($dsn, $conf['user'], $conf['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_PERSISTENT => true
                ]);*/
                self::$conn = new PDO($dsn, $conf['usuario'], $conf['pwd'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_PERSISTENT => true
                ]);

                // 💡 Asegurar estado limpio por si se reutiliza una conexión
                self::resetConnectionState();

            } catch (PDOException $e) {
                error_log("Error MySQL: " . $e->getMessage());
                throw new Exception("No se pudo conectar a la base de datos.");
            }
        }

        return self::$conn;
    }
    
    /**
     * Obtiene una conexión a la base de datos MySQL para operaciones de escritura.
     * @return PDO Objeto de conexión a la base de datos.
     */
    private static function resetConnectionState(): void {
        try {
            // ✅ Reestablecer opciones que podrían estar sucias por conexiones anteriores
            self::$conn->exec("ROLLBACK"); // por si quedó una transacción abierta
            self::$conn->exec("SET autocommit = 1");
            self::$conn->exec("SET SESSION sql_mode = ''");
        } catch (PDOException $e) {
            // No interrumpir flujo, pero registrar
            error_log("Error al limpiar estado de conexión: " . $e->getMessage());
        }
    }

    public static function closeConnection(): void {
        //self::$conn = null; para produccion comentar
    }
}