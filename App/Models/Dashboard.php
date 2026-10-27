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
        // 1. Obtener el usuario logueado en la sesión
        $userSession = Session::user();

        if (!$userSession || !isset($userSession['id_usuario'])) {
            return null; // No hay usuario autenticado
        }

        $idUsuario = $userSession['id_usuario'];

        try {
            // 2. Consultar rol, área y nombre del área directamente en la base de datos
            $sql = "SELECT u.id_area, r.nombre AS rol, a.nombre AS nombre_area 
                    FROM usuarios u
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

            $rolActual = $userData['rol'] ?? 'CONSULTOR';
            $idAreaUsuario = $userData['id_area'] ?? null;
            $nombreArea = $userData['nombre_area'] ?? 'Sin área asignada';

            // 3. Obtener los expedientes pendientes según el rol o área detectados
            $expedientesPendientes = [];

            if ($rolActual === 'ADMINISTRADOR' || $rolActual === 'MESA_DE_PARTES') {
                // Roles globales ven todos los pendientes generales (Registrados)
                $sqlExp = "SELECT e.*, p.nombres, p.apellido_paterno, p.razon_social 
                           FROM expedientes e
                           INNER JOIN personas p ON e.id_remitente = p.id_persona
                           WHERE e.estado_actual = 'Registrado'
                           ORDER BY e.fecha_registro DESC";

                $stmtExp = $this->db->query($sqlExp);
                $expedientesPendientes = $stmtExp->fetchAll(PDO::FETCH_ASSOC);

            } elseif (!is_null($idAreaUsuario)) {
                // Roles de área ven solo los pendientes derivados a su oficina
                $sqlExp = "SELECT e.*, p.nombres, p.apellido_paterno, p.razon_social, d.estado as estado_derivacion
                           FROM expedientes e
                           INNER JOIN personas p ON e.id_remitente = p.id_persona
                           INNER JOIN derivaciones d ON e.id_expediente = d.id_expediente
                           WHERE d.id_area_destino = ? 
                           AND d.estado IN ('Pendiente', 'Recibido', 'En_Atencion')
                           ORDER BY d.fecha_envio DESC";

                $stmtExp = $this->db->prepare($sqlExp);
                $stmtExp->execute([$idAreaUsuario]);
                $expedientesPendientes = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
            }

            // 4. Retornar un arreglo compacto con toda la información unificada
            return [
                'id_area' => $idAreaUsuario,
                'rol' => $rolActual,
                'nombre_area' => $nombreArea,
                'expedientes_pendientes' => $expedientesPendientes
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