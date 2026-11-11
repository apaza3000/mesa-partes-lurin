<?php

namespace App\Controllers;

use Src\Core\Controller;
use Src\Core\Session;
use App\Models\Auditoria;
use App\Models\Dashboard;
use Src\Core\Request;
use Src\Core\Validator;

class DashboardController extends Controller
{
    private Dashboard $DashboardModel;

    public function __construct()
    {
        $this->DashboardModel = new Dashboard();
    }


    public function index()
    {
        if (!Session::isAuthenticated()) {
            return $this->redirect("/login");
        }

        // 1. Obtener datos del usuario y sus expedientes correspondientes
        $datosUsuarioBD = $this->DashboardModel->obtenerDatosDelUserDesdeBD();

        $nombrePersona = $datosUsuarioBD['nombre_persona'] ?? 'Nombre_de_usuario_no_encontrado';
        $rolActual = $datosUsuarioBD['rol'] ?? 'Rol_no_encontrado';
        $areaActual = $datosUsuarioBD['nombre_area'] ?? 'Sin área asignada (Acceso Global)';
        $expedientesPendientes = $datosUsuarioBD['expedientes_pendientes'] ?? [];
        $totalPendientesArea = $datosUsuarioBD['total_pendientes_area'] ?? 0;

        // 2. Métricas generales para las tarjetas del dashboard
        $getTotalExpedientes = $this->DashboardModel->getTotalExpedientes();
        $getExpedientesRegistrados = $this->DashboardModel->getExpedientesRegistrados();
        $expedientesEnProceso = $this->DashboardModel->getExpedientesEnProceso();
        $tramitesAtendidos_Archivados = $this->DashboardModel->getTramitesAtendidos_Archivados();
        $getExpedientesObservados = $this->DashboardModel->getExpedientesObservados();

        // 3. NUEVO: Verificaciones para el componente "Estado del Sistema"
        $dbConectada = $this->DashboardModel->verificarEstadoBD();

        $sesionProximaAExpirar = false;
        if (isset($_SESSION['ultimo_actividad'])) {
            $tiempoInactivo = time() - $_SESSION['ultimo_actividad'];
            $limiteInactividad = 15 * 60; // 15 minutos (ajusta según tu config)
            if ($tiempoInactivo > ($limiteInactividad - 180)) { // Advertir si faltan menos de 3 min
                $sesionProximaAExpirar = true;
            }
        }

        $estadoSistema = [
            'db_status' => $dbConectada,
            'session_warning' => $sesionProximaAExpirar
        ];

        // 4. Retornar la vista enviando también el estado del sistema
        return $this->view('dashboard.index', [
            'usuario' => $nombrePersona,
            'rolActual' => $rolActual,
            'areaActual' => $areaActual,

            'expedientesPendientes' => $expedientesPendientes,
            'totalPendientesArea' => $totalPendientesArea,
            'getTotalExpedientes' => $getTotalExpedientes,
            'getExpedientesRegistrados' => $getExpedientesRegistrados,
            'expedientesEnProceso' => $expedientesEnProceso,
            'tramitesAtendidos_Archivados' => $tramitesAtendidos_Archivados,
            'getExpedientesObservados' => $getExpedientesObservados,
            'estadoSistema' => $estadoSistema
        ], 'app');
    }

}