<?php

namespace App\Controllers;

use App\Models\EntregaModel;
use PDO;
use RuntimeException;
use Src\Core\Conexion;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Throwable;

class ExpedienteController extends Controller
{
    private ?PDO $db = null;

    public function index(): void
    {
        if (!$this->ensureAuthenticated()) {
            return;
        }

        try {
            $stmt = $this->database()->query(
                'SELECT e.id_expediente, e.codigo_expediente, e.asunto, e.prioridad,
                        e.canal_ingreso, e.estado_actual, e.fecha_registro,
                        p.id_persona AS id_remitente, p.tipo_documento,
                        p.numero_documento, p.nombres, p.apellido_paterno,
                        p.apellido_materno, p.razon_social
                 FROM expedientes e
                 INNER JOIN personas p ON p.id_persona = e.id_remitente
                 ORDER BY e.fecha_registro DESC, e.id_expediente DESC'
            );

            $this->json(['expedientes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            $this->reportError('listar expedientes', $e);
            $this->json(['error' => 'No se pudieron cargar los expedientes.'], 500);
        }
    }

    public function create(): void
    {
        if (!$this->ensureAuthenticated()) {
            return;
        }

        try {
            $stmt = $this->database()->query(
                "SELECT id_tipo_doc, nombre
                 FROM tipos_documento
                 WHERE estado = 'Activo'
                 ORDER BY nombre"
            );

            $this->json([
                'tipos_documento' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'campos_requeridos' => [
                    'tipo_documento',
                    'numero_documento',
                    'nombres',
                    'apellido_paterno',
                    'email',
                    'asunto',
                    'prioridad',
                    'doc_tipo',
                    'doc_asunto',
                    'doc_folios',
                ],
            ]);
        } catch (Throwable $e) {
            $this->reportError('cargar formulario de expediente', $e);
            $this->json(['error' => 'No se pudo cargar el formulario de expediente.'], 500);
        }
    }

    public function store(): void
    {
        if (!$this->ensureAuthenticated()) {
            return;
        }

        $datos = (new Request())->all();
        $errores = $this->validateCreateData($datos);

        if ($errores) {
            $this->json(['errores' => $errores], 422);
        }

        try {
            $idTipoDocumento = (int) $datos['doc_tipo'];
            $stmt = $this->database()->prepare(
                "SELECT 1
                 FROM tipos_documento
                 WHERE id_tipo_doc = :id AND estado = 'Activo'"
            );
            $stmt->execute([':id' => $idTipoDocumento]);
            if (!$stmt->fetchColumn()) {
                $this->json(['errores' => ['doc_tipo' => 'Seleccione un tipo de documento activo.']], 422);
            }

            $datosExpediente = [
                'tipo_documento' => trim((string) $datos['tipo_documento']),
                'numero_documento' => trim((string) $datos['numero_documento']),
                'nombres' => trim((string) $datos['nombres']),
                'apellido_paterno' => trim((string) $datos['apellido_paterno']),
                'apellido_materno' => $this->stringValue($datos, 'apellido_materno'),
                'email' => trim((string) $datos['email']),
                'telefono' => $this->stringValue($datos, 'telefono'),
                'direccion' => $this->stringValue($datos, 'direccion'),
                'asunto' => trim((string) $datos['asunto']),
                'prioridad' => trim((string) $datos['prioridad']),
            ];
            $documento = [
                'id_tipo_doc' => $idTipoDocumento,
                'numero_documento' => $this->stringValue($datos, 'doc_numero'),
                'asunto' => trim((string) $datos['doc_asunto']),
                'folios' => (int) $datos['doc_folios'],
                'fecha_documento' => $this->stringValue($datos, 'doc_fecha'),
            ];

            $resultado = (new EntregaModel())->registrarExpediente($datosExpediente, $documento, []);
            if (empty($resultado['ok'])) {
                error_log('Error al registrar expediente: ' . ($resultado['mensaje'] ?? 'Error desconocido.'));
                $this->json(['error' => 'No se pudo registrar el expediente.'], 500);
            }

            $this->json([
                'mensaje' => 'Expediente registrado correctamente.',
                'expediente' => [
                    'id_expediente' => (int) $resultado['id_expediente'],
                    'id_documento' => (int) $resultado['id_documento'],
                    'codigo_expediente' => $resultado['codigo'],
                ],
            ], 201);
        } catch (Throwable $e) {
            $this->reportError('registrar expediente', $e);
            $this->json(['error' => 'No se pudo registrar el expediente.'], 500);
        }
    }

    public function show(string $id): void
    {
        if (!$this->ensureAuthenticated()) {
            return;
        }

        $idExpediente = $this->parseId($id);
        if ($idExpediente === null) {
            $this->json(['error' => 'El identificador del expediente no es válido.'], 400);
        }

        try {
            $db = $this->database();
            $stmt = $db->prepare(
                'SELECT e.*, p.id_persona AS id_remitente, p.tipo_documento,
                        p.numero_documento, p.nombres, p.apellido_paterno,
                        p.apellido_materno, p.razon_social, p.email, p.telefono, p.direccion
                 FROM expedientes e
                 INNER JOIN personas p ON p.id_persona = e.id_remitente
                 WHERE e.id_expediente = :id'
            );
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->json(['error' => 'El expediente no existe.'], 404);
            }

            $stmt = $db->prepare(
                'SELECT d.*, td.nombre AS tipo_doc_nombre
                 FROM documentos d
                 INNER JOIN tipos_documento td ON td.id_tipo_doc = d.id_tipo_doc
                 WHERE d.id_expediente = :id
                 ORDER BY d.es_principal DESC, d.id_documento ASC'
            );
            $stmt->execute([':id' => $idExpediente]);
            $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtAdjuntos = $db->prepare(
                'SELECT id_archivo, id_documento, nombre_original, nombre_archivo,
                        ruta_archivo, extension, mime_type, tamanio, hash_archivo, creado_en
                 FROM archivos_adjuntos
                 WHERE id_documento = :id
                 ORDER BY id_archivo ASC'
            );
            foreach ($documentos as &$documento) {
                $stmtAdjuntos->execute([':id' => $documento['id_documento']]);
                $documento['adjuntos'] = $stmtAdjuntos->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($documento);

            $this->json(['expediente' => $expediente, 'documentos' => $documentos]);
        } catch (Throwable $e) {
            $this->reportError('obtener expediente', $e);
            $this->json(['error' => 'No se pudo cargar el expediente.'], 500);
        }
    }

    public function seguimiento(string $id): void
    {
        if (!$this->ensureAuthenticated()) {
            return;
        }

        $idExpediente = $this->parseId($id);
        if ($idExpediente === null) {
            $this->json(['error' => 'El identificador del expediente no es válido.'], 400);
        }

        try {
            $db = $this->database();
            $stmt = $db->prepare(
                'SELECT id_expediente, codigo_expediente, estado_actual, fecha_registro,
                        fecha_atencion, fecha_archivado
                 FROM expedientes
                 WHERE id_expediente = :id'
            );
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->json(['error' => 'El expediente no existe.'], 404);
            }

            $stmt = $db->prepare(
                'SELECT h.*, u.username, p.nombres, p.apellido_paterno
                 FROM historial_expediente h
                 LEFT JOIN usuarios u ON u.id_usuario = h.id_usuario
                 LEFT JOIN personas p ON p.id_persona = u.id_persona
                 WHERE h.id_expediente = :id
                 ORDER BY h.fecha ASC, h.id_historial ASC'
            );
            $stmt->execute([':id' => $idExpediente]);
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare(
                'SELECT d.*, ao.nombre AS area_origen, ad.nombre AS area_destino
                 FROM derivaciones d
                 INNER JOIN areas ao ON ao.id_area = d.id_area_origen
                 INNER JOIN areas ad ON ad.id_area = d.id_area_destino
                 WHERE d.id_expediente = :id
                 ORDER BY d.fecha_envio ASC, d.id_derivacion ASC'
            );
            $stmt->execute([':id' => $idExpediente]);
            $derivaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare(
                'SELECT o.*, p.nombres, p.apellido_paterno
                 FROM observaciones o
                 LEFT JOIN usuarios u ON u.id_usuario = o.id_usuario
                 LEFT JOIN personas p ON p.id_persona = u.id_persona
                 WHERE o.id_expediente = :id
                 ORDER BY o.fecha_registro ASC, o.id_observacion ASC'
            );
            $stmt->execute([':id' => $idExpediente]);
            $observaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->json([
                'expediente' => $expediente,
                'historial' => $historial,
                'derivaciones' => $derivaciones,
                'observaciones' => $observaciones,
            ]);
        } catch (Throwable $e) {
            $this->reportError('cargar seguimiento de expediente', $e);
            $this->json(['error' => 'No se pudo cargar el seguimiento del expediente.'], 500);
        }
    }

    private function ensureAuthenticated(): bool
    {
        if (Session::isAuthenticated()) {
            return true;
        }

        $this->json(['error' => 'No has iniciado sesión.'], 401);
        return false;
    }

    private function database(): PDO
    {
        if ($this->db === null) {
            $db = Conexion::getConexion();
            if ($db === null) {
                throw new RuntimeException('No se pudo establecer la conexión a la base de datos.');
            }
            $this->db = $db;
        }

        return $this->db;
    }

    private function parseId(string $id): ?int
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return (int) $id;
    }

