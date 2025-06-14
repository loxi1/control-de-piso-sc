<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/loader/env_loader.php');

class DB {
    private static ?PDO $conn = null;

    public static function getConnection(string $db = 'bd_mes'): PDO {
        if (self::$conn === null) {
            $conf = EnvConfig::getMySQL();
            $dsn = "mysql:host={$conf['host']};port={$conf['port']};dbname={$db};charset=utf8mb4";

            try {
                self::$conn = new PDO($dsn, $conf['user'], $conf['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
            } catch (PDOException $e) {
                error_log("Error MySQL: " . $e->getMessage());
                throw new Exception("No se pudo conectar a la base de datos.");
            }
        }

        return self::$conn;
    }

    public static function closeConnection(): void {
        self::$conn = null;
    }
}
