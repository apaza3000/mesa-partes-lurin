<?php

namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

class TipoDocumento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Listar tipos de documentos habilitados
    // OPT-02: fuente única del catálogo (la usan MesaPartes y EntregaModel).
    public function obtenerActivos(): array
    {
        try {
            $sql = "SELECT id_tipo_doc, nombre FROM tipos_documento WHERE estado = 'Activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
