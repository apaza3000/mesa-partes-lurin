<?php

namespace App\Controllers;

use App\Models\Persona;
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
        $numero = trim((new Request())->get('numero_documento', ''));
        if ($numero === '') {
            $this->json(['encontrado' => false, 'mensaje' => 'Ingrese un DNI o RUC.'], 422);
        }
        $persona = $this->persona->buscarPorDocumento($numero);
        $this->json(['encontrado' => (bool) $persona, 'persona' => $persona]);
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
        $resultado = $id ? $this->persona->update($id, $normalizados) : $this->persona->create($normalizados);
        if ($resultado === false) {
            $this->view($vista, [
                'persona' => array_merge($persona, $normalizados),
                'errors' => ['db' => 'No se pudo guardar la persona. Verifique que no exista el documento.']
            ], 'app');
            return;
        }
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