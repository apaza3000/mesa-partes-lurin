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
        $dbConectada = false;
        try {
            $conexion = \Src\Core\Conexion::getConexion();
            if ($conexion) {
                $dbConectada = true;
            }
        } catch (\Exception $e) {
            $dbConectada = false;
        }
        $sesionActiva = isset($_SESSION['user']) && !empty($_SESSION['user']);
        $datosUsuarioBD = $this->DashboardModel->obtenerDatosDelUserDesdeBD();

        $rolActual = $datosUsuarioBD['rol'] ?? 'CONSULTOR';
        $expedientesPendientes = $datosUsuarioBD['expedientes_pendientes'] ?? [];

        $totalDocumentosRegistrados = $this->DashboardModel->getTotalDocumentosRegistrados();
        $expedientesRegistrados = $this->DashboardModel->getExpedientesRegistrados();
        $expedientesEnProceso = $this->DashboardModel->getExpedientesEnProceso();
        $tramitesAtendidos_Archivados = $this->DashboardModel->getTramitesAtendidos_Archivados();

        $datosUsuarioBD = $this->DashboardModel->obtenerDatosDelUserDesdeBD();

        $usuario = Session::user();


        return $this->view('dashboard.index', [
            'dbConectada' => $dbConectada,
            'sesionActiva' => $sesionActiva,

            'totalDocumentosRegistrados' => $totalDocumentosRegistrados,
            'expedientesRegistrados' => $expedientesRegistrados,
            'expedientesEnProceso' => $expedientesEnProceso,
            'tramitesAtendidos_Archivados' => $tramitesAtendidos_Archivados,
            'rolActual' => $rolActual,
            'usuario' => $datosUsuarioBD,
        ], 'app');
    }

}