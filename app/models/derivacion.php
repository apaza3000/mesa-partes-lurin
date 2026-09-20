<?php
namespace App\Models;

use PDO;
use PDOException;

class Derivacion implements InterfaceModel {
    private PDO $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function getAll(): array {
        try {
            $sql = "SELECT * FROM derivaciones ORDER BY id_derivacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById(int | string $id): array {
        try {
            $sql = "SELECT * FROM derivaciones WHERE id_derivacion = :id";
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
            $sql = "INSERT INTO derivaciones (id_documento, id_area_origen, id_area_destino, id_usuario_envia, observaciones) 
                    VALUES (:id_documento, :id_area_origen, :id_area_destino, :id_usuario_envia, :observaciones)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_documento'     => $datos['id_documento'],
                ':id_area_origen'   => $datos['id_area_origen'],
                ':id_area_destino'  => $datos['id_area_destino'],
                ':id_usuario_envia' => $datos['id_usuario_envia'],
                ':observaciones'    => $datos['observaciones']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(array $datos, int | string $id): bool {
        return false;
    }

    public function delete(int | string $id): bool {
        return false;
    }

    public function crearDerivacion(array $datos): bool {
        return $this->create($datos);
    }

    public function obtenerPorDocumento(int $idDocumento): array {
        try {
            $sql = "SELECT * FROM derivaciones WHERE id_documento = :id_documento ORDER BY fecha_derivacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_documento' => $idDocumento]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}