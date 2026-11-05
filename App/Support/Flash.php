<?php

namespace App\Support;

use Src\Core\Session;

/**
 * Mensajes flash de una sola lectura (OPT-07 / MEN-05).
 *
 * Unifica las cinco convenciones que convivían (`rol_mensaje`, `rol_error`,
 * `flash`, `flash_error`, `usuario_mensaje`, `error`, `mensaje`) en una única
 * clave de sesión y un único partial de vista.
 */
final class Flash
{
    private const ClaveSesion = 'flash_mensajes';

    public const Exito = 'success';
    public const Error = 'danger';
    public const Aviso = 'warning';
    public const Info = 'info';

    /** Claves históricas que `pull()` todavía cosecha para no perder mensajes. */
    private const Legado = [
        'rol_mensaje' => self::Exito,
        'flash' => self::Exito,
        'usuario_mensaje' => self::Exito,
        'mensaje' => self::Exito,
        'flash_success' => self::Exito,
        'rol_error' => self::Error,
        'flash_error' => self::Error,
        'error' => self::Error,
    ];

    public static function push(string $tipo, string $mensaje): void
    {
        $mensaje = trim($mensaje);
        if ($mensaje === '') {
            return;
        }

        $lista = Session::get(self::ClaveSesion, []);
        if (!is_array($lista)) {
            $lista = [];
        }

        $lista[] = [
            'tipo' => in_array($tipo, [self::Exito, self::Error, self::Aviso, self::Info], true) ? $tipo : self::Info,
            'mensaje' => $mensaje,
        ];

        Session::set(self::ClaveSesion, $lista);
    }

    public static function exito(string $mensaje): void
    {
        self::push(self::Exito, $mensaje);
    }

    public static function error(string $mensaje): void
    {
        self::push(self::Error, $mensaje);
    }

    public static function aviso(string $mensaje): void
    {
        self::push(self::Aviso, $mensaje);
    }

    /**
     * Devuelve todos los mensajes pendientes (nuevos y de convenciones antiguas)
     * y limpia la sesión.
     *
     * @return array<int, array{tipo: string, mensaje: string}>
     */
    public static function pull(): array
    {
        $lista = Session::get(self::ClaveSesion, []);
        if (!is_array($lista)) {
            $lista = [];
        }

        foreach (self::Legado as $clave => $tipo) {
            if (isset($_SESSION[$clave]) && is_string($_SESSION[$clave]) && trim($_SESSION[$clave]) !== '') {
                $lista[] = ['tipo' => $tipo, 'mensaje' => trim($_SESSION[$clave])];
            }
            unset($_SESSION[$clave]);
        }

        Session::remove(self::ClaveSesion);

        return $lista;
    }
}
