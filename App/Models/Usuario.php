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
            ORDER BY u.id_usuario DESC LIMIT 20'
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }

    }
    public function getAllRol(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT id_rol, nombre FROM roles ORDER BY nombre ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener el total de usuarios para calcular el total de páginas
    public function getTotalUsuarios(): int
    {
        try {
            $stmt = $this->db->query('SELECT COUNT(*) FROM usuarios');
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Obtener los usuarios de la página actual
    public function getUsuariosPaginados(int $pagina = 1, int $porPagina = 10): array
    {
        try {
            // Calculamos a partir de qué registro empezar
            $offset = ($pagina - 1) * $porPagina;

            $stmt = $this->db->prepare('
            SELECT u.id_usuario, u.username, u.avatar, u.estado, u.creado_en 
            FROM usuarios u 
            ORDER BY u.id_usuario ASC 
            LIMIT :limit OFFSET :offset
        ');

            // PDO requiere asociar LIMIT y OFFSET como ENTEROS
            $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

}