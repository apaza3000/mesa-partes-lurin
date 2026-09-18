<?php

namespace app\models;

use InterfaceModel;
use Override;
use PDO;
use PDOException;

class Area implements InterfaceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }


    public function getAll(bool $incluirInactivas = true): array
    {
        try {
            $sql = "SELECT a.*, p.nombre AS area_padre
                    FROM areas a
                    LEFT JOIN areas p ON p.id_area = a.id_area_padre";
            if (!$incluirInactivas) {
                $sql .= " WHERE a.estado = 'Activo'";
            }
            $sql .= " ORDER BY a.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener un área por su ID
   
    public function getById($idArea): array
    {
        try {
            $sql = "SELECT * FROM areas WHERE id_area = :id_area";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_area' => $idArea]);
            $resultado = $stmt->fetch();
            return $resultado ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
    #[Override]
    public function delete(int|string $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE areas SET estado = 'Inactivo', actualizado_en = CURRENT_TIMESTAMP
                 WHERE id_area = :id_area AND estado = 'Activo'"
            );
            $stmt->execute([':id_area' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    #[Override]
    public function update(array $datos, int|string $id): bool
    {
        try {
            $sql = "UPDATE areas
                    SET id_area_padre = :id_area_padre, nombre = :nombre,
                        siglas = :siglas, estado = :estado,
                        actualizado_en = CURRENT_TIMESTAMP
                    WHERE id_area = :id_area";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_area_padre' => $datos['id_area_padre'] ?: null,
                ':nombre' => $datos['nombre'],
                ':siglas' => $datos['siglas'],
                ':estado' => $datos['estado'],
                ':id_area' => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    #[Override]
    public function create(array $datos): bool
    {
        try {
            $sql = "INSERT INTO areas (id_area_padre, nombre, siglas, estado)
                    VALUES (:id_area_padre, :nombre, :siglas, :estado)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_area_padre' => $datos['id_area_padre'] ?: null,
                ':nombre' => $datos['nombre'],
                ':siglas' => $datos['siglas'],
                ':estado' => $datos['estado']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}