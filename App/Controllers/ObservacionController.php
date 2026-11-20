<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Derivacion;
use App\Models\Observacion;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class ObservacionController extends Controller
{
    use AuthorizesRequest;

    /** SEC-06: observar solo pertenece a perfiles de área. */
    private const RolesPermitidos = Roles::AtencionArea;

    private Observacion $observacionModel;
    private Derivacion $derivacionModel;

    public function __construct()
    {
        $this->observacionModel = new Observacion();
        $this->derivacionModel = new Derivacion();
    }

    /**
     * TAREA E05-1: Registrar la observación de un expediente recepcionado por el área.
     * El formulario llega desde el modal de la bandeja de entrada del área.
     */
    public function crear(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        foreach ($datos as $k => $v) {
            if (is_string($v)) {
                $datos[$k] = trim($v);
            }
        }

        // Pestaña de origen para devolver al usuario al mismo punto de la bandeja
        $tabsValidos = ['pendientes', 'en_proceso', 'atendidos_devueltos', 'todos'];
        $tab = in_array($datos['tab'] ?? '', $tabsValidos, true) ? $datos['tab'] : 'en_proceso';
        $volver = '/area/bandeja-entrada?tab=' . $tab;

        $idDerivacion = (int) ($datos['id_derivacion'] ?? 0);

        // SEC-01: CSRF obligatorio y sin ramas fail-open.
        $this->requerirCsrf($datos, $volver);

        $contexto = $this->contextoObservacion($idDerivacion);
        if (!$contexto['ok']) {
            Flash::error($contexto['message']);
            $this->redirect($volver);
            return;
        }

        $errors = $this->validarObservacion($datos);
        if (!empty($errors)) {
            Flash::error(implode(' ', $errors));
            $this->redirect($volver);
            return;
        }

        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

        $resultado = $this->observacionModel->observar(
            $idDerivacion,
            $idUsuario,
            $datos['descripcion']
        );

        if ($resultado['success']) {
            // E13-4: encolar aviso por correo (best-effort).
            try {
                $derivacion = $contexto['derivacion'];
                $idExp = (int) ($derivacion['id_expediente'] ?? 0);
                if ($idExp > 0) {
                    $db = \Src\Core\Conexion::getConexion();
                    $stmt = $db->prepare(
                        "SELECT e.codigo_expediente, e.asunto, p.email
                         FROM expedientes e
                         INNER JOIN personas p ON p.id_persona = e.id_remitente
                         WHERE e.id_expediente = :id LIMIT 1"
                    );
                    $stmt->execute([':id' => $idExp]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($row && !empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                        $urlConsulta = \App\Services\PdfCargoService::urlConsulta($row['codigo_expediente']);
                        $qrBase64 = '';
                        try {
                            $barcode = new \TCPDF2DBarcode($urlConsulta, 'QRCODE,M');
                            $png = $barcode->getBarcodePngData(4, 4, [0, 0, 0]);
                            if ($png) { $qrBase64 = base64_encode($png); }
                        } catch (\Throwable $e) { error_log('E13 qr obs: ' . $e->getMessage()); }
                        $html = '<p>Su expediente ' . htmlspecialchars($row['codigo_expediente']) . ' ha sido observado.</p>'
                            . '<p>Motivo: ' . htmlspecialchars($datos['descripcion']) . '</p>'
                            . '<p>Subsane en: <a href="' . htmlspecialchars($urlConsulta) . '">' . htmlspecialchars($urlConsulta) . '</a></p>';
                        if ($qrBase64 !== '') {
                            $html .= '<div style="text-align:center;margin-top:12px;"><img src="data:image/png;base64,' . $qrBase64 . '" alt="QR" width="120" height="120"></div>';
                        }
                        (new \App\Models\CorreoCola())->encolar([
                            'destinatario' => $row['email'],
                            'cc' => null,
                            'asunto' => 'Expediente observado - ' . $row['codigo_expediente'],
                            'cuerpo_html' => $html,
                            'id_expediente' => $idExp,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                error_log('E13 observacion encolar: ' . $e->getMessage());
            }
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/area/bandeja-entrada?tab=en_proceso');
    }

    /**
     * Validación común de acceso: derivación existente, del área del usuario (o admin)
     * y en estado observable.
     */
    private function contextoObservacion(int $idDerivacion): array
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
            return ['ok' => false, 'message' => 'No tiene permisos para observar trámites de otra área.'];
        }

        if (!in_array($derivacion['estado'], ['Recibido', 'En_Atencion'], true)) {
            return ['ok' => false, 'message' => 'Solo se pueden observar expedientes ya recepcionados por el área (estado Recibido o En Atención).'];
        }

        return ['ok' => true, 'derivacion' => $derivacion];
    }

    /**
     * Los motivos o requisitos faltantes son obligatorios y acotados
     * (la columna descripcion admite hasta 1000 caracteres).
     */
    private function validarObservacion(array $datos): array
    {
        $errors = [];

        $descripcion = (string) ($datos['descripcion'] ?? '');
        $longitud = mb_strlen($descripcion);

        if ($descripcion === '') {
            $errors[] = 'Detalle los motivos o requisitos faltantes de la observación.';
        } elseif ($longitud < 10) {
            $errors[] = 'La descripción de la observación debe tener al menos 10 caracteres.';
        } elseif ($longitud > 1000) {
            $errors[] = 'La descripción de la observación no debe exceder 1000 caracteres.';
        }

        return $errors;
    }
}
