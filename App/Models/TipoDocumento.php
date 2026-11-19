<?php

namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

/**
 * Catálogo de tipos de documento.
 *
 * TAREA E07-1: además de `obtenerActivos()` (consumida por ventanilla y portal),
 * implementa el CRUD completo para el módulo de administración.
 */
class TipoDocumento implements InterfaceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Listar tipos de documentos habilitados
    // OPT-02: fuente única del catálogo (la usan MesaPartes y EntregaModel).
    public function obtenerActivos(): array
    {
        try {
            $sql = "SELECT id_tipo_doc, nombre FROM tipos_documento WHERE estado = 'Activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Todos los tipos con el número de documentos asociados.
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT td.*,
                           (SELECT COUNT(*) FROM documentos d WHERE d.id_tipo_doc = td.id_tipo_doc) AS total_documentos
                    FROM tipos_documento td
                    ORDER BY td.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en TipoDocumento::getAll: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int|string $id): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tipos_documento WHERE id_tipo_doc = :id");
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create(array $datos): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tipos_documento (nombre, descripcion, estado)
                 VALUES (:nombre, :descripcion, :estado)"
            );
            return $stmt->execute([
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?? null,
                ':estado'      => $datos['estado'] ?? 'Activo',
            ]);
        } catch (PDOException $e) {
            error_log('Error en TipoDocumento::create: ' . $e->getMessage());
            return false;
        }
    }

    public function update(array $datos, int|string $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE tipos_documento
                 SET nombre = :nombre, descripcion = :descripcion, estado = :estado
                 WHERE id_tipo_doc = :id"
            );
            return $stmt->execute([
                ':id'          => $id,
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?? null,
                ':estado'      => $datos['estado'] ?? 'Activo',
            ]);
        } catch (PDOException $e) {
            error_log('Error en TipoDocumento::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Solo se elimina físicamente si no tiene documentos asociados.
     */
    public function delete(int|string $id): bool
    {
        try {
            if ($this->contarDocumentos((int) $id) > 0) {
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM tipos_documento WHERE id_tipo_doc = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cambiarEstado(int|string $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("UPDATE tipos_documento SET estado = :estado WHERE id_tipo_doc = :id");
            return $stmt->execute([':id' => $id, ':estado' => $estado]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existeNombre(string $nombre, int|string|null $exceptoId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tipos_documento WHERE LOWER(nombre) = LOWER(:nombre)";
            $params = [':nombre' => trim($nombre)];

            if ($exceptoId !== null) {
                $sql .= " AND id_tipo_doc <> :id";
                $params[':id'] = $exceptoId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function contarDocumentos(int $idTipo): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM documentos WHERE id_tipo_doc = :id");
            $stmt->execute([':id' => $idTipo]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function ultimoId(): ?int
    {
        try {
            $id = (int) $this->db->lastInsertId();
            return $id > 0 ? $id : null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