    private function validateCreateData(array $datos): array
    {
        $errores = [];
        $required = [
            'tipo_documento',
            'numero_documento',
            'nombres',
            'apellido_paterno',
            'email',
            'asunto',
            'prioridad',
            'doc_tipo',
            'doc_asunto',
            'doc_folios',
        ];

        foreach ($required as $campo) {
            if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
                $errores[$campo] = 'Este campo es obligatorio.';
            }
        }

        if (isset($datos['tipo_documento']) && !in_array($datos['tipo_documento'], ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'], true)) {
            $errores['tipo_documento'] = 'Seleccione un tipo de documento válido.';
        }
        if (isset($datos['prioridad']) && !in_array($datos['prioridad'], ['Normal', 'Urgente'], true)) {
            $errores['prioridad'] = 'Seleccione una prioridad válida.';
        }
        if (isset($datos['numero_documento']) && is_scalar($datos['numero_documento']) && strlen((string) $datos['numero_documento']) > 20) {
            $errores['numero_documento'] = 'El número de documento no puede superar 20 caracteres.';
        }
        if (isset($datos['email']) && is_scalar($datos['email']) && !filter_var((string) $datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Ingrese un correo electrónico válido.';
        }
        if (isset($datos['email']) && is_scalar($datos['email']) && strlen((string) $datos['email']) > 150) {
            $errores['email'] = 'El correo electrónico no puede superar 150 caracteres.';
        }
        if (isset($datos['asunto']) && is_scalar($datos['asunto']) && strlen((string) $datos['asunto']) > 500) {
            $errores['asunto'] = 'El asunto no puede superar 500 caracteres.';
        }
        if (isset($datos['doc_asunto']) && is_scalar($datos['doc_asunto']) && strlen((string) $datos['doc_asunto']) > 500) {
            $errores['doc_asunto'] = 'El asunto del documento no puede superar 500 caracteres.';
        }
        if (isset($datos['doc_tipo']) && is_scalar($datos['doc_tipo']) && filter_var($datos['doc_tipo'], FILTER_VALIDATE_INT) === false) {
            $errores['doc_tipo'] = 'Seleccione un tipo de documento válido.';
        }
        if (isset($datos['doc_folios']) && is_scalar($datos['doc_folios']) && (filter_var($datos['doc_folios'], FILTER_VALIDATE_INT) === false || (int) $datos['doc_folios'] < 1)) {
            $errores['doc_folios'] = 'Ingrese un número de folios válido.';
        }

        return $errores;
    }

    private function stringValue(array $datos, string $campo): string
    {
        return isset($datos[$campo]) && is_scalar($datos[$campo])
            ? trim((string) $datos[$campo])
            : '';
    }

    private function reportError(string $action, Throwable $error): void
    {
        error_log('Error al ' . $action . ': ' . $error->getMessage());
    }
}
