<?php
namespace App\Controllers;

use App\Models\ConsultaExpediente;
use App\Support\Csrf;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class TrazabilidadController extends Controller
{
    public function index(): void
    {
        if (!Session::isAuthenticated()) { $this->redirect('/login'); return; }
        $codigo = trim((string) (new Request())->get('codigo', ''));
        $expediente = null; $historial = []; $derivaciones = []; $observaciones = [];
        if ($codigo !== '') {
            $model = new ConsultaExpediente();
            $expediente = $model->buscarPorCodigo($codigo);
            if ($expediente) {
                $historial = $model->obtenerHistorial((int)$expediente['id_expediente']);
                $derivaciones = $model->obtenerDerivaciones((int)$expediente['id_expediente']);
                $observaciones = $model->obtenerObservaciones((int)$expediente['id_expediente']);
            }
        }
        $this->view('trazabilidad.index', [
            'titulo' => 'Trazabilidad',
            'codigo' => $codigo,
            'expediente' => $expediente,
            'historial' => $historial,
            'derivaciones' => $derivaciones,
            'observaciones' => $observaciones,
            'csrf_token' => Csrf::token(),
        ], 'app');
    }
}