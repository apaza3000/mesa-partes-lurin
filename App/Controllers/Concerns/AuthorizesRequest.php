<?php

namespace App\Controllers\Concerns;

use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Session;

/**
 * Control de acceso reutilizable en la capa de aplicación (OPT-06).
 *
 * Promueve a patrón común lo que solo `MesaPartesController` hacía bien:
 * autenticación obligatoria, roles por acción, CSRF y respuesta 403/401 en AJAX.
 * No interviene `Src\Core`: cada controlador sigue llamando explícitamente a sus
 * guardas al inicio de los métodos.
 */
trait AuthorizesRequest
{
    /**
     * ¿La petición pide JSON (fetch/AJAX)? Determina si se responde 401/403 o
     * se redirige a una vista.
     */
    protected function esPeticionAjax(): bool
    {
        $esAjax = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        $acepta = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $pideJson = str_contains($acepta, 'application/json')
            || str_contains($acepta, 'text/json')
            || strtolower(pathinfo((string) ($_SERVER['SCRIPT_NAME'] ?? ''), PATHINFO_EXTENSION)) === 'json';

        return $esAjax || $pideJson;
    }

    /**
     * Obliga a tener sesión iniciada. Detiene la ejecución si no la hay.
     */
    protected function requerirAutenticacion(): void
    {
        if (Session::isAuthenticated()) {
            return;
        }

        Flash::aviso('Debe iniciar sesión para acceder a esta sección.');

        if ($this->esPeticionAjax()) {
            $this->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        $this->redirect('/login');
    }

    /**
     * Obliga a sesión + al menos uno de los roles permitidos.
     *
     * @param string[] $permitidos
     */
    protected function requerirRol(array $permitidos, string $destino = '/dashboard'): void
    {
        $this->requerirAutenticacion();

        if (Roles::usuarioTiene($permitidos)) {
            return;
        }

        $this->sinAcceso(
            'Su usuario no está autorizado para operar en este módulo.',
            $destino
        );
    }

    /**
     * Respuesta estándar de falta de permiso: 403 en AJAX, flash + redirect si no.
     */
    protected function sinAcceso(string $mensaje = 'Acceso denegado.', string $destino = '/dashboard'): void
    {
        if ($this->esPeticionAjax()) {
            $this->json(['success' => false, 'message' => $mensaje], 403);
        }

        // Checklist §8-3: la denegación de rol responde 403 con página propia.
        $this->responderCon(403, $mensaje, $destino);
    }

    /**
     * Obliga a un token CSRF válido y rechaza el request cuando no acompaña o no coincide.
     */
    protected function requerirCsrf(array $datos, ?string $destino = null): void
    {
        if (Csrf::checkRequest($datos)) {
            return;
        }

        $mensaje = 'La sesión del formulario expiró. Vuelva a intentarlo.';

        if ($this->esPeticionAjax()) {
            $this->json(['success' => false, 'message' => $mensaje], 419);
        }

        // Checklist §8-5: todo POST sin token válido se rechaza con 419
        // (equivalente Laravel), con página de aviso y enlace de retorno.
        $this->responderCon(419, $mensaje, $destino ?? $this->origen() ?? '/login');
    }

    /**
     * Responde con un código 4xx propio y una página mínima de aviso (el
     * `redirect()` del core fija siempre 302 y `Src/Core` no se modifica).
     * El mensaje queda además en flash para la siguiente navegación.
     */
    private function responderCon(int $status, string $mensaje, string $retorno): never
    {
        Flash::error($mensaje);
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');

        $seguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        $enlace = htmlspecialchars($retorno, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>' . $status
            . '</title></head><body><h1>' . $status . '</h1><p>' . $seguro
            . '</p><p><a href="' . $enlace . '">Volver</a></p></body></html>';
        exit;
    }

    /**
     * URL de procedencia (para devolver al usuario tras un error de CSRF).
     */
    protected function origen(): ?string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (!is_string($referer) || $referer === '') {
            return null;
        }

        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $ruta = parse_url($referer, PHP_URL_PATH);

        // Se acepta solo el referer interno: misma ruta relativa.
        if ($host !== '' && !str_starts_with($referer, 'http://' . $host) && !str_starts_with($referer, 'https://' . $host)) {
            return null;
        }

        $query = parse_url($referer, PHP_URL_QUERY);

        return is_string($ruta) && $ruta !== ''
            ? $ruta . (is_string($query) && $query !== '' ? '?' . $query : '')
            : null;
    }

    /**
     * Primer mensaje de un tipo concreto dentro de la cola de flash (OPT-07).
     * Permite a las vistas que aún leen `flash_success`/`flash_error` seguir
     * funcionando mientras se migra al partial único.
     *
     * @param array<int, array{tipo: string, mensaje: string}> $mensajes
     */
    protected function primerMensaje(array $mensajes, string $tipo): string
    {
        foreach ($mensajes as $m) {
            if ($m['tipo'] === $tipo) {
                return $m['mensaje'];
            }
        }

        return '';
    }

    /**
     * Roles del usuario autenticado, normalizados.
     *
     * @return string[]
     */
    protected function rolesActuales(): array
    {
        return Roles::delUsuarioActual();
    }

    /**
     * ¿El usuario autenticado tiene alguno de los roles?
     */
    protected function tieneRol(array $permitidos): bool
    {
        return Roles::usuarioTiene($permitidos);
    }
}
