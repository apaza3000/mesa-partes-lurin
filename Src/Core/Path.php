<?php

namespace Src\Core;


class Path
{
    protected static string $basePath;

    // Constantes para diferentes directorios
    private const APP_DIR = 'app';
    private const PUBLIC_DIR = 'public';
    private const STORAGE_DIR = 'storage';
    private const DOCUMENTS_DIR = 'documents';
    private const SRC_DIR="src";

    /**
     * Establece la ruta base del proyecto.
     */
    public static function setBasePath(string $basePath): void
    {
        self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio base.
     */
    public static function base(string $path = ''): string
    {
        return self::getFullPath($path);
    }

    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio de aplicación.
     */
    public static function app(string $path = ''): string
    {
        return self::getFullPath(self::APP_DIR . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio público.
     */
    public static function public(string $path = ''): string
    {
        return self::getFullPath(self::PUBLIC_DIR . DIRECTORY_SEPARATOR . $path);
    }
    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio src.
     */
    public static function src(string $path = ''): string
    {
        return self::getFullPath(self::SRC_DIR . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio de almacenamiento.
     */
    public static function storage(string $path = ''): string
    {
        return self::getFullPath(self::STORAGE_DIR . DIRECTORY_SEPARATOR . $path);
    }
    /**
     * Obtiene la ruta completa hacia un archivo o directorio en el directorio de almacenamiento.
     */
    public static function documents(string $path = ''): string
    {
        return self::getFullPath(self::STORAGE_DIR . DIRECTORY_SEPARATOR . self::DOCUMENTS_DIR . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Método auxiliar para generar la ruta completa combinando la ruta base con el subdirectorio.
     */
    protected static function getFullPath(string $subPath): string
    {
        $base = rtrim(self::$basePath, DIRECTORY_SEPARATOR);
        $relative = $subPath === '' ? '' : ltrim($subPath, DIRECTORY_SEPARATOR);
        $fullPath = $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . $relative;

        if ($relative !== '') {
            self::makeDir($fullPath);
        }

        return $fullPath;
    }

    public static function makeDir(string $fullPath, int $permissions = 0777, bool $recursive = true): bool
    {
        if (is_dir($fullPath)) {
            return true; // El directorio ya existe
        }

        return mkdir($fullPath, $permissions, $recursive);
    }
}
