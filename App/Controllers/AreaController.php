<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Area;
use App\Models\Auditoria;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;

class AreaController extends Controller
{
    use AuthorizesRequest;

    /** SEC-02: el alta y baja de áreas es exclusiva de ADMINISTRADOR. */
    private const RolesPermitidos = Roles::Administracion;

    private Area  $area;
    public function __construct()
    {
        $this->area = new Area();
    }

    public function index()
    {
        $this->requerirRol(self::RolesPermitidos);
        $areas = $this->area->getAll();
        return $this->view("areas.index", ['areas' => $areas], "app");
    }

    public function edit($id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $area = $this->area->getById($id);
        $areas = $this->area->getAll();
        return $this->view("areas.editar", ["area" => $area, "areasPadre" => $areas], "app");
    }

    public function create()
    {
        $this->requerirRol(self::RolesPermitidos);

        $areas = $this->area->getAll();
        return $this->view("areas.crear", ["areasPadre" => $areas], "app");
    }

    public function store()
    {

        $this->requerirRol(self::RolesPermitidos);

        $req  = new Request();
        $data = $this->sanearEntrada($req->all());
        $this->requerirCsrf($data, '/areas/nuevo');

        $rule = [
            'nombre'        => ['required', 'string', 'maxLength' => Catalogos::limite('areas.nombre')],
            'siglas'        => ['required', 'string', 'maxLength' => Catalogos::limite('areas.siglas')],
            'estado'        => ['required', 'string', 'in' => Catalogos::EstadoSimple],
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
        Flash::exito('Área creada correctamente.');
        $this->auditar('crear', $this->area->ultimoId(), 'Creó el área "' . $datos['nombre'] . '" (' . $datos['siglas'] . ').');
        $this->redirect('/areas');
    }

    public function update($id)
    {


        $this->requerirRol(self::RolesPermitidos);
        $id = (int) $id;

        // 1. Verificar que exista
        $areaActual = $this->area->getById($id);

        if (!$areaActual) {
            Flash::error('El área no existe.');
            return $this->redirect('/areas');
        }

        // 2. Obtener datos del request
        $req  = new Request();
        $data = $this->sanearEntrada($req->all());
        $this->requerirCsrf($data, '/areas/' . $id . '/editar');

        // 3. Reglas (igual que store, quizá sin 'id_area_padre' por el tema de ciclos)
        $rule = [
            'nombre'        => ['required', 'string', 'maxLength' => Catalogos::limite('areas.nombre')],
            'siglas'        => ['required', 'string', 'maxLength' => Catalogos::limite('areas.siglas')],
            'estado'        => ['required', 'string', 'in' => Catalogos::EstadoSimple],
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
        Flash::exito('Área actualizada correctamente.');
        $this->auditar('editar', $id, 'Actualizó el área "' . $datos['nombre'] . '".');
        return $this->redirect('/areas');
    }

    public function delete($id)
    {

        $this->requerirRol(self::RolesPermitidos);

        $id = (int) $id;
        $this->requerirCsrf((new Request())->all(), '/areas');

        // 1. Verificar que exista
        $areaActual = $this->area->getById($id);

        if (!$areaActual) {
            Flash::error('El área no existe.');
            return $this->redirect('/areas');
        }

        // 2. Intentar eliminar
        $ok = $this->area->delete($id);

        if (!$ok) {
            Flash::error('No se pudo eliminar el área. Quizá tenga dependencias.');
        } else {
            Flash::exito('Área eliminada correctamente.');
            $this->auditar('eliminar', $id, 'Eliminó el área "' . $areaActual['nombre'] . '".');
        }

        return $this->redirect('/areas');
    }

    /**
     * SEC-07: nombres de área se muestran en menús y bandej as, se sanea la entrada.
     */
    private function sanearEntrada(array $data): array
    {
        if (isset($data['nombre'])) {
            $data['nombre'] = Catalogos::sanear($data['nombre'], 'areas.nombre');
        }
        if (isset($data['siglas'])) {
            $data['siglas'] = mb_strtoupper(Catalogos::sanear($data['siglas'], 'areas.siglas'));
        }

        return $data;
    }

    private function auditar(string $accion, ?int $idRegistro, string $descripcion): void
    {
        try {
            $usuario = Session::user() ?? [];
            (new Auditoria())->insertar(
                $usuario['id_usuario'] ?? null,
                'areas',
                $accion,
                'areas',
                $idRegistro,
                $descripcion,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de áreas falló: ' . $e->getMessage());
        }
    }
}
