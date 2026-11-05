<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Support\Catalogos;
use Src\Core\Conexion;

class Observacion implements InterfaceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT o.*, e.codigo_expediente, e.asunto AS asunto_expediente,
                           CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')) AS usuario_nombre
                    FROM observaciones o
                    INNER JOIN expedientes e ON o.id_expediente = e.id_expediente
                    LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario
                    LEFT JOIN personas p ON u.id_persona = p.id_persona
                    ORDER BY o.id_observacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en Observacion::getAll: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int|string $id): array
    {
        try {
            $sql = "SELECT o.*, e.codigo_expediente, e.asunto AS asunto_expediente, e.estado_actual AS estado_expediente
                    FROM observaciones o
                    INNER JOIN expedientes e ON o.id_expediente = e.id_expediente
                    WHERE o.id_observacion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: [];
        } catch (PDOException $e) {
            error_log('Error en Observacion::getById: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alta simple de una observación (sin efectos sobre el expediente).
     * Para el flujo completo de la Épica 5 usar observar().
     */
    public function create(array $datos): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO observaciones (id_expediente, id_usuario, descripcion, estado, fecha_registro)
                 VALUES (:id_exp, :id_usuario, :descripcion, :estado, NOW())"
            );
            return $stmt->execute([
                ':id_exp'       => (int) ($datos['id_expediente'] ?? 0),
                ':id_usuario'   => (int) ($datos['id_usuario'] ?? 0),
                ':descripcion'  => (string) ($datos['descripcion'] ?? ''),
                ':estado'       => $datos['estado'] ?? 'Pendiente',
            ]);
        } catch (PDOException $e) {
            error_log('Error en Observacion::create: ' . $e->getMessage());
            return false;
        }
    }

    public function update(array $datos, int|string $id): bool
    {
        return false;
    }

    public function delete(int|string $id): bool
    {
        return false;
    }

    /**
     * Listar las observaciones de un expediente (para la trazabilidad del área y del ciudadano)
     */
    public function listarPorExpediente(int $idExpediente): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT o.*, u.username AS usuario_username
                 FROM observaciones o
                 LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario
                 WHERE o.id_expediente = :id_exp
                 ORDER BY o.id_observacion DESC"
            );
            $stmt->execute([':id_exp' => $idExpediente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en Observacion::listarPorExpediente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * TAREA E05-1: Observar un expediente recepcionado por el área.
     * Registra la observación ('Pendiente'), pasa el expediente a 'Observado',
     * anota el requerimiento en la derivación, deja historial, genera la
     * notificación al administrado y registra auditoría. Todo en una sola transacción.
     *
     * @param int    $idDerivacion derivación activa del área (Recibido/En_Atencion)
     * @param int    $idUsuario    usuario del área que observa
     * @param string $descripcion  motivos o requisitos faltantes
     */
    public function observar(int $idDerivacion, int $idUsuario, string $descripcion): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Bloquear y validar la derivación
            $stmt = $this->db->prepare("SELECT * FROM derivaciones WHERE id_derivacion = :id FOR UPDATE");
            $stmt->execute([':id' => $idDerivacion]);
            $derivacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$derivacion) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La derivación no existe.'];
            }

            if (!in_array($derivacion['estado'], ['Recibido', 'En_Atencion'], true)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Solo se pueden observar expedientes ya recepcionados por el área (estado Recibido o En Atención); estado actual: ' . $derivacion['estado'] . '.'];
            }

            $idExpediente = (int) $derivacion['id_expediente'];

            // 2. Expediente con su estado real (para el historial y la notificación)
            $stmt = $this->db->prepare(
                "SELECT codigo_expediente, asunto, estado_actual, id_remitente
                 FROM expedientes
                 WHERE id_expediente = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El expediente asociado a la derivación no existe.'];
            }

            if ($expediente['estado_actual'] === 'Observado') {
                $this->db->rollBack();
                return ['success' => false, 'message' => "El expediente {$expediente['codigo_expediente']} ya se encuentra observado y pendiente de subsanación."];
            }

            if (!in_array($expediente['estado_actual'], ['Registrado', 'En_Proceso'], true)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede observar un expediente en estado ' . $expediente['estado_actual'] . '.'];
            }

            // 3. Registro de la observación
            $stmtObs = $this->db->prepare(
                "INSERT INTO observaciones (id_expediente, id_usuario, descripcion, estado, fecha_registro)
                 VALUES (:id_exp, :id_usuario, :descripcion, 'Pendiente', NOW())"
            );
            $stmtObs->execute([
                ':id_exp'      => $idExpediente,
                ':id_usuario'  => $idUsuario,
                ':descripcion' => $descripcion,
            ]);
            $idObservacion = (int) $this->db->lastInsertId();

            // 4. Expediente observado
            $stmtExp = $this->db->prepare(
                "UPDATE expedientes
                 SET estado_actual = 'Observado',
                     actualizado_en = NOW()
                 WHERE id_expediente = :id_exp"
            );
            $stmtExp->execute([':id_exp' => $idExpediente]);

            // 5. Anotación del requerimiento en la derivación (el área sigue a la espera de la subsanación)
            // VAL-01: la columna es VARCHAR(1000); se recorta la nota acumulada para
            // no provocar 1406 al concatenar varias observaciones.
            $notaObservacion = trim(
                ($derivacion['observaciones'] ?? '') .
                "\n[OBSERVACION " . date('Y-m-d H:i') . ']: ' . $descripcion
            );
            $notaObservacion = Catalogos::ajustar('derivaciones.observaciones', $notaObservacion);
            $stmtDer = $this->db->prepare(
                "UPDATE derivaciones
                 SET observaciones = :obs
                 WHERE id_derivacion = :id"
            );
            $stmtDer->execute([':obs' => $notaObservacion, ':id' => $idDerivacion]);

            // 6. Historial de estado
            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, :anterior, 'Observado', :id_usuario, :comentario, NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':anterior'   => $expediente['estado_actual'],
                ':id_usuario' => $idUsuario,
                ':comentario' => 'Expediente observado por el área. Observación #' . $idObservacion . ': '
                    . mb_substr($descripcion, 0, 900),
            ]);

            // 7. Notificación al administrado (remitente del expediente)
            $idPersona = (int) ($expediente['id_remitente'] ?? 0);
            $areaDestino = $this->nombreArea((int) $derivacion['id_area_destino']);
            $stmtNot = $this->db->prepare(
                "INSERT INTO notificaciones (id_usuario, id_persona, id_expediente, titulo, mensaje, leida, creado_en)
                 VALUES (NULL, :id_persona, :id_exp, :titulo, :mensaje, false, NOW())"
            );
            $stmtNot->execute([
                ':id_persona' => $idPersona > 0 ? $idPersona : null,
                ':id_exp'     => $idExpediente,
                ':titulo'     => mb_substr('Expediente ' . $expediente['codigo_expediente'] . ' observado — requiere subsanación', 0, 200),
                ':mensaje'    => mb_substr(
                    'Su expediente ' . $expediente['codigo_expediente'] . ' (' . $expediente['asunto'] . ') '
                    . 'ha sido observado por ' . ($areaDestino !== '' ? $areaDestino : 'el área responsable')
                    . '. Requisitos u observaciones a subsanar: ' . $descripcion
                    . ' Presente lo requerido por mesa de partes dentro del plazo de ley para continuar con el trámite.',
                    0,
                    1000
                ),
            ]);
            $idNotificacion = (int) $this->db->lastInsertId();

            // 8. Auditoría
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'area',
                'observar',
                'expedientes',
                $idExpediente,
                "Expediente {$expediente['codigo_expediente']} observado con requerimiento #{$idObservacion}. Notificación #{$idNotificacion} generada al administrado.",
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'success'         => true,
                'id_observacion'  => $idObservacion,
                'id_notificacion' => $idNotificacion,
                'codigo'          => $expediente['codigo_expediente'],
                'message'         => "Expediente {$expediente['codigo_expediente']} observado correctamente. Se notificó al administrado para su subsanación.",
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Observacion::observar: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al observar el expediente: ' . $e->getMessage()];
        }
    }

    /**
     * Nombre del área (para las notificaciones); devuelve cadena vacía si no existe
     */
    private function nombreArea(int $idArea): string
    {
        if ($idArea <= 0) {
            return '';
        }
        try {
            $stmt = $this->db->prepare("SELECT nombre FROM areas WHERE id_area = :id");
            $stmt->execute([':id' => $idArea]);
            return (string) ($stmt->fetchColumn() ?: '');
        } catch (PDOException $e) {
            return '';
        }
    }
}
