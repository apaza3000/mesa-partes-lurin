<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Auditoria;
use App\Models\Persona;
use App\Services\ApiDecolectaService;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use App\Validators\PersonaValidator;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * Padrón de personas (remitentes).
 *
 * SEC-03: antes ninguna acción exigía sesión, por lo que el padrón completo era
 * público. Ahora toda ruta exige autenticación y las de escritura exigen rol de
 * ventanilla o administración.
 */
class PersonaController extends Controller
{
    use AuthorizesRequest;

    /** Consulta del padrón: cualquier rol autenticado. */
    private const RolesLectura = Roles::Consulta;

    /** Alta, edición y consulta a fuentes externas: ventanilla o administración. */
    private const RolesEscritura = Roles::MesaDePartes;

    private Persona $persona;
    private PersonaValidator $validator;

    public function __construct()
    {
        $this->persona = new Persona();
        $this->validator = new PersonaValidator($this->persona);
    }

    public function index(): void
    {
        $this->requerirRol(self::RolesLectura);

        $this->view('personas.index', ['personas' => $this->persona->getAll()], 'app');
    }

    public function create(): void
    {
        $this->requerirRol(self::RolesEscritura);

        $this->view('personas.create', [
            'persona' => [],
            'errors' => [],
            'tiposDocumento' => Catalogos::TipoDocumento,
            'tiposPersona' => Catalogos::TipoPersona,
            'estados' => Catalogos::EstadoSimple,
        ], 'app');
    }

    public function store(): void
    {
        $this->requerirRol(self::RolesEscritura);
        $this->requerirCsrf((new Request())->all(), '/personas/nuevo');

        $this->save(null);
    }

    public function show($id): void
    {
        $this->requerirRol(self::RolesLectura);

        $persona = $this->persona->getById((int) $id);
        if (!$persona) {
            Flash::error('La persona no existe.');
            $this->redirect('/personas');
        }
        $this->view('personas.show', [
            'persona' => $persona,
            'expedientes' => $this->persona->getExpedientes((int) $id)
        ], 'app');
    }

    public function edit($id): void
    {
        $this->requerirRol(self::RolesEscritura);

        $persona = $this->persona->getById((int) $id);
        if (!$persona) {
            Flash::error('La persona no existe.');
            $this->redirect('/personas');
        }
        $this->view('personas.edit', [
            'persona' => $persona,
            'errors' => [],
            'tiposDocumento' => Catalogos::TipoDocumento,
            'tiposPersona' => Catalogos::TipoPersona,
            'estados' => Catalogos::EstadoSimple,
        ], 'app');
    }

    public function update($id): void
    {
        $this->requerirRol(self::RolesEscritura);
        $this->requerirCsrf((new Request())->all(), '/personas/' . (int) $id . '/editar');

        $this->save((int) $id);
    }

    public function buscar(): void
    {
        $this->requerirRol(self::RolesLectura);

        $request = new Request();
        $tipoDocumento = $this->tipoDocumentoValido($request->get('tipo_documento', ''));
        $numero = $this->numeroValido($request->get('numero_documento', ''), $tipoDocumento);

        $persona = $this->persona->buscarPorDocumento($tipoDocumento, $numero);
        $encontrada = $persona !== null;

        $this->auditar(
            'buscar',
            $encontrada ? (int) $persona['id_persona'] : null,
            'Búsqueda de persona por ' . $tipoDocumento . ': ' . ($encontrada ? 'encontrada.' : 'sin resultados.')
        );

        $this->json(['encontrado' => (bool) $persona, 'persona' => $persona]);
    }

    /**
     * OPT-04: punto único de consulta externa del padrón. Antes existía un
     * segundo método `consultarDocumento()` sin ruta que duplicaba la lógica;
     * eliminado. El contrato de respuesta es {encontrado, data|mensaje}.
     *
     * Proxy hacia la API externa Decolecta. Se reserva a ventanilla porque consume
     * cuota externa y devuelve datos personales (SEC-03).
     */
    public function consultarDecolectaApi(): void
    {
        $this->requerirRol(self::RolesEscritura);

        $request = new Request();
        $tipoDocumento = $this->tipoDocumentoValido($request->get('tipo_documento', ''));

        // La API externa solo resuelve DNI y RUC.
        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC.'], 422);
        }

        $numero = $this->numeroValido($request->get('numero_documento', ''), $tipoDocumento);

        $apiDeco = new ApiDecolectaService();

        // `numeroValido()` ya garantiza la longitud, así que siempre hay llamada real.
        $resultado = $tipoDocumento === 'DNI'
            ? $apiDeco->getByDNI($numero)
            : $apiDeco->getByRUC($numero);

        // Defensa por si el servicio devuelve una estructura incompleta (antes
        // provocaba un warning que rompía el JSON).
        if (!is_array($resultado) || !array_key_exists('encontrado', $resultado)) {
            $resultado = ['encontrado' => false, 'mensaje' => 'Respuesta inválida del servicio.', 'persona' => null];
        }

        $estado = 200;

        if (!($resultado['encontrado'] ?? false)) {
            $estado = 503;
        }

