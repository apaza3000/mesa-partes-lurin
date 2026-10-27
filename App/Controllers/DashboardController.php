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
        $datosUsuarioBD = $this->DashboardModel->obtenerDatosDelUserDesdeBD();

        $rolActual = $datosUsuarioBD['rol'] ?? 'CONSULTOR';
        $expedientesPendientes = $datosUsuarioBD['expedientes_pendientes'] ?? [];
        $totalPendientesArea = $datosUsuarioBD['total_pendientes_area'] ?? 0;

        $totalDocumentosRegistrados = $this->DashboardModel->getTotalDocumentosRegistrados();
        $expedientesEnProceso = $this->DashboardModel->getExpedientesEnProceso();
        $tramitesAtendidos_Archivados = $this->DashboardModel->getTramitesAtendidos_Archivados();
        $totalObservados = $this->DashboardModel->getDocumentosObservados();

        return $this->view('dashboard.index', [
            'usuario' => $datosUsuarioBD,
            'rolActual' => $rolActual,
            'expedientesPendientes' => $expedientesPendientes,
            'totalPendientesArea' => $totalPendientesArea,
            'totalDocumentosRegistrados' => $totalDocumentosRegistrados,
            'expedientesEnProceso' => $expedientesEnProceso,
            'tramitesAtendidos_Archivados' => $tramitesAtendidos_Archivados,
            'totalObservados' => $totalObservados
        ], 'app');
    }

}