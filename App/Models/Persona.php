<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Persona
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
                'SELECT * FROM personas ORDER BY id_persona DESC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById(int|string $id): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM personas WHERE id_persona = :id');
            $stmt->execute([':id' => (int) $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create(array $datos): int|bool
    {
        try {
            $sql = 'INSERT INTO personas (
                tipo_persona, tipo_documento, numero_documento, nombres, apellido_paterno,
                apellido_materno, razon_social, email, telefono, direccion, estado
            ) VALUES (
                :tipo_persona, :tipo_documento, :numero_documento, :nombres, :apellido_paterno,
                :apellido_materno, :razon_social, :email, :telefono, :direccion, :estado
            )';
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':tipo_persona' => $datos['tipo_persona'] ?? 'Natural',
                ':tipo_documento' => $datos['tipo_documento'] ?? 'DNI',
                ':numero_documento' => $datos['numero_documento'] ?? '',
                ':nombres' => $datos['nombres'] ?? null,
                ':apellido_paterno' => $datos['apellido_paterno'] ?? null,
                ':apellido_materno' => $datos['apellido_materno'] ?? null,
                ':razon_social' => $datos['razon_social'] ?? null,
                ':email' => $datos['email'] ?? null,
                ':telefono' => $datos['telefono'] ?? null,
                ':direccion' => $datos['direccion'] ?? null,
                ':estado' => $datos['estado'] ?? 'Activo'
            ]);

            if (!$resultado) {
                return false;
            }

            $id = $this->db->lastInsertId();
            return $id === false || $id === '0' ? false : (int) $id;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(array $datos, int|string $id): bool
    {
        try {
            $sql = 'UPDATE personas SET
                tipo_persona = :tipo_persona,
                tipo_documento = :tipo_documento,
                numero_documento = :numero_documento,
                nombres = :nombres,
                apellido_paterno = :apellido_paterno,
                apellido_materno = :apellido_materno,
                razon_social = :razon_social,
                email = :email,
                telefono = :telefono,
                direccion = :direccion,
                estado = :estado,
                actualizado_en = CURRENT_TIMESTAMP
                WHERE id_persona = :id';
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => (int) $id,
                ':tipo_persona' => $datos['tipo_persona'] ?? 'Natural',
                ':tipo_documento' => $datos['tipo_documento'] ?? 'DNI',
                ':numero_documento' => $datos['numero_documento'] ?? '',
                ':nombres' => $datos['nombres'] ?? null,
                ':apellido_paterno' => $datos['apellido_paterno'] ?? null,
                ':apellido_materno' => $datos['apellido_materno'] ?? null,
                ':razon_social' => $datos['razon_social'] ?? null,
                ':email' => $datos['email'] ?? null,
                ':telefono' => $datos['telefono'] ?? null,
                ':direccion' => $datos['direccion'] ?? null,
                ':estado' => $datos['estado'] ?? 'Activo'
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int|string $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM personas WHERE id_persona = :id');
            return $stmt->execute([':id' => (int) $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existeDocumento(string $tipoDocumento, string $numero, ?int $id = null): bool
    {
        try {
            $sql = 'SELECT 1 FROM personas WHERE tipo_documento = :tipo_documento AND numero_documento = :numero_documento';
            $params = [
                ':tipo_documento' => $tipoDocumento,
                ':numero_documento' => $numero,
            ];

            if ($id !== null) {
                $sql .= ' AND id_persona != :id';
                $params[':id'] = $id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarPorDocumento(string $numero): ?array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM personas WHERE numero_documento = :numero_documento LIMIT 1'
            );
            $stmt->execute([':numero_documento' => $numero]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            return $persona ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getExpedientes(int|string $id): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id_expediente, codigo_expediente, asunto, prioridad, canal_ingreso, estado_actual, fecha_registro
                 FROM expedientes
                 WHERE id_remitente = :id
                 ORDER BY fecha_registro DESC'
            );
            $stmt->execute([':id' => (int) $id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrarAuditoria(?int $idUsuario, string $modulo, string $accion, string $tabla, ?int $idRegistro, string $descripcion): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO auditoria (id_usuario, modulo, accion, tabla_afectada, id_registro, descripcion, ip)
                 VALUES (:id_usuario, :modulo, :accion, :tabla_afectada, :id_registro, :descripcion, :ip)'
            );

            return $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':modulo' => $modulo,
                ':accion' => $accion,
                ':tabla_afectada' => $tabla,
                ':id_registro' => $idRegistro,
                ':descripcion' => $descripcion,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}