<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Notificacion;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E08-1: notificaciones web del usuario autenticado.
 *
 * El header consume `index` y `contador` por AJAX; el desplegable marca
 * notificaciones mediante POST con CSRF.
 */
class NotificacionController extends Controller
{
    use AuthorizesRequest;

    private Notificacion $model;

    public function __construct()
    {
        $this->model = new Notificacion();
    }

    /**
     * Lista JSON de las notificaciones del usuario.
     */
    public function index(): void
    {
        $this->requerirAutenticacion();

        $request = new Request();
        $soloNoLeidas = (string) ($request->get('todas') ?? '') !== '1';
        $idUsuario = $this->idUsuario();

        $this->json([
            'success'       => true,
            'sin_leer'      => $this->model->sinLeer($idUsuario),
            'notificaciones'=> $this->model->listar($idUsuario, $soloNoLeidas, 10),
        ]);
    }

    /**
     * Solo el contador (para el refresco cada 60 s).
     */
    public function contador(): void
    {
        $this->requerirAutenticacion();

        $this->json([
            'success'  => true,
            'sin_leer' => $this->model->sinLeer($this->idUsuario()),
        ]);
    }

    public function marcarLeida(): void
    {
        $this->requerirAutenticacion();

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos);

        $id = (int) ($datos['id_notificacion'] ?? $datos['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Notificación inválida.'], 422);
            return;
        }

        $ok = $this->model->marcarLeida($id, $this->idUsuario());

        $this->json([
            'success'  => $ok,
            'sin_leer' => $this->model->sinLeer($this->idUsuario()),
        ], $ok ? 200 : 404);
    }

    public function marcarTodas(): void
    {
        $this->requerirAutenticacion();

        $request = new Request();
        $this->requerirCsrf($request->all());

        $ok = $this->model->marcarTodasLeidas($this->idUsuario());

        $this->json([
            'success'  => $ok,
            'sin_leer' => $this->model->sinLeer($this->idUsuario()),
        ]);
    }

    private function idUsuario(): int
    {
        $user = Session::user() ?? [];

        return (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
    }
}
