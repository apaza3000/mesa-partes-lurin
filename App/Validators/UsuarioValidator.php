<?php

namespace App\Validators;

class UsuarioValidator
{

    // Validar datos de entrada para la creación del usuario
    public static function validar(array $datos): array
    {
        $errores = [];

        // Validaciones de Documento y Persona
        if (empty($datos['tipo_documento'])) {
            $errores[] = "El tipo de documento es obligatorio.";
        }

        if (empty($datos['numero_documento'])) {
            $errores[] = "El número de documento es obligatorio.";
        }

        if (empty($datos['nombres'])) {
            $errores[] = "El campo 'Nombres' es obligatorio.";
        }

        if (empty($datos['apellido_paterno'])) {
            $errores[] = "El campo 'Apellido Paterno' es obligatorio.";
        }

        // Validaciones de Credenciales y Sistema
        if (empty($datos['username'])) {
            $errores[] = "El campo 'Usuario (Username)' es obligatorio.";
        }

        if (empty($datos['password']) || strlen($datos['password']) < 6) {
            $errores[] = "La contraseña es obligatoria y debe tener al menos 6 caracteres.";
        }

        if (empty($datos['id_rol'])) {
            $errores[] = "Debe seleccionar un rol para el usuario.";
        }

        return [
            'esValido' => count($errores) === 0,
            'errores' => $errores
        ];
    }
}