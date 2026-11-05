<?php

namespace App\Models;

use App\Services\ExpedienteService;
use Exception;
use PDO;
use PDOException;
use Src\Core\Conexion;

/**
 * Modelo del módulo de Recepción Presencial e Interna (Ventanilla — EPICA 2).
 * Registra expedientes por canal Presencial/Interno, crea la derivación inicial
 * al área destino y alimenta la bandeja de Mesa de Partes.
 */
class MesaPartes
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /* ============================================================
     * BÚSQUEDA EN VIVO DE PERSONAS (DNI / RUC / NOMBRE)
     * ============================================================ */
    public function buscarPersonas(string $q, int $limite = 10): array
    {
        $busqueda = trim($q);
        if ($busqueda === '') {
            return [];
        }

        $limite = max(1, min($limite, 20));

        try {
            $esDocumento = (bool) preg_match('/^[0-9]+$/', $busqueda);

            if ($esDocumento) {
                $sql = "SELECT id_persona, tipo_persona, tipo_documento, numero_documento,
                                COALESCE(NULLIF(razon_social, ''),
                    TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno))) AS nombre_completo,
                                email, telefono, direccion
                         FROM personas
                         WHERE numero_documento LIKE :busqueda
                            OR COALESCE(NULLIF(razon_social, ''),
                    TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno))) LIKE :busqueda
                         ORDER BY numero_documento ASC
                         LIMIT {$limite}";
            } else {
                $sql = "SELECT id_persona, tipo_persona, tipo_documento, numero_documento,
                                COALESCE(NULLIF(razon_social, ''),
                    TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno))) AS nombre_completo,
                                email, telefono, direccion
                         FROM personas
                         WHERE COALESCE(NULLIF(razon_social, ''),
                    TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno))) LIKE :busqueda
                         ORDER BY id_persona DESC
                         LIMIT {$limite}";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MesaPartes::buscarPersonas: ' . $e->getMessage());
            return [];
        }
    }

    /* ============================================================
     * GENERAR CÓDIGO DE EXPEDIENTE (EXP-2026-00001)
     * OPT-01: el numerador vive en ExpedienteService (fuente única,
     * SELECT ... FOR UPDATE dentro de la transacción ya abierta).
     * ============================================================ */
    private function generarCodigoExpediente(): string
    {
        return (new ExpedienteService())->generarCodigo();
    }

    /* ============================================================
     * OBTENER EL ÁREA DE MESA DE PARTES (origen de la derivación)
     * Si la institución no tiene un área de Mesa de Partes registrada,
     * se usa el área del usuario que registra.
     * ============================================================ */
    public function obtenerAreaMesaPartes(?int $idAreaUsuario = null): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id_area, nombre, siglas FROM areas
                 WHERE estado = 'Activo' AND (UPPER(siglas) LIKE '%MP%' OR UPPER(nombre) LIKE '%MESA%')
                 ORDER BY id_area ASC LIMIT 1"
            );
            $stmt->execute();
            $area = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($area) {
                return $area;
            }

            if ($idAreaUsuario && $idAreaUsuario > 0) {
                return $this->getArea($idAreaUsuario);
            }

            return null;
        } catch (PDOException $e) {
            error_log('Error en MesaPartes::obtenerAreaMesaPartes: ' . $e->getMessage());
            return null;
        }
    }

    /* ============================================================
     * REGISTRAR EXPEDIENTE DE VENTANILLA
     * 1 expediente -> 1 documento -> N adjuntos -> (opcional) derivación inicial
     * ============================================================ */
    public function registrarExpediente(array $datos, array $documento, array $archivos, int $idUsuario, ?int $idAreaDestino = null, string $instrucciones = '', ?int $idAreaUsuario = null): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Persona: reutilizar (buscar o crear) el patrón del portal virtual
            $idPersona = $this->resolverPersona($datos);

            // 2. Código correlativo de expediente
            $codigo = $this->generarCodigoExpediente();

            // 3. Expediente con el canal de ventanilla
            $stmt = $this->db->prepare(
                'INSERT INTO expedientes
                 (codigo_expediente, id_remitente, asunto, prioridad, canal_ingreso, estado_actual)
                 VALUES (:codigo, :id_remitente, :asunto, :prioridad, :canal, :estado)'
            );
            $canal = in_array($datos['canal_ingreso'] ?? '', ['Presencial', 'Interno'], true) ? $datos['canal_ingreso'] : 'Presencial';
            $stmt->execute([
                ':codigo'       => $codigo,
                ':id_remitente' => $idPersona,
                ':asunto'       => $datos['asunto'],
                ':prioridad'    => $datos['prioridad'] ?? 'Normal',
                ':canal'        => $canal,
                ':estado'       => 'Registrado',
            ]);
            $idExpediente = (int) $this->db->lastInsertId();

            // 4. Documento físico registrado por ventanilla
            $stmt = $this->db->prepare(
                'INSERT INTO documentos
                 (id_expediente, id_tipo_doc, numero_documento, asunto, folios,
                  fecha_documento, tipo_documento_registro, es_principal, creado_por)
                 VALUES (:id_exp, :id_tipo, :numero, :asunto, :folios,
                         :fecha, :tipo_registro, 1, :creado_por)'
            );
            $stmt->execute([
                ':id_exp'         => $idExpediente,
                ':id_tipo'        => $documento['id_tipo_doc'],
                ':numero'         => $documento['numero_documento'] !== '' ? $documento['numero_documento'] : null,
                ':asunto'         => $documento['asunto'],
                ':folios'         => (int) $documento['folios'],
                ':fecha'          => $documento['fecha_documento'] !== '' ? $documento['fecha_documento'] : null,
                ':tipo_registro'  => $canal === 'Interno' ? 'Generado' : 'Ingresado',
                ':creado_por'     => $idUsuario,
            ]);
            $idDocumento = (int) $this->db->lastInsertId();

            // 5. Adjuntos escaneados/upload
            if (!empty($archivos)) {
                $stmt = $this->db->prepare(
                    'INSERT INTO archivos_adjuntos
                     (id_documento, nombre_original, nombre_archivo, ruta_archivo,
                      extension, mime_type, tamanio, hash_archivo, creado_por)
                     VALUES (:id_doc, :nom_orig, :nom_arch, :ruta,
                             :ext, :mime, :tam, :hash, :creado_por)'
                );
                foreach ($archivos as $archivo) {
                    $stmt->execute([
                        ':id_doc'     => $idDocumento,
                        ':nom_orig'   => $archivo['nombre_original'],
                        ':nom_arch'   => $archivo['nombre_archivo'],
                        ':ruta'       => $archivo['ruta_archivo'],
                        ':ext'        => $archivo['extension'],
                        ':mime'       => $archivo['mime_type'],
                        ':tam'        => $archivo['tamanio'],
                        ':hash'       => $archivo['hash_archivo'],
                        ':creado_por' => $idUsuario,
                    ]);
                }
            }

            // 6. Historial de estado
            $stmt = $this->db->prepare(
                'INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, NULL, :estado, :id_usuario, :comentario, NOW())'
            );
            $stmt->execute([
                ':id_exp'     => $idExpediente,
                ':estado'     => 'Registrado',
                ':id_usuario' => $idUsuario,
                ':comentario' => sprintf('Expediente registrado en Mesa de Partes por ventanilla (canal %s).', $canal),
            ]);

            // 7. Derivación inicial al área destino
            $idAreaDestinoRegistrada = null;
            if ($idAreaDestino && $idAreaDestino > 0) {
                $areaOrigen = $this->obtenerAreaMesaPartes($idAreaUsuario);
                $idAreaOrigen = (int) ($areaOrigen['id_area'] ?? 0);

                if ($idAreaOrigen <= 0) {
                    throw new Exception(
                        'No es posible originar la derivación: el usuario que registra no tiene un área asignada '
                        . 'y no existe un área de Mesa de Partes activa. Asigne un área al usuario o cree el área de Mesa de Partes.'
                    );
                }

                $stmt = $this->db->prepare(
                    'INSERT INTO derivaciones
                     (id_expediente, id_area_origen, id_area_destino, id_usuario_envia, observaciones, estado, fecha_envio)
                     VALUES (:id_exp, :origen, :destino, :usuario_envia, :observaciones, :estado, NOW())'
                );
                $stmt->execute([
                    ':id_exp'         => $idExpediente,
                    ':origen'         => $idAreaOrigen,
                    ':destino'        => $idAreaDestino,
                    ':usuario_envia'  => $idUsuario,
                    ':observaciones'  => $instrucciones !== '' ? $instrucciones : 'Derivación inicial emitida desde Mesa de Partes.',
                    ':estado'         => 'Pendiente',
                ]);
                $idAreaDestinoRegistrada = $idAreaDestino;

                $areaDestino = $this->getArea((int) $idAreaDestino);
                $stmt = $this->db->prepare(
                    'INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                     VALUES (:id_exp, :anterior, :nuevo, :id_usuario, :comentario, NOW())'
                );
                $stmt->execute([
                    ':id_exp'     => $idExpediente,
                    ':anterior'   => 'Registrado',
                    ':nuevo'      => 'Registrado',
                    ':id_usuario' => $idUsuario,
                    ':comentario' => 'Derivado inicialmente al área ' . ($areaDestino['nombre'] ?? ('#' . $idAreaDestino)) . ' desde Mesa de Partes.',
                ]);
            }

            // 8. Auditoría obligatoria
            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'mesa_partes',
                'crear',
                'expedientes',
                $idExpediente,
                "Expediente {$codigo} registrado por ventanilla (canal {$canal}).",
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'ok'            => true,
                'id_expediente' => $idExpediente,
                'id_documento'  => $idDocumento,
                'id_persona'    => $idPersona,
                'codigo'        => $codigo,
                'id_area_destino' => $idAreaDestinoRegistrada,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en MesaPartes::registrarExpediente: ' . $e->getMessage());
            return [
                'ok'      => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /* ============================================================
     * BUSCAR O CREAR LA PERSONA REMITENTE
     * ============================================================ */
    private function resolverPersona(array $datos): int
    {
        $stmt = $this->db->prepare(
            'SELECT id_persona FROM personas WHERE tipo_documento = :tipo AND numero_documento = :numero LIMIT 1'
        );
        $stmt->execute([
            ':tipo'    => $datos['tipo_documento'],
            ':numero'  => $datos['numero_documento'],
        ]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $stmt = $this->db->prepare(
                'UPDATE personas
                 SET tipo_persona = :tipo_persona, nombres = :nombres,
                     apellido_paterno = :ap, apellido_materno = :am, razon_social = :razon,
                     email = :email, telefono = :telefono, direccion = :direccion,
                     actualizado_en = CURRENT_TIMESTAMP
                 WHERE id_persona = :id'
            );
            $stmt->execute([
                ':tipo_persona' => $datos['tipo_persona'] ?? 'Natural',
                ':nombres'      => $this->nullable($datos['nombres'] ?? ''),
                ':ap'           => $this->nullable($datos['apellido_paterno'] ?? ''),
                ':am'           => $this->nullable($datos['apellido_materno'] ?? ''),
                ':razon'        => $this->nullable($datos['razon_social'] ?? ''),
                ':email'        => $this->nullable($datos['email'] ?? ''),
                ':telefono'     => $this->nullable($datos['telefono'] ?? ''),
                ':direccion'    => $this->nullable($datos['direccion'] ?? ''),
                ':id'           => (int) $existente['id_persona'],
            ]);
            return (int) $existente['id_persona'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO personas
             (tipo_persona, tipo_documento, numero_documento, nombres, apellido_paterno,
              apellido_materno, razon_social, email, telefono, direccion, estado)
             VALUES
             (:tipo_persona, :tipo_documento, :numero_documento, :nombres, :ap,
              :am, :razon, :email, :telefono, :direccion, :estado)'
        );
        $stmt->execute([
            ':tipo_persona'     => $datos['tipo_persona'] ?? 'Natural',
            ':tipo_documento'   => $datos['tipo_documento'],
            ':numero_documento' => $datos['numero_documento'],
            ':nombres'          => $this->nullable($datos['nombres'] ?? ''),
            ':ap'               => $this->nullable($datos['apellido_paterno'] ?? ''),
            ':am'               => $this->nullable($datos['apellido_materno'] ?? ''),
            ':razon'            => $this->nullable($datos['razon_social'] ?? ''),
            ':email'            => $this->nullable($datos['email'] ?? ''),
            ':telefono'         => $this->nullable($datos['telefono'] ?? ''),
            ':direccion'        => $this->nullable($datos['direccion'] ?? ''),
            ':estado'           => 'Activo',
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function nullable(string $valor): ?string
    {
        $valor = trim($valor);
        return $valor === '' ? null : $valor;
    }

    /* ============================================================
     * BANDEJA DE MESA DE PARTES: LISTADO CON FILTROS
     * ============================================================ */
    public function listarMesaPartes(array $filtros): array
    {
        try {
            $sql = "SELECT e.id_expediente, e.codigo_expediente, e.asunto, e.prioridad, e.canal_ingreso,
                           e.estado_actual, e.fecha_registro,
                           p.id_persona, p.tipo_documento AS remitente_tipo_doc, p.numero_documento AS remitente_num_doc,
                           COALESCE(NULLIF(p.razon_social, ''),
                    TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) AS remitente_nombre,
                           d.numero_documento AS doc_numero, d.folios AS doc_folios,
                           td.nombre AS tipo_doc_nombre,
                           der.id_derivacion, der.estado AS estado_derivacion, der.fecha_recepcion,
                           ad.nombre AS area_destino_nombre, ad.siglas AS area_destino_siglas,
                           u.username AS usuario_registra
                    FROM expedientes e
                    INNER JOIN personas p ON e.id_remitente = p.id_persona
                    LEFT JOIN documentos d ON d.id_documento = (
                        SELECT d2.id_documento FROM documentos d2
                        WHERE d2.id_expediente = e.id_expediente
                        ORDER BY d2.es_principal DESC, d2.id_documento ASC LIMIT 1
                    )
                    LEFT JOIN tipos_documento td ON td.id_tipo_doc = d.id_tipo_doc
                    LEFT JOIN derivaciones der ON der.id_derivacion = (
                        SELECT der2.id_derivacion FROM derivaciones der2
                        WHERE der2.id_expediente = e.id_expediente
                        ORDER BY der2.id_derivacion ASC LIMIT 1
                    )
                    LEFT JOIN areas ad ON ad.id_area = der.id_area_destino
                    LEFT JOIN usuarios u ON u.id_usuario = d.creado_por
                    WHERE e.canal_ingreso IN ('Presencial', 'Interno')";

            $params = [];

            if (!empty($filtros['desde'])) {
                $sql .= ' AND e.fecha_registro >= :desde';
                $params[':desde'] = $filtros['desde'] . ' 00:00:00';
            }
            if (!empty($filtros['hasta'])) {
                $sql .= ' AND e.fecha_registro <= :hasta';
                $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
            }
            if (!empty($filtros['canal'])) {
                $sql .= ' AND e.canal_ingreso = :canal';
                $params[':canal'] = $filtros['canal'];
            }
            if (!empty($filtros['area_id'])) {
                $sql .= ' AND der.id_area_destino = :area_id';
                $params[':area_id'] = (int) $filtros['area_id'];
            }
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'Sin_Derivar') {
                    $sql .= ' AND der.id_derivacion IS NULL';
                } else {
                    $sql .= ' AND der.estado = :estado';
                    $params[':estado'] = $filtros['estado'];
                }
            }
            if (!empty($filtros['q'])) {
                $sql .= " AND (e.codigo_expediente LIKE :q
                               OR e.asunto LIKE :q
                               OR p.numero_documento LIKE :q
                               OR COALESCE(NULLIF(p.razon_social, ''),
                    TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) LIKE :q)";
                $params[':q'] = '%' . $filtros['q'] . '%';
            }

            // Conteo total (antes de añadir ORDER BY / LIMIT) para la paginación
            $total = $this->contarExpedientes($sql, $params);
            $paginas = max(1, (int) ceil($total / ($filtros['por_pagina'] ?? 20)));
            $paginaActual = 1;

            $sql .= ' ORDER BY e.id_expediente DESC';

            if (!empty($filtros['paginar'])) {
                $paginaActual = max(1, min((int) ($filtros['pagina'] ?? 1), $paginas));
                $porPagina = max(1, (int) ($filtros['por_pagina'] ?? 20));
                $offset = ($paginaActual - 1) * $porPagina;

                $sql .= ' LIMIT ' . $porPagina . ' OFFSET ' . $offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'registros'    => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total'        => $total,
                'paginas'      => $paginas,
                'pagina_actual'=> $paginaActual,
            ];
        } catch (PDOException $e) {
            error_log('Error en MesaPartes::listarMesaPartes: ' . $e->getMessage());
            return ['registros' => [], 'total' => 0, 'paginas' => 1, 'pagina_actual' => 1];
        }
    }

    /**
     * Consulta COUNT sobre el mismo FROM/WHERE del listado.
     * Debe invocarse ANTES de añadir el ORDER BY y el LIMIT finales:
     * los ORDER BY de las subconsultas correlacionadas forman parte del
     * JOIN y no pueden eliminarse de la versión contada.
     */
    private function contarExpedientes(string $sqlBase, array $params): int
    {
        $select = substr($sqlBase, strpos($sqlBase, 'FROM'));
        $stmt = $this->db->prepare('SELECT COUNT(*) ' . $select);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    /* ============================================================
     * INDICADORES DE LA BANDEJA
     * ============================================================ */
    public function contarIndicadores(string $desde, string $hasta): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) AS total,
                        SUM(CASE WHEN e.prioridad = 'Urgente' THEN 1 ELSE 0 END) AS urgentes,
                        SUM(CASE WHEN der.id_derivacion IS NULL THEN 1 ELSE 0 END) AS sin_derivar,
                        SUM(CASE WHEN der.estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes_recepcion,
                        SUM(CASE WHEN e.canal_ingreso = 'Interno' THEN 1 ELSE 0 END) AS internos
                    FROM expedientes e
                    LEFT JOIN derivaciones der ON der.id_derivacion = (
                        SELECT der2.id_derivacion FROM derivaciones der2
                        WHERE der2.id_expediente = e.id_expediente
                        ORDER BY der2.id_derivacion ASC LIMIT 1
                    )
                    WHERE e.canal_ingreso IN ('Presencial', 'Interno')
                      AND e.fecha_registro BETWEEN :desde AND :hasta";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':desde' => $desde . ' 00:00:00',
                ':hasta' => $hasta . ' 23:59:59',
            ]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total'               => (int) ($fila['total'] ?? 0),
                'urgentes'            => (int) ($fila['urgentes'] ?? 0),
                'sin_derivar'         => (int) ($fila['sin_derivar'] ?? 0),
                'pendientes_recepcion'=> (int) ($fila['pendientes_recepcion'] ?? 0),
                'internos'            => (int) ($fila['internos'] ?? 0),
            ];
        } catch (PDOException $e) {
            error_log('Error en MesaPartes::contarIndicadores: ' . $e->getMessage());
            return ['total' => 0, 'urgentes' => 0, 'sin_derivar' => 0, 'pendientes_recepcion' => 0, 'internos' => 0];
        }
    }

    /* ============================================================
     * DETALLE DEL EXPEDIENTE (modales de bandeja y cargo)
     * ============================================================ */
    public function getExpedienteCompleto(int $idExpediente): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT e.*, p.tipo_persona, p.tipo_documento AS remitente_tipo_doc,
                        p.numero_documento AS remitente_num_doc, p.nombres, p.apellido_paterno, p.apellido_materno,
                        p.razon_social, p.email, p.telefono, p.direccion,
                        COALESCE(NULLIF(p.razon_social, ''),
                 TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) AS remitente_nombre,
                        (SELECT COUNT(*) FROM documentos d WHERE d.id_expediente = e.id_expediente) AS total_documentos,
                        (SELECT COALESCE(SUM(d.folios), 0) FROM documentos d WHERE d.id_expediente = e.id_expediente) AS total_folios
                 FROM expedientes e
                 INNER JOIN personas p ON p.id_persona = e.id_remitente
                 WHERE e.id_expediente = :id"
            );
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                return [];
            }

            $stmt = $this->db->prepare(
                'SELECT d.*, td.nombre AS tipo_documento_nombre, u.username AS usuario_creador
                 FROM documentos d
                 LEFT JOIN tipos_documento td ON td.id_tipo_doc = d.id_tipo_doc
                 LEFT JOIN usuarios u ON u.id_usuario = d.creado_por
                 WHERE d.id_expediente = :id_exp
                 ORDER BY d.es_principal DESC, d.id_documento ASC'
            );
            $stmt->execute([':id_exp' => $idExpediente]);
            $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($documentos as &$documento) {
                $stmt = $this->db->prepare('SELECT * FROM archivos_adjuntos WHERE id_documento = :id_doc');
                $stmt->execute([':id_doc' => $documento['id_documento']]);
                $documento['adjuntos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($documento);

            $stmt = $this->db->prepare(
                'SELECT der.*, ao.nombre AS area_origen_nombre, ad.nombre AS area_destino_nombre,
                        ad.siglas AS area_destino_siglas, ue.username AS usuario_envia
                 FROM derivaciones der
                 LEFT JOIN areas ao ON ao.id_area = der.id_area_origen
                 LEFT JOIN areas ad ON ad.id_area = der.id_area_destino
                 LEFT JOIN usuarios ue ON ue.id_usuario = der.id_usuario_envia
                 WHERE der.id_expediente = :id_exp
                 ORDER BY der.id_derivacion ASC'
            );
            $stmt->execute([':id_exp' => $idExpediente]);
            $derivaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare(
                'SELECT h.*, u.username AS usuario, e2.codigo_expediente
                 FROM historial_expediente h
                 LEFT JOIN usuarios u ON u.id_usuario = h.id_usuario
                 LEFT JOIN expedientes e2 ON e2.id_expediente = h.id_expediente
                 WHERE h.id_expediente = :id_exp
                 ORDER BY h.fecha ASC, h.id_historial ASC'
            );
            $stmt->execute([':id_exp' => $idExpediente]);
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'expediente'   => $expediente,
                'documentos'   => $documentos,
                'derivaciones' => $derivaciones,
                'historial'    => $historial,
            ];
        } catch (PDOException $e) {
            error_log('Error en MesaPartes::getExpedienteCompleto: ' . $e->getMessage());
            return [];
        }
    }

    public function getArea(int $idArea): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM areas WHERE id_area = :id AND estado = 'Activo'");
            $stmt->execute([':id' => $idArea]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarAreasActivas(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id_area, nombre, siglas FROM areas WHERE estado = 'Activo' ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarTiposDocumento(): array
    {
        // OPT-02: catálogo único vía App\Models\TipoDocumento.
        return (new TipoDocumento())->obtenerActivos();
    }

    public function getTipoDocumento(int $idTipoDoc): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM tipos_documento WHERE id_tipo_doc = :id');
            $stmt->execute([':id' => $idTipoDoc]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function existeArea(int $idArea): bool
    {
        return $this->getArea($idArea) !== [];
    }
}
