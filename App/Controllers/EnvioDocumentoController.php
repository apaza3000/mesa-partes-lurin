<?php

namespace App\Controllers;

use App\Models\Area;
use App\Models\EntregaModel;
use App\Support\Catalogos;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use App\Support\UsuarioActual;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E11-3 / E11-4: envío de documento por usuario autenticado.
 *
 * Formulario similar a ventanilla (mesa-partes/registrar.php) pero con los
 * datos del remitente (la persona vinculada a la cuenta) precargados y
 * bloqueados. Solo el área/Mesa de Partes destino y los datos del documento
 * son editables.
 */
class EnvioDocumentoController extends Controller
{
    private EntregaModel $model;

    public function __construct()
    {
        $this->model = new EntregaModel();
    }

    public function index(): void
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $persona = UsuarioActual::persona();
        $tiposDocumento = $this->model->listarTiposDocumento();
        $areas = (new Area())->getAll();

        // Precargar los datos del remitente desde la sesión.
        $datosViejos = [
            'id_persona'       => $persona['id_persona'] ?? null,
            'tipo_persona'     => $persona['tipo_persona'] ?? 'Natural',
            'tipo_documento'   => $persona['tipo_documento'] ?? 'DNI',
            'numero_documento' => $persona['numero_documento'] ?? '',
            'nombres'          => $persona['nombres'] ?? '',
            'apellido_paterno' => $persona['apellido_paterno'] ?? '',
            'apellido_materno' => $persona['apellido_materno'] ?? '',
            'email'            => $persona['email'] ?? '',
            'telefono'         => $persona['telefono'] ?? '',
            'direccion'        => $persona['direccion'] ?? '',
            'razon_social'     => $persona['razon_social'] ?? '',
            'prioridad'        => 'Normal',
            'doc_folios'       => '1',
            'canal_ingreso'    => 'Virtual',
        ];

