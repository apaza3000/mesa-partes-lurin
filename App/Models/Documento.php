<?php
namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class Documento
{
    private PDO $db;
 
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Conexion::getConexion();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO documentos
             (id_expediente, id_tipo_doc, numero_documento, asunto, folios, fecha_documento,
              tipo_documento_registro, es_principal, creado_por)
             VALUES (:expediente, :tipo, :numero, :asunto, :folios, :fecha,
                     'Ingresado', 1, :creado_por)"
        );
        $stmt->execute([
            ':expediente' => (int) $datos['id_expediente'],
            ':tipo' => (int) $datos['id_tipo_doc'],
            ':numero' => $datos['numero_documento'],
            ':asunto' => $datos['asunto'],
            ':folios' => (int) $datos['folios'],
            ':fecha' => $datos['fecha_documento'],
            ':creado_por' => $datos['creado_por'],
        ]);
        return (int) $this->db->lastInsertId();
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