        $this->json($resultado, $estado);
    }

    private function save(?int $id): void
    {
        $datos = $this->sanearEntrada((new Request())->all());
        $errores = $this->validator->validar($datos, $id);
        $persona = $id ? ($this->persona->getById($id) ?? []) : [];
        $vista = $id ? 'personas.edit' : 'personas.create';

        if ($errores) {
            // Checklist §8-9: la revalidación responde 422 con mensaje de campo legible.
            http_response_code(422);
            $this->view($vista, [
                'persona' => array_merge($persona, $datos),
                'errors' => $errores,
                'tiposDocumento' => Catalogos::TipoDocumento,
                'tiposPersona' => Catalogos::TipoPersona,
                'estados' => Catalogos::EstadoSimple,
            ], 'app');
            return;
        }

        $normalizados = $this->normalizar($datos);
        $resultado = $id ? $this->persona->update($normalizados, $id) : $this->persona->create($normalizados);
        if ($resultado === false) {
            http_response_code(422);
            $this->view($vista, [
                'persona' => array_merge($persona, $normalizados),
                'errors' => ['db' => 'No se pudo guardar la persona. Verifique que no exista el documento.'],
                'tiposDocumento' => Catalogos::TipoDocumento,
                'tiposPersona' => Catalogos::TipoPersona,
                'estados' => Catalogos::EstadoSimple,
            ], 'app');
            return;
        }

        $idPersona = $id ?? (int) $resultado;
        $this->auditar(
            $id ? 'actualizar' : 'crear',
            $idPersona,
            ($id ? 'Se actualizó' : 'Se registró') . ' la persona remitente ' . $normalizados['numero_documento'] . '.'
        );

        Flash::exito($id ? 'Persona actualizada correctamente.' : 'Persona registrada correctamente.');
        $this->redirect('/personas');
    }

    /**
     * VAL-05: valida y normaliza el tipo de documento contra el catálogo real.
     * Responde 422 y detiene la ejecución si no es uno de los admitidos.
     */
    private function tipoDocumentoValido(mixed $valor): string
    {
        $tipo = is_string($valor) ? mb_strtoupper(trim($valor)) : '';

        if (!Catalogos::es(Catalogos::TipoDocumento, $tipo)) {
            $this->json(['encontrado' => false, 'mensaje' => 'Seleccione un tipo de documento válido.'], 422);
        }

        return $tipo;
    }

    /** Solo dígitos para DNI/RUC y alfanumérico acotado para el resto (VAL-03). */
    private function numeroValido(mixed $valor, string $tipo): string
    {
        $numero = is_string($valor) ? trim($valor) : '';

        if ($tipo === 'DNI' || $tipo === 'RUC') {
            $longitud = $tipo === 'DNI' ? 8 : 11;

            if (!preg_match('/^[0-9]{' . $longitud . '}$/D', $numero)) {
                $this->json([
                    'encontrado' => false,
                    'mensaje' => 'Ingrese un ' . $tipo . ' válido de ' . $longitud . ' dígitos.',
                ], 422);
            }

            return $numero;
        }

        if (!preg_match('/^[A-Za-z0-9-]{4,20}$/D', $numero)) {
            $this->json([
                'encontrado' => false,
                'mensaje' => 'Número de documento no válido (entre 4 y 20 caracteres alfanuméricos).',
            ], 422);
        }

        return mb_strtoupper($numero);
    }

    /** SEC-07 / VAL-02: sin HTML y sin caracteres de 4 bytes antes de tocar la BD. */
    private function sanearEntrada(array $datos): array
    {
        $campos = [
            'nombres' => 'personas.nombres',
            'apellido_paterno' => 'personas.apellido_paterno',
            'apellido_materno' => 'personas.apellido_materno',
            'razon_social' => 'personas.razon_social',
            'email' => 'personas.email',
            'telefono' => 'personas.telefono',
            'direccion' => 'personas.direccion',
            'numero_documento' => 'personas.numero_documento',
        ];

        foreach ($campos as $campo => $columna) {
            if (array_key_exists($campo, $datos)) {
                // checklist §9: se limpia pero NO se recorta; el validador debe
                // rechazar el desborde con mensaje de campo legible.
                $datos[$campo] = Catalogos::limpiar($datos[$campo]);
            }
        }

        return $datos;
    }

    /** OPT-03: auditoría única vía App\Models\Auditoria (sin variantes silenciosas). */
    private function auditar(string $accion, ?int $idRegistro, string $descripcion): void
    {
        try {
            $usuario = Session::user() ?? [];
            (new Auditoria())->insertar(
                $usuario['id_usuario'] ?? Session::get('usuario_id'),
                'personas',
                $accion,
                'personas',
                $idRegistro,
                $descripcion,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de personas falló: ' . $e->getMessage());
        }
    }

    private function normalizar(array $datos): array
    {
        return [
            'tipo_persona' => trim($datos['tipo_persona'] ?? 'Natural'),
            'tipo_documento' => mb_strtoupper(trim($datos['tipo_documento'] ?? 'DNI')),
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
