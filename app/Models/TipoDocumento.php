<?php
namespace App\Models;

use PDO;

class TipoDocumento {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll($incluirInactivos = true) {
        $sql = "SELECT * FROM tipos_documento WHERE deleted_at IS NULL";
        if (!$incluirInactivos) {
            $sql .= " AND estado = 1";
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM tipos_documento WHERE id = :id AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO tipos_documento (codigo, nombre, descripcion, requiere_archivo, estado) 
                VALUES (:codigo, :nombre, :descripcion, :requiere_archivo, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo' => strtoupper(trim($data['codigo'])),
            ':nombre' => trim($data['nombre']),
            ':descripcion' => trim($data['descripcion'] ?? ''),
            ':requiere_archivo' => !empty($data['requiere_archivo']) ? 1 : 0,
            ':estado' => 1
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE tipos_documento 
                SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, requiere_archivo = :requiere_archivo 
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':codigo' => strtoupper(trim($data['codigo'])),
            ':nombre' => trim($data['nombre']),
            ':descripcion' => trim($data['descripcion'] ?? ''),
            ':requiere_archivo' => !empty($data['requiere_archivo']) ? 1 : 0
        ]);
    }

    public function changeState($id, $estado) {
        $sql = "UPDATE tipos_documento SET estado = :estado WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':estado' => $estado]);
    }

    public function isUsedInDocuments($id) {
        // En sprint posteriores se verifica la FK en la tabla trámites/documentos
        return false; 
    }

    public function softDelete($id) {
        $sql = "UPDATE tipos_documento SET deleted_at = NOW(), estado = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
