<?php

namespace App\Models;

use Exception;
use Src\Core\Conexion;

class EntregaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /* ============================================================
     * BUSCAR PERSONA POR DOCUMENTO
     * ============================================================ */
    public function buscarPersonaPorDocumento($tipo, $numero)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM personas 
             WHERE tipo_documento = ? AND numero_documento = ? LIMIT 1"
        );
        $stmt->execute([$tipo, $numero]);
        return $stmt->fetch();
    }

    /* ============================================================
     * GENERAR CÓDIGO DE EXPEDIENTE
     * Formato: EXP-2026-00001
     * ============================================================ */
    private function generarCodigoExpediente()
    {
        $anio = (int) date('Y');

        $stmt = $this->db->prepare(
            "SELECT id_numerador, ultimo_numero 
             FROM numeradores 
             WHERE tipo = 'EXPEDIENTE' AND anio = ? 
             FOR UPDATE"
        );
        $stmt->execute([$anio]);
        $num = $stmt->fetch();

        if (!$num) {
            $stmt = $this->db->prepare(
                "INSERT INTO numeradores (tipo, anio, ultimo_numero) VALUES ('EXPEDIENTE', ?, 1)"
            );
            $stmt->execute([$anio]);
            $correlativo = 1;
        } else {
            $correlativo = $num['ultimo_numero'] + 1;
            $stmt = $this->db->prepare(
                "UPDATE numeradores SET ultimo_numero = ? WHERE id_numerador = ?"
            );
            $stmt->execute([$correlativo, $num['id_numerador']]);
        }

        return sprintf('EXP-%d-%05d', $anio, $correlativo);
    }

    /* ============================================================
     * REGISTRAR EXPEDIENTE COMPLETO
     * 1 expediente → 1 documento → N adjuntos
     * ============================================================ */
    public function registrarExpediente($datos, $documento, $archivos)
    {
        try {
            $this->db->beginTransaction();

            // 1. Persona: buscar o crear
            $persona = $this->buscarPersonaPorDocumento(
                $datos['tipo_documento'],
                $datos['numero_documento']
            );

            if ($persona) {
                $idPersona = $persona['id_persona'];

                // Actualizar datos de contacto si vinieron nuevos
                $stmt = $this->db->prepare(
                    "UPDATE personas 
                     SET nombres = :nombres,
                         apellido_paterno = :ap,
                         apellido_materno = :am,
                         email = :email,
                         telefono = :telefono,
                         direccion = :direccion,
                         actualizado_en = CURRENT_TIMESTAMP
                     WHERE id_persona = :id"
                );
                $stmt->execute([
                    ':nombres'  => $datos['nombres'],
                    ':ap'       => $datos['apellido_paterno'],
                    ':am'       => $datos['apellido_materno'],
                    ':email'    => $datos['email'],
                    ':telefono' => $datos['telefono'],
                    ':direccion' => $datos['direccion'],
                    ':id'       => $idPersona,
                ]);
            } else {
                $stmt = $this->db->prepare(
                    "INSERT INTO personas 
                     (tipo_persona, tipo_documento, numero_documento, nombres,
                      apellido_paterno, apellido_materno, email, telefono, direccion, estado)
                     VALUES ('Natural', :tipo_documento, :numero_documento, :nombres,
                             :apellido_paterno, :apellido_materno, :email, :telefono, :direccion, 'Activo')"
                );
                $stmt->execute([
                    ':tipo_documento'   => $datos['tipo_documento'],
                    ':numero_documento' => $datos['numero_documento'],
                    ':nombres'          => $datos['nombres'],
                    ':apellido_paterno' => $datos['apellido_paterno'],
                    ':apellido_materno' => $datos['apellido_materno'],
                    ':email'            => $datos['email'],
                    ':telefono'         => $datos['telefono'],
                    ':direccion'        => $datos['direccion'],
                ]);
                $idPersona = $this->db->lastInsertId();
            }

            // 2. Generar código
            $codigo = $this->generarCodigoExpediente();

            // 3. Insertar expediente (UNO SOLO)
            $stmt = $this->db->prepare(
                "INSERT INTO expedientes 
                 (codigo_expediente, id_remitente, asunto, prioridad, canal_ingreso, estado_actual)
                 VALUES (:codigo, :id_remitente, :asunto, :prioridad, 'Virtual', 'Registrado')"
            );
            $stmt->execute([
                ':codigo'       => $codigo,
                ':id_remitente' => $idPersona,
                ':asunto'       => $datos['asunto'],
                ':prioridad'    => $datos['prioridad'],
            ]);
            $idExpediente = $this->db->lastInsertId();

            // 4. Insertar documento (UNO SOLO)
            $stmt = $this->db->prepare(
                "INSERT INTO documentos 
                 (id_expediente, id_tipo_doc, numero_documento, asunto, folios,
                  fecha_documento, tipo_documento_registro, es_principal, creado_por)
                 VALUES (:id_exp, :id_tipo, :numero, :asunto, :folios,
                         :fecha, 'Ingresado', 1, NULL)"
            );
            $stmt->execute([
                ':id_exp'  => $idExpediente,
                ':id_tipo' => $documento['id_tipo_doc'],
                ':numero'  => $documento['numero_documento'] ?: null,
                ':asunto'  => $documento['asunto'],
                ':folios'  => (int) $documento['folios'],
                ':fecha'   => $documento['fecha_documento'] ?: null,
            ]);
            $idDocumento = $this->db->lastInsertId();

            // 5. Insertar N adjuntos para ese documento
            if (!empty($archivos)) {
                $stmt = $this->db->prepare(
                    "INSERT INTO archivos_adjuntos 
                     (id_documento, nombre_original, nombre_archivo, ruta_archivo,
                      extension, mime_type, tamanio, hash_archivo, creado_por)
                     VALUES (:id_doc, :nom_orig, :nom_arch, :ruta,
                             :ext, :mime, :tam, :hash, NULL)"
                );
                foreach ($archivos as $file) {
                    $stmt->execute([
                        ':id_doc'   => $idDocumento,
                        ':nom_orig' => $file['nombre_original'],
                        ':nom_arch' => $file['nombre_archivo'],
                        ':ruta'     => $file['ruta_archivo'],
                        ':ext'      => $file['extension'],
                        ':mime'     => $file['mime_type'],
                        ':tam'      => $file['tamanio'],
                        ':hash'     => $file['hash_archivo'],
                    ]);
                }
            }

            // 6. Historial inicial
            $stmt = $this->db->prepare(
                "INSERT INTO historial_expediente 
                 (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario)
                 VALUES (:id_exp, NULL, 'Registrado', NULL, 'Expediente registrado por el ciudadano vía web')"
            );
            $stmt->execute([':id_exp' => $idExpediente]);

            $this->db->commit();

            return [
                'ok'            => true,
                'id_expediente' => $idExpediente,
                'id_documento'  => $idDocumento,
                'codigo'        => $codigo,
                'id_persona'    => $idPersona,
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'ok'      => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /* ============================================================
     * CATÁLOGO DE TIPOS DE DOCUMENTO
     * ============================================================ */
    public function listarTiposDocumento()
    {
        $stmt = $this->db->query(
            "SELECT id_tipo_doc, nombre FROM tipos_documento 
             WHERE estado = 'Activo' ORDER BY nombre"
        );
        return $stmt->fetchAll();
    }
}
