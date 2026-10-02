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
        $telefono = trim($datos['telefono'] ?? '');
        $direccion = trim($datos['direccion'] ?? '');

        if (!in_array($tipoPersona, ['Natural', 'Juridica'], true)) {
            $errores['tipo_persona'] = 'Seleccione un tipo de persona válido.';
        }

        if (!in_array($tipoDocumento, ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'], true)) {
            $errores['tipo_documento'] = 'Seleccione un tipo de documento válido.';
        }

        if ($numeroDocumento === '') {
            $errores['numero_documento'] = 'El número de documento es obligatorio.';
        } elseif ($tipoDocumento === 'DNI' && !preg_match('/^[0-9]{8}$/D', $numeroDocumento)) {
            $errores['numero_documento'] = 'El DNI debe contener exactamente 8 dígitos.';
        } elseif ($tipoDocumento === 'RUC' && !preg_match('/^[0-9]{11}$/D', $numeroDocumento)) {
            $errores['numero_documento'] = 'El RUC debe contener exactamente 11 dígitos.';
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

        if ($email === '') {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del correo electrónico no es válido.';
        } elseif (strlen($email) > 150) {
            $errores['email'] = 'El correo electrónico no puede superar los 150 caracteres.';
        }

        if ($telefono === '') {
            $errores['telefono'] = 'El teléfono es obligatorio.';
        } elseif (strlen($telefono) > 30) {
            $errores['telefono'] = 'El teléfono no puede superar los 30 caracteres.';
        }

        if ($direccion === '') {
            $errores['direccion'] = 'La dirección es obligatoria.';
        } elseif (strlen($direccion) > 255) {
            $errores['direccion'] = 'La dirección no puede superar los 255 caracteres.';
        }

        if (!in_array(trim($datos['estado'] ?? 'Activo'), ['Activo', 'Inactivo'], true)) {
            $errores['estado'] = 'Seleccione un estado válido.';
        }

        return $errores;
    }
}
