<?php 
namespace App\Models;

use PDO;
use PDOException;

class Conexion
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConexion(): PDO
    {
        if (self::$instance === null) {
            $host = getenv("DB_SERVER") ?: "localhost";
            $db   = getenv("DB_NAME")   ?: "intranet_lurin";
            $user = getenv("DB_USER")   ?: "root";
            $pass = getenv("DB_PASSWORD") ?: "";
            $charset = getenv("DB_CHARSET") ?: "utf8mb4";
            $port = getenv("DB_PORT")     ?: "3306";

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}