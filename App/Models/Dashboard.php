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
            $nombreArea = $userData['nombre_area'] ?? 'Acceso Global';

            $expedientesPendientes = [];
            $totalPendientes = 0;

            // LÓGICA DIFERENCIADA POR ROL:
            if ($rolActual === 'ADMINISTRADOR') {
                // El Administrador ve TODOS los pendientes generales de la institución
                $sqlExp = "SELECT e.*, p.nombres, p.apellido_paterno, p.razon_social 
                       FROM expedientes e
                       INNER JOIN personas p ON e.id_remitente = p.id_persona
                       WHERE e.estado_actual = 'Registrado'
                       ORDER BY e.fecha_registro DESC";

                $stmtExp = $this->db->query($sqlExp);
                $expedientesPendientes = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
                $totalPendientes = count($expedientesPendientes);

            } elseif ($rolActual === 'MESA_DE_PARTES') {
                // Mesa de partes también ve los trámites recién ingresados para derivarlos
                $sqlExp = "SELECT e.*, p.nombres, p.apellido_paterno, p.razon_social 
                       FROM expedientes e
                       INNER JOIN personas p ON e.id_remitente = p.id_persona
                       WHERE e.estado_actual = 'Registrado'
                       ORDER BY e.fecha_registro DESC";

                $stmtExp = $this->db->query($sqlExp);
                $expedientesPendientes = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
                $totalPendientes = count($expedientesPendientes);

            } elseif (!is_null($idAreaUsuario)) {
                // Los usuarios comunes de área solo ven lo derivado a su oficina
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
                $totalPendientes = count($expedientesPendientes);
            }

            return [
                'id_area' => $idAreaUsuario,
                'rol' => $rolActual,
                'nombre_area' => $nombreArea,
                'expedientes_pendientes' => $expedientesPendientes,
                'total_pendientes_area' => $totalPendientes // Servirá tanto para el gráfico como para la tarjeta global del Admin
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

    public function getDocumentosObservados()
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





}