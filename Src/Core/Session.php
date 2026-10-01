<?php

namespace Src\Core;

class Session
{
    private static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();

        $_SESSION[$key] = $value;
    }

    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        self::start();

        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();

        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();

        unset($_SESSION[$key]);
    }

    // =========================
    // AUTENTICACIÓN
    // =========================

    public static function login(array $user): void
    {
        self::start();

        session_regenerate_id(true);

        $_SESSION['user'] = $user;
        $_SESSION['authenticated'] = true;
    }

    public static function isAuthenticated(): bool
    {
        self::start();

        return $_SESSION['authenticated'] ?? false;
    }

    public static function user(): ?array
    {
        self::start();

        return $_SESSION['user'] ?? null;
    }

    public static function logout(): void
    {
        self::start();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }


        session_destroy();
    }
}
