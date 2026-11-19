<?php

namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Auditoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function insertar($id_usuario, $modulo, $accion, $tabla, $id_registro, $descripcion, $ip)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO auditoria
                (id_usuario, modulo, accion, tabla_afectada, id_registro, descripcion, ip)
             VALUES
                (:id_usuario, :modulo, :accion, :tabla_afectada, :id_registro, :descripcion, :ip)"
        );
        return $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':modulo' => $modulo,
            ':accion' => $accion,
            ':tabla_afectada' => $tabla,
            ':id_registro' => $id_registro,
            ':descripcion' => $descripcion,
            ':ip' => $ip
        ]);
    }

    /**
     * TAREA E10-1: consulta paginada del visor de auditoría.
     *
     * @param array{fecha_desde?:string,fecha_hasta?:string,id_usuario?:int|string,modulo?:string,accion?:string,texto?:string} $filtros
     * @return array<int, array<string, mixed>>
     */
    public function listar(array $filtros, int $pagina = 1, int $porPagina = 25): array
    {
        [$where, $params] = $this->construirFiltros($filtros);

        $pagina = max(1, $pagina);
        $porPagina = max(5, min(100, $porPagina));
        $offset = ($pagina - 1) * $porPagina;

        try {
            $sql = "SELECT a.*, u.username,
                           COALESCE(NULLIF(p.razon_social, ''), CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS usuario_nombre
                    FROM auditoria a
                    LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
                    LEFT JOIN personas p ON p.id_persona = u.id_persona
                    {$where}
                    ORDER BY a.fecha DESC, a.id_auditoria DESC
                    LIMIT {$porPagina} OFFSET {$offset}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Auditoria::listar: ' . $e->getMessage());
            return [];
        }
    }

    public function contar(array $filtros = []): int
    {
        [$where, $params] = $this->construirFiltros($filtros);

        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM auditoria a LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario {$where}"
            );
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtener(int $id): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT a.*, u.username,
                        COALESCE(NULLIF(p.razon_social, ''), CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS usuario_nombre
                 FROM auditoria a
                 LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
                 LEFT JOIN personas p ON p.id_persona = u.id_persona
                 WHERE a.id_auditoria = :id"
            );
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Módulos y acciones distintos (para los filtros del visor).
     *
     * @return array{modulos:string[],acciones:string[]}
     */
    public function catalogos(): array
    {
        try {
            $modulos = $this->db->query("SELECT DISTINCT modulo FROM auditoria ORDER BY modulo ASC")->fetchAll(PDO::FETCH_COLUMN);
            $acciones = $this->db->query("SELECT DISTINCT accion FROM auditoria ORDER BY accion ASC")->fetchAll(PDO::FETCH_COLUMN);

            return ['modulos' => $modulos ?: [], 'acciones' => $acciones ?: []];
        } catch (PDOException $e) {
            return ['modulos' => [], 'acciones' => []];
        }
    }

    /**
     * Usuarios que aparecen en el filtro del visor.
     *
     * @return array<int, array{id_usuario:int,username:string,nombre:string}>
     */
    public function usuarios(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT u.id_usuario, u.username,
                        COALESCE(NULLIF(p.razon_social, ''), CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno)) AS nombre
                 FROM usuarios u
                 LEFT JOIN personas p ON p.id_persona = u.id_persona
                 ORDER BY u.username ASC"
            );

            return array_map(static fn(array $u): array => [
                'id_usuario' => (int) $u['id_usuario'],
                'username'   => (string) $u['username'],
                'nombre'     => trim((string) ($u['nombre'] ?? '')),
            ], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function construirFiltros(array $filtros): array
    {
        $where = 'WHERE 1 = 1';
        $params = [];

        $desde = trim((string) ($filtros['fecha_desde'] ?? ''));
        if ($desde !== '') {
            $where .= ' AND a.fecha >= :fecha_desde';
            $params[':fecha_desde'] = $desde . ' 00:00:00';
        }

        $hasta = trim((string) ($filtros['fecha_hasta'] ?? ''));
        if ($hasta !== '') {
            $where .= ' AND a.fecha <= :fecha_hasta';
            $params[':fecha_hasta'] = $hasta . ' 23:59:59';
        }

        $idUsuario = (int) ($filtros['id_usuario'] ?? 0);
        if ($idUsuario > 0) {
            $where .= ' AND a.id_usuario = :id_usuario';
            $params[':id_usuario'] = $idUsuario;
        }

        $modulo = trim((string) ($filtros['modulo'] ?? ''));
        if ($modulo !== '') {
            $where .= ' AND a.modulo = :modulo';
            $params[':modulo'] = $modulo;
        }

        $accion = trim((string) ($filtros['accion'] ?? ''));
        if ($accion !== '') {
            $where .= ' AND a.accion = :accion';
            $params[':accion'] = $accion;
        }

        $texto = trim((string) ($filtros['texto'] ?? ''));
        if ($texto !== '') {
            $where .= ' AND (a.descripcion LIKE :texto OR u.username LIKE :texto OR a.ip LIKE :texto)';
            $params[':texto'] = '%' . $texto . '%';
        }

        return [$where, $params];
    }
}