        $this->view('envio.index', [
            'titulo'            => 'Enviar documento',
            'areas'             => $areas,
            'tiposDocumento'    => $tiposDocumento,
            'errors'            => [],
            'datos_viejos'      => $datosViejos,
            'persona'           => $persona,
            'tiposDocumentoPersona' => Catalogos::TipoDocumento,
            'prioridades'       => Catalogos::Prioridad,
            'csrf_token'        => Csrf::token(),
            'flash_success'     => null,
            'flash_error'       => null,
        ], 'app');
    }

    public function store(): void
    {
        if (!Session::isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $request = new Request();
        $datos = $request->all();

        // Forzar id_persona desde la sesión (nunca confiar en el POST).
        $idPersonaSesion = UsuarioActual::idPersona();
        $datos['id_persona'] = $idPersonaSesion;

        // Precargar datos de la persona si faltan en el POST (campos bloqueados).
        $persona = UsuarioActual::persona();
        foreach (['tipo_persona', 'tipo_documento', 'numero_documento', 'nombres', 'apellido_paterno', 'apellido_materno', 'email', 'telefono', 'direccion', 'razon_social'] as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '' || $datos[$campo] === null) {
                $datos[$campo] = $persona[$campo] ?? '';
            }
        }

        // CSRF
        $this->requerirCsrf($datos, '/enviar-documento');

        // Validar id_persona coincide con sesión.
        $idPersonaPost = isset($datos['id_persona']) ? (int) $datos['id_persona'] : 0;
        if ($idPersonaSesion === null || $idPersonaPost !== $idPersonaSesion) {
            Flash::error('Los datos del remitente no coinciden con tu cuenta.');
            $this->redirect('/enviar-documento');
            return;
        }

        // Validaciones (reutilizadas del portal público).
        $errors = $this->validar($datos);

        // Adjuntos
        $archivosSubidos = [];
        $erroresArchivos = [];
        $hayAdjuntos = is_array($_FILES['adjuntos']['name'] ?? null) && !empty($_FILES['adjuntos']['name'][0]);
        if ($hayAdjuntos) {
            $totalArchivos = count($_FILES['adjuntos']['name']);
            $maxSize = 10 * 1024 * 1024;
            $extensionesOk = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $anio = date('Y');
            $rutaBase = __DIR__ . "/../../public/uploads/expedientes/$anio";
            if (!is_dir($rutaBase)) {
                mkdir($rutaBase, 0775, true);
            }
            for ($i = 0; $i < $totalArchivos; $i++) {
                if (($_FILES['adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                $nombreOriginal = $_FILES['adjuntos']['name'][$i];
                $tmp = $_FILES['adjuntos']['tmp_name'][$i];
                $tamanio = $_FILES['adjuntos']['size'][$i];
                $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                if (!in_array($ext, $extensionesOk)) {
                    $erroresArchivos[] = "El archivo '$nombreOriginal' tiene una extensión no permitida.";
                    continue;
                }
                if ($tamanio > $maxSize) {
                    $erroresArchivos[] = "El archivo '$nombreOriginal' supera los 10 MB.";
                    continue;
                }
                $nombreArchivo = uniqid('doc_', true) . '.' . $ext;
                $rutaDestino = $rutaBase . '/' . $nombreArchivo;
                if (!move_uploaded_file($tmp, $rutaDestino)) {
                    $erroresArchivos[] = "No se pudo guardar el archivo '$nombreOriginal'.";
                    continue;
                }
                $archivosSubidos[] = [
                    'nombre_original' => $nombreOriginal,
                    'nombre_archivo'  => $nombreArchivo,
                    'ruta_archivo'    => "uploads/expedientes/$anio/$nombreArchivo",
                    'extension'       => $ext,
                    'mime_type'       => mime_content_type($rutaDestino),
                    'tamanio'         => $tamanio,
                    'hash_archivo'    => hash_file('sha256', $rutaDestino),
                ];
            }
        }

        if (empty($archivosSubidos)) {
            $errors['adjuntos'][] = 'Debe adjuntar al menos un archivo.';
        }
        if (!empty($erroresArchivos)) {
            $errors['adjuntos'] = array_merge($errors['adjuntos'] ?? [], $erroresArchivos);
        }

        if (!empty($errors)) {
            $areas = (new Area())->getAll();
            $tiposDocumento = $this->model->listarTiposDocumento();
            $this->view('envio.index', [
                'titulo'            => 'Enviar documento',
                'areas'             => $areas,
                'tiposDocumento'    => $tiposDocumento,
                'errors'            => $errors,
                'datos_viejos'      => $datos,
                'persona'           => $persona,
                'tiposDocumentoPersona' => Catalogos::TipoDocumento,
                'prioridades'       => Catalogos::Prioridad,
                'csrf_token'        => Csrf::token(),
                'flash_success'     => null,
                'flash_error'       => null,
            ], 'app');
            return;
        }

        // Documento único
        $documento = [
            'id_tipo_doc'      => $datos['doc_tipo'] ?? null,
            'numero_documento' => $datos['doc_numero'] ?? null,
            'asunto'           => $datos['doc_asunto'] ?? $datos['asunto'],
            'folios'           => $datos['doc_folios'] ?? 1,
            'fecha_documento'  => $datos['doc_fecha'] ?? null,
        ];

        $datos['canal_ingreso'] = $datos['canal_ingreso'] ?? 'Virtual';
        $datos['id_usuario_creador'] = (int) Session::get('usuario_id');

        // E11-4: derivación automática a Mesa de Partes o área elegida.
        $resultado = $this->model->registrarExpediente($datos, $documento, $archivosSubidos);

        if ($resultado['ok']) {
            try {
                $idAreaDestino = isset($datos['id_area_destino']) ? (int) $datos['id_area_destino'] : null;
                $idAreaMesa = null;
                foreach ((new Area())->getAll() as $a) {
                    if (stripos((string)($a['nombre'] ?? ''), 'mesa') !== false) {
                        $idAreaMesa = (int) $a['id_area'];
                        break;
                    }
                }
                $destino = $idAreaDestino ?: $idAreaMesa;
                if ($destino) {
                    $deriv = new \App\Models\Derivacion();
                    $deriv->create([
                        'id_expediente' => $resultado['id_expediente'],
                        'id_area_origen' => Session::get('usuario_area') ?: $destino,
                        'id_area_destino' => $destino,
                        'observaciones' => 'Expediente registrado por usuario autenticado',
                        'id_usuario_envia' => Session::get('usuario_id'),
                    ]);
                }
            } catch (\Throwable $e) {
                error_log('E11-4 derivacion: ' . $e->getMessage());
            }
        }

        if (!$resultado['ok']) {
            $areas = (new Area())->getAll();
            $tiposDocumento = $this->model->listarTiposDocumento();
            $this->view('envio.index', [
                'titulo'            => 'Enviar documento',
                'areas'             => $areas,
                'tiposDocumento'    => $tiposDocumento,
                'errors'            => ['general' => ['Error al registrar: ' . $resultado['mensaje']]],
                'datos_viejos'      => $datos,
                'persona'           => $persona,
                'tiposDocumentoPersona' => Catalogos::TipoDocumento,
                'prioridades'       => Catalogos::Prioridad,
                'csrf_token'        => Csrf::token(),
                'flash_success'     => null,
                'flash_error'       => null,
            ], 'app');
            return;
        }

        // Actualizar expediente: derivación inicial si se eligió área.
        // (EntregaModel ya crea expediente; derivación inicial no es automática en virtual.
        //  Se deja para E11-4 si se requiere; por ahora solo registro.)

        $_SESSION['expediente_creado'] = [
            'codigo' => $resultado['codigo'],
            'asunto' => $datos['asunto'],
            'email'  => $datos['email'],
        ];

        try {
            // Reutilizar la lógica de encolado del portal público.
            $ctrl = new \App\Controllers\EntregaController();
            $ref = new \ReflectionClass($ctrl);
            $m = $ref->getMethod('encolarCorreoConstancia');
            $m->setAccessible(true);
            $m->invoke($ctrl, $resultado['codigo'], $datos);
        } catch (\Throwable $e) {
            error_log('E13 envio encolar: ' . $e->getMessage());
        }

        $this->redirect('/entregas/exitoso');
    }

    private function validar(array $datos): array
    {
        $errors = [];
        // Remitente (debe venir de la sesión; validamos mínimos)
        if (empty($datos['tipo_documento'])) {
            $errors['tipo_documento'][] = 'Seleccione el tipo de documento.';
        } elseif (!Catalogos::es(Catalogos::TipoDocumento, $datos['tipo_documento'])) {
            $errors['tipo_documento'][] = 'Tipo de documento no válido.';
        }
        if (empty($datos['numero_documento'])) {
            $errors['numero_documento'][] = 'Ingrese el número de documento.';
        }
        if (empty($datos['nombres']) && ($datos['tipo_persona'] ?? 'Natural') === 'Natural') {
            $errors['nombres'][] = 'Ingrese sus nombres.';
        }
        if (empty($datos['apellido_paterno']) && ($datos['tipo_persona'] ?? 'Natural') === 'Natural') {
            $errors['apellido_paterno'][] = 'Ingrese su apellido paterno.';
        }
        if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Ingrese un correo electrónico válido.';
        }
        // Expediente + documento
        if (empty($datos['asunto'])) {
            $errors['asunto'][] = 'Ingrese el asunto del expediente.';
        }
        if (empty($datos['prioridad'])) {
            $errors['prioridad'][] = 'Seleccione la prioridad.';
        } elseif (!Catalogos::es(Catalogos::Prioridad, $datos['prioridad'])) {
            $errors['prioridad'][] = 'Prioridad no válida.';
        }
        if (empty($datos['doc_tipo'])) {
            $errors['doc_tipo'][] = 'Seleccione el tipo de documento.';
        }
        if (empty($datos['doc_asunto'])) {
            $errors['doc_asunto'][] = 'Ingrese el asunto del documento.';
        }
        if (empty($datos['doc_folios']) || !is_numeric($datos['doc_folios']) || (int) $datos['doc_folios'] < 1) {
            $errors['doc_folios'][] = 'Los folios deben ser un número mayor que cero.';
        }
        if (empty($datos['terminos'])) {
            $errors['terminos'][] = 'Debe aceptar la declaración jurada.';
        }
        return $errors;
    }
}
