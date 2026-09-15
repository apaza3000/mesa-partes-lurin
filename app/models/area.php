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


    // Listar todas las áreas activas
    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM areas WHERE estado = 'Activo' ORDER BY nombre ASC";
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
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function update(array $datos, int|string $id): bool
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function create(array $datos): bool
    {
        throw new \Exception('Not implemented');
    }
}