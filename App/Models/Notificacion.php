<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Support\Catalogos;
use Src\Core\Conexion;

/**
 * Notificaciones del usuario autenticado (SID-08 / TAREA E08-1).
 *
 * El header usa `sinLeer()`/`ultimas()`; el controlador usa `listar()`,
 * `marcarLeida()` y `marcarTodasLeidas()`. Los flujos de derivación y
 * atención generan filas con `crear()`/`paraArea()`.
 */
class Notificacion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * ¿Cuántas notificaciones sin leer tiene el usuario?
     * Ante cualquier fallo devuelve 0: el header no puede romper una pantalla
     * por un problema de consultas secundarias.
     */
    public function sinLeer(int $idUsuario): int
    {
        if ($idUsuario <= 0) {
            return 0;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = false'
            );
            $stmt->execute([':id_usuario' => $idUsuario]);

            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Últimas notificaciones sin leer, para el desplegable del header.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ultimas(int $idUsuario, int $cuantas = 5): array
    {
        return $this->listar($idUsuario, true, $cuantas);
    }

    /**
     * Lista de notificaciones con el código del expediente (para el enlace).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listar(int $idUsuario, bool $soloNoLeidas = false, int $cuantas = 10): array
    {
        if ($idUsuario <= 0) {
            return [];
        }

        $cuantas = max(1, min(50, $cuantas));

        try {
            $sql = 'SELECT n.id_notificacion, n.titulo, n.mensaje, n.leida, n.creado_en,
                           n.id_expediente, e.codigo_expediente
                    FROM notificaciones n
                    LEFT JOIN expedientes e ON e.id_expediente = n.id_expediente
                    WHERE n.id_usuario = :id_usuario';

            if ($soloNoLeidas) {
                $sql .= ' AND n.leida = false';
            }

            $sql .= ' ORDER BY n.creado_en DESC, n.id_notificacion DESC LIMIT ' . $cuantas;

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_usuario' => $idUsuario]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Alta de una notificación. Devuelve el id o null si falla.
     */
    public function crear(array $datos): ?int
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO notificaciones (id_usuario, id_persona, id_expediente, titulo, mensaje, leida, creado_en)
                 VALUES (:id_usuario, :id_persona, :id_expediente, :titulo, :mensaje, false, NOW())"
            );
            $stmt->execute([
                ':id_usuario'    => !empty($datos['id_usuario']) ? (int) $datos['id_usuario'] : null,
                ':id_persona'    => !empty($datos['id_persona']) ? (int) $datos['id_persona'] : null,
                ':id_expediente' => !empty($datos['id_expediente']) ? (int) $datos['id_expediente'] : null,
                ':titulo'        => Catalogos::ajustar('notificaciones.titulo', (string) ($datos['titulo'] ?? 'Notificación')),
                ':mensaje'       => Catalogos::ajustar('notificaciones.mensaje', (string) ($datos['mensaje'] ?? '')),
            ]);

            $id = (int) $this->db->lastInsertId();
            return $id > 0 ? $id : null;
        } catch (PDOException $e) {
            error_log('Error en Notificacion::crear: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notifica a todos los usuarios activos de un área.
     */
    public function paraArea(int $idArea, array $datos): int
    {
        if ($idArea <= 0) {
            return 0;
        }

        try {
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_area = :id_area AND estado = 'Activo'");
            $stmt->execute([':id_area' => $idArea]);
            $creadas = 0;

            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $idUsuario) {
                $datos['id_usuario'] = (int) $idUsuario;
                if ($this->crear($datos) !== null) {
                    $creadas++;
                }
            }

            return $creadas;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function marcarLeida(int $idNotificacion, int $idUsuario): bool
    {
        if ($idNotificacion <= 0 || $idUsuario <= 0) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE notificaciones
                 SET leida = true, fecha_lectura = NOW()
                 WHERE id_notificacion = :id AND id_usuario = :id_usuario"
            );
            return $stmt->execute([':id' => $idNotificacion, ':id_usuario' => $idUsuario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function marcarTodasLeidas(int $idUsuario): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE notificaciones
                 SET leida = true, fecha_lectura = NOW()
                 WHERE id_usuario = :id_usuario AND leida = false"
            );
            return $stmt->execute([':id_usuario' => $idUsuario]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
