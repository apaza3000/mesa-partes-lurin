<?php

namespace App\Services;

use PDO;
use Src\Core\Conexion;

/**
 * OPT-01: fuente única de la regla de negocio del expediente.
 *
 * El código `EXP-AAAA-NNNNN` y el alta del expediente virtual (portal del
 * ciudadano) estaban duplicados en `MesaPartes` y `EntregaModel`. Aquí viven
 * la generación del correlativo (transacción única con `SELECT ... FOR UPDATE`
 * sobre `numeradores`, respaldada por el `uk_numerador (tipo, anio)` de la BD)
 * y el alta completa 1 expediente → 1 documento → N adjuntos → historial.
 * Los modelos quedan solo con CRUD.
 */
class ExpedienteService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /* ============================================================
     * CÓDIGO CORRELATIVO: EXP-2026-00001
     * Debe llamarse dentro de una transacción (FOR UPDATE). Si no hay
     * ninguna, se abre una propia para no perder la garantía de unicidad.
     * ============================================================ */
    public function generarCodigo(string $tipo = 'EXPEDIENTE'): string
    {
        $anio = (int) date('Y');
        $transaccionPropia = !$this->db->inTransaction();

        if ($transaccionPropia) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT id_numerador, ultimo_numero FROM numeradores WHERE tipo = :tipo AND anio = :anio FOR UPDATE'
            );
            $stmt->execute([':tipo' => $tipo, ':anio' => $anio]);
            $numerador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$numerador) {
                $stmt = $this->db->prepare(
                    'INSERT INTO numeradores (tipo, anio, ultimo_numero) VALUES (:tipo, :anio, 1)'
                );
                $stmt->execute([':tipo' => $tipo, ':anio' => $anio]);
                $correlativo = 1;
            } else {
                $correlativo = (int) $numerador['ultimo_numero'] + 1;
                $stmt = $this->db->prepare('UPDATE numeradores SET ultimo_numero = :ultimo WHERE id_numerador = :id');
                $stmt->execute([':ultimo' => $correlativo, ':id' => $numerador['id_numerador']]);
            }

            if ($transaccionPropia) {
                $this->db->commit();
            }

            return sprintf('EXP-%d-%05d', $anio, $correlativo);
        } catch (\Throwable $e) {
            if ($transaccionPropia && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /* ============================================================
     * ALTA DEL EXPEDIENTE VIRTUAL (portal ciudadano, canal Virtual)
     * 1 expediente → 1 documento → N adjuntos → historial inicial.
     * ============================================================ */
    public function crearExpedienteVirtual(array $datos, array $documento, array $archivos): array
    {
        try {
            $this->db->beginTransaction();

            // 1. Persona: buscar o crear (patrón compartido con ventanilla).
            $idPersona = $this->resolverPersona($datos);

            // 2. Código correlativo — única implementación (OPT-01).
            $codigo = $this->generarCodigo();

            // 3. Expediente (UNO SOLO)
            $stmt = $this->db->prepare(
                'INSERT INTO expedientes
                 (codigo_expediente, id_remitente, asunto, prioridad, canal_ingreso, estado_actual)
                 VALUES (:codigo, :id_remitente, :asunto, :prioridad, :canal, :estado)'
            );
            $stmt->execute([
                ':codigo'        => $codigo,
                ':id_remitente'  => $idPersona,
                ':asunto'        => $datos['asunto'],
                ':prioridad'     => $datos['prioridad'] ?? 'Normal',
                ':canal'         => $datos['canal_ingreso'] ?? 'Virtual',
                ':estado'        => 'Registrado',
            ]);
            $idExpediente = (int) $this->db->lastInsertId();

            // 4. Documento (UNO SOLO)
            $stmt = $this->db->prepare(
                'INSERT INTO documentos
                 (id_expediente, id_tipo_doc, numero_documento, asunto, folios,
                  fecha_documento, tipo_documento_registro, es_principal, creado_por)
                 VALUES (:id_exp, :id_tipo, :numero, :asunto, :folios,
                         :fecha, :tipo_registro, 1, NULL)'
            );
            $stmt->execute([
                ':id_exp'         => $idExpediente,
                ':id_tipo'        => $documento['id_tipo_doc'],
                ':numero'         => $documento['numero_documento'] ?: null,
                ':asunto'         => $documento['asunto'],
                ':folios'         => (int) $documento['folios'],
                ':fecha'          => $documento['fecha_documento'] ?: null,
                ':tipo_registro'  => 'Ingresado',
            ]);
            $idDocumento = (int) $this->db->lastInsertId();

            // 5. N adjuntos para ese documento
            if (!empty($archivos)) {
                $stmt = $this->db->prepare(
                    'INSERT INTO archivos_adjuntos
                     (id_documento, nombre_original, nombre_archivo, ruta_archivo,
                      extension, mime_type, tamanio, hash_archivo, creado_por)
                     VALUES (:id_doc, :nom_orig, :nom_arch, :ruta,
                             :ext, :mime, :tam, :hash, NULL)'
                );
                foreach ($archivos as $archivo) {
                    $stmt->execute([
                        ':id_doc'   => $idDocumento,
                        ':nom_orig' => $archivo['nombre_original'],
                        ':nom_arch' => $archivo['nombre_archivo'],
                        ':ruta'     => $archivo['ruta_archivo'],
                        ':ext'      => $archivo['extension'],
                        ':mime'     => $archivo['mime_type'],
                        ':tam'      => $archivo['tamanio'],
                        ':hash'     => $archivo['hash_archivo'],
                    ]);
                }
            }

            // 6. Historial inicial
            $stmt = $this->db->prepare(
                "INSERT INTO historial_expediente
                 (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario)
                 VALUES (:id_exp, NULL, 'Registrado', NULL, :comentario)"
            );
            $stmt->execute([
                ':id_exp'      => $idExpediente,
                ':comentario'  => $datos['comentario_historial'] ?? 'Expediente registrado por el ciudadano vía web',
            ]);

            $this->db->commit();

            return [
                'ok'            => true,
                'id_expediente' => $idExpediente,
                'id_documento'  => $idDocumento,
                'codigo'        => $codigo,
                'id_persona'    => $idPersona,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en ExpedienteService::crearExpedienteVirtual: ' . $e->getMessage());
            return [
                'ok'      => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /** Busca o crea la persona remitente por tipo + número de documento. */
    private function resolverPersona(array $datos): int
    {
        $stmt = $this->db->prepare(
            'SELECT id_persona FROM personas WHERE tipo_documento = :tipo AND numero_documento = :numero LIMIT 1'
        );
        $stmt->execute([
            ':tipo'   => $datos['tipo_documento'],
            ':numero' => $datos['numero_documento'],
        ]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $idPersona = (int) $existente['id_persona'];

            // Actualizar datos de contacto si vinieron nuevos.
            $stmt = $this->db->prepare(
                'UPDATE personas
                 SET nombres = :nombres,
                     apellido_paterno = :ap,
                     apellido_materno = :am,
                     email = :email,
                     telefono = :telefono,
                     direccion = :direccion,
                     actualizado_en = CURRENT_TIMESTAMP
                 WHERE id_persona = :id'
            );
            $stmt->execute([
                ':nombres'   => $datos['nombres'] ?? null,
                ':ap'        => $datos['apellido_paterno'] ?? null,
                ':am'        => $datos['apellido_materno'] ?? null,
                ':email'     => $datos['email'] ?? null,
                ':telefono'  => $datos['telefono'] ?? null,
                ':direccion' => $datos['direccion'] ?? null,
                ':id'        => $idPersona,
            ]);
            return $idPersona;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO personas
             (tipo_persona, tipo_documento, numero_documento, nombres,
              apellido_paterno, apellido_materno, email, telefono, direccion, estado)
             VALUES (:tipo_persona, :tipo_documento, :numero_documento, :nombres,
                     :apellido_paterno, :apellido_materno, :email, :telefono, :direccion, :estado)'
        );
        $stmt->execute([
            ':tipo_persona'      => $datos['tipo_persona'] ?? 'Natural',
            ':tipo_documento'    => $datos['tipo_documento'],
            ':numero_documento'  => $datos['numero_documento'],
            ':nombres'           => $datos['nombres'] ?? '',
            ':apellido_paterno'  => $datos['apellido_paterno'] ?? '',
            ':apellido_materno'  => $datos['apellido_materno'] ?? '',
            ':email'             => $datos['email'] ?? null,
            ':telefono'          => $datos['telefono'] ?? null,
            ':direccion'         => $datos['direccion'] ?? null,
            ':estado'            => $datos['estado_persona'] ?? 'Activo',
        ]);
        return (int) $this->db->lastInsertId();
    }
}
