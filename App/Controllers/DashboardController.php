<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Dashboard;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Session;

class DashboardController extends Controller
{
    use AuthorizesRequest;

    private Dashboard $DashboardModel;

    public function __construct()
    {
        $this->DashboardModel = new Dashboard();
    }


    public function index()
    {
        // El panel es el punto de entrada de cualquier rol autenticado.
        $this->requerirAutenticacion();

        $datosUsuarioBD = $this->DashboardModel->obtenerDatosDelUserDesdeBD();

        // MEN-01: la vista debe saber si realmente hay conexión con la BD.
        $dbConectada = is_array($datosUsuarioBD) && $datosUsuarioBD !== [];

        if (!$dbConectada) {
            Flash::error('No fue posible leer los datos del usuario. Verifique su cuenta con el administrador.');
            $datosUsuarioBD = [];
        }

        $roles = Roles::delUsuarioActual();
        $rolActual = Roles::prioritario($roles) ?? 'CONSULTA';

        $expedientesPendientes = $datosUsuarioBD['expedientes_pendientes'] ?? [];
        // MEN-02: el gráfico necesita un entero, no el array de filas.
        $misPendientes = is_array($expedientesPendientes)
            ? count($expedientesPendientes)
            : (int) $expedientesPendientes;

        $totalDocumentosRegistrados = $this->DashboardModel->getTotalDocumentosRegistrados();
        $expedientesEnProceso = $this->DashboardModel->getExpedientesEnProceso();
        $tramitesAtendidos_Archivados = $this->DashboardModel->getTramitesAtendidos_Archivados();
        $totalObservados = $this->DashboardModel->getDocumentosObservados();

        return $this->view('dashboard.index', [
            'usuario' => $datosUsuarioBD,
            'rolActual' => $rolActual,
            'rolesUsuario' => $roles,
            'expedientesPendientes' => $expedientesPendientes,
            'misPendientes' => $misPendientes,
            'totalPendientesArea' => $datosUsuarioBD['total_pendientes_area'] ?? 0,
            'totalDocumentosRegistrados' => $totalDocumentosRegistrados,
            // MEN-01: variables que la vista ya consumía sin recibir nunca valor.
            'expedientesRegistrados' => $this->DashboardModel->getExpedientesRegistrados(),
            'dbConectada' => $dbConectada,
            'sesionActiva' => $this->sesionRestante(),
            'expedientesEnProceso' => $expedientesEnProceso,
            'tramitesAtendidos_Archivados' => $tramitesAtendidos_Archivados,
            'totalObservados' => $totalObservados
        ], 'app');
    }

    /**
     * Minutos restantes de la sesión, calculados desde el arranque de la sesión.
     * Sustituye al marcador fijo que hacía que el panel siempre avisara de
     * "sesión próxima a expirar" (MEN-01).
     */
    private function sesionRestante(): int
    {
        $vida = (int) (ini_get('session.gc_maxlifetime') ?: 1800);
        $inicio = (int) (Session::get('usuario_login_ts') ?? ($_SESSION['usuario_login_ts'] ?? time()));
        $restante = $vida - (time() - $inicio);

        return max(0, (int) floor($restante / 60));
    }
}
