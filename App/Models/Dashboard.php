<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use Src\Core\Conexion;

class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }
//PROVICIONAL CONSEGIR ROL DEL USUARIO ACTUAL

    public function getTotalDocumentosRegistrados()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from documentos'
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getExpedientesRegistrados()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from expedientes where estado_actual = "Registrado"'
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }


    public function getExpedientesEnProceso()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from expedientes where estado_actual = "En_Proceso"'
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getTramitesAtendidos_Archivados()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from expedientes where estado_actual = "Atendido" or estado_actual = "Archivado"'
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }



}