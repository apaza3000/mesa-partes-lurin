<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\MesaPartes;
use App\Models\Auditoria;
use App\Models\Persona;
use App\Services\DocumentoLookupService;
use App\Services\PdfCargoService;
use App\Support\Catalogos;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * Módulo de Recepción Presencial e Interna en Ventanilla (EPICA 2).
 * Roles autorizados: MESA_DE_PARTES y ADMINISTRADOR.
 */
class MesaPartesController extends Controller
{
    use AuthorizesRequest;

    private const RolesPermitidos = Roles::MesaDePartes;

    private MesaPartes $modelo;

    public function __construct()
    {
        date_default_timezone_set('America/Lima');
        $this->modelo = new MesaPartes();
    }

    /* ============================================================
     * TAREA E02-1: FORMULARIO DE REGISTRO EN VENTANILLA
     * ============================================================ */
    public function register(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $mensajes = Flash::pull();

        $this->view('mesa-partes.registrar', [
            'titulo'         => 'Registro de Documento en Ventanilla',
            'areas'          => $this->modelo->listarAreasActivas(),
            'tiposDocumento' => $this->modelo->listarTiposDocumento(),
            'errors'         => [],
            'datos_viejos'   => [],
            'flash_success'  => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'    => $this->primerMensaje($mensajes, Flash::Error),
            'csrf_token'     => Csrf::token(),
        ], 'app');
    }

    /* ============================================================
     * PROCESAR EL REGISTRO EN VENTANILLA
     * ============================================================ */
    public function store(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $request = new Request();
        $datos = $this->normalizar($request->all());

        // SEC-01: CSRF real, sin la rama fail-open que tenía `validarCsrf()`.
        $this->requerirCsrf($datos, '/mesa-partes/registrar');

        $errors = $this->validar($datos);

        // ----- Adjuntos (escaneo / PDF) -----
        $archivosSubidos = [];
        $erroresArchivos = [];
        $maxSize = 10 * 1024 * 1024; // 10 MB por archivo
        $extensionesPermitidas = ['pdf'];
        $rutaBase = __DIR__ . '/../../public/uploads/expedientes/' . date('Y');

        // FAT: un multipart malformado puede traer `name` como string; solo
        // tratamos como adjuntos un array genuino de archivos (evita count() sobre string).
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

                if (!in_array($ext, $extensionesPermitidas, true)) {
                    $erroresArchivos[] = "El archivo \"$nombreOriginal\" no es un PDF válido.";
                    continue;
                }
                if ($tamanio > $maxSize) {
                    $erroresArchivos[] = "El archivo \"$nombreOriginal\" supera el límite de 10 MB.";
                    continue;
                }

                $nombreArchivo = uniqid('doc_', true) . '.' . $ext;
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
        } elseif (empty($datos['id_persona_existente'])) {
            $erroresArchivos[] = 'Debe adjuntar el escaneo del documento recibido (PDF).';
        }

        if (!empty($erroresArchivos)) {
            $errors['adjuntos'] = array_merge($errors['adjuntos'] ?? [], $erroresArchivos);
        }

        // ----- Validación de área destino -----
        $idAreaDestino = (int) ($datos['id_area_destino'] ?? 0);
        if ($idAreaDestino > 0 && !$this->modelo->existeArea($idAreaDestino)) {
            $errors['id_area_destino'] = ['El área destino seleccionada no existe o está inactiva.'];
        }

        if (!empty($errors)) {
            $this->view('mesa-partes.registrar', [
                'titulo'         => 'Registro de Documento en Ventanilla',
                'areas'          => $this->modelo->listarAreasActivas(),
                'tiposDocumento' => $this->modelo->listarTiposDocumento(),
                'errors'         => $errors,
                'datos_viejos'   => $datos,
                'flash_success'  => '',
                'flash_error'    => '',
                'csrf_token'     => Csrf::token(),
            ], 'app');
            return;
        }

        $documento = [
            'id_tipo_doc'      => (int) $datos['doc_tipo'],
            'numero_documento' => $datos['doc_numero'],
            'asunto'           => $datos['doc_asunto'] !== '' ? $datos['doc_asunto'] : $datos['asunto'],
            'folios'           => (int) $datos['doc_folios'],
            'fecha_documento'  => $datos['doc_fecha'],
        ];

        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $idAreaUsuario = Session::get('usuario_area') ?? ($user['id_area'] ?? null);
        $idAreaUsuario = $idAreaUsuario !== null && $idAreaUsuario !== '' ? (int) $idAreaUsuario : null;

