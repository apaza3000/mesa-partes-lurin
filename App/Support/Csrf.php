<?php

namespace App\Support;

use Src\Core\Session;

/**
 * Token CSRF de la capa de aplicación.
 *
 * Reemplaza las tres copias de `validarCsrf()` que tenían ramas `fail-open`
 * (SEC-01 / OPT-05): si no hay token en sesión, la validación FALLA.
 */
final class Csrf
{
    private const ClaveSesion = 'csrf_token';

    /**
     * Devuelve el token de la sesión; lo genera la primera vez.
     */
    public static function token(): string
    {
        $token = Session::get(self::ClaveSesion);

        if (!is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::ClaveSesion, $token);
        }

        return $token;
    }

    /**
     * ¿El token recibido coincide con el de la sesión?
     */
    public static function check(mixed $sent): bool
    {
        $token = Session::get(self::ClaveSesion);

        if (!is_string($token) || $token === '') {
            return false;
        }

        if (!is_string($sent) || $sent === '') {
            return false;
        }

        return hash_equals($token, $sent);
    }

    /**
     * Valida el token vindo del cuerpo de la petición o del header X-CSRF-Token.
     */
    public static function checkRequest(array $datos): bool
    {
        $sent = $datos['csrf_token'] ?? self::header();

        return self::check($sent);
    }

    /**
     * Input oculto listo para insertar en un <form method="post">.
     */
    public static function campo(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Header X-CSRF-Token enviado por la aplicación (AJAX).
     */
    public static function header(): ?string
    {
        $cabecera = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if ($cabecera === null) {
            return null;
        }

        return trim((string) $cabecera);
    }

    /**
     * Rota el token (p. ej. tras un login) y devuelve el nuevo.
     */
    public static function rotar(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::ClaveSesion, $token);

        return $token;
    }

    /**
     * Elimina el token de la sesión (logout).
     */
    public static function borrar(): void
    {
        Session::remove(self::ClaveSesion);
    }
}
