<?php

namespace App\Models;

use Exception;
use PDO;
use Src\Core\Session;
use PDOException;
use Src\Core\Conexion;

class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }
    /*
     ===========================================================
     OBTENER DATOS DEL USUARIO ACTUAL
     ===========================================================
     */
    public function obtenerDatosDelUserDesdeBD()
    {
        $userSession = Session::user();

        if (!$userSession || !isset($userSession['id_usuario'])) {
            return null;
        }

        $idUsuario = $userSession['id_usuario'];

        try {
            $sql = "SELECT p.nombres, u.id_area, r.nombre AS rol, a.nombre AS nombre_area 
                    FROM usuarios u
                    LEFT JOIN personas p ON p.id_persona = u.id_persona
                    LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
                    LEFT JOIN roles r ON ur.id_rol = r.id_rol
                    LEFT JOIN areas a ON u.id_area = a.id_area
                    WHERE u.id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                return null;
            }

            $rolActual = $userData['rol'] ?? 'rol_no_encontrado';
            $idAreaUsuario = $userData['id_area'] ?? null;
            $nombreArea = $userData['nombre_area'] ?? 'area_no_encontrada';
            $nombrePersona = $userData['nombres'] ?? 'Nombre_de_usuario_no_encontrado';

            $expedientesPendientes = [];
            $totalPendientes = 0;

            if (!is_null($idAreaUsuario)) {
                $sqlExp = "SELECT e.*, p.nombres, p.apellido_paterno, p.razon_social, d.estado as estado_derivacion
                           FROM expedientes e
                           INNER JOIN personas p ON e.id_remitente = p.id_persona
                           INNER JOIN derivaciones d ON e.id_expediente = d.id_expediente
                           WHERE d.id_area_destino = ? 
                           AND d.estado = 'Pendiente'
                           ORDER BY d.fecha_envio DESC";

                $stmtExp = $this->db->prepare($sqlExp);
                $stmtExp->execute([$idAreaUsuario]);
                $expedientesPendientes = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
                $totalPendientes = count($expedientesPendientes);


            }

            return [
                'nombre_persona' => $nombrePersona,
                'id_area' => $idAreaUsuario,
                'rol' => $rolActual,
                'nombre_area' => $nombreArea,
                'expedientes_pendientes' => $expedientesPendientes,
                'total_pendientes_area' => $totalPendientes
            ];

        } catch (Exception $e) {
            return null;
        }
    }
    /*
     ===========================================================
     DATA DASHBOARD
     ===========================================================
     */
    public function verificarEstadoBD()
    {
        try {
            // Un query ultraligero para probar la conexión activa
            $stmt = $this->db->query("SELECT 1");
            return ($stmt !== false);
        } catch (Exception $e) {
            return false;
        }
    }


    public function getTotalExpedientes()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from expedientes'
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

    public function getExpedientesObservados()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(*) as total from expedientes where estado_actual = "Observado"'
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
    public function getMisExpedientesPendientes()
    {
        try {
            $stmt = $this->db->query(
                'SELECT COUNT(e.id_expediente) AS Espedientes_por_area,
                    d.estado
                    FROM expedientes e

                    INNER JOIN derivaciones d ON e.id_expediente = d.id_expediente
                    INNER JOIN areas a ON d.id_area_destino = a.id_area
                    WHERE a.nombre = ? AND d.estado = "Pendiente"
                    '
            );

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }





}