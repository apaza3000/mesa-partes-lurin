<?php
namespace App\Models;

use PDO;
use PDOException;
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
        try {
            $stmt = $this->db->query(
                'SELECT 
                    u.id_usuario, 
                    /*u.avatar,*/
                    u.username, 
                    u.estado, 
                    u.creado_en,
                    r.nombre AS rol_nombre
            FROM usuarios AS u
            LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
            LEFT JOIN roles r ON ur.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC LIMIT 10
            
            
            /*
            SELECT 
                    u.id_usuario,
                    u.username,
                    u.avatar,
                    u.estado AS estado_usuario,
                    u.creado_en AS fecha_registro,
                    r.id_rol,
                    r.nombre AS nombre_rol,
                    r.descripcion AS descripcion_rol
                FROM `usuarios` u
                INNER JOIN `usuario_roles` ur ON u.id_usuario = ur.id_usuario
                INNER JOIN `roles` r ON ur.id_rol = r.id_rol; LIMIT 10
                
                */'
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

}