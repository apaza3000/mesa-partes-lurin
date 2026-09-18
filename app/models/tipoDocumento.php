<?php

require_once __DIR__ . '/Conexion.php';

class tipoDocumento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Listar tipos de documentos habilitados
    public function obtenerActivos(): array
    {
        try {
            $sql = "SELECT * FROM tipos_documento WHERE estado = 'Activo' ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}