        $resultado = $this->modelo->registrarExpediente(
            $datos,
            $documento,
            $archivosSubidos,
            $idUsuario,
            $idAreaDestino > 0 ? $idAreaDestino : null,
            $datos['instrucciones'] ?? '',
            $idAreaUsuario
        );

        if (!$resultado['ok']) {
            $this->view('mesa-partes.registrar', [
                'titulo'         => 'Registro de Documento en Ventanilla',
                'areas'          => $this->modelo->listarAreasActivas(),
                'tiposDocumento' => $this->modelo->listarTiposDocumento(),
                'errors'         => ['general' => ['No se pudo registrar el expediente: ' . $resultado['mensaje']]],
                'datos_viejos'   => $datos,
                'flash_success'  => '',
                'flash_error'    => '',
                'csrf_token'     => Csrf::token(),
            ], 'app');
            return;
        }

        $_SESSION['mesa_partes_exitoso'] = [
            'codigo'        => $resultado['codigo'],
            'id_expediente' => $resultado['id_expediente'],
            'asunto'        => $datos['asunto'],
            'prioridad'     => $datos['prioridad'],
            'canal'         => $datos['canal_ingreso'],
            'area_destino'  => $idAreaDestino > 0 ? ($this->modelo->getArea($idAreaDestino)['nombre'] ?? null) : null,
            'folios'        => $documento['folios'],
            'remitente'     => $this->nombreRemitente($datos),
            'documento'     => ($this->modelo->getTipoDocumento($documento['id_tipo_doc'])['nombre'] ?? 'Documento')
                . ($documento['numero_documento'] !== '' ? ' N° ' . $documento['numero_documento'] : ''),
        ];

