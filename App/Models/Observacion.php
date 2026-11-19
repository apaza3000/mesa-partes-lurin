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
                 VALUES (:id_usuario, :id_persona, :id_exp, :titulo, :mensaje, false, NOW())"
            );
            $stmtNot->execute([
                ':id_usuario' => $this->usuarioDePersona($idPersona),
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
     * TAREA E05-2: Subsanación presentada por el administrado desde el portal público.
     *
     * Pasa la observación pendiente a 'Subsanada', guarda los documentos de
     * sustento (si los hay), deja historial y notifica a los usuarios del área
     * que observó el expediente. El expediente permanece 'Observado' hasta que
     * el área levante la observación.
     *
     * @param array<int, array<string, mixed>> $archivos adjuntos ya movidos a public/uploads
     */
    public function subsanar(string $codigo, string $comentario, array $archivos = [], ?int $idUsuario = null): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM expedientes WHERE codigo_expediente = :codigo FOR UPDATE");
            $stmt->execute([':codigo' => $codigo]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se encontró el expediente indicado.'];
            }

            if ($expediente['estado_actual'] !== 'Observado') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El expediente no se encuentra observado; no requiere subsanación.'];
            }

            $idExpediente = (int) $expediente['id_expediente'];

            $stmtObs = $this->db->prepare(
                "SELECT * FROM observaciones
                 WHERE id_expediente = :id_exp AND estado = 'Pendiente'
                 ORDER BY id_observacion ASC LIMIT 1 FOR UPDATE"
            );
            $stmtObs->execute([':id_exp' => $idExpediente]);
            $observacion = $stmtObs->fetch(PDO::FETCH_ASSOC);

            if (!$observacion) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No hay observaciones pendientes para este expediente.'];
            }

            $idObservacion = (int) $observacion['id_observacion'];

            $stmtUp = $this->db->prepare(
                "UPDATE observaciones
                 SET estado = 'Subsanada',
                     fecha_subsanacion = NOW()
                 WHERE id_observacion = :id"
            );
            $stmtUp->execute([':id' => $idObservacion]);

            $documentosCreados = $this->registrarDocumentosSubsanacion($idExpediente, $idObservacion, $archivos, $idUsuario);

            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, 'Observado', 'Observado', :id_usuario, :comentario, NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':id_usuario' => $idUsuario,
                ':comentario' => Catalogos::ajustar(
                    'historial_expediente.comentario',
                    'Subsanación presentada por el administrado para la observación #' . $idObservacion
                    . '. Motivo: ' . $comentario
                    . ($documentosCreados > 0 ? ' (con documentos adjuntos).' : '.')
                ),
            ]);

            $this->notificarArea(
                $idExpediente,
                $expediente['codigo_expediente'],
                'Subsanación presentada — ' . $expediente['codigo_expediente'],
                'El administrado presentó la subsanación de la observación #' . $idObservacion
                . ' del expediente ' . $expediente['codigo_expediente'] . '. Motivo: ' . $comentario
            );

            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'consulta',
                'subsanar',
                'observaciones',
                $idObservacion,
                'Subsanación pública presentada para el expediente ' . $expediente['codigo_expediente']
                . ' (' . $documentosCreados . ' documento(s)).',
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'success'        => true,
                'id_observacion' => $idObservacion,
                'codigo'         => $expediente['codigo_expediente'],
                'message'        => 'Subsanación registrada. El área revisará los documentos presentados.',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Observacion::subsanar: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo registrar la subsanación. Intente nuevamente.'];
        }
    }


    /**
     * Observaciones pendientes de un expediente identificado por su código.
     */
    public function listarPendientesPorCodigo(string $codigo): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT o.* FROM observaciones o
                 INNER JOIN expedientes e ON e.id_expediente = o.id_expediente
                 WHERE e.codigo_expediente = :codigo AND o.estado = 'Pendiente'
                 ORDER BY o.id_observacion ASC"
            );
            $stmt->execute([':codigo' => $codigo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Registra los adjuntos de la subsanación como documentos del expediente.
     */
    private function registrarDocumentosSubsanacion(int $idExpediente, int $idObservacion, array $archivos, ?int $idUsuario): int
    {
        if ($archivos === []) {
            return 0;
        }

        $stmtTipo = $this->db->prepare("SELECT id_tipo_doc FROM tipos_documento WHERE nombre = 'Otros' LIMIT 1");
        $stmtTipo->execute();
        $idTipo = (int) ($stmtTipo->fetchColumn() ?: 0);

        if ($idTipo <= 0) {
            $stmtTipo = $this->db->prepare("SELECT id_tipo_doc FROM tipos_documento WHERE estado = 'Activo' ORDER BY id_tipo_doc ASC LIMIT 1");
            $stmtTipo->execute();
            $idTipo = (int) ($stmtTipo->fetchColumn() ?: 0);
        }

        if ($idTipo <= 0) {
            return 0;
        }

        $stmtDoc = $this->db->prepare(
            "INSERT INTO documentos
                (id_expediente, id_tipo_doc, numero_documento, asunto, folios, fecha_documento,
                 tipo_documento_registro, es_principal, creado_por, creado_en)
             VALUES
                (:id_exp, :id_tipo, :numero, :asunto, :folios, CURDATE(), 'Ingresado', false, :creado_por, NOW())"
        );
        $stmtDoc->execute([
            ':id_exp'     => $idExpediente,
            ':id_tipo'    => $idTipo,
            ':numero'     => 'SUBSANACION-' . $idObservacion,
            ':asunto'     => Catalogos::ajustar('documentos.asunto', 'Subsanación de la observación #' . $idObservacion),
            ':folios'     => max(1, count($archivos)),
            ':creado_por' => $idUsuario,
        ]);
        $idDocumento = (int) $this->db->lastInsertId();

        $stmtArch = $this->db->prepare(
            "INSERT INTO archivos_adjuntos
                (id_documento, nombre_original, nombre_archivo, ruta_archivo, extension, mime_type, tamanio, hash_archivo, creado_por, creado_en)
             VALUES
                (:id_doc, :nombre_original, :nombre_archivo, :ruta, :extension, :mime, :tamanio, :hash, :creado_por, NOW())"
        );

        foreach ($archivos as $archivo) {
            $stmtArch->execute([
                ':id_doc'         => $idDocumento,
                ':nombre_original'=> (string) ($archivo['nombre_original'] ?? 'adjunto'),
                ':nombre_archivo' => (string) ($archivo['nombre_archivo'] ?? ''),
                ':ruta'           => (string) ($archivo['ruta_archivo'] ?? ''),
                ':extension'      => (string) ($archivo['extension'] ?? ''),
                ':mime'           => (string) ($archivo['mime_type'] ?? 'application/octet-stream'),
                ':tamanio'        => (int) ($archivo['tamanio'] ?? 0),
                ':hash'           => (string) ($archivo['hash_archivo'] ?? ''),
                ':creado_por'     => $idUsuario,
            ]);
        }

        return 1;
    }

    /**
     * Notifica a todos los usuarios activos del área que tiene el expediente.
     */
    private function notificarArea(int $idExpediente, string $codigo, string $titulo, string $mensaje): void
    {
        $stmtArea = $this->db->prepare(
            "SELECT id_area_destino FROM derivaciones
             WHERE id_expediente = :id_exp
             ORDER BY id_derivacion DESC LIMIT 1"
        );
        $stmtArea->execute([':id_exp' => $idExpediente]);
        $idArea = (int) ($stmtArea->fetchColumn() ?: 0);

        if ($idArea <= 0) {
            return;
        }

        $stmtUsers = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_area = :id_area AND estado = 'Activo'");
        $stmtUsers->execute([':id_area' => $idArea]);

        $stmtNot = $this->db->prepare(
            "INSERT INTO notificaciones (id_usuario, id_expediente, titulo, mensaje, leida, creado_en)
             VALUES (:id_usuario, :id_exp, :titulo, :mensaje, false, NOW())"
        );

        foreach ($stmtUsers->fetchAll(PDO::FETCH_COLUMN) as $idUsuario) {
            $stmtNot->execute([
                ':id_usuario' => (int) $idUsuario,
                ':id_exp'     => $idExpediente,
                ':titulo'     => Catalogos::ajustar('notificaciones.titulo', $titulo),
                ':mensaje'    => Catalogos::ajustar('notificaciones.mensaje', $mensaje),
            ]);
        }
    }

    /**
     * Usuario vinculado a una persona (si el administrado tiene cuenta).
     */
    private function usuarioDePersona(int $idPersona): ?int
    {
        if ($idPersona <= 0) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_persona = :id_persona LIMIT 1");
            $stmt->execute([':id_persona' => $idPersona]);
            $id = (int) ($stmt->fetchColumn() ?: 0);
            return $id > 0 ? $id : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /* ============================================================
     * TAREA E05-2: SUBSANACIÓN DE OBSERVACIÓN (portal público)
     * El administrado presenta el descargo y, opcionalmente, adjuntos.
     * La observación pasa a 'Subsanada'; el expediente sigue 'Observado'
     * hasta que el área la levante (levantar()).
     * ============================================================ */

    /**
     * Expediente por código junto con sus observaciones vigentes.
     * Devuelve ['expediente' => array|null, 'observaciones' => array].
     */
    public function buscarPorCodigo(string $codigo): array
    {
        $codigo = trim($codigo);

        if ($codigo === '') {
            return ['expediente' => null, 'observaciones' => []];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT e.id_expediente, e.codigo_expediente, e.asunto, e.estado_actual,
                        e.fecha_registro, e.id_remitente,
                        p.tipo_documento, p.numero_documento,
                        COALESCE(NULLIF(p.razon_social, ''),
                            TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) AS remitente_nombre
                 FROM expedientes e
                 INNER JOIN personas p ON p.id_persona = e.id_remitente
                 WHERE e.codigo_expediente = :codigo
                 LIMIT 1"
            );
            $stmt->execute([':codigo' => $codigo]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if (!$expediente) {
                return ['expediente' => null, 'observaciones' => []];
            }

            $stmt = $this->db->prepare(
                "SELECT o.*, u.username AS observador
                 FROM observaciones o
                 LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
                 WHERE o.id_expediente = :id_exp
                   AND o.estado IN ('Pendiente', 'Subsanada')
                 ORDER BY o.id_observacion ASC"
            );
            $stmt->execute([':id_exp' => (int) $expediente['id_expediente']]);

            return [
                'expediente'    => $expediente,
                'observaciones' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (PDOException $e) {
            error_log('Error en Observacion::buscarPorCodigo: ' . $e->getMessage());
            return ['expediente' => null, 'observaciones' => []];
        }
    }

    /**
     * El área levanta una observación ya subsanada. Si no quedan otras
     * observaciones vigentes, el expediente vuelve a 'En_Proceso'.
     */
    public function levantar(int $idObservacion, int $idUsuario): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "SELECT o.*, e.codigo_expediente, e.asunto AS asunto_expediente,
                        e.estado_actual, e.id_remitente
                 FROM observaciones o
                 INNER JOIN expedientes e ON e.id_expediente = o.id_expediente
                 WHERE o.id_observacion = :id
                 FOR UPDATE"
            );
            $stmt->execute([':id' => $idObservacion]);
            $observacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$observacion) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La observación indicada no existe.'];
            }

            if ($observacion['estado'] !== 'Subsanada') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Solo se pueden levantar observaciones en estado Subsanada.'];
            }

            $idExpediente = (int) $observacion['id_expediente'];

            $stmt = $this->db->prepare(
                "UPDATE observaciones SET estado = 'Levantada' WHERE id_observacion = :id"
            );
            $stmt->execute([':id' => $idObservacion]);

            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM observaciones
                 WHERE id_expediente = :id_exp
                   AND id_observacion <> :id_obs
                   AND estado IN ('Pendiente', 'Subsanada')"
            );
            $stmt->execute([':id_exp' => $idExpediente, ':id_obs' => $idObservacion]);
            $restantes = (int) $stmt->fetchColumn();

            $nuevoEstado = $observacion['estado_actual'];
            if ($restantes === 0 && $observacion['estado_actual'] === 'Observado') {
                $stmt = $this->db->prepare(
                    "UPDATE expedientes
                     SET estado_actual = 'En_Proceso', actualizado_en = NOW()
                     WHERE id_expediente = :id_exp"
                );
                $stmt->execute([':id_exp' => $idExpediente]);
                $nuevoEstado = 'En_Proceso';
            }

            $stmt = $this->db->prepare(
                "INSERT INTO historial_expediente
                 (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, :anterior, :nuevo, :id_usuario, :comentario, NOW())"
            );
            $stmt->execute([
                ':id_exp'     => $idExpediente,
                ':anterior'   => $observacion['estado_actual'],
                ':nuevo'      => $nuevoEstado,
                ':id_usuario' => $idUsuario,
                ':comentario' => Catalogos::ajustar(
                    'historial_expediente.comentario',
                    'Observación #' . $idObservacion . ' levantada por el área tras verificar la subsanación.'
                        . ($restantes === 0 ? ' El expediente reanuda su trámite.' : ' Quedan ' . $restantes . ' observación(es) vigente(s).')
                ),
            ]);

            // Notificación al administrado (y a su cuenta si la tuviera)
            $titulo = Catalogos::ajustar(
                'notificaciones.titulo',
                'Observación levantada — ' . $observacion['codigo_expediente']
            );
            $mensaje = Catalogos::ajustar(
                'notificaciones.mensaje',
                'La observación #' . $idObservacion . ' del expediente ' . $observacion['codigo_expediente']
                    . ' fue levantada. ' . ($restantes === 0
                        ? 'Su trámite continúa en proceso.'
                        : 'Aún quedan observaciones pendientes de subsanar.')
            );

            $idUsuarioPersona = $this->idUsuarioDePersona((int) $observacion['id_remitente']);

            $stmt = $this->db->prepare(
                "INSERT INTO notificaciones
                 (id_usuario, id_persona, id_expediente, titulo, mensaje, leida, creado_en)
                 VALUES (:id_usuario, :id_persona, :id_exp, :titulo, :mensaje, false, NOW())"
            );
            $stmt->execute([
                ':id_usuario' => $idUsuarioPersona,
                ':id_persona' => (int) $observacion['id_remitente'],
                ':id_exp'     => $idExpediente,
                ':titulo'     => $titulo,
                ':mensaje'    => $mensaje,
            ]);

            (new Auditoria())->insertar(
                $idUsuario,
                'area',
                'levantar',
                'observaciones',
                $idObservacion,
                'Observación #' . $idObservacion . ' del expediente ' . $observacion['codigo_expediente']
                    . ' levantada. Estado del expediente: ' . $nuevoEstado . '.',
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'success'        => true,
                'codigo'         => $observacion['codigo_expediente'],
                'estado_nuevo'   => $nuevoEstado,
                'observaciones_restantes' => $restantes,
                'message'        => 'Observación levantada correctamente.'
                    . ($restantes === 0 ? ' El expediente volvió a En Proceso.' : ''),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Observacion::levantar: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo levantar la observación: ' . $e->getMessage()];
        }
    }

    /**
     * Bandeja del área: observaciones subsanadas pendientes de levantar.
     * Con $idArea = 0 se listan todas (administrador).
     */
    public function listarSubsanadas(int $idArea = 0): array
    {
        try {
            $sql = "SELECT o.*, e.codigo_expediente, e.asunto AS asunto_expediente,
                           e.estado_actual, e.id_remitente,
                           COALESCE(NULLIF(p.razon_social, ''),
                               TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) AS remitente_nombre,
                           p.numero_documento AS remitente_documento,
                           u.username AS observador,
                           ad.nombre AS area_destino_nombre, ad.siglas AS area_destino_siglas
                    FROM observaciones o
                    INNER JOIN expedientes e ON e.id_expediente = o.id_expediente
                    INNER JOIN personas p ON p.id_persona = e.id_remitente
                    LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
                    LEFT JOIN derivaciones d ON d.id_derivacion = (
                        SELECT d2.id_derivacion FROM derivaciones d2
                        WHERE d2.id_expediente = e.id_expediente
                        ORDER BY d2.id_derivacion DESC LIMIT 1
                    )
                    LEFT JOIN areas ad ON ad.id_area = d.id_area_destino
                    WHERE o.estado = 'Subsanada'";

            $params = [];
            if ($idArea > 0) {
                $sql .= " AND d.id_area_destino = :id_area";
                $params[':id_area'] = $idArea;
            }

            $sql .= " ORDER BY o.fecha_subsanacion DESC, o.id_observacion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en Observacion::listarSubsanadas: ' . $e->getMessage());
            return [];
        }
    }

    /** Cuenta las subsanaciones pendientes de levantar para el badge del sidebar. */
    public function contarSubsanadas(int $idArea = 0): int
    {
        return count($this->listarSubsanadas($idArea));
    }

    /** Cuenta de usuario asociada a una persona (0 si no tiene cuenta). */
    private function idUsuarioDePersona(int $idPersona): ?int
    {
        if ($idPersona <= 0) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_persona = :id LIMIT 1");
            $stmt->execute([':id' => $idPersona]);
            $id = (int) $stmt->fetchColumn();

            return $id > 0 ? $id : null;
        } catch (PDOException $e) {
            return null;
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
