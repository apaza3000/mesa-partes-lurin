<?php

namespace App\Models;

use App\Config\Conexion;
use PDO;
use PDOException;

class Derivacion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    // Derivar un expediente de un área a otra
    public function crearDerivacion(array $datos): bool
    {
        try {
            $sql = "INSERT INTO derivaciones (id_documento, id_area_origen, id_area_destino, id_usuario_envia, observaciones, estado_derivacion) 
                    VALUES (:id_documento, :id_area_origen, :id_area_destino, :id_usuario_envia, :observaciones, 'Pendiente')";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_documento'    => $datos['id_documento'],
                ':id_area_origen'  => $datos['id_area_origen'],
                ':id_area_destino' => $datos['id_area_destino'],
                ':id_usuario_envia'=> $datos['id_usuario_envia'],
                ':observaciones'   => $datos['observaciones'] ?? null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener historial de pases/derivaciones de un documento especifico
    public function obtenerPorDocumento(int $idDocumento): array
    {
        try {
            $sql = "SELECT d.*, 
                           ao.nombre AS area_origen, 
                           ad.nombre AS area_destino,
                           CONCAT(p.nombre, ' ', p.apellido_P) AS usuario_remitente
                    FROM derivaciones d
                    INNER JOIN areas ao ON d.id_area_origen = ao.id_area
                    INNER JOIN areas ad ON d.id_area_destino = ad.id_area
                    INNER JOIN usuarios u ON d.id_usuario_envia = u.id_usuario
                    INNER JOIN persona p ON u.id_persona = p.id_persoan
                    WHERE d.id_documento = :id_documento
                    ORDER BY d.fecha_envio DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_documento' => $idDocumento]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}