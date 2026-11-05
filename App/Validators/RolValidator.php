<?php

namespace App\Validators;

class RolValidator {

    // Validar datos de entrada para el rol
    public static function validar(array $datos): array {
        $errores = [];

        // Validar que el nombre del rol no esté vacío
        if (empty($datos['nombre_rol'])) {
            $errores[] = "El campo 'Nombre del Rol' es obligatorio.";
        }

        // Validar que el slug no esté vacío
        if (empty($datos['slug'])) {
            $errores[] = "El campo 'Slug' es obligatorio.";
        }

        return [
            'esValido' => count($errores) === 0,
            'errores'  => $errores
        ];
    }
}