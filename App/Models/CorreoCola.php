<?php

namespace App\Models;

use PDO;
use Src\Core\Conexion;

class CorreoCola
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function encolar(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO correos_cola
             (destinatario, cc, asunto, cuerpo_html, adjunto_nombre, adjunto_contenido, estado, id_expediente)
             VALUES (:dest, :cc, :asunto, :cuerpo, :adjnom, :adjcont, "Pendiente", :idexp)'
        );
        $stmt->execute([
            ':dest'    => $datos['destinatario'],
            ':cc'      => $datos['cc'] ?? null,
            ':asunto'  => $datos['asunto'],
            ':cuerpo'  => $datos['cuerpo_html'],
            ':adjnom'  => $datos['adjunto_nombre'] ?? null,
            ':adjcont' => $datos['adjunto_contenido'] ?? null,
            ':idexp'   => $datos['id_expediente'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function pendientes(int $limite = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM correos_cola WHERE estado = 'Pendiente' ORDER BY creado_en ASC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarEnviado(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE correos_cola SET estado='Enviado', enviado_en=NOW() WHERE id_correo=:id"
        );
        $stmt->execute([':id' => $id]);
    }

    public function marcarError(int $id, string $error): void
    {
        $stmt = $this->db->prepare(
            "UPDATE correos_cola SET estado=IF(intentos>=3,'Error','Pendiente'), intentos=intentos+1, ultimo_error=:err WHERE id_correo=:id"
        );
        $stmt->execute([':id' => $id, ':err' => $error]);
    }
}
