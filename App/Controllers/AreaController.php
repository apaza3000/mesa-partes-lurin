<?php

namespace App\Controllers;

use App\Models\Area;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Validator;

class AreaController extends Controller
{
    private Area  $area;
    public function __construct()
    {
        $this->area = new Area();
    }

    public function index()
    {
        $areas = $this->area->getAll();
        return $this->view("areas.index", ['areas' => $areas], "app");
    }

    public function edit($id)
    {

        $area = $this->area->getById($id);
        $areas = $this->area->getAll();
        return $this->view("areas.editar", ["area" => $area, "areasPadre" => $areas], "app");
    }

    public function create()
    {
        $areas = $this->area->getAll();
        return $this->view("areas.crear", ["areasPadre" => $areas], "app");
    }

    public function store()
    {
        $req  = new Request();
        $data = $req->all();

        $rule = [
            'nombre'        => ['required', 'string', 'maxLength' => 100],
            'siglas'        => ['required', 'string', 'maxLength' => 20],
            'estado'        => ['required', 'string', 'in' => ['Activo', 'Inactivo']],
            'id_area_padre' => ['nullable', 'number'], // opcional
        ];

        // 3. Validar
        $validator = new Validator();

        if (!$validator->validate($rule, $data)) {
            // Necesitamos las áreas padre para repoblar el <select>
            $areasPadre = $this->area->getAll();

            return $this->view(
                "areas.crear",
                [
                    "areasPadre"   => $areasPadre,
                    "errors"       => $validator->getErrors(),
                    "datos_viejos" => $data,
                ],
                "app"
            );
        }

        // 4. Normalizar datos (usando $data, no $_POST)
        $datos = [
            'nombre'        => trim($data['nombre']),
            'siglas'        => strtoupper(trim($data['siglas'])),
            'estado'        => $data['estado'] ?? 'Activo',
            'id_area_padre' => !empty($data['id_area_padre'])
                ? (int) $data['id_area_padre']
                : null,
        ];

        // 5. Guardar en BD
        $ok = $this->area->create($datos);

        if (!$ok) {
            $areasPadre = $this->area->getAll();

            return $this->view(
                "areas.crear",
                [
                    "areasPadre"   => $areasPadre,
                    "errors"       => ['db' => 'No se pudo guardar el área. Intenta de nuevo.'],
                    "datos_viejos" => $data,
                ],
                "app"
            );
        }

        // 6. Redirigir al listado con flash
        $_SESSION['flash'] = 'Área creada correctamente.';
        $this->redirect('/areas');
    }

    public function update($id)
    {
        $id = (int) $id;

        // 1. Verificar que exista
        $areaActual = $this->area->getById($id);

        if (!$areaActual) {
            $_SESSION['flash_error'] = 'El área no existe.';
            return $this->redirect('/areas');
        }

        // 2. Obtener datos del request
        $req  = new Request();
        $data = $req->all();

        // 3. Reglas (igual que store, quizá sin 'id_area_padre' por el tema de ciclos)
        $rule = [
            'nombre'        => ['required', 'string', 'maxLength' => 100],
            'siglas'        => ['required', 'string', 'maxLength' => 20],
            'estado'        => ['required', 'string', 'in' => ['Activo', 'Inactivo']],
            'id_area_padre' => ['nullable', 'number'],
        ];

        // 4. Validar
        $validator = new Validator();

        if (!$validator->validate($rule, $data)) {
            return $this->view(
                "areas.editar",
                [
                    "area"       => array_merge($areaActual, $data), // mantiene lo escrito
                    "areasPadre" => $this->area->getAll(),
                    "errors"     => $validator->getErrors(),
                    "datos_viejos" => $data,
                ],
                "app"
            );
        }

        // 5. Normalizar
        $datos = [
            'nombre'        => trim($data['nombre']),
            'siglas'        => strtoupper(trim($data['siglas'])),
            'estado'        => $data['estado'] ?? 'Activo',
            'id_area_padre' => !empty($data['id_area_padre'])
                ? (int) $data['id_area_padre']
                : null,
        ];

        // 6. Evitar que sea su propio padre
        if ($datos['id_area_padre'] === $id) {
            $datos['id_area_padre'] = null;
        }

        // 7. Guardar
        $ok = $this->area->update($datos, $id);

        if (!$ok) {
            return $this->view(
                "areas.editar",
                [
                    "area"       => array_merge($areaActual, $datos),
                    "areasPadre" => $this->area->getAll(),
                    "errors"     => ['db' => 'No se pudo actualizar el área. Intenta de nuevo.'],
                    "datos_viejos" => $datos,
                ],
                "app"
            );
        }

        // 8. Redirigir con flash
        $_SESSION['flash'] = 'Área actualizada correctamente.';
        return $this->redirect('/areas');
    }

    public function delete($id)
    {
        $id = (int) $id;

        // 1. Verificar que exista
        $areaActual = $this->area->getById($id);

        if (!$areaActual) {
            $_SESSION['flash_error'] = 'El área no existe.';
            return $this->redirect('/areas');
        }

        // 2. Intentar eliminar
        $ok = $this->area->delete($id);

        if (!$ok) {
            $_SESSION['flash_error'] = 'No se pudo eliminar el área. Quizá tenga dependencias.';
        } else {
            $_SESSION['flash'] = 'Área eliminada correctamente.';
        }

        return $this->redirect('/areas');
    }
}
