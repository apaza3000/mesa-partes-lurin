<?php

namespace src\Core;

use PDO;
use PDOException;

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 3));
}

class Conexion
{
    private static $pdo = null;

    public static function getConexion(): ?PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $configPath = ROOT_DIR . '/backend/config/database.php';
        if (!file_exists($configPath)) {
            throw new PDOException('No existe el archivo de configuración de la base de datos: ' . $configPath);
        }

        $config = require $configPath;
        $dsn = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=' . $config['charset'];

        try {
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw $e;
        }

        return self::$pdo;
    }
}
