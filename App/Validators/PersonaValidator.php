<?php

namespace App\Validators;

use App\Models\Persona;
use App\Support\Catalogos;

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
        } elseif (!Catalogos::cabe('personas.numero_documento', $numeroDocumento)) {
            $errores['numero_documento'] = 'El número de documento no puede superar los ' . Catalogos::limite('personas.numero_documento') . ' caracteres.';
        } elseif ($this->persona->existeDocumento($tipoDocumento, $numeroDocumento, $id)) {
            $errores['numero_documento'] = 'El documento ya está registrado.';
        }

        if ($tipoPersona === 'Natural') {
            if (trim($datos['nombres'] ?? '') === '') {
                $errores['nombres'] = 'Los nombres son obligatorios.';
            } elseif (!Catalogos::cabe('personas.nombres', $datos['nombres'])) {
                $errores['nombres'] = 'Los nombres no pueden superar los ' . Catalogos::limite('personas.nombres') . ' caracteres.';
            }
            if (trim($datos['apellido_paterno'] ?? '') === '') {
                $errores['apellido_paterno'] = 'El apellido paterno es obligatorio.';
            } elseif (!Catalogos::cabe('personas.apellido_paterno', $datos['apellido_paterno'])) {
                $errores['apellido_paterno'] = 'El apellido paterno no puede superar los ' . Catalogos::limite('personas.apellido_paterno') . ' caracteres.';
            }
            if (trim($datos['apellido_materno'] ?? '') === '') {
                $errores['apellido_materno'] = 'El apellido materno es obligatorio.';
            } elseif (!Catalogos::cabe('personas.apellido_materno', $datos['apellido_materno'])) {
                $errores['apellido_materno'] = 'El apellido materno no puede superar los ' . Catalogos::limite('personas.apellido_materno') . ' caracteres.';
            }
        }

        if ($tipoPersona === 'Juridica' && trim($datos['razon_social'] ?? '') === '') {
            $errores['razon_social'] = 'La razón social es obligatoria.';
        } elseif (isset($datos['razon_social']) && !Catalogos::cabe('personas.razon_social', $datos['razon_social'])) {
            $errores['razon_social'] = 'La razón social no puede superar los ' . Catalogos::limite('personas.razon_social') . ' caracteres.';
        }

        if ($email === '') {
            $errores['email'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del correo electrónico no es válido.';
        } elseif (!Catalogos::cabe('personas.email', $email)) {
            $errores['email'] = 'El correo electrónico no puede superar los ' . Catalogos::limite('personas.email') . ' caracteres.';
        }

        if ($telefono === '') {
            $errores['telefono'] = 'El teléfono es obligatorio.';
        } elseif (!Catalogos::cabe('personas.telefono', $telefono)) {
            $errores['telefono'] = 'El teléfono no puede superar los ' . Catalogos::limite('personas.telefono') . ' caracteres.';
        }

        if ($direccion === '') {
            $errores['direccion'] = 'La dirección es obligatoria.';
        } elseif (!Catalogos::cabe('personas.direccion', $direccion)) {
            $errores['direccion'] = 'La dirección no puede superar los ' . Catalogos::limite('personas.direccion') . ' caracteres.';
        }

        if (!in_array(trim($datos['estado'] ?? 'Activo'), ['Activo', 'Inactivo'], true)) {
            $errores['estado'] = 'Seleccione un estado válido.';
        }

        return $errores;
    }
}
