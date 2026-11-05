<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\ConsutlaExpediente;
use App\Services\PdfCargoService;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class ConsultaExpedienteController extends Controller
{
    use AuthorizesRequest;


    private $model;

    public function __construct()
    {
        $this->model = new ConsutlaExpediente();
    }

    /* ============================================================
     * FORMULARIO DE BÚSQUEDA
     * Si llega ?codigo=... (por ejemplo desde el QR del cargo),
     * se ejecuta la consulta directamente.
     * ============================================================ */
    public function index()
    {
        $codigo = trim((string) ($this->input('codigo') ?? ''));
        if ($codigo !== '') {
            return $this->buscar();
        }

        return $this->view("consulta/index", [
            'errors'       => [],
            'datos_viejos' => [],
        ]);
    }

    /* ============================================================
     * BUSCAR EXPEDIENTE
     * ============================================================ */
    public function buscar()
    {
        $request = new Request();
        $datos   = $request->all();

        $errors = [];

        $codigo   = trim($datos['codigo'] ?? '');
        $numDoc   = trim($datos['numero_documento'] ?? '');

        if ($codigo === '') {
            $errors['codigo'][] = 'Ingrese el código del expediente.';
        }

        if (!empty($errors)) {
            return $this->view("consulta/index", [
                'errors'       => $errors,
                'datos_viejos' => $datos,
            ]);
        }

        $expediente = $this->model->buscarPorCodigo($codigo, $numDoc ?: null);

        if (!$expediente) {
            return $this->view("consulta/index", [
                'errors'       => ['general' => ['No se encontró ningún expediente con los datos proporcionados.']],
                'datos_viejos' => $datos,
            ]);
        }

        // Cargar data relacionada
        $documentos     = $this->model->obtenerDocumentos($expediente['id_expediente']);
        $historial      = $this->model->obtenerHistorial($expediente['id_expediente']);
        $derivaciones   = $this->model->obtenerDerivaciones($expediente['id_expediente']);
        $observaciones  = $this->model->obtenerObservaciones($expediente['id_expediente']);

        return $this->view("consulta/detalle", [
            'expediente'    => $expediente,
            'documentos'    => $documentos,
            'historial'     => $historial,
            'derivaciones'  => $derivaciones,
            'observaciones' => $observaciones,
        ]);
    }

    /* ============================================================
     * CARGO DE RECEPCIÓN EN PDF (EPICA 3 — TAREA E03-1)
     * Descarga pública del cargo formal a partir del código del
     * expediente (enlazada desde el código QR del comprobante).
     * ============================================================ */
    public function imprimirCargo(string $codigo): void
    {
        $codigo = trim($codigo);

        $expediente = $this->model->buscarPorCodigo($codigo);
        if (!$expediente) {
            http_response_code(404);
            $this->view("consulta/index", [
                'errors'       => ['general' => ['No se encontró el expediente solicitado para emitir el cargo.']],
                'datos_viejos' => ['codigo' => $codigo],
            ]);
            return;
        }

        $documentos   = $this->model->obtenerDocumentos((int) $expediente['id_expediente']);
        $derivaciones = $this->model->obtenerDerivaciones((int) $expediente['id_expediente']);

        $datos = $this->normalizarParaCargo($expediente, $documentos, $derivaciones);

        $contenido = (new PdfCargoService())->generarCargo($datos);
        PdfCargoService::stream($contenido, 'Cargo-' . $expediente['codigo_expediente'] . '.pdf', 'inline');
    }

    /**
     * Adapta los datos de los modelos al formato del servicio PDF.
     */
    private function normalizarParaCargo(array $expediente, array $documentos, array $derivaciones): array
    {
        $nombre = trim((string) ($expediente['razon_social'] ?? ''));
        if ($nombre === '') {
            $nombre = trim(($expediente['nombres'] ?? '') . ' ' . ($expediente['apellido_paterno'] ?? '') . ' ' . ($expediente['apellido_materno'] ?? ''));
        }

        $totalFolios = 0;
        $docs = [];
        foreach ($documentos as $doc) {
            $totalFolios += (int) ($doc['folios'] ?? 0);
            $docs[] = [
                'tipo'   => $doc['tipo_doc_nombre'] ?? 'Documento',
                'numero' => $doc['numero_documento'] ?? '',
                'asunto' => $doc['asunto'] ?? '',
                'folios' => (int) ($doc['folios'] ?? 0),
            ];
        }

        $ders = [];
        foreach ($derivaciones as $der) {
            $ders[] = [
                'origen'          => $der['area_origen'] ?? 'Mesa de Partes',
                'destino'         => $der['area_destino'] ?? '—',
                'estado'          => $der['estado'] ?? 'Pendiente',
                'fecha_recepcion' => $der['fecha_recepcion'] ?? null,
            ];
        }

        return [
            'codigo'         => $expediente['codigo_expediente'],
            'canal'          => $expediente['canal_ingreso'] ?? 'Virtual',
            'prioridad'      => $expediente['prioridad'] ?? 'Normal',
            'estado'         => $expediente['estado_actual'] ?? 'Registrado',
            'asunto'         => $expediente['asunto'] ?? '',
            'fecha_registro' => $expediente['fecha_registro'] ?? '',
            'remitente'      => [
                'tipo_documento'   => $expediente['tipo_documento'] ?? 'DNI',
                'numero_documento' => $expediente['numero_documento'] ?? '',
                'nombre'           => $nombre !== '' ? $nombre : '—',
                'direccion'        => $expediente['direccion'] ?? '',
                'telefono'         => $expediente['telefono'] ?? '',
                'email'            => $expediente['email'] ?? '',
            ],
            'documentos'     => $docs,
            'total_folios'   => $totalFolios,
            'derivaciones'   => $ders,
            'consulta_url'   => PdfCargoService::urlConsulta((string) $expediente['codigo_expediente']),
        ];
    }

    /* ============================================================
     * DESCARGAR ADJUNTO
     * SEC-05: antes bastaba con enumerar `id_archivo` para bajar cualquier
     * documento del sistema (IDOR). Ahora se exige un factor de posesión:
     * el código del expediente al que pertenece el archivo (el mismo que ya
     * se usó para consultar), salvo que exista sesión de un perfil operativo.
     * La consulta SQL vive en el modelo, no en el controlador.
     * ============================================================ */
    public function descargar($idArchivo, $codigo = null)
    {
        $idArchivo = (int) $idArchivo;
        $codigo = trim((string) ($codigo ?? $this->input('codigo') ?? ''));

        $archivo = $idArchivo > 0 ? $this->model->obtenerAdjunto($idArchivo) : null;

        if (!$archivo) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        // Perfiles que sí gestionan expedientes: pueden ver cualquier adjunto.
        // El rol CONSULTA y el público anónimo requieren el código del expediente.
        $esOperador = Session::isAuthenticated()
            && Roles::usuarioTiene(array_merge(Roles::Administracion, Roles::MesaDePartes, Roles::AtencionArea));

        if (!$esOperador) {
            if ($codigo === '' || !hash_equals((string) $archivo['codigo_expediente'], $codigo)) {
                http_response_code(403);
                exit('No está autorizado para descargar este archivo.');
            }
        }

        $rutaFisica = realpath(__DIR__ . '/../../public/' . $archivo['ruta_archivo']);
        $raizPublic = realpath(__DIR__ . '/../../public');

        // Solo se sirve un archivo real dentro de public/ (evita path traversal).
        if ($rutaFisica === false || $raizPublic === false || !str_starts_with($rutaFisica, $raizPublic)) {
            http_response_code(404);
            exit('El archivo no existe en el servidor.');
        }

        header('Content-Type: ' . ($archivo['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($archivo['nombre_original']) . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        readfile($rutaFisica);
        exit;
    }
}
