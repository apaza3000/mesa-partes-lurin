<?php

namespace App\Controllers;

use App\Models\EntregaModel;
use Src\Core\Controller;
use Src\Core\Request;

class EntregaController extends Controller
{

    private $model;

    public function __construct()
    {
        $this->model = new EntregaModel();
    }

    /* ============================================================
     * FORMULARIO POR PASOS
     * ============================================================ */
    public function index()
    {
        $tiposDocumento = $this->model->listarTiposDocumento();

        return $this->view("entrega/index", [
            'errors'         => [],
            'datos_viejos'   => [],
            'tiposDocumento' => $tiposDocumento,
        ]);
    }

    /* ============================================================
     * PROCESAR ENVÍO
     * ============================================================ */
    public function store()
    {
        $request = new Request();
        $datos   = $request->all();

        // ===== Validaciones =====
        $errors = $this->validar($datos);

        // ===== Procesar adjuntos =====
        $archivosSubidos = [];
        $erroresArchivos = [];

        if (!empty($_FILES['adjuntos']['name'][0])) {
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
                if ($_FILES['adjuntos']['error'][$i] !== UPLOAD_ERR_OK) {
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
            return $this->view("entregas/index", [
                'errors'         => $errors,
                'datos_viejos'   => $datos,
                'tiposDocumento' => $this->model->listarTiposDocumento(),
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
            return $this->view("entregas/index", [
                'errors'         => ['general' => ['Error al registrar: ' . $resultado['mensaje']]],
                'datos_viejos'   => $datos,
                'tiposDocumento' => $this->model->listarTiposDocumento(),
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
        if (empty($datos['tipo_documento']))   $errors['tipo_documento'][]   = 'Seleccione el tipo de documento.';
        if (empty($datos['numero_documento'])) $errors['numero_documento'][] = 'Ingrese el número de documento.';
        if (empty($datos['nombres']))          $errors['nombres'][]          = 'Ingrese sus nombres.';
        if (empty($datos['apellido_paterno'])) $errors['apellido_paterno'][] = 'Ingrese su apellido paterno.';
        if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL))
            $errors['email'][] = 'Ingrese un correo electrónico válido.';

        // Paso 2: Expediente + Documento
        if (empty($datos['asunto']))     $errors['asunto'][]     = 'Ingrese el asunto del expediente.';
        if (empty($datos['prioridad']))  $errors['prioridad'][]  = 'Seleccione la prioridad.';
        if (empty($datos['doc_tipo']))   $errors['doc_tipo'][]   = 'Seleccione el tipo de documento.';
        if (empty($datos['doc_asunto'])) $errors['doc_asunto'][] = 'Ingrese el asunto del documento.';
        if (empty($datos['doc_folios'])) $errors['doc_folios'][] = 'Ingrese el número de folios.';

        // Paso 4: Declaración
        if (empty($datos['terminos']))
            $errors['terminos'][] = 'Debe aceptar la declaración jurada.';

        return $errors;
    }
}