        $this->redirect('/mesa-partes/exitoso');
    }

    /* ============================================================
     * PANTALLA DE ÉXITO + CARGO DE RECEPCIÓN
     * ============================================================ */
    public function exitoso(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $expediente = Session::get('mesa_partes_exitoso');
        if (empty($expediente)) {
            $this->redirect('/mesa-partes/registrar');
            return;
        }
        Session::remove('mesa_partes_exitoso');

        $this->view('mesa-partes.exitoso', [
            'titulo'     => 'Documento registrado en ventanilla',
            'expediente' => $expediente,
        ], 'app');
    }

    /* ============================================================
     * TAREA E02-2: BANDEJA DE MESA DE PARTES
     * ============================================================ */
    public function bandeja(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $request = new Request();

        $hoy = date('Y-m-d');
        $filtros = [
            'desde'  => $this->fechaValida((string) ($request->get('desde') ?? '')) ? (string) $request->get('desde') : $hoy,
            'hasta'  => $this->fechaValida((string) ($request->get('hasta') ?? '')) ? (string) $request->get('hasta') : $hoy,
            'canal'  => in_array((string) ($request->get('canal') ?? ''), ['Presencial', 'Interno'], true) ? (string) $request->get('canal') : '',
            'estado' => $this->estadoPermitido((string) ($request->get('estado') ?? '')) ? (string) $request->get('estado') : '',
            'area_id' => max(0, (int) ($request->get('area_id') ?? 0)),
            'q'      => trim((string) ($request->get('q') ?? '')),
            'pagina' => max(1, (int) ($request->get('pagina') ?? 1)),
            'por_pagina' => 20,
            'paginar' => true,
        ];

        $resultado = $this->modelo->listarMesaPartes($filtros);
        $indicadores = $this->modelo->contarIndicadores($filtros['desde'], $filtros['hasta']);

        $mensajes = Flash::pull();

        $this->view('mesa-partes.bandeja', [
            'titulo'        => 'Bandeja de Mesa de Partes',
            'filtros'       => $filtros,
            'expedientes'   => $resultado['registros'],
            'total'         => $resultado['total'],
            'paginas'       => $resultado['paginas'],
            'paginaActual'  => $resultado['pagina_actual'],
            'indicadores'   => $indicadores,
            'areas'         => $this->modelo->listarAreasActivas(),
            // Origen de la derivación: se excluye del selector de destinos en la vista.
            'areaOrigenId'  => (int) ($this->modelo->obtenerAreaMesaPartes()['id_area'] ?? 0),
            'flash_success' => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'   => $this->primerMensaje($mensajes, Flash::Error),
            'csrf_token'    => Csrf::token(),
        ], 'app');
    }

    /* ============================================================
     * CARGO DE RECEPCIÓN IMPRIMIBLE
     * (E03-1 entregará el PDF formal con QR; por ahora cargo web imprimible)
     * ============================================================ */
    public function imprimirCargo(string|int $idExpediente): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $detalle = $this->modelo->getExpedienteCompleto((int) $idExpediente);
        if (empty($detalle)) {
            Flash::error('No se encontró el expediente solicitado.');
            $this->redirect('/mesa-partes/bandeja');
            return;
        }

        $this->view('mesa-partes.cargo', [
            'titulo'    => 'Cargo de Recepción — ' . $detalle['expediente']['codigo_expediente'],
            'expediente' => $detalle['expediente'],
            'documentos' => $detalle['documentos'],
            'derivaciones' => $detalle['derivaciones'],
        ], 'app');
    }

    /* ============================================================
     * TAREA E03-1: CARGO DE RECEPCIÓN EN PDF (descarga interna)
     * ============================================================ */
    public function cargoPdf(string|int $idExpediente): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $detalle = $this->modelo->getExpedienteCompleto((int) $idExpediente);
        if (empty($detalle)) {
            Flash::error('No se encontró el expediente solicitado.');
            $this->redirect('/mesa-partes/bandeja');
            return;
        }

        $datos = $this->normalizarParaPdf($detalle);

        $this->registrarAuditoriaImpresion((int) $idExpediente, $datos['codigo'], 'cargo');

        $contenido = (new PdfCargoService())->generarCargo($datos);
        PdfCargoService::stream($contenido, 'Cargo-' . $datos['codigo'] . '.pdf', 'attachment');
    }

    /* ============================================================
     * TAREA E03-2: HOJA DE RUTA FÍSICA EN PDF
     * ============================================================ */
    public function hojaRuta(string|int $idExpediente): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $detalle = $this->modelo->getExpedienteCompleto((int) $idExpediente);
        if (empty($detalle)) {
            Flash::error('No se encontró el expediente solicitado.');
            $this->redirect('/mesa-partes/bandeja');
            return;
        }

        $datos = $this->normalizarParaPdf($detalle);

        $this->registrarAuditoriaImpresion((int) $idExpediente, $datos['codigo'], 'hoja de ruta');

        $contenido = (new PdfCargoService())->generarHojaRuta($datos);
        PdfCargoService::stream($contenido, 'HojaRuta-' . $datos['codigo'] . '.pdf', 'attachment');
    }

    /* ============================================================
     * DERIVAR EXPEDIENTE DESDE LA BANDEJA DE MESA DE PARTES
     * ============================================================ */
    public function derivar(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $request = new Request();
        $idExpediente = (int) $request->input('id_expediente', 0);
        $idAreaDestino = (int) $request->input('id_area_destino', 0);
        $observaciones = trim((string) $request->input('observaciones', ''));

        // Validar CSRF
        $this->requerirCsrf($request->all(), '/mesa-partes/bandeja');

        if ($idExpediente <= 0) {
            Flash::error('Identificador de expediente no válido.');
            $this->redirect('/mesa-partes/bandeja');
            return;
        }

        if ($idAreaDestino <= 0) {
            Flash::error('Debe seleccionar un área de destino válida.');
            $this->redirect('/mesa-partes/bandeja');
            return;
        }

        $user = Session::user();
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        $resultado = $this->modelo->derivarExpediente(
            $idExpediente,
            $idAreaDestino,
            $idUsuario,
            $observaciones,
            $idAreaUsuario
        );

        if ($resultado['success']) {
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/mesa-partes/bandeja');
    }

    /**
     * Adapta la estructura de getExpedienteCompleto() al formato del servicio PDF.
     */
    private function normalizarParaPdf(array $detalle): array
    {
        $e = $detalle['expediente'] ?? [];

        $documentos = [];
        $totalFolios = 0;
        foreach ($detalle['documentos'] ?? [] as $doc) {
            $folios = (int) ($doc['folios'] ?? 0);
            $totalFolios += $folios;
            $documentos[] = [
                'tipo'   => $doc['tipo_documento_nombre'] ?? 'Documento',
                'numero' => $doc['numero_documento'] ?? '',
                'asunto' => $doc['asunto'] ?? '',
                'folios' => $folios,
            ];
        }

        $derivaciones = [];
        foreach ($detalle['derivaciones'] ?? [] as $der) {
            $derivaciones[] = [
                'origen'          => $der['area_origen_nombre'] ?? 'Mesa de Partes',
                'destino'         => $der['area_destino_nombre'] ?? '—',
                'estado'          => $der['estado'] ?? 'Pendiente',
                'fecha_recepcion' => $der['fecha_recepcion'] ?? null,
            ];
        }

        return [
            'codigo'         => $e['codigo_expediente'] ?? '',
            'canal'          => $e['canal_ingreso'] ?? 'Presencial',
            'prioridad'      => $e['prioridad'] ?? 'Normal',
            'estado'         => $e['estado_actual'] ?? 'Registrado',
            'asunto'         => $e['asunto'] ?? '',
            'fecha_registro' => $e['fecha_registro'] ?? '',
            'remitente'      => [
                'tipo_documento'   => $e['remitente_tipo_doc'] ?? 'DNI',
                'numero_documento' => $e['remitente_num_doc'] ?? '',
                'nombre'           => $e['remitente_nombre'] ?? '—',
                'direccion'        => $e['direccion'] ?? '',
                'telefono'         => $e['telefono'] ?? '',
                'email'            => $e['email'] ?? '',
            ],
            'documentos'     => $documentos,
            'total_folios'   => $totalFolios ?: (int) ($e['total_folios'] ?? 0),
            'derivaciones'   => $derivaciones,
            'consulta_url'   => PdfCargoService::urlConsulta((string) ($e['codigo_expediente'] ?? '')),
        ];
    }

    /**
     * Auditoría obligatoria de la emisión de documentos formales en PDF.
     */
    private function registrarAuditoriaImpresion(int $idExpediente, string $codigo, string $tipo): void
    {
        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

        (new Auditoria())->insertar(
            $idUsuario,
            'mesa_partes',
            'imprimir',
            'expedientes',
            $idExpediente,
            "Se emitió el {$tipo} en PDF del expediente {$codigo}.",
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }

    /* ============================================================
     * ENDPOINT AJAX: BÚSQUEDA EN VIVO DE REMITENTE
     * ============================================================ */
    public function buscarPersona(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $request = new Request();
        $q = trim((string) ($request->get('q') ?? ''));

        if (mb_strlen($q) < 3) {
            $this->json(['resultados' => []]);
            return;
        }

        $this->json(['resultados' => $this->modelo->buscarPersonas($q)]);
    }

    /* ============================================================
     * ENDPOINT AJAX: VERIFICACIÓN DE DOCUMENTO (BD → RENIEC/SUNAT)
     * Primero consulta la base local; el servicio externo SOLO se usa
     * cuando la persona no existe aún en `personas`.
     * ============================================================ */
    public function consultarDocumento(): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $request = new Request();
        $tipoDocumento = strtoupper(trim((string) ($request->get('tipo_documento') ?? '')));
        $numero = trim((string) ($request->get('numero_documento') ?? ''));

        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC para la búsqueda externa.'], 422);
            return;
        }

        $longitud = $tipoDocumento === 'DNI' ? 8 : 11;
        if (!preg_match('/^[0-9]{' . $longitud . '}$/D', $numero)) {
            $this->json(['encontrado' => false, 'mensaje' => "Ingrese un {$tipoDocumento} válido de {$longitud} dígitos."], 422);
            return;
        }

        // 1) Fuente preferente: base de datos local (coincidencia exacta).
        $personaLocal = (new Persona())->buscarPorDocumento($tipoDocumento, $numero);
        if ($personaLocal) {
            $this->json([
                'encontrado' => true,
                'origen'     => 'interna',
                'mensaje'    => 'El remitente ya está registrado en la base local.',
                'data'       => [
                    'id_persona'        => (int) $personaLocal['id_persona'],
                    'tipo_persona'      => $personaLocal['tipo_persona'] ?? 'Natural',
                    'tipo_documento'    => $personaLocal['tipo_documento'],
                    'numero_documento'  => $personaLocal['numero_documento'],
                    'nombres'           => $personaLocal['nombres'] ?? '',
                    'apellido_paterno'  => $personaLocal['apellido_paterno'] ?? '',
                    'apellido_materno'  => $personaLocal['apellido_materno'] ?? '',
                    'razon_social'      => $personaLocal['razon_social'] ?? '',
                    'email'             => $personaLocal['email'] ?? '',
                    'telefono'          => $personaLocal['telefono'] ?? '',
                    'direccion'         => $personaLocal['direccion'] ?? '',
                ],
            ]);
            return;
        }

        // 2) Solo si no existe en BD: servicio externo RENIEC / SUNAT.
        $resultado = (new DocumentoLookupService())->consultar($tipoDocumento, $numero);

        // Se expone la misma estructura 'data' que usa el formulario de personas
        if (!empty($resultado['encontrado']) && isset($resultado['persona'])) {
            $resultado['data'] = array_merge(
                ['numero_documento' => $numero],
                $resultado['persona']
            );
            $resultado['origen'] = 'externa';
        }

        $estado = ($resultado['encontrado'] ?? false) ? 200 : (str_contains($resultado['mensaje'] ?? '', 'disponible') ? 503 : 422);
        $this->json($resultado, $estado);
    }

    /* ============================================================
     * ENDPOINT AJAX: DETALLE DEL EXPEDIENTE
     * ============================================================ */
    public function detalle(string|int $idExpediente): void
    {
        $this->requerirRol(self::RolesPermitidos, '/dashboard');

        $detalle = $this->modelo->getExpedienteCompleto((int) $idExpediente);
        if (empty($detalle)) {
            $this->json(['success' => false, 'message' => 'Expediente no encontrado.'], 404);
            return;
        }

        $this->json(['success' => true] + $detalle);
    }

    /* ============================================================
     * NORMALIZACIÓN Y VALIDACIÓN DE ENTRADA
     * El control de acceso quedó en `AuthorizesRequest` (OPT-06); antes
     * existían aquí `requerirAutenticacion()`, `tieneAccesoMesaPartes()` y
     * un `validarCsrf()` con rama fail-open (SEC-01).
     * ============================================================ */
    private function normalizar(array $datos): array
    {
        $normalizado = [];
        foreach ($datos as $clave => $valor) {
            $normalizado[$clave] = is_string($valor) ? trim($valor) : $valor;
        }

        foreach (['nombres', 'apellido_paterno', 'apellido_materno', 'razon_social', 'email', 'telefono', 'direccion', 'asunto', 'doc_numero', 'doc_asunto', 'instrucciones'] as $campo) {
            if (!isset($normalizado[$campo]) || !is_string($normalizado[$campo])) {
                $normalizado[$campo] = (string) ($normalizado[$campo] ?? '');
            }
            // VAL-02: la base sigue en utf8mb3; un emoji aborta el INSERT (1366).
            $normalizado[$campo] = Catalogos::sinCuatroBytes($normalizado[$campo]);
        }

        return $normalizado;
    }

    /**
     * VAL-03: el `maxlength` del HTML no es un control de servidor. Se compara
     * en caracteres contra el límite real de la columna en db.sql.
     */
    private function limitar(array &$errors, array $datos, string $campo, string $tablaColumna, string $etiqueta): void
    {
        $valor = (string) ($datos[$campo] ?? '');

        if (!Catalogos::cabe($tablaColumna, $valor)) {
            $errors[$campo][] = sprintf(
                '%s no debe exceder %d caracteres (tiene %d).',
                $etiqueta,
                Catalogos::limite($tablaColumna),
                mb_strlen($valor, 'UTF-8')
            );
        }
    }

    private function validar(array $datos): array
    {
        $errors = [];

        // ----- Canal y prioridad -----
        if (!Catalogos::es(['Presencial', 'Interno'], (string) ($datos['canal_ingreso'] ?? ''))) {
            $errors['canal_ingreso'][] = 'Seleccione el canal de ingreso (Presencial o Interno).';
        }
        if (!Catalogos::es(Catalogos::Prioridad, (string) ($datos['prioridad'] ?? ''))) {
            $errors['prioridad'][] = 'Seleccione la prioridad del trámite.';
        }

        // ----- Remitente -----
        if (!Catalogos::es(Catalogos::TipoDocumento, (string) ($datos['tipo_documento'] ?? ''))) {
            $errors['tipo_documento'][] = 'Seleccione un tipo de documento válido.';
        }

        $numero = (string) ($datos['numero_documento'] ?? '');
        if ($numero === '') {
            $errors['numero_documento'][] = 'Ingrese el número de documento del remitente.';
        } elseif (($datos['tipo_documento'] ?? '') === 'DNI' && !preg_match('/^[0-9]{8}$/D', $numero)) {
            $errors['numero_documento'][] = 'El DNI debe contener exactamente 8 dígitos.';
        } elseif (($datos['tipo_documento'] ?? '') === 'RUC' && !preg_match('/^[0-9]{11}$/D', $numero)) {
            $errors['numero_documento'][] = 'El RUC debe contener exactamente 11 dígitos.';
        } elseif (!preg_match('/^[A-Za-z0-9\-]{4,20}$/D', $numero)) {
            $errors['numero_documento'][] = 'Número de documento inválido.';
        }

        if (!Catalogos::es(Catalogos::TipoPersona, (string) ($datos['tipo_persona'] ?? ''))) {
            $errors['tipo_persona'][] = 'Seleccione el tipo de persona.';
        }

        if (($datos['tipo_persona'] ?? 'Natural') === 'Natural') {
            if (mb_strlen((string) ($datos['nombres'] ?? '')) < 3) {
                $errors['nombres'][] = 'Ingrese los nombres del remitente.';
            }
            if (mb_strlen((string) ($datos['apellido_paterno'] ?? '')) < 2) {
                $errors['apellido_paterno'][] = 'Ingrese el apellido paterno.';
            }
        } elseif (mb_strlen((string) ($datos['razon_social'] ?? '')) < 3) {
            $errors['razon_social'][] = 'Ingrese la razón social del remitente.';
        }

        $email = (string) ($datos['email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'El correo electrónico no es válido.';
        }

        // ----- Expediente / documento -----
        if (mb_strlen((string) ($datos['asunto'] ?? '')) < 5) {
            $errors['asunto'][] = 'Describa el asunto del expediente (mínimo 5 caracteres).';
        }
        if (empty($datos['doc_tipo'])) {
            $errors['doc_tipo'][] = 'Seleccione el tipo de documento físico recibido.';
        }

        $folios = (int) ($datos['doc_folios'] ?? 0);
        if ($folios < 1 || $folios > 9999) {
            $errors['doc_folios'][] = 'Indique el número exacto de folios (1 a 9999).';
        }

        if (!empty($datos['doc_fecha']) && !$this->fechaValida((string) $datos['doc_fecha'])) {
            $errors['doc_fecha'][] = 'La fecha del documento no es válida.';
        }

        if (!empty($datos['id_area_destino']) && (int) $datos['id_area_destino'] === 0) {
            $errors['id_area_destino'][] = 'Seleccione el área destino inicial o deje la opción "No derivar aún".';
        }

        // ----- VAL-03: topes reales de columna (db.sql), no el maxlength del HTML -----
        $this->limitar($errors, $datos, 'nombres', 'personas.nombres', 'Los nombres');
        $this->limitar($errors, $datos, 'apellido_paterno', 'personas.apellido_paterno', 'El apellido paterno');
        $this->limitar($errors, $datos, 'apellido_materno', 'personas.apellido_materno', 'El apellido materno');
        $this->limitar($errors, $datos, 'razon_social', 'personas.razon_social', 'La razón social');
        $this->limitar($errors, $datos, 'email', 'personas.email', 'El correo electrónico');
        $this->limitar($errors, $datos, 'telefono', 'personas.telefono', 'El teléfono');
        $this->limitar($errors, $datos, 'direccion', 'personas.direccion', 'La dirección');
        $this->limitar($errors, $datos, 'asunto', 'expedientes.asunto', 'El asunto del expediente');
        $this->limitar($errors, $datos, 'doc_numero', 'documentos.numero_documento', 'El número del documento');
        $this->limitar($errors, $datos, 'doc_asunto', 'documentos.asunto', 'El asunto del documento');
        $this->limitar($errors, $datos, 'instrucciones', 'derivaciones.observaciones', 'Las instrucciones de derivación');

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

    private function estadoPermitido(string $estado): bool
    {
        return in_array($estado, ['Sin_Derivar', 'Pendiente', 'Recibido', 'En_Atencion', 'Atendido', 'Devuelto'], true);
    }

    private function nombreRemitente(array $datos): string
    {
        if (($datos['tipo_persona'] ?? 'Natural') === 'Juridica') {
            return (string) ($datos['razon_social'] ?? '');
        }
        return trim(implode(' ', array_filter([
            $datos['nombres'] ?? '',
            $datos['apellido_paterno'] ?? '',
            $datos['apellido_materno'] ?? '',
        ])));
    }
}
