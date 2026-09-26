<?php

namespace App\Validators;

use App\Models\Persona;

class PersonaValidator
{
    public function __construct(private Persona $persona)
    {
    }

    public function validar(array $datos, ?int $id = null): array
    {
        $errores = [];
        $tipoPersona = trim($datos['tipo_persona'] ?? '');
        $tipoDocumento = trim($datos['tipo_documento'] ?? '');
        $numeroDocumento = trim($datos['numero_documento'] ?? '');
        $email = trim($datos['email'] ?? '');

        if (!in_array($tipoPersona, ['Natural', 'Juridica'], true)) {
            $errores['tipo_persona'] = 'Seleccione un tipo de persona válido.';
        }

        if (!in_array($tipoDocumento, ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'], true)) {
            $errores['tipo_documento'] = 'Seleccione un tipo de documento válido.';
        }

        if ($numeroDocumento === '') {
            $errores['numero_documento'] = 'El número de documento es obligatorio.';
        } elseif (strlen($numeroDocumento) > 20) {
            $errores['numero_documento'] = 'El número de documento no puede superar los 20 caracteres.';
        } elseif ($this->persona->existeDocumento($tipoDocumento, $numeroDocumento, $id)) {
            $errores['numero_documento'] = 'El documento ya está registrado.';
        }

        if ($tipoPersona === 'Natural') {
            if (trim($datos['nombres'] ?? '') === '') {
                $errores['nombres'] = 'Los nombres son obligatorios.';
            }
            if (trim($datos['apellido_paterno'] ?? '') === '') {
                $errores['apellido_paterno'] = 'El apellido paterno es obligatorio.';
            }
            if (trim($datos['apellido_materno'] ?? '') === '') {
                $errores['apellido_materno'] = 'El apellido materno es obligatorio.';
            }
        }

        if ($tipoPersona === 'Juridica' && trim($datos['razon_social'] ?? '') === '') {
            $errores['razon_social'] = 'La razón social es obligatoria.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del correo electrónico no es válido.';
        }

        if (!in_array(trim($datos['estado'] ?? 'Activo'), ['Activo', 'Inactivo'], true)) {
            $errores['estado'] = 'Seleccione un estado válido.';
        }

        return $errores;
    }
}
