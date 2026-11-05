<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\EntregaModel;
use App\Support\Catalogos;
use App\Support\Csrf;
use Src\Core\Controller;
use Src\Core\Request;

class EntregaController extends Controller
{
    use AuthorizesRequest;

    private $model;

    public function __construct()
    {
        $this->model = new EntregaModel();
    }

    /* ============================================================
     * FORMULARIO POR PASOS (puerta ciudadana, sin sesión)
     * ============================================================ */
    public function index()
    {
        $tiposDocumento = $this->model->listarTiposDocumento();

        return $this->view("entrega/index", [
            'errors'         => [],
            'datos_viejos'   => [],
            'tiposDocumento' => $tiposDocumento,
            // VAL-05: catálogos ENUM para que la vista no invente opciones.
            'tiposDocumentoPersona' => Catalogos::TipoDocumento,
            'prioridades'    => Catalogos::Prioridad,
            'csrf_token'     => Csrf::token(),
        ]);
    }

    /* ============================================================
     * PROCESAR ENVÍO
     * ============================================================ */
    public function store()
    {
        $request = new Request();
        $datos   = $this->sanearEntrada($request->all());

        // SEC-01: formulario público, pero igualmente sujeto a token.
        $this->requerirCsrf($datos, '/entregas');

        // ===== Validaciones =====
        $errors = $this->validar($datos);

        // ===== Procesar adjuntos =====
        $archivosSubidos = [];
        $erroresArchivos = [];

        // FAT: solo tratamos como adjuntos un array genuino de archivos
        // (un multipart malformado trae `name` como string y rompe count()).
        $hayAdjuntos = is_array($_FILES['adjuntos']['name'] ?? null) && !empty($_FILES['adjuntos']['name'][0]);
        if ($hayAdjuntos) {
            $totalArchivos = count($_FILES['adjuntos']['name']);
            $maxSize       = 10 * 1024 * 1024; // 10 MB
            $extensionesOk = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            // Ruta destino: /public/uploads/expedientes/{anio}/{codigo-temp}/
            $anio      = date('Y');
            $rutaBase  = __DIR__ . "/../../public/uploads/expedientes/$anio";

            if (!is_dir($rutaBase)) {
                mkdir($rutaBase, 0775, true);
            }

            for ($i = 0; $i < $totalArchivos; $i++) {
                if (($_FILES['adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                $nombreOriginal = $_FILES['adjuntos']['name'][$i];
                $tmp            = $_FILES['adjuntos']['tmp_name'][$i];
                $tamanio        = $_FILES['adjuntos']['size'][$i];
                $ext            = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

                if (!in_array($ext, $extensionesOk)) {
                    $erroresArchivos[] = "El archivo '$nombreOriginal' tiene una extensión no permitida.";
                    continue;
                }
                if ($tamanio > $maxSize) {
                    $erroresArchivos[] = "El archivo '$nombreOriginal' supera los 10 MB.";
                    continue;
                }

                $nombreArchivo = uniqid('doc_', true) . '.' . $ext;
                $rutaDestino   = $rutaBase . '/' . $nombreArchivo;

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
            // FAT-03: la carpeta real es view/pages/entrega (antes "entregas/index"
            // provocaba un error fatal justo cuando había que mostrar los errores).
            return $this->view("entrega/index", [
                'errors'         => $errors,
                'datos_viejos'   => $datos,
                'tiposDocumento' => $this->model->listarTiposDocumento(),
                'tiposDocumentoPersona' => Catalogos::TipoDocumento,
                'prioridades'    => Catalogos::Prioridad,
                'csrf_token'     => Csrf::token(),
            ]);
        }

        // ===== Documento único =====
        $documento = [
            'id_tipo_doc'      => $datos['doc_tipo'] ?? null,
            'numero_documento' => $datos['doc_numero'] ?? null,
            'asunto'           => $datos['doc_asunto'] ?? $datos['asunto'],
            'folios'           => $datos['doc_folios'] ?? 1,
            'fecha_documento'  => $datos['doc_fecha'] ?? null,
        ];

        // ===== Registrar =====
        $resultado = $this->model->registrarExpediente($datos, $documento, $archivosSubidos);

        if (!$resultado['ok']) {
            return $this->view("entrega/index", [
                'errors'         => ['general' => ['Error al registrar: ' . $resultado['mensaje']]],
                'datos_viejos'   => $datos,
                'tiposDocumento' => $this->model->listarTiposDocumento(),
                'tiposDocumentoPersona' => Catalogos::TipoDocumento,
                'prioridades'    => Catalogos::Prioridad,
                'csrf_token'     => Csrf::token(),
            ]);
        }

        // Renombrar carpeta tmp a código de expediente (opcional pero limpio)
        // $this->renombrarCarpetaExpediente($carpeta ?? null, $resultado['codigo']);

        $_SESSION['expediente_creado'] = [
            'codigo' => $resultado['codigo'],
            'asunto' => $datos['asunto'],
            'email'  => $datos['email'],
        ];

        $this->redirect("/entregas/exitoso");
        exit;
    }


    /* ============================================================
     * PÁGINA DE ÉXITO
     * ============================================================ */
    public function exitoso()
    {
        if (empty($_SESSION['expediente_creado'])) {
            $this->redirect("/entregas");
            exit;
        }
        $expediente = $_SESSION['expediente_creado'];
        unset($_SESSION['expediente_creado']);

        return $this->view("entrega.exitoso", [
            'expediente' => $expediente,
        ]);
    }

    /* ============================================================
     * VALIDACIONES
     * ============================================================ */
    private function validar($datos)
    {
        $errors = [];

        // Paso 1: Persona
        if (empty($datos['tipo_documento'])) {
            $errors['tipo_documento'][] = 'Seleccione el tipo de documento.';
        } elseif (!Catalogos::es(Catalogos::TipoDocumento, $datos['tipo_documento'])) {
            // VAL-05: antes la vista solo ofrecía DNI/CE/Pasaporte; ahora se
            // valida contra el ENUM real de la BD.
            $errors['tipo_documento'][] = 'Tipo de documento no válido.';
        }

        if (empty($datos['numero_documento'])) {
            $errors['numero_documento'][] = 'Ingrese el número de documento.';
        } elseif (!Catalogos::cabe('personas.numero_documento', $datos['numero_documento'])) {
            $errors['numero_documento'][] = 'El número de documento no puede exceder ' . Catalogos::limite('personas.numero_documento') . ' caracteres.';
        }

        if (empty($datos['nombres'])) {
            $errors['nombres'][] = 'Ingrese sus nombres.';
        } elseif (!Catalogos::cabe('personas.nombres', $datos['nombres'])) {
            $errors['nombres'][] = 'Los nombres no pueden exceder ' . Catalogos::limite('personas.nombres') . ' caracteres.';
        }

        if (empty($datos['apellido_paterno'])) {
            $errors['apellido_paterno'][] = 'Ingrese su apellido paterno.';
        } elseif (!Catalogos::cabe('personas.apellido_paterno', $datos['apellido_paterno'])) {
            $errors['apellido_paterno'][] = 'El apellido paterno no puede exceder ' . Catalogos::limite('personas.apellido_paterno') . ' caracteres.';
        }

        if (!Catalogos::cabe('personas.apellido_materno', $datos['apellido_materno'] ?? '')) {
            $errors['apellido_materno'][] = 'El apellido materno no puede exceder ' . Catalogos::limite('personas.apellido_materno') . ' caracteres.';
        }

        if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Ingrese un correo electrónico válido.';
        } elseif (!Catalogos::cabe('personas.email', $datos['email'])) {
            $errors['email'][] = 'El correo no puede exceder ' . Catalogos::limite('personas.email') . ' caracteres.';
        }

        // VAL-03: campos que llegan hasta MySQL sin tope provocaban errores 1406.
        foreach (['telefono' => 'personas.telefono', 'direccion' => 'personas.direccion'] as $campo => $columna) {
            if (isset($datos[$campo]) && !Catalogos::cabe($columna, $datos[$campo])) {
                $errors[$campo][] = 'Este campo no puede exceder ' . Catalogos::limite($columna) . ' caracteres.';
            }
        }

        // Paso 2: Expediente + Documento
        if (empty($datos['asunto'])) {
            $errors['asunto'][] = 'Ingrese el asunto del expediente.';
        } elseif (!Catalogos::cabe('expedientes.asunto', $datos['asunto'])) {
            $errors['asunto'][] = 'El asunto no puede exceder ' . Catalogos::limite('expedientes.asunto') . ' caracteres.';
        }

        if (empty($datos['prioridad'])) {
            $errors['prioridad'][] = 'Seleccione la prioridad.';
        } elseif (!Catalogos::es(Catalogos::Prioridad, $datos['prioridad'])) {
            $errors['prioridad'][] = 'Prioridad no válida.';
        }

        if (empty($datos['doc_tipo']))   $errors['doc_tipo'][]   = 'Seleccione el tipo de documento.';

        if (empty($datos['doc_asunto'])) {
            $errors['doc_asunto'][] = 'Ingrese el asunto del documento.';
        } elseif (!Catalogos::cabe('documentos.asunto', $datos['doc_asunto'])) {
            $errors['doc_asunto'][] = 'El asunto del documento no puede exceder ' . Catalogos::limite('documentos.asunto') . ' caracteres.';
        }

        if (empty($datos['doc_folios'])) {
            $errors['doc_folios'][] = 'Ingrese el número de folios.';
        } elseif (!is_numeric($datos['doc_folios']) || (int) $datos['doc_folios'] < 1) {
            $errors['doc_folios'][] = 'Los folios deben ser un número mayor que cero.';
        }

        if (isset($datos['doc_numero']) && !Catalogos::cabe('documentos.numero_documento', $datos['doc_numero'])) {
            $errors['doc_numero'][] = 'El número del documento no puede exceder ' . Catalogos::limite('documentos.numero_documento') . ' caracteres.';
        }

        // Paso 4: Declaración
        if (empty($datos['terminos']))
            $errors['terminos'][] = 'Debe aceptar la declaración jurada.';

        return $errors;
    }

    /** SEC-07 / VAL-02: sin HTML ni caracteres que MySQL rechace. */
    private function sanearEntrada(array $datos): array
    {
        $campos = [
            'nombres' => 'personas.nombres',
            'apellido_paterno' => 'personas.apellido_paterno',
            'apellido_materno' => 'personas.apellido_materno',
            'razon_social' => 'personas.razon_social',
            'numero_documento' => 'personas.numero_documento',
            'email' => 'personas.email',
            'telefono' => 'personas.telefono',
            'direccion' => 'personas.direccion',
            'asunto' => 'expedientes.asunto',
            'doc_asunto' => 'documentos.asunto',
            'doc_numero' => 'documentos.numero_documento',
        ];

        foreach ($campos as $campo => $columna) {
            if (array_key_exists($campo, $datos)) {
                // checklist §9: limpiar sin recortar; `validar()` rechaza el desborde.
                $datos[$campo] = Catalogos::limpiar($datos[$campo]);
            }
        }

        if (array_key_exists('tipo_documento', $datos)) {
            $datos['tipo_documento'] = mb_strtoupper(trim((string) $datos['tipo_documento']));
        }

        return $datos;
    }
}
