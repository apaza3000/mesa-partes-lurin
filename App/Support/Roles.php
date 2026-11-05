<?php

namespace App\Support;

use Src\Core\Session;

/**
 * Roles reales del sistema, tal como están sembrados en db.sql (tabla `roles`).
 *
 * Es la única fuente de verdad para el control de acceso en la capa de aplicación:
 * las vistas (sidebar/header) y los controladores deben comparar siempre contra estas
 * constantes y nunca contra literales inventados.
 */
final class Roles
{
    public const ADMINISTRADOR = 'ADMINISTRADOR';
    public const MESA_DE_PARTES = 'MESA_DE_PARTES';
    public const RESPONSABLE_AREA = 'RESPONSABLE_AREA';
    public const USUARIO_AREA = 'USUARIO_AREA';
    public const CONSULTA = 'CONSULTA';

    /** Todos los roles válidos (orden alfabético del seed). */
    public const Todos = [
        self::ADMINISTRADOR,
        self::CONSULTA,
        self::MESA_DE_PARTES,
        self::RESPONSABLE_AREA,
        self::USUARIO_AREA,
    ];

    /** Gestión maestra: roles, áreas, usuarios. */
    public const Administracion = [self::ADMINISTRADOR];

    /** Ventanilla de registro y recepción. */
    public const MesaDePartes = [self::ADMINISTRADOR, self::MESA_DE_PARTES];

    /** Bandeja y flujo de atención por área. */
    public const AtencionArea = [
        self::ADMINISTRADOR,
        self::RESPONSABLE_AREA,
        self::USUARIO_AREA,
    ];

    /** Quien puede mover un expediente (registrarlo o atenderlo). */
    public const Operacion = [
        self::ADMINISTRADOR,
        self::MESA_DE_PARTES,
        self::RESPONSABLE_AREA,
        self::USUARIO_AREA,
    ];

    /** Consulta pública autenticada (solo lectura). */
    public const Consulta = [
        self::ADMINISTRADOR,
        self::CONSULTA,
        self::MESA_DE_PARTES,
        self::RESPONSABLE_AREA,
        self::USUARIO_AREA,
    ];

    /** Módulos que toda persona autenticada puede ver (padrón, consulta). */
    public const Autenticado = self::Consulta;

    /**
     * Convierte cualquier representación de roles (string GROUP_CONCAT, array,
     * nulo) en una lista normalizada en mayúsculas.
     *
     * @return string[]
     */
    public static function normalize(mixed $roles): array
    {
        if ($roles === null) {
            return [];
        }

        $lista = is_array($roles) ? $roles : explode(',', (string) $roles);

        $lista = array_map(
            static fn($r) => mb_strtoupper(trim((string) $r)),
            $lista
        );
        $lista = array_values(array_filter($lista, static fn($r) => $r !== ''));

        return array_values(array_unique($lista));
    }

    /**
     * Roles del usuario autenticado. Lee primero la sesión estructurada y cae al
     * rol único histórico para no romper sesiones ya abiertas.
     *
     * @return string[]
     */
    public static function delUsuarioActual(): array
    {
        $roles = self::normalize(Session::get('usuario_roles'));

        if ($roles === []) {
            $usuario = Session::user() ?? [];
            $roles = self::normalize($usuario['roles'] ?? null);
        }

        if ($roles === []) {
            $roles = self::normalize(Session::get('usuario_rol'));
        }

        if ($roles === []) {
            $usuario = Session::user() ?? [];
            $roles = self::normalize($usuario['rol'] ?? null);
        }

        return $roles;
    }

    /**
     * ¿El usuario autenticado posee alguno de los roles pedidos?
     * Lista vacía de permitidos = denegado (deny by default).
     */
    public static function usuarioTiene(array $permitidos): bool
    {
        $permitidos = self::normalize($permitidos);

        if ($permitidos === []) {
            return false;
        }

        return array_intersect($permitidos, self::delUsuarioActual()) !== [];
    }

    /**
     * Rol principal del usuario: el de mayor jerarquía poseída, no el primero
     * alfabético (SEC-02/AuthController). Sirve de respaldo cuando una pantalla
     * solo admite un rol.
     */
    public static function prioritario(array $roles): ?string
    {
        $preferencia = [
            self::ADMINISTRADOR,
            self::RESPONSABLE_AREA,
            self::USUARIO_AREA,
            self::MESA_DE_PARTES,
            self::CONSULTA,
        ];

        $roles = self::normalize($roles);

        foreach ($preferencia as $rol) {
            if (in_array($rol, $roles, true)) {
                return $rol;
            }
        }

        return $roles[0] ?? null;
    }

    /** Etiqueta legible para mostrar en pantalla (sin HTML). */
    public static function etiqueta(string $rol): string
    {
        return match ($rol) {
            self::ADMINISTRADOR => 'Administrador',
            self::MESA_DE_PARTES => 'Mesa de Partes',
            self::RESPONSABLE_AREA => 'Responsable de área',
            self::USUARIO_AREA => 'Usuario de área',
            self::CONSULTA => 'Consulta',
            default => $rol,
        };
    }
}
