<?php

namespace App\Validators;

use App\Models\Persona;

class PersonaValidator
{
    public function __construct(private Persona $persona = new Persona())
    {
    }

    public function validar(array $datos, ?int $id = null): array
    {
        $errores = [];
        $tipo = trim($datos['tipo_persona'] ?? '');
        $tipoDocumento = trim($datos['tipo_documento'] ?? '');
        $numero = trim($datos['numero_documento'] ?? '');
        $email = trim($datos['email'] ?? '');

        if (!in_array($tipo, ['Natural', 'Juridica'], true)) {
            $errores['tipo_persona'] = 'Seleccione un tipo de persona válido.';
        }
        if (!in_array($tipoDocumento, ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'], true)) {
            $errores['tipo_documento'] = 'Seleccione un tipo de documento válido.';
        }
        if ($tipoDocumento === 'DNI' && !preg_match('/^[0-9]{8}$/', $numero)) {
            $errores['numero_documento'] = 'El DNI debe contener exactamente 8 dígitos.';
        } elseif ($tipoDocumento === 'RUC' && !preg_match('/^[0-9]{11}$/', $numero)) {
            $errores['numero_documento'] = 'El RUC debe contener exactamente 11 dígitos.';
        } elseif ($numero === '') {
            $errores['numero_documento'] = 'El número de documento es obligatorio.';
        } elseif ($this->persona->existeDocumento($tipoDocumento, $numero, $id)) {
            $errores['numero_documento'] = 'El documento ya está registrado.';
        }

        if ($tipo === 'Natural') {
            foreach (['nombres' => 'Los nombres', 'apellido_paterno' => 'El apellido paterno', 'apellido_materno' => 'El apellido materno'] as $campo => $etiqueta) {
                if (trim($datos[$campo] ?? '') === '') {
                    $errores[$campo] = "$etiqueta son obligatorios.";
                }
            }
        } elseif ($tipo === 'Juridica' && trim($datos['razon_social'] ?? '') === '') {
            $errores['razon_social'] = 'La razón social es obligatoria.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El formato del correo electrónico no es válido.';
        }
        return $errores;
    }
}