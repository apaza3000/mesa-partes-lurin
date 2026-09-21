<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Area implements InterfaceModel {
    private PDO $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function getAll(): array {

            $sql = "SELECT a.*, p.nombre AS area_padre 
                    FROM areas a 
                    LEFT JOIN areas p ON p.id_area = a.id_area_padre 
                    ORDER BY a.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
     
    }

    public function getById(int | string $id): array {
        try {
            $sql = "SELECT * FROM areas WHERE id_area = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch();
            return $resultado ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create(array $datos): bool {
        try {
            $sql = "INSERT INTO areas (nombre, siglas, estado, id_area_padre) 
                    VALUES (:nombre, :siglas, :estado, :id_area_padre)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre'        => $datos['nombre'],
                ':siglas'        => $datos['siglas'],
                ':estado'        => $datos['estado'] ?? 'Activo',
                ':id_area_padre' => $datos['id_area_padre'] ?? null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(array $datos, int | string $id): bool {
        try {
            $sql = "UPDATE areas 
                    SET nombre = :nombre, siglas = :siglas, estado = :estado, id_area_padre = :id_area_padre 
                    WHERE id_area = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'            => $id,
                ':nombre'        => $datos['nombre'],
                ':siglas'        => $datos['siglas'],
                ':estado'        => $datos['estado'] ?? 'Activo',
                ':id_area_padre' => $datos['id_area_padre'] ?? null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int | string $id): bool {
        try {
            $sql = "DELETE FROM areas WHERE id_area = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}