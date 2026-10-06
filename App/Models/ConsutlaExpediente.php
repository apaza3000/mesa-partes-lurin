<?php

namespace App\Models;

use Src\Core\Conexion;



class ConsutlaExpediente
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /* ============================================================
     * BUSCAR EXPEDIENTE POR CÓDIGO
     * Opcionalmente validar con DNI del remitente
     * ============================================================ */
    public function buscarPorCodigo($codigo, $numeroDocumento = null)
    {
        $sql = "SELECT 
                    e.*,
                    p.id_persona,
                    p.tipo_documento,
                    p.numero_documento,
                    p.nombres,
                    p.apellido_paterno,
                    p.apellido_materno,
                    p.razon_social,
                    p.email,
                    p.telefono,
                    p.direccion
                FROM expedientes e
                INNER JOIN personas p ON p.id_persona = e.id_remitente
                WHERE e.codigo_expediente = :codigo";

        $params = [':codigo' => $codigo];

        if (!empty($numeroDocumento)) {
            $sql .= " AND p.numero_documento = :num_doc";
            $params[':num_doc'] = $numeroDocumento;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /* ============================================================
     * DOCUMENTO PRINCIPAL + ADJUNTOS
     * ============================================================ */
    public function obtenerDocumentos($idExpediente)
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, td.nombre AS tipo_doc_nombre
             FROM documentos d
             INNER JOIN tipos_documento td ON td.id_tipo_doc = d.id_tipo_doc
             WHERE d.id_expediente = ?
             ORDER BY d.es_principal DESC, d.id_documento ASC"
        );
        $stmt->execute([$idExpediente]);
        $documentos = $stmt->fetchAll();

        foreach ($documentos as &$doc) {
            $stmt = $this->db->prepare(
                "SELECT id_archivo, nombre_original, nombre_archivo, ruta_archivo,
                        extension, mime_type, tamanio, creado_en
                 FROM archivos_adjuntos
                 WHERE id_documento = ?
                 ORDER BY id_archivo ASC"
            );
            $stmt->execute([$doc['id_documento']]);
            $doc['adjuntos'] = $stmt->fetchAll();
        }

        return $documentos;
    }

    /* ============================================================
     * HISTORIAL DE ESTADOS
     * ============================================================ */
    public function obtenerHistorial($idExpediente)
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, 
                    u.username,
                    p.nombres, p.apellido_paterno
             FROM historial_expediente h
             LEFT JOIN usuarios u ON u.id_usuario = h.id_usuario
             LEFT JOIN personas p ON p.id_persona = u.id_persona
             WHERE h.id_expediente = ?
             ORDER BY h.fecha ASC"
        );
        $stmt->execute([$idExpediente]);
        return $stmt->fetchAll();
    }

    /* ============================================================
     * DERIVACIONES
     * ============================================================ */
    public function obtenerDerivaciones($idExpediente)
    {
        $stmt = $this->db->prepare(
            "SELECT d.*,
                    ao.nombre AS area_origen,
                    ad.nombre AS area_destino
             FROM derivaciones d
             INNER JOIN areas ao ON ao.id_area = d.id_area_origen
             INNER JOIN areas ad ON ad.id_area = d.id_area_destino
             WHERE d.id_expediente = ?
             ORDER BY d.fecha_envio ASC"
        );
        $stmt->execute([$idExpediente]);
        return $stmt->fetchAll();
    }

    /* ============================================================
     * OBSERVACIONES
     * ============================================================ */
    public function obtenerObservaciones($idExpediente)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    p.nombres, p.apellido_paterno
             FROM observaciones o
             LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
             LEFT JOIN personas p ON p.id_persona = u.id_persona
             WHERE o.id_expediente = ?
             ORDER BY o.fecha_registro ASC"
        );
        $stmt->execute([$idExpediente]);
        return $stmt->fetchAll();
    }
}
