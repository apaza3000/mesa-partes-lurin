<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Documento
{
    private PDO $db;
 
    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Obtener todos los documentos con información de la persona y el tipo de documento
    public function obtenerTodos(): array
    {
        try {
            $sql = "SELECT d.*, td.nombre AS tipo_documento,
                    CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')) AS remitente
                    FROM documentos d
                    INNER JOIN tipos_documento td ON d.id_tipo_doc = td.id_tipo_doc
                    INNER JOIN personas p ON d.id_persona = p.id_persona
                    ORDER BY d.creado_en DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Registrar un nuevo expediente/documento
    public function registrar(array $datos): bool
    {
        try {
            $sql = "INSERT INTO documentos (codigo_unico, id_tipo_doc, numero_documento, asunto, folios, estado_actual, id_persona)
                    VALUES (:codigo_unico, :id_tipo_doc, :numero_documento, :asunto, :folios, :estado_actual, :id_persona)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':codigo_unico' => $datos['codigo_unico'],
                ':id_tipo_doc' => $datos['id_tipo_doc'],
                ':numero_documento' => $datos['numero_documento'],
                ':asunto' => $datos['asunto'],
                ':folios' => $datos['folios'],
                ':estado_actual' => $datos['estado_actual'],
                ':id_persona' => $datos['id_persona']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * TAREA E04-1: Registrar el documento de respuesta emitido por el área
     * (tipo_documento_registro = 'Respuesta') junto con sus archivos adjuntos.
     *
     * Trabaja DENTRO de la transacción abierta por el llamador (Derivacion::atender):
     * no captura excepciones para que el rollback quede a cargo del orquestador.
     *
     * @param array $datos     id_expediente, id_tipo_doc, numero_documento, asunto, folios, fecha_documento
     * @param array $archivos  adjuntos ya subidos (nombre_original, nombre_archivo, ruta_archivo, ...)
     * @return int id_documento generado
     */
    public function registrarRespuesta(array $datos, array $archivos, int $idUsuario): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO documentos
             (id_expediente, id_tipo_doc, numero_documento, asunto, folios,
              fecha_documento, tipo_documento_registro, es_principal, creado_por)
             VALUES (:id_exp, :id_tipo, :numero, :asunto, :folios,
                     :fecha, :tipo_registro, 0, :creado_por)'
        );
        $stmt->execute([
            ':tipo_registro'  => 'Respuesta',
            ':id_exp'         => (int) $datos['id_expediente'],
            ':id_tipo'        => (int) $datos['id_tipo_doc'],
            ':numero'         => $datos['numero_documento'],
            ':asunto'         => $datos['asunto'],
            ':folios'         => max(1, (int) ($datos['folios'] ?? 1)),
            ':fecha'          => !empty($datos['fecha_documento']) ? $datos['fecha_documento'] : date('Y-m-d'),
            ':creado_por'     => $idUsuario,
        ]);
        $idDocumento = (int) $this->db->lastInsertId();

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

        return $idDocumento;
    }
}