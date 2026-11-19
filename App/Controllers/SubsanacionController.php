<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Area;
use App\Models\Observacion;
use App\Support\Catalogos;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E05-2: Subsanación de observaciones.
 *
 * El administrado presenta su descargo desde el portal público
 * (`/consulta/subsanar/{codigo}`) y el área lo revisa en
 * `/area/subsanaciones`, donde puede levantar la observación.
 */
class SubsanacionController extends Controller
{
    use AuthorizesRequest;

    private const RolesArea = Roles::AtencionArea;

    private Observacion $model;

    public function __construct()
    {
        $this->model = new Observacion();
    }

    /**
     * Formulario público de subsanación (sin sesión).
     */
    public function formulario(string $codigo): void
    {
        $codigo = trim($codigo);
        $datos = $this->model->buscarPorCodigo($codigo);

        $errors = [];
        $exito = '';

        if ($datos['expediente'] === null) {
            $errors['general'][] = 'No se encontró ningún expediente con el código indicado.';
        } elseif ($datos['expediente']['estado_actual'] !== 'Observado') {
            $errors['general'][] = 'El expediente no se encuentra observado; no requiere subsanación.';
        }

        if (($_GET['registrada'] ?? '') === '1') {
            $exito = 'La subsanación fue registrada. El área revisará la documentación presentada.';
        }

        $this->view('consulta.subsanar', [
            'codigo'        => $codigo,
            'expediente'    => $datos['expediente'],
            'observaciones' => $datos['observaciones'],
            'errors'        => $errors,
            'datos_viejos'  => ['codigo' => $codigo],
            'exito'         => $exito,
            'csrf_token'    => Csrf::token(),
        ]);
    }

