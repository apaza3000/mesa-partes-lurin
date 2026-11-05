<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Area;
use App\Models\Derivacion;
use App\Models\TipoDocumento;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class BandejaAreaController extends Controller
{
    use AuthorizesRequest;

    /** SEC-06: solo perfiles de área pueden operar la bandeja. */
    private const RolesPermitidos = Roles::AtencionArea;

    private Derivacion $derivacionModel;
    private Area $areaModel;
    private TipoDocumento $tipoDocumentoModel;

    public function __construct()
    {
        $this->derivacionModel = new Derivacion();
        $this->areaModel = new Area();
        $this->tipoDocumentoModel = new TipoDocumento();
    }

    /**
     * Bandeja principal de gestión de expedientes del área
     */
    public function index(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $user = Session::user();
        $esAdmin = $this->tieneRol(Roles::Administracion);

        // Área asignada al usuario
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        // Si es admin, puede filtrar por cualquier área o ver todas (0 = todas)
        $areaFiltro = $idAreaUsuario;
        if ($esAdmin) {
            $areaFiltro = isset($_GET['area_id']) ? (int) $_GET['area_id'] : $idAreaUsuario;
        }

        // Pestaña activa: pendientes (default), en_proceso, atendidos_devueltos, todos
        $tab = $_GET['tab'] ?? 'pendientes';
        $estadoParam = null;

        if ($tab === 'pendientes') {
            $estadoParam = 'Pendiente';
        } elseif ($tab === 'en_proceso') {
            $estadoParam = 'en_proceso';
        } elseif ($tab === 'atendidos_devueltos') {
            $estadoParam = 'atendidos_devueltos';
        }

        // Obtener conteos y lista de derivaciones
        $conteos = $this->derivacionModel->contarPorArea($areaFiltro);
        $derivaciones = $this->derivacionModel->listarPorArea($areaFiltro, $estadoParam);

        // Lista de áreas para el filtro y para el modal de re-derivación
        $todasLasAreas = $this->areaModel->getAll();

        // Obtener nombre del área actual
        $nombreAreaActual = 'Todas las Áreas';
        if ($areaFiltro > 0) {
            $areaInfo = $this->areaModel->getById($areaFiltro);
            $nombreAreaActual = $areaInfo['nombre'] ?? "Área #{$areaFiltro}";
        }

        $mensajes = Flash::pull();
        $flashSuccess = '';
        $flashError = '';
        foreach ($mensajes as $m) {
            if ($m['tipo'] === Flash::Exito && $flashSuccess === '') {
                $flashSuccess = $m['mensaje'];
            }
            if ($m['tipo'] === Flash::Error && $flashError === '') {
                $flashError = $m['mensaje'];
            }
        }

        $this->view('area.bandeja', [
            'titulo'           => 'Bandeja de Entrada — Gestión de Área',
            'tab'              => $tab,
            'conteos'          => $conteos,
            'derivaciones'     => $derivaciones,
            'esAdmin'          => $esAdmin,
            'idAreaUsuario'    => $idAreaUsuario,
            'areaFiltro'       => $areaFiltro,
            'nombreAreaActual' => $nombreAreaActual,
            'todasLasAreas'    => $todasLasAreas,
            'flash_success'    => $flashSuccess,
            'flash_error'      => $flashError,
            'csrf_token'       => Csrf::token(),
        ], 'app');
    }

    /**
     * Procesar la recepción de un expediente en el área
     */
    public function recepcionar(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/area/bandeja-entrada');

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);

        if ($idDerivacion <= 0) {
            Flash::error('Identificador de derivación inválido.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $user = Session::user();
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        // Validar que la derivación pertenece al área del usuario o sea admin
        $derivacion = $this->derivacionModel->getById($idDerivacion);
        if (!$derivacion) {
            Flash::error('No se encontró la derivación especificada.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if (!$esAdmin && (int)$derivacion['id_area_destino'] !== $idAreaUsuario) {
            Flash::error('No tiene permisos para recepcionar trámites de otra área.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $resultado = $this->derivacionModel->recepcionar($idDerivacion, $idUsuario);

        if ($resultado['success']) {
            Flash::exito("¡Éxito! El expediente {$derivacion['codigo_expediente']} ha sido recepcionado e ingresó al estado 'En Proceso'.");
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/bandeja-entrada?tab=en_proceso');
    }

    /**
     * Devolver un expediente al área de origen con motivo
     */
    public function devolver(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/area/bandeja-entrada');

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);
        $motivo = trim((string) ($datos['motivo'] ?? ''));

        if ($idDerivacion <= 0) {
            Flash::error('Identificador de derivación inválido.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if (empty($motivo)) {
            Flash::error('El motivo de devolución es estrictamente obligatorio.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $user = Session::user();
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        $derivacion = $this->derivacionModel->getById($idDerivacion);
        if (!$derivacion) {
            Flash::error('No se encontró la derivación especificada.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if (!$esAdmin && (int)$derivacion['id_area_destino'] !== $idAreaUsuario) {
            Flash::error('No tiene permisos para devolver trámites de otra área.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $resultado = $this->derivacionModel->devolver($idDerivacion, $idUsuario, $motivo);

        if ($resultado['success']) {
            Flash::exito("El expediente {$derivacion['codigo_expediente']} ha sido devuelto con éxito.");
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/bandeja-entrada?tab=atendidos_devueltos');
    }

    /**
     * Re-derivar expediente a otra área institucional
     */
    public function derivar(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/area/bandeja-entrada');

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);
        $idAreaDestino = (int) ($datos['id_area_destino'] ?? 0);
        $observaciones = trim((string) ($datos['observaciones'] ?? ''));

        if ($idDerivacion <= 0) {
            Flash::error('Identificador de derivación inválido.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if ($idAreaDestino <= 0) {
            Flash::error('Debe seleccionar un área de destino válida.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if (empty($observaciones)) {
            Flash::error('Las instrucciones o proveído de derivación son obligatorios.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $user = Session::user();
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $esAdmin = $this->tieneRol(Roles::Administracion);
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        // Verificar que la derivación existe
        $derivacion = $this->derivacionModel->getById($idDerivacion);
        if (!$derivacion) {
            Flash::error('No se encontró la derivación especificada.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        if (!$esAdmin && (int)$derivacion['id_area_destino'] !== $idAreaUsuario) {
            Flash::error('No tiene permisos para derivar trámites asignados a otra área.');
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $resultado = $this->derivacionModel->reDerivar($idDerivacion, $idAreaDestino, $idUsuario, $observaciones);

        if ($resultado['success']) {
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/bandeja-entrada?tab=atendidos_devueltos');
    }

    /**
     * TAREA E04-1: Formulario de atención con emisión de documento de respuesta
     */
    public function atender(string|int $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $contexto = $this->contextoAtencion((int) $id);
        if (!$contexto['ok']) {
            Flash::error($contexto['message']);
            $this->redirect('/area/bandeja-entrada');
            return;
        }

        $derivacion = $contexto['derivacion'];
        $mensajes = Flash::pull();

        $this->view('area.atender', [
            'titulo'        => 'Atender Expediente — ' . $derivacion['codigo_expediente'],
            'derivacion'    => $derivacion,
            'documentos'    => $this->derivacionModel->obtenerDocumentosConAdjuntos((int) $derivacion['id_expediente']),
            'tiposRespuesta'=> $this->tipoDocumentoModel->obtenerActivos(),
            'errors'        => [],
            'datos_viejos'  => [],
            'csrf_token'    => Csrf::token(),
            'flash_success' => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'   => $this->primerMensaje($mensajes, Flash::Error),
        ], 'app');
    }

    /**
     * TAREA E04-1: Procesar la atención (documento de respuesta + adjuntos PDF)
     */
    public function storeAtencion(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        foreach ($datos as $k => $v) {
            if (is_string($v)) {
                $datos[$k] = trim($v);
            }
        }

        // SEC-01: validación CSRF real (sin ramas fail-open) antes de tocar estado.
        $this->requerirCsrf($datos, '/area/bandeja-entrada');

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);
        $contexto = $this->contextoAtencion($idDerivacion);
        if (!$contexto['ok']) {
            Flash::error($contexto['message']);
            $this->redirect('/area/bandeja-entrada');
            return;
        }
        $derivacion = $contexto['derivacion'];

        $errors = $this->validarAtencion($datos);

        // ----- Adjunto de respuesta (PDF) -----
        $archivosSubidos = [];
        $erroresArchivos = [];
        $maxSize = 10 * 1024 * 1024; // 10 MB por archivo
        $rutaBase = __DIR__ . '/../../public/uploads/expedientes/' . date('Y');

        // FAT: solo tratamos como adjuntos un array genuino de archivos
        // (un multipart malformado trae `name` como string y rompe count()).
        $hayAdjuntos = is_array($_FILES['adjuntos']['name'] ?? null) && !empty($_FILES['adjuntos']['name'][0]);
        if ($hayAdjuntos) {
            if (!is_dir($rutaBase)) {
                mkdir($rutaBase, 0775, true);
            }

            $totalArchivos = count($_FILES['adjuntos']['name']);
            for ($i = 0; $i < $totalArchivos; $i++) {
                if (($_FILES['adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $erroresArchivos[] = 'No se pudo recibir el archivo "' . $_FILES['adjuntos']['name'][$i] . '".';
                    continue;
                }

                $nombreOriginal = $_FILES['adjuntos']['name'][$i];
                $tmp = $_FILES['adjuntos']['tmp_name'][$i];
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

                $nombreArchivo = uniqid('resp_', true) . '.pdf';
                $rutaDestino = $rutaBase . '/' . $nombreArchivo;

                if (!move_uploaded_file($tmp, $rutaDestino)) {
                    $erroresArchivos[] = "No se pudo almacenar el archivo \"$nombreOriginal\".";
                    continue;
                }

                $archivosSubidos[] = [
                    'nombre_original' => $nombreOriginal,
                    'nombre_archivo'  => $nombreArchivo,
                    'ruta_archivo'    => 'uploads/expedientes/' . date('Y') . '/' . $nombreArchivo,
                    'extension'       => $ext,
                    'mime_type'       => mime_content_type($rutaDestino) ?: 'application/pdf',
                    'tamanio'         => $tamanio,
                    'hash_archivo'    => hash_file('sha256', $rutaDestino),
                ];
            }
        } else {
            $erroresArchivos[] = 'Debe adjuntar el documento de respuesta en PDF (Oficio/Resolución/Informe escaneado y firmado).';
        }

        if (!empty($erroresArchivos)) {
            $errors['adjuntos'] = array_merge($errors['adjuntos'] ?? [], $erroresArchivos);
        }

        if (!empty($errors)) {
            $this->view('area.atender', [
                'titulo'        => 'Atender Expediente — ' . $derivacion['codigo_expediente'],
                'derivacion'    => $derivacion,
                'documentos'    => $this->derivacionModel->obtenerDocumentosConAdjuntos((int) $derivacion['id_expediente']),
                'tiposRespuesta'=> $this->tipoDocumentoModel->obtenerActivos(),
                'errors'        => $errors,
                'datos_viejos'  => $datos,
                'csrf_token'    => Csrf::token(),
                'flash_success' => '',
                'flash_error'   => '',
            ], 'app');
            return;
        }

        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

        $respuesta = [
            'id_tipo_doc'      => (int) $datos['id_tipo_respuesta'],
            'numero_documento' => $datos['num_respuesta'],
            'asunto'           => $datos['resumen'],
            'folios'           => (int) $datos['folios_respuesta'],
            'fecha_documento'  => $datos['fecha_respuesta'] !== '' ? $datos['fecha_respuesta'] : date('Y-m-d'),
        ];

        $resultado = $this->derivacionModel->atender($idDerivacion, $idUsuario, $respuesta, $archivosSubidos);

        if ($resultado['success']) {
            Flash::exito($resultado['message']
                . ' Se registró el documento de respuesta ' . $respuesta['numero_documento'] . '.');
            $this->redirect('/area/bandeja-entrada?tab=atendidos_devueltos');
        } else {
            // Los archivos ya movidos quedan huérfanos si falló la transacción; se limpian los nuevos
            foreach ($archivosSubidos as $archivo) {
                $rutaFisica = __DIR__ . '/../../public/' . $archivo['ruta_archivo'];
                if (is_file($rutaFisica)) {
                    unlink($rutaFisica);
                }
            }
            Flash::error($resultado['message']);
            $this->redirect('/area/atender/' . $idDerivacion);
        }
    }

    /**
     * Validación común de acceso al formulario de atención:
     * derivación existente, de su área (o admin) y en estado atendible.
     */
    private function contextoAtencion(int $idDerivacion): array
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
            return ['ok' => false, 'message' => 'No tiene permisos para atender trámites de otra área.'];
        }

        if (!in_array($derivacion['estado'], ['Recibido', 'En_Atencion'], true)) {
            return ['ok' => false, 'message' => 'Solo se pueden atender expedientes ya recepcionados por el área (estado Recibido o En Atención).'];
        }

        return ['ok' => true, 'derivacion' => $derivacion];
    }

    private function validarAtencion(array $datos): array
    {
        $errors = [];

        $tipos = $this->tipoDocumentoModel->obtenerActivos();
        $idsTipos = array_map(static fn ($t) => (int) $t['id_tipo_doc'], $tipos);
        if (empty($datos['id_tipo_respuesta']) || !in_array((int) ($datos['id_tipo_respuesta'] ?? 0), $idsTipos, true)) {
            $errors['id_tipo_respuesta'][] = 'Seleccione el tipo de documento de respuesta.';
        }

        $numero = (string) ($datos['num_respuesta'] ?? '');
        if ($numero === '' || mb_strlen($numero) < 3 || mb_strlen($numero) > 100) {
            $errors['num_respuesta'][] = 'Ingrese el número del documento emitido (ej. Oficio N° 124-2026-MDL/GDU).';
        }

        $resumen = (string) ($datos['resumen'] ?? '');
        if (mb_strlen($resumen) < 10) {
            $errors['resumen'][] = 'Redacte el resumen o conclusión de la respuesta (mínimo 10 caracteres).';
        } elseif (mb_strlen($resumen) > 500) {
            $errors['resumen'][] = 'El resumen no debe exceder 500 caracteres.';
        }

        $folios = (int) ($datos['folios_respuesta'] ?? 0);
        if ($folios < 1 || $folios > 9999) {
            $errors['folios_respuesta'][] = 'Indique los folios del documento de respuesta (1 a 9999).';
        }

        if (!empty($datos['fecha_respuesta']) && !$this->fechaValida((string) $datos['fecha_respuesta'])) {
            $errors['fecha_respuesta'][] = 'La fecha del documento de respuesta no es válida.';
        }

        return $errors;
    }

    private function fechaValida(string $fecha): bool
    {
        $partes = explode('-', $fecha);
        if (count($partes) !== 3) {
            return false;
        }
        return checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }

    /**
     * Endpoint AJAX para obtener los detalles completos de una derivación y expediente
     */
    public function detalle(int|string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $idDerivacion = (int) $id;
        $derivacion = $this->derivacionModel->getById($idDerivacion);

        if (!$derivacion) {
            $this->json(['success' => false, 'message' => 'Derivación no encontrada.'], 404);
            return;
        }

        // SEC-05 (variante local): el detalle solo se sirve a la área destinataria
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? (Session::user()['id_area'] ?? 0));
        if (!$this->tieneRol(Roles::Administracion) && (int) $derivacion['id_area_destino'] !== $idAreaUsuario) {
            $this->json(['success' => false, 'message' => 'No tiene permisos para ver este expediente.'], 403);
            return;
        }

        $idExpediente = (int) $derivacion['id_expediente'];
        $documentos = $this->derivacionModel->obtenerDocumentosConAdjuntos($idExpediente);

        $this->json([
            'success'    => true,
            'derivacion' => $derivacion,
            'documentos' => $documentos
        ]);
    }
}

