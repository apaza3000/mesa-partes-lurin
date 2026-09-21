<?php

namespace App\Controllers;

use App\Models\Rol;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Validator;

class RolController extends Controller
{
    private Rol $rol;

    public function __construct()
    {
        $this->rol = new Rol();
    }

    public function index(): void
    {
        $this->view('roles.index', ['roles' => $this->rol->getAll()], 'plantilla');
    }

    public function create(): void
    {
        $this->view('roles.crear', [], 'plantilla');
    }

    public function edit(int|string $id): void
    {
        $this->view('roles.editar', ['rol' => $this->rol->getById($id)], 'plantilla');
    }

    public function store(): void
    {
        $data = (new Request())->all();
        $validator = new Validator();

        if (!$this->validate($validator, $data)) {
            $this->view('roles.crear', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
            ], 'plantilla');
            return;
        }

        if (!$this->rol->create($this->normalize($data))) {
            $this->view('roles.crear', [
                'errors' => ['db' => 'No se pudo guardar el rol.'],
                'datos_viejos' => $data,
            ], 'plantilla');
            return;
        }

        $_SESSION['rol_mensaje'] = 'Rol creado correctamente.';
        $this->redirect('/roles');
    }

    public function update(int|string $id): void
    {
        $id = (int) $id;
        $rolActual = $this->rol->getById($id);

        if (!$rolActual) {
            $_SESSION['rol_error'] = 'El rol no existe.';
            $this->redirect('/roles');
        }

        $data = (new Request())->all();
        $validator = new Validator();

        if (!$this->validate($validator, $data)) {
            $this->view('roles.editar', [
                'rol' => array_merge($rolActual, $data),
                'errors' => $validator->getErrors(),
            ], 'plantilla');
            return;
        }

        if (!$this->rol->update($this->normalize($data), $id)) {
            $this->view('roles.editar', [
                'rol' => array_merge($rolActual, $data),
                'errors' => ['db' => 'No se pudo actualizar el rol.'],
            ], 'plantilla');
            return;
        }

        $_SESSION['rol_mensaje'] = 'Rol actualizado correctamente.';
        $this->redirect('/roles');
    }

    public function delete(int|string $id): void
    {
        if (!$this->rol->getById($id)) {
            $_SESSION['rol_error'] = 'El rol no existe.';
        } elseif ($this->rol->delete($id)) {
            $_SESSION['rol_mensaje'] = 'Rol eliminado correctamente.';
        } else {
            $_SESSION['rol_error'] = 'No se pudo eliminar el rol. Puede tener usuarios asociados.';
        }

        $this->redirect('/roles');
    }

    private function validate(Validator $validator, array $data): bool
    {
        return $validator->validate([
            'nombre' => ['required', 'string', 'max' => 50],
            'descripcion' => ['nullable', 'string', 'max' => 255],
            'estado' => ['required', 'string', 'in' => ['Activo', 'Inactivo']],
        ], $data);
    }

    private function normalize(array $data): array
    {
        return [
            'nombre' => trim($data['nombre']),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'estado' => $data['estado'],
        ];
    }
}
