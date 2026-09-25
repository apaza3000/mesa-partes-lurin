<?php
/*
namespace App\Models;

use PDO;
use PDOException;
use Exception;
use Src\Core\Conexion;
*/

namespace App\Models;

use PDO;
use Src\Core\Conexion;

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Trae todos los usuarios junto con sus roles asignados
     */
    public function getAll(): array
    {
        $sql = "SELECT u.id_usuario, u.username, u.estado, r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
            LEFT JOIN roles r ON ur.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}