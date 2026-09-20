<?php
namespace App\Models;

use PDO;
use PDOException;

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
                    CONCAT(p.nombre, ' ', p.apellido_p, ' ', p.apellido_m) AS remitente
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
}