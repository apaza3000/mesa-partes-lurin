<?php

namespace App\Models;

use Src\Core\Conexion;

/**
 * Notificaciones del usuario autenticado (SID-08).
 *
 * Sustituye en el header los contadores falsos que estaban escritos a mano
 * ("3" y "15") por datos reales de la tabla `notificaciones`.
 */
class Notificacion
{
    private \PDO $db;

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
        if ($idUsuario <= 0) {
            return [];
        }

        $cuantas = max(1, min(20, $cuantas));

        try {
            $stmt = $this->db->prepare(
                'SELECT id_notificacion, titulo, mensaje, creado_en
                 FROM notificaciones
                 WHERE id_usuario = :id_usuario AND leida = false
                 ORDER BY creado_en DESC
                 LIMIT ' . $cuantas
            );
            $stmt->execute([':id_usuario' => $idUsuario]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
