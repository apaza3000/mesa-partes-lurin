<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Derivacion;
use App\Models\Observacion;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class ObservacionController extends Controller
{
    use AuthorizesRequest;

    /** SEC-06: observar solo pertenece a perfiles de área. */
    private const RolesPermitidos = Roles::AtencionArea;

    private Observacion $observacionModel;
    private Derivacion $derivacionModel;

    public function __construct()
    {
        $this->observacionModel = new Observacion();
        $this->derivacionModel = new Derivacion();
    }

    /**
     * TAREA E05-1: Registrar la observación de un expediente recepcionado por el área.
     * El formulario llega desde el modal de la bandeja de entrada del área.
     */
    public function crear(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        foreach ($datos as $k => $v) {
            if (is_string($v)) {
                $datos[$k] = trim($v);
            }
        }

        // Pestaña de origen para devolver al usuario al mismo punto de la bandeja
        $tabsValidos = ['pendientes', 'en_proceso', 'atendidos_devueltos', 'todos'];
        $tab = in_array($datos['tab'] ?? '', $tabsValidos, true) ? $datos['tab'] : 'en_proceso';
        $volver = '/area/bandeja-entrada?tab=' . $tab;

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);

        // SEC-01: CSRF obligatorio y sin ramas fail-open.
        $this->requerirCsrf($datos, $volver);

        $contexto = $this->contextoObservacion($idDerivacion);
        if (!$contexto['ok']) {
            Flash::error($contexto['message']);
            $this->redirect($volver);
            return;
        }

        $errors = $this->validarObservacion($datos);
        if (!empty($errors)) {
            Flash::error(implode(' ', $errors));
            $this->redirect($volver);
            return;
        }

        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

        $resultado = $this->observacionModel->observar(
            $idDerivacion,
            $idUsuario,
            $datos['descripcion']
        );

        if ($resultado['success']) {
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/bandeja-entrada?tab=en_proceso');
    }

    /**
     * Validación común de acceso: derivación existente, del área del usuario (o admin)
     * y en estado observable.
     */
    private function contextoObservacion(int $idDerivacion): array
    {
        if ($idDerivacion <= 0) {
            return ['ok' => false, 'message' => 'Identificador de derivación inválido.'];
        }

        $derivacion = $this->derivacionModel->getById($idDerivacion);
        if (!$derivacion) {
            return ['ok' => false, 'message' => 'No se encontró la derivación especificada.'];
        }

        $user = Session::user() ?? [];
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        if (!$esAdmin && (int) $derivacion['id_area_destino'] !== $idAreaUsuario) {
            return ['ok' => false, 'message' => 'No tiene permisos para observar trámites de otra área.'];
        }

        if (!in_array($derivacion['estado'], ['Recibido', 'En_Atencion'], true)) {
            return ['ok' => false, 'message' => 'Solo se pueden observar expedientes ya recepcionados por el área (estado Recibido o En Atención).'];
        }

        return ['ok' => true, 'derivacion' => $derivacion];
    }

    /**
     * Los motivos o requisitos faltantes son obligatorios y acotados
     * (la columna descripcion admite hasta 1000 caracteres).
     */
    private function validarObservacion(array $datos): array
    {
        $errors = [];

        $descripcion = (string) ($datos['descripcion'] ?? '');
        $longitud = mb_strlen($descripcion);

        if ($descripcion === '') {
            $errors[] = 'Detalle los motivos o requisitos faltantes de la observación.';
        } elseif ($longitud < 10) {
            $errors[] = 'La descripción de la observación debe tener al menos 10 caracteres.';
        } elseif ($longitud > 1000) {
            $errors[] = 'La descripción de la observación no debe exceder 1000 caracteres.';
        }

        return $errors;
    }
}
