<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/InterfaceModel.php';

class Rol implements InterfaceModel {
    private PDO $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function obtenerTodos(): array {
        try {
            $sql = "SELECT * FROM roles ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAll(): array {
        return $this->obtenerTodos();
    }

    public function obtenerPorId(int $id): array {
        try {
            $sql = "SELECT * FROM roles WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch();
            return $resultado ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById(int | string $id): array {
        return $this->obtenerPorId((int)$id);
    }

    public function obtenerPorSlug(string $slug): array {
        try {
            $sql = "SELECT * FROM roles WHERE slug = :slug";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':slug' => $slug]);
            $resultado = $stmt->fetch();
            return $resultado ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrar(array $datos): bool {
        try {
            $sql = "INSERT INTO roles (nombre_rol, slug, estado) VALUES (:nombre_rol, :slug, :estado)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre_rol' => $datos['nombre_rol'],
                ':slug'       => $datos['slug'],
                ':estado'     => $datos['estado'] ?? 1
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function create(array $datos): bool {
        return $this->registrar($datos);
    }

    public function actualizar(int $id, array $datos): bool {
        try {
            $sql = "UPDATE roles SET nombre_rol = :nombre_rol, slug = :slug, estado = :estado WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'         => $id,
                ':nombre_rol' => $datos['nombre_rol'],
                ':slug'       => $datos['slug'],
                ':estado'     => $datos['estado']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(array $datos, int | string $id): bool {
        return $this->actualizar((int)$id, $datos);
    }

    public function cambiarEstado(int $id, int $estado): bool {
        try {
            $sql = "UPDATE roles SET estado = :estado WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id'     => $id,
                ':estado' => $estado
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int | string $id): bool {
        return false;
    }
}