<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Support\Catalogos;
use Src\Core\Conexion;

class Derivacion implements InterfaceModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT d.*, 
                           e.codigo_expediente, e.asunto AS asunto_expediente, e.prioridad,
                           ao.nombre AS area_origen_nombre, ao.siglas AS area_origen_siglas,
                           ad.nombre AS area_destino_nombre, ad.siglas AS area_destino_siglas
                    FROM derivaciones d
                    LEFT JOIN expedientes e ON d.id_expediente = e.id_expediente
                    LEFT JOIN areas ao ON d.id_area_origen = ao.id_area
                    LEFT JOIN areas ad ON d.id_area_destino = ad.id_area
                    ORDER BY d.id_derivacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById(int|string $id): array
    {
        try {
            $sql = "SELECT d.*,
                           e.codigo_expediente, e.asunto AS asunto_expediente, e.prioridad, e.estado_actual AS estado_expediente,
                           e.canal_ingreso, e.fecha_registro AS fecha_registro_expediente,
                           p.numero_documento AS remitente_documento,
                           COALESCE(NULLIF(p.razon_social, ''), CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS remitente_nombre,
                           ao.nombre AS area_origen_nombre, ao.siglas AS area_origen_siglas,
                           ad.nombre AS area_destino_nombre, ad.siglas AS area_destino_siglas,
                           ue.username AS usuario_envia_username,
                           ur.username AS usuario_recibe_username
                    FROM derivaciones d
                    INNER JOIN expedientes e ON d.id_expediente = e.id_expediente
                    INNER JOIN personas p ON e.id_remitente = p.id_persona
                    LEFT JOIN areas ao ON d.id_area_origen = ao.id_area
                    LEFT JOIN areas ad ON d.id_area_destino = ad.id_area
                    LEFT JOIN usuarios ue ON d.id_usuario_envia = ue.id_usuario
                    LEFT JOIN usuarios ur ON d.id_usuario_recibe = ur.id_usuario
                    WHERE d.id_derivacion = :id";
            $stmt = $this->db->prepare($sql);
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
            $sql = "INSERT INTO derivaciones (id_expediente, id_area_origen, id_area_destino, id_usuario_envia, observaciones, estado, fecha_envio) 
                    VALUES (:id_expediente, :id_area_origen, :id_area_destino, :id_usuario_envia, :observaciones, :estado, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_expediente'    => $datos['id_expediente'] ?? $datos['id_documento'] ?? 0,
                ':id_area_origen'   => $datos['id_area_origen'],
                ':id_area_destino'  => $datos['id_area_destino'],
                ':id_usuario_envia' => $datos['id_usuario_envia'],
                ':observaciones'    => $datos['observaciones'] ?? '',
                ':estado'           => $datos['estado'] ?? 'Pendiente'
            ]);
        } catch (PDOException $e) {
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
     * Listar derivaciones destinadas a un área, con filtros opcionales de estado.
     * Si $idArea === 0, lista para todas las áreas (modo administrador).
     */
    public function listarPorArea(int $idArea, ?string $estado = null): array
    {
        try {
            $sql = "SELECT d.id_derivacion, d.id_expediente, d.id_area_origen, d.id_area_destino,
                           d.id_usuario_envia, d.id_usuario_recibe, d.observaciones, d.estado AS estado_derivacion,
                           d.fecha_envio, d.fecha_recepcion, d.fecha_atencion,
                           e.codigo_expediente, e.asunto AS asunto_expediente, e.prioridad, e.estado_actual AS estado_expediente,
                           e.canal_ingreso, e.fecha_registro AS fecha_registro_expediente,
                           p.tipo_documento AS remitente_tipo_doc, p.numero_documento AS remitente_num_doc,
                           COALESCE(NULLIF(p.razon_social, ''), CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS remitente_nombre,
                           ao.nombre AS area_origen_nombre, ao.siglas AS area_origen_siglas,
                           ad.nombre AS area_destino_nombre, ad.siglas AS area_destino_siglas,
                           ue.username AS usuario_envia_username,
                           ur.username AS usuario_recibe_username
                    FROM derivaciones d
                    INNER JOIN expedientes e ON d.id_expediente = e.id_expediente
                    INNER JOIN personas p ON e.id_remitente = p.id_persona
                    LEFT JOIN areas ao ON d.id_area_origen = ao.id_area
                    LEFT JOIN areas ad ON d.id_area_destino = ad.id_area
                    LEFT JOIN usuarios ue ON d.id_usuario_envia = ue.id_usuario
                    LEFT JOIN usuarios ur ON d.id_usuario_recibe = ur.id_usuario
                    WHERE 1 = 1";

            $params = [];

            if ($idArea > 0) {
                $sql .= " AND d.id_area_destino = :id_area";
                $params[':id_area'] = $idArea;
            }

            if (!empty($estado)) {
                if ($estado === 'en_proceso') {
                    $sql .= " AND d.estado IN ('Recibido', 'En_Atencion')";
                } elseif ($estado === 'atendidos_devueltos') {
                    $sql .= " AND d.estado IN ('Atendido', 'Devuelto')";
                } else {
                    $sql .= " AND d.estado = :estado";
                    $params[':estado'] = $estado;
                }
            }

            $sql .= " ORDER BY d.id_derivacion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Derivacion::listarPorArea: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta derivaciones por grupos de estado para los badges de las pestañas
     */
    public function contarPorArea(int $idArea): array
    {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN d.estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN d.estado IN ('Recibido', 'En_Atencion') THEN 1 ELSE 0 END) AS en_proceso,
                        SUM(CASE WHEN d.estado IN ('Atendido', 'Devuelto') THEN 1 ELSE 0 END) AS atendidos_devueltos,
                        COUNT(d.id_derivacion) AS total
                    FROM derivaciones d
                    WHERE 1 = 1";

            $params = [];
            if ($idArea > 0) {
                $sql .= " AND d.id_area_destino = :id_area";
                $params[':id_area'] = $idArea;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'pendientes'          => (int) ($res['pendientes'] ?? 0),
                'en_proceso'          => (int) ($res['en_proceso'] ?? 0),
                'atendidos_devueltos' => (int) ($res['atendidos_devueltos'] ?? 0),
                'total'               => (int) ($res['total'] ?? 0)
            ];
        } catch (PDOException $e) {
            return ['pendientes' => 0, 'en_proceso' => 0, 'atendidos_devueltos' => 0, 'total' => 0];
        }
    }

    /**
     * Recepcionar derivación: actualiza estado a 'Recibido', fecha de recepción, usuario que recibe
     * y actualiza el expediente a 'En_Proceso'.
     */
    public function recepcionar(int $idDerivacion, int $idUsuarioRecibe): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Obtener derivación actual
            $stmt = $this->db->prepare("SELECT * FROM derivaciones WHERE id_derivacion = :id FOR UPDATE");
            $stmt->execute([':id' => $idDerivacion]);
            $derivacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$derivacion) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La derivación no existe.'];
            }

            if ($derivacion['estado'] !== 'Pendiente') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La derivación ya fue procesada anteriormente (' . $derivacion['estado'] . ').'];
            }

            // 2. Actualizar derivación
            $stmtUp = $this->db->prepare(
                "UPDATE derivaciones 
                 SET estado = 'Recibido',
                     id_usuario_recibe = :id_usuario,
                     fecha_recepcion = NOW()
                 WHERE id_derivacion = :id"
            );
            $stmtUp->execute([
                ':id_usuario' => $idUsuarioRecibe,
                ':id'         => $idDerivacion
            ]);

            // 3. Actualizar expediente a 'En_Proceso'
            $idExpediente = (int) $derivacion['id_expediente'];
            $stmtExp = $this->db->prepare(
                "UPDATE expedientes 
                 SET estado_actual = 'En_Proceso', 
                     actualizado_en = NOW() 
                 WHERE id_expediente = :id_exp"
            );
            $stmtExp->execute([':id_exp' => $idExpediente]);

            // 4. Inserción en historial_expediente
            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, 'Registrado', 'En_Proceso', :id_usuario, 'Expediente recepcionado por el área de destino.', NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':id_usuario' => $idUsuarioRecibe
            ]);

            // 5. Registro de auditoría
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuarioRecibe,
                'derivaciones',
                'recepcionar',
                'derivaciones',
                $idDerivacion,
                "Derivación recepcionada para el expediente ID #{$idExpediente}",
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'Expediente recepcionado correctamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Derivacion::recepcionar: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al recepcionar el expediente: ' . $e->getMessage()];
        }
    }

    /**
     * Devolver derivación: actualiza estado a 'Devuelto', anota motivo y actualiza expediente.
     */
    public function devolver(int $idDerivacion, int $idUsuario, string $motivo): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM derivaciones WHERE id_derivacion = :id FOR UPDATE");
            $stmt->execute([':id' => $idDerivacion]);
            $derivacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$derivacion) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La derivación no existe.'];
            }

            if (!in_array($derivacion['estado'], ['Pendiente', 'Recibido', 'En_Atencion'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede devolver una derivación en estado ' . $derivacion['estado'] . '.'];
            }

            $idExpediente = (int) $derivacion['id_expediente'];
            // VAL-01: nota acumulada recortada al límite de la columna (1000).
            $motivoCompleto = Catalogos::ajustar('derivaciones.observaciones', trim($derivacion['observaciones'] . "\n[DEVOLUCIÓN " . date('Y-m-d H:i') . "]: " . $motivo));

            // 1. Actualizar derivación
            $stmtUp = $this->db->prepare(
                "UPDATE derivaciones 
                 SET estado = 'Devuelto',
                     observaciones = :obs,
                     fecha_atencion = NOW()
                 WHERE id_derivacion = :id"
            );
            $stmtUp->execute([
                ':obs' => $motivoCompleto,
                ':id'  => $idDerivacion
            ]);

            // 2. Inserción en historial_expediente
            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, 'En_Proceso', 'Devuelto', :id_usuario, :comentario, NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':id_usuario' => $idUsuario,
                ':comentario' => "Expediente devuelto: " . mb_substr($motivo, 0, 950)
            ]);

            // 3. Auditoría
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'derivaciones',
                'devolver',
                'derivaciones',
                $idDerivacion,
                "Expediente devuelto por el área. Motivo: " . mb_substr($motivo, 0, 200),
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'El expediente ha sido devuelto satisfactoriamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Derivacion::devolver: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al devolver el expediente: ' . $e->getMessage()];
        }
    }

    /**
     * Re-derivar / Pasar expediente a otra área institucional
     */
    public function reDerivar(int $idDerivacionOrigen, int $idAreaDestino, int $idUsuarioEnvia, string $observaciones): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Obtener derivación de origen
            $stmt = $this->db->prepare("SELECT * FROM derivaciones WHERE id_derivacion = :id FOR UPDATE");
            $stmt->execute([':id' => $idDerivacionOrigen]);
            $derivacionOrigen = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$derivacionOrigen) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La derivación de origen no existe.'];
            }

            if (in_array($derivacionOrigen['estado'], ['Atendido', 'Devuelto'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede derivar un trámite que ya está en estado ' . $derivacionOrigen['estado'] . '.'];
            }

            $idExpediente = (int) $derivacionOrigen['id_expediente'];
            $idAreaOrigen = (int) $derivacionOrigen['id_area_destino']; // el área actual que ahora reenvía

            if ($idAreaOrigen === $idAreaDestino) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El área destino no puede ser la misma área de origen.'];
            }

            // 2. Obtener datos del área destino
            $stmtArea = $this->db->prepare("SELECT nombre, siglas FROM areas WHERE id_area = :id");
            $stmtArea->execute([':id' => $idAreaDestino]);
            $areaDestino = $stmtArea->fetch(PDO::FETCH_ASSOC);

            if (!$areaDestino) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El área destino especificada no existe.'];
            }

            // 3. Cerrar derivación anterior (marcar como Atendida/Pase en esta área)
            // VAL-01: nota acumulada recortada al límite de la columna (1000).
            $notaPase = Catalogos::ajustar('derivaciones.observaciones', trim(($derivacionOrigen['observaciones'] ?? '') . "\n[PASE A " . $areaDestino['siglas'] . " - " . date('Y-m-d H:i') . "]: " . $observaciones));
            $stmtCerrar = $this->db->prepare(
                "UPDATE derivaciones 
                 SET estado = 'Atendido',
                     fecha_atencion = NOW(),
                     observaciones = :obs
                 WHERE id_derivacion = :id"
            );
            $stmtCerrar->execute([
                ':obs' => $notaPase,
                ':id'  => $idDerivacionOrigen
            ]);

            // 4. Crear nueva derivación hacia el área destino
            $stmtNueva = $this->db->prepare(
                "INSERT INTO derivaciones (id_expediente, id_area_origen, id_area_destino, id_usuario_envia, observaciones, estado, fecha_envio)
                 VALUES (:id_exp, :id_origen, :id_destino, :id_usuario, :obs, 'Pendiente', NOW())"
            );
            $stmtNueva->execute([
                ':id_exp'     => $idExpediente,
                ':id_origen'  => $idAreaOrigen,
                ':id_destino' => $idAreaDestino,
                ':id_usuario' => $idUsuarioEnvia,
                ':obs'        => $observaciones
            ]);
            $nuevaDerivacionId = (int) $this->db->lastInsertId();

            // 5. Actualizar expediente a 'En_Proceso'
            $stmtExp = $this->db->prepare(
                "UPDATE expedientes 
                 SET estado_actual = 'En_Proceso', 
                     actualizado_en = NOW() 
                 WHERE id_expediente = :id_exp"
            );
            $stmtExp->execute([':id_exp' => $idExpediente]);

            // 6. Registrar en historial_expediente
            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, 'En_Proceso', 'En_Proceso', :id_usuario, :comentario, NOW())"
            );
            $comentarioHist = "Re-derivado a {$areaDestino['nombre']} ({$areaDestino['siglas']}). Proveído: " . mb_substr($observaciones, 0, 900);
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':id_usuario' => $idUsuarioEnvia,
                ':comentario' => $comentarioHist
            ]);

            // 7. Auditoría
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuarioEnvia,
                'derivaciones',
                're-derivar',
                'derivaciones',
                $nuevaDerivacionId,
                "Expediente ID #{$idExpediente} re-derivado a {$areaDestino['siglas']}",
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();
            return [
                'success' => true,
                'message' => "Expediente re-derivado con éxito a {$areaDestino['nombre']}."
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en Derivacion::reDerivar: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al re-derivar el expediente: ' . $e->getMessage()];
        }
    }

    /**
     * TAREA E04-1: Atender el expediente con emisión de documento de respuesta.
     * Cierra la derivación ('Atendido'), marca el expediente como 'Atendido' con
     * fecha_atencion, registra el documento de respuesta con sus adjuntos,
     * el historial de estado y la auditoría. Todo en una sola transacción.
     *
     * @param array $respuesta id_tipo_doc, numero_documento, asunto (resumen), folios, fecha_documento
     * @param array $archivos  adjuntos PDF ya subidos al disco
     */
    public function atender(int $idDerivacion, int $idUsuario, array $respuesta, array $archivos): array
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
                return ['success' => false, 'message' => 'Solo se pueden atender derivaciones recepcionadas (Recibido/En_Atencion); estado actual: ' . $derivacion['estado'] . '.'];
            }

            $idExpediente = (int) $derivacion['id_expediente'];

            // 2. Expediente con su estado real (para el historial)
            $stmt = $this->db->prepare('SELECT codigo_expediente, estado_actual FROM expedientes WHERE id_expediente = :id FOR UPDATE');
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El expediente asociado a la derivación no existe.'];
            }

            // 3. Documento de respuesta + adjuntos (misma conexión PDO: participa de la transacción)
            $respuesta['id_expediente'] = $idExpediente;
            $documento = new Documento();
            $idDocumento = $documento->registrarRespuesta($respuesta, $archivos, $idUsuario);

            // 4. Cerrar la derivación como atendida, dejando constancia de la respuesta
            // VAL-01: nota acumulada recortada al límite de la columna (1000).
            $notaRespuesta = Catalogos::ajustar(
                'derivaciones.observaciones',
                trim(
                    ($derivacion['observaciones'] ?? '') .
                    "\n[RESPUESTA " . date('Y-m-d H:i') . ']: '
                    . $respuesta['numero_documento'] . ' — ' . $respuesta['asunto']
                )
            );
            $stmtUp = $this->db->prepare(
                "UPDATE derivaciones
                 SET estado = 'Atendido',
                     fecha_atencion = NOW(),
                     observaciones = :obs
                 WHERE id_derivacion = :id"
            );
            $stmtUp->execute([':obs' => $notaRespuesta, ':id' => $idDerivacion]);

            // 5. Expediente atendido
            $stmtExp = $this->db->prepare(
                "UPDATE expedientes
                 SET estado_actual = 'Atendido',
                     fecha_atencion = NOW(),
                     actualizado_en = NOW()
                 WHERE id_expediente = :id_exp"
            );
            $stmtExp->execute([':id_exp' => $idExpediente]);

            // 6. Historial de estado
            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, :anterior, 'Atendido', :id_usuario, :comentario, NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':anterior'   => $expediente['estado_actual'],
                ':id_usuario' => $idUsuario,
                ':comentario' => 'Expediente atendido con ' . $respuesta['numero_documento']
                    . ' (documento de respuesta registrado).',
            ]);

            // 7. Auditoría
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'area',
                'atender',
                'expedientes',
                $idExpediente,
                "Expediente {$expediente['codigo_expediente']} atendido con documento de respuesta #{$idDocumento} ({$respuesta['numero_documento']}).",
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'success'      => true,
                'id_documento' => $idDocumento,
                'codigo'       => $expediente['codigo_expediente'],
                'message'      => "Expediente {$expediente['codigo_expediente']} atendido correctamente.",
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Derivacion::atender: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al atender el expediente: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener documentos y archivos adjuntos de un expediente
     */
    public function obtenerDocumentosConAdjuntos(int $idExpediente): array
    {
        try {
            $sqlDocs = "SELECT d.*, td.nombre AS tipo_documento_nombre
                        FROM documentos d
                        LEFT JOIN tipos_documento td ON d.id_tipo_doc = td.id_tipo_doc
                        WHERE d.id_expediente = :id_exp
                        ORDER BY d.es_principal DESC, d.id_documento ASC";
            $stmtDocs = $this->db->prepare($sqlDocs);
            $stmtDocs->execute([':id_exp' => $idExpediente]);
            $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($documentos as &$doc) {
                $sqlArch = "SELECT * FROM archivos_adjuntos WHERE id_documento = :id_doc";
                $stmtArch = $this->db->prepare($sqlArch);
                $stmtArch->execute([':id_doc' => $doc['id_documento']]);
                $doc['adjuntos'] = $stmtArch->fetchAll(PDO::FETCH_ASSOC);
            }

            return $documentos;
        } catch (PDOException $e) {
            return [];
        }
    }
}
