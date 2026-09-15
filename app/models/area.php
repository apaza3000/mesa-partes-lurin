<?php

namespace App\Models;

use App\Config\Conexion;
use PDO;
use PDOException;

class Area
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Listar todas las áreas activas
    public function obtenerTodas(): array
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
    public function obtenerPorId(int $idArea): ?array
    {
        try {
            $sql = "SELECT * FROM areas WHERE id_area = :id_area";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_area' => $idArea]);
            $resultado = $stmt->fetch();
            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}