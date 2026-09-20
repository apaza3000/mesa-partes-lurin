<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Rol implements InterfaceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->db->query(
                'SELECT id_rol, nombre, descripcion, estado FROM roles ORDER BY nombre ASC'
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById(int|string $id): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id_rol, nombre, descripcion, estado FROM roles WHERE id_rol = :id'
            );
            $stmt->execute([':id' => (int) $id]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create(array $datos): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO roles (nombre, descripcion, estado)
                 VALUES (:nombre, :descripcion, :estado)'
            );
            return $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
                ':estado' => $datos['estado'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(array $datos, int|string $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE roles
                 SET nombre = :nombre, descripcion = :descripcion, estado = :estado
                 WHERE id_rol = :id'
            );
            return $stmt->execute([
                ':id' => (int) $id,
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
                ':estado' => $datos['estado'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int|string $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM roles WHERE id_rol = :id');
            return $stmt->execute([':id' => (int) $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
