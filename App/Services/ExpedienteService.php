<?php

namespace App\Services;

use PDO;
use RuntimeException;
use Src\Core\Conexion;

class ExpedienteService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $conexion = $db ?? Conexion::getConexion();
        if ($conexion === null) {
            throw new RuntimeException('No se pudo establecer la conexión a la base de datos.');
        }

        $this->db = $conexion;
    }

    public function transBegin(): bool
    {
        return $this->db->beginTransaction();
    }

    public function transCommit(): bool
    {
        return $this->db->commit();
    }

    public function transRollback(): bool
    {
        return $this->db->rollBack();
    }
}
