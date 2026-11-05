<?php

namespace App\Support;

/**
 * Tratamiento de errores de la capa de aplicación (SEC-09).
 *
 * Antes de esta clase cualquier excepción se imprimía en el cuerpo de la
 * respuesta con HTTP 200, filtrando rutas absolutas y trazas al navegador.
 * No se interviene `Src/Core`: el registro se hace desde `index.php`.
 */
final class ManejadorErrores
{
    private static bool $registrado = false;

    /** Entorno de desarrollo: muestra el error, pero nunca al navegador. */
    public static function esDesarrollo(): bool
    {
        $entorno = strtolower((string) (env('APP_ENV') ?? 'produccion'));

        return in_array($entorno, ['local', 'development', 'dev'], true);
    }

    /**
     * Instala el manejo: errores al log, trazas fuera del HTML y página 500.
     */
    public static function registrar(): void
    {
        if (self::$registrado) {
            return;
        }
        self::$registrado = true;

        $desarrollo = self::esDesarrollo();

        // La traza nunca viaja en la respuesta: solo al archivo de log.
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        error_reporting($desarrollo ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $log = self::rutaLog();
        if ($log !== null) {
            ini_set('error_log', $log);
        }

        // Avisos y errores de PHP se convierten en excepciones para tratarlos igual.
        set_error_handler(static function (int $numero, string $texto, string $archivo = '', int $linea = 0): bool {
            if (!(error_reporting() & $numero)) {
                return false; // respeta el operador de supresión @
            }

            throw new \ErrorException($texto, 0, $numero, $archivo, $linea);
        });

        register_shutdown_function(static function (): void {
            $fatal = error_get_last();

            if ($fatal === null) {
                return;
            }

            if (!in_array($fatal['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            self::registrarEnLog($fatal['message'], $fatal['file'], $fatal['line']);
            self::responder500();
        });

        set_exception_handler(static function (\Throwable $e): void {
            self::registrarEnLog($e->getMessage(), $e->getFile(), $e->getLine(), $e);
            self::responder500();
        });
    }

    /**
     * Archivo de log dentro de `storage/` (creándolo si falta).
     */
    private static function rutaLog(): ?string
    {
        $directorio = dirname(__DIR__, 2) . '/storage';

        if (!is_dir($directorio) && !@mkdir($directorio, 0775, true)) {
            return null;
        }

        return $directorio . '/php-error.log';
    }

    private static function registrarEnLog(string $mensaje, string $archivo = '', int $linea = 0, ?\Throwable $e = null): void
    {
        $texto = sprintf(
            "[%s] %s%s | %s:%d | %s%s",
            date('Y-m-d H:i:s'),
            $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            ' ' . ($_SERVER['REQUEST_URI'] ?? '-'),
            $archivo !== '' ? $archivo : '-',
            $linea,
            $mensaje,
            $e !== null ? "\n" . $e->getTraceAsString() : ''
        );

        $log = self::rutaLog();

        if ($log !== null && @file_put_contents($log, $texto . PHP_EOL, FILE_APPEND | LOCK_EX) !== false) {
            return;
        }

        // Sin log en disco: al menos queda en el log del servidor (error_log ya apunta ahí).
        error_log($texto);
    }

    /**
     * Respuesta genérica 500 sin detalles internos.
     */
    private static function responder500(): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        $vista = dirname(__DIR__, 2) . '/view/pages/errors/500.php';

        if (is_file($vista)) {
            require $vista;
        } else {
            echo '<h1>Error interno del servidor</h1>';
        }
    }
}
