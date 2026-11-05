<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use Src\Core\Conexion;

class Auditoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function insertar($id_usuario, $modulo, $accion, $tabla, $id_registro, $descripcion, $ip)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO auditoria
                (id_usuario, modulo, accion, tabla_afectada, id_registro, descripcion, ip)
             VALUES
                (:id_usuario, :modulo, :accion, :tabla_afectada, :id_registro, :descripcion, :ip)"
        );
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':modulo' => $modulo,
            ':accion' => $accion,
            ':tabla_afectada' => $tabla,
            ':id_registro' => $id_registro,
            ':descripcion' => $descripcion,
            ':ip' => $ip
        ]);

    }
}
