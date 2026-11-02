<?php

namespace App\Models;

use PDO;

class Expediente
{
    public function __construct(private PDO $db) {}

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO expedientes
             (codigo_unico, id_remitente, asunto, prioridad, canal_ingreso, estado_actual)
             VALUES (:codigo, :remitente, :asunto, :prioridad, :canal, 'Registrado')"
        );
        $stmt->execute([
            ':codigo' => $datos['codigo_expediente'],
            ':remitente' => (int) $datos['id_remitente'],
            ':asunto' => $datos['asunto'],
            ':prioridad' => $datos['prioridad'],
            ':canal' => $datos['canal_ingreso'] ?? 'Virtual',
        ]);
        return (int) $this->db->lastInsertId();
    }
}
