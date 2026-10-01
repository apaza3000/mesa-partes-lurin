<?php

namespace App\Controllers;

use App\Models\Persona;
use App\Services\ApiDecolectaService;
use App\Services\DocumentoLookupService;
use App\Validators\PersonaValidator;
use Src\Core\Controller;
use Src\Core\Request;

class PersonaController extends Controller
{
    private Persona $persona;
    private PersonaValidator $validator;

    public function __construct()
    {
        $this->persona = new Persona();
        $this->validator = new PersonaValidator($this->persona);
    }

    public function index(): void
    {
        $this->view('personas.index', ['personas' => $this->persona->getAll()], 'app');
    }

    public function create(): void
    {
        $this->view('personas.create', ['persona' => [], 'errors' => []], 'app');
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function show($id): void
    {
        $persona = $this->persona->getById((int) $id);
        if (!$persona) {
            $_SESSION['flash_error'] = 'La persona no existe.';
            $this->redirect('/personas');
        }
        $this->view('personas.show', [
            'persona' => $persona,
            'expedientes' => $this->persona->getExpedientes((int) $id)
        ], 'app');
    }

    public function edit($id): void
    {
        $persona = $this->persona->getById((int) $id);
        if (!$persona) {
            $_SESSION['flash_error'] = 'La persona no existe.';
            $this->redirect('/personas');
        }
        $this->view('personas.edit', ['persona' => $persona, 'errors' => []], 'app');
    }

    public function update($id): void
    {
        $this->save((int) $id);
    }

    public function buscar(): void
    {
        $request = new Request();
        $tipoDocumento = $request->get('tipo_documento', '');
        $numero = $request->get('numero_documento', '');
        $tipoDocumento = is_string($tipoDocumento) ? trim($tipoDocumento) : '';
        $numero = is_string($numero) ? trim($numero) : '';

        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC.'], 422);
        }


        $longitud = $tipoDocumento === 'DNI' ? 8 : 11;
        if (!preg_match('/^[0-9]{' . $longitud . '}$/D', $numero)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Ingrese un ' . $tipoDocumento . ' válido de ' . $longitud . ' dígitos.'], 422);
        }

        $persona = $this->persona->buscarPorDocumento($tipoDocumento, $numero);
        $encontrada = $persona !== null;
        $this->persona->registrarAuditoria(
            $_SESSION['usuario_id'] ?? null,
            'personas',
            'buscar',
            'personas',
            $encontrada ? (int) $persona['id_persona'] : null,
            'Búsqueda de persona por ' . $tipoDocumento . ': ' . ($encontrada ? 'encontrada.' : 'sin resultados.')
        );

        $this->json(['encontrado' => (bool) $persona, 'persona' => $persona]);
    }

    public function consultarDocumento(): void
    {
        $request = new Request();
        $tipoDocumento = $request->get('tipo_documento', '');
        $numero = $request->get('numero_documento', '');

        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC.'], 422);
        }

        $tipoDocumento = is_string($tipoDocumento) ? trim($tipoDocumento) : '';
        $numero = is_string($numero) ? trim($numero) : '';
        $resultado = (new DocumentoLookupService())->consultar($tipoDocumento, $numero);
        $estado = isset($resultado['persona']) ? 200 : 422;

        if (str_contains($resultado['mensaje'] ?? '', 'no está disponible') || str_contains($resultado['mensaje'] ?? '', 'no está configurada')) {
            $estado = 503;
        }

        $this->json($resultado, $estado);
    }
    public function consultarDecolectaApi(): void
    {
        $apiDeco = new ApiDecolectaService();
        $request = new Request();
        $tipoDocumento = $request->get('tipo_documento', '');
        $numero = $request->get('numero_documento', '');

        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC.'], 422);
        }

        $tipoDocumento = is_string($tipoDocumento) ? trim($tipoDocumento) : '';
        $numero = is_string($numero) ? trim($numero) : '';

        $resultado = [];

        if ($tipoDocumento == "DNI" && strlen($numero) == 8) {
            $resultado = $apiDeco->getByDNI($numero);
        }
        if ($tipoDocumento == "RUC" && strlen($numero) == 11) {
            $resultado = $apiDeco->getByRUC($numero);
        }

        $estado = 200;

        if (!$resultado["encontrado"]) {
            $estado = 503;
        }

        $this->json($resultado, $estado);
    }

    private function save(?int $id): void
    {
        $datos = (new Request())->all();
        $errores = $this->validator->validar($datos, $id);
        $persona = $id ? ($this->persona->getById($id) ?? []) : [];
        $vista = $id ? 'personas.edit' : 'personas.create';

        if ($errores) {
            $this->view($vista, ['persona' => array_merge($persona, $datos), 'errors' => $errores], 'app');
            return;
        }

        $normalizados = $this->normalizar($datos);
        $resultado = $id ? $this->persona->update($normalizados, $id) : $this->persona->create($normalizados);
        if ($resultado === false) {
            $this->view($vista, [
                'persona' => array_merge($persona, $normalizados),
                'errors' => ['db' => 'No se pudo guardar la persona. Verifique que no exista el documento.']
            ], 'app');
            return;
        }

        $idPersona = $id ?? (int) $resultado;
        $idUsuario = $_SESSION['usuario_id'] ?? null;
        $accion = $id ? 'actualizar' : 'crear';
        $descripcion = $id
            ? 'Se actualizó la persona remitente ' . $normalizados['numero_documento'] . '.'
            : 'Se registró la persona remitente ' . $normalizados['numero_documento'] . '.';

        $this->persona->registrarAuditoria(
            $idUsuario,
            'personas',
            $accion,
            'personas',
            $idPersona,
            $descripcion
        );

        $_SESSION['flash'] = $id ? 'Persona actualizada correctamente.' : 'Persona registrada correctamente.';
        $this->redirect('/personas');
    }

    private function normalizar(array $datos): array
    {
        return [
            'tipo_persona' => trim($datos['tipo_persona'] ?? 'Natural'),
            'tipo_documento' => trim($datos['tipo_documento'] ?? 'DNI'),
            'numero_documento' => trim($datos['numero_documento'] ?? ''),
            'nombres' => trim($datos['nombres'] ?? ''),
            'apellido_paterno' => trim($datos['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($datos['apellido_materno'] ?? ''),
            'razon_social' => trim($datos['razon_social'] ?? ''),
            'email' => trim($datos['email'] ?? ''),
            'telefono' => trim($datos['telefono'] ?? ''),
            'direccion' => trim($datos['direccion'] ?? ''),
            'estado' => trim($datos['estado'] ?? 'Activo')
        ];
    }
}