    /**
     * Registro público de la subsanación con adjuntos PDF.
     */
    public function store(): void
    {
        $request = new Request();
        $datos = $request->all();

        foreach ($datos as $clave => $valor) {
            if (is_string($valor)) {
                $datos[$clave] = trim($valor);
            }
        }

        $this->requerirCsrf($datos, '/consulta');

        $codigo = (string) ($datos['codigo'] ?? '');
        $comentario = (string) ($datos['comentario'] ?? '');

        $errors = [];

        if ($codigo === '') {
            $errors['codigo'][] = 'Ingrese el código del expediente.';
        }

        $pendientes = $codigo !== '' ? $this->model->listarPendientesPorCodigo($codigo) : [];
        if ($codigo !== '' && $pendientes === []) {
            $errors['general'][] = 'El expediente no tiene observaciones pendientes de subsanar.';
        }

        $longitud = mb_strlen($comentario);
        if ($longitud === 0) {
            $errors['comentario'][] = 'Describa el descargo o sustento de la subsanación.';
        } elseif ($longitud < 10) {
            $errors['comentario'][] = 'El detalle de la subsanación debe tener al menos 10 caracteres.';
        } elseif ($longitud > Catalogos::limite('observaciones.descripcion')) {
            $errors['comentario'][] = 'El detalle no debe exceder 1000 caracteres.';
        }

        $archivos = [];
        $erroresArchivos = [];
        $maxSize = 10 * 1024 * 1024;
        $rutaBase = __DIR__ . '/../../public/uploads/subsanaciones/' . date('Y');

        $hayAdjuntos = is_array($_FILES['adjuntos']['name'] ?? null) && !empty($_FILES['adjuntos']['name'][0]);

        if ($hayAdjuntos) {
            if (!is_dir($rutaBase)) {
                mkdir($rutaBase, 0775, true);
            }

            $total = count($_FILES['adjuntos']['name']);
            for ($i = 0; $i < $total; $i++) {
                if (($_FILES['adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $erroresArchivos[] = 'No se pudo recibir el archivo "' . $_FILES['adjuntos']['name'][$i] . '".';
                    continue;
                }

                $nombreOriginal = (string) $_FILES['adjuntos']['name'][$i];
                $tmp = (string) $_FILES['adjuntos']['tmp_name'][$i];
                $tamanio = (int) $_FILES['adjuntos']['size'][$i];
                $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

                if ($ext !== 'pdf') {
                    $erroresArchivos[] = "El archivo \"$nombreOriginal\" no es un PDF válido.";
                    continue;
                }

                if ($tamanio > $maxSize) {
                    $erroresArchivos[] = "El archivo \"$nombreOriginal\" supera el límite de 10 MB.";
                    continue;
                }

                $nombreArchivo = uniqid('sub_', true) . '.pdf';
                $rutaDestino = $rutaBase . '/' . $nombreArchivo;

                if (!move_uploaded_file($tmp, $rutaDestino)) {
                    $erroresArchivos[] = "No se pudo almacenar el archivo \"$nombreOriginal\".";
                    continue;
                }

                $archivos[] = [
                    'nombre_original' => Catalogos::sanear($nombreOriginal, 'archivos_adjuntos.nombre_original'),
                    'nombre_archivo'  => $nombreArchivo,
                    'ruta_archivo'    => 'uploads/subsanaciones/' . date('Y') . '/' . $nombreArchivo,
                    'extension'       => $ext,
                    'mime_type'       => mime_content_type($rutaDestino) ?: 'application/pdf',
                    'tamanio'         => $tamanio,
                    'hash_archivo'    => hash_file('sha256', $rutaDestino),
                ];
            }
        }

        if ($erroresArchivos !== []) {
            $errors['adjuntos'] = $erroresArchivos;
        }

        if ($errors !== []) {
            $busqueda = $this->model->buscarPorCodigo($codigo);

            $this->view('consulta.subsanar', [
                'codigo'        => $codigo,
                'expediente'    => $busqueda['expediente'],
                'observaciones' => $busqueda['observaciones'],
                'errors'        => $errors,
                'datos_viejos'  => $datos,
                'exito'         => '',
                'csrf_token'    => Csrf::token(),
            ]);
            return;
        }

        $resultado = $this->model->subsanar($codigo, $comentario, $archivos, null);

        if (!$resultado['success']) {
            foreach ($archivos as $archivo) {
                $rutaFisica = __DIR__ . '/../../public/' . $archivo['ruta_archivo'];
                if (is_file($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }

            $busqueda = $this->model->buscarPorCodigo($codigo);

            $this->view('consulta.subsanar', [
                'codigo'        => $codigo,
                'expediente'    => $busqueda['expediente'],
                'observaciones' => $busqueda['observaciones'],
                'errors'        => ['general' => [$resultado['message']]],
                'datos_viejos'  => $datos,
                'exito'         => '',
                'csrf_token'    => Csrf::token(),
            ]);
            return;
        }

        $this->redirect('/consulta/subsanar/' . rawurlencode($codigo) . '?registrada=1');
    }

    /**
     * Bandeja del área con las observaciones ya subsanadas.
     */
    public function bandeja(): void
    {
        $this->requerirRol(self::RolesArea);

        $user = Session::user() ?? [];
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idArea = $esAdmin
            ? max(0, (int) ($_GET['area_id'] ?? 0))
            : (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        $mensajes = Flash::pull();

        $this->view('area.subsanaciones', [
            'titulo'        => 'Subsanaciones pendientes de revisión',
            'subsanaciones' => $this->model->listarSubsanadas($idArea),
            'esAdmin'       => $esAdmin,
            'idArea'        => $idArea,
            'areaFiltro'    => $idArea,
            'areas'         => $esAdmin ? (new Area())->getAll() : [],
            'flash_success' => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'   => $this->primerMensaje($mensajes, Flash::Error),
            'csrf_token'    => Csrf::token(),
        ], 'app');
    }

    /**
     * El área levanta una observación subsanada.
     */
    public function levantar(): void
    {
        $this->requerirRol(self::RolesArea);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/area/subsanaciones');

        $idObservacion = (int) ($datos['id_observacion'] ?? 0);
        if ($idObservacion <= 0) {
            Flash::error('Identificador de observación inválido.');
            $this->redirect('/area/subsanaciones');
            return;
        }

        $user = Session::user() ?? [];
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idArea = $esAdmin ? 0 : (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        $permitidas = array_column($this->model->listarSubsanadas($idArea), 'id_observacion');
        if (!in_array($idObservacion, array_map('intval', $permitidas), true)) {
            Flash::error('La observación no pertenece a su área o ya fue levantada.');
            $this->redirect('/area/subsanaciones');
            return;
        }

        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $resultado = $this->model->levantar($idObservacion, $idUsuario);

        if ($resultado['success']) {
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/subsanaciones');
    }
}
