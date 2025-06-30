<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/loader/env_loader.php');

class DB {
    private static ?PDO $conn = null;

    /**
     * Devuelve una conexión PDO a MySQL, reutilizable si ya está creada.
     * @param string $db Nombre de la base de datos (por defecto 'bd_mes')
     * @return PDO
     */
    public static function getConnection(string $db = 'mes'): PDO {
        if (self::$conn === null) {
            $conf = EnvConfig::getMySQL($db);
            $dsn = "mysql:host={$conf['host']};port={$conf['port']};dbname={$conf['dbname']};charset=utf8mb4";

            try {
                // 👇 Usar conexión persistente solo si NO estamos en Windows
                $usePersist = !self::isWindows();

                self::$conn = new PDO($dsn, $conf['user'], $conf['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_PERSISTENT => $usePersist
                ]);

                self::resetConnectionState();

            } catch (PDOException $e) {
                error_log("❌ Error al conectar MySQL: " . $e->getMessage());
                throw new Exception("No se pudo conectar a la base de datos.");
            }
        }

        return self::$conn;
    }

    /**
     * Restablece el estado de conexión para evitar problemas de conexiones anteriores.
     */
    private static function resetConnectionState(): void {
        try {
            self::$conn->exec("ROLLBACK"); // por si hay una transacción abierta
            self::$conn->exec("SET autocommit = 1");
            self::$conn->exec("SET SESSION sql_mode = ''");
        } catch (PDOException $e) {
            error_log("⚠️ Error al limpiar estado de conexión: " . $e->getMessage());
        }
    }

    /**
     * Detecta si el entorno es Windows.
     * @return bool
     */
    private static function isWindows(): bool {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Método opcional para cerrar la conexión.
     */
    public static function closeConnection(): void {
        // self::$conn = null; // puedes habilitarlo si realmente necesitas liberar memoria
    }
}