<?php

namespace Src\Core;

use PDO;
use PDOException;



class Conexion
{

    private static $pdo = null;


    // Método para obtener la instancia de PDO
    public static function getConexion(): ?PDO
    {
        $config = require Path::base() . 'config/database.php';

        if (self::$pdo === null) {
            try {
                $dsn = $config['driver'] . ':host=' . $config['host']
                    . ';port=' . $config['port']
                    . ';dbname=' . $config['database']
                    . ';charset=' . $config['charset'];

                self::$pdo = new PDO($dsn, $config['username'], $config['password']);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        return self::$pdo;
    }
}
