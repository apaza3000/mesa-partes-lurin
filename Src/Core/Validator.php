<?php

namespace Src\Core;


class Validator
{
    protected $errors = [];
    protected $rules = [];

    protected $messages = [];
    protected $fields = [];

    private $mess_default = [
        'required' => "{0} es un campo obligatorio.",
        'email' => "El formato de correo electrónico es inválido para {0}.",
        'number' => "{0} debe ser un número válido.",
        'integer' => "{0} debe ser un número entero.",
        'minLength' => "{0} debe tener al menos {1} caracteres.",
        'maxLength' => "{0} no puede exceder los {1} caracteres.",
        'max' => "{0} no puede ser mayor que {1}.",
        'min' => "{0} no puede ser menor que {1}.",
        'unique' => "El valor de {0} ya está registrado en {1}.",
        'uniqueExcept' => "El valor de {0} ya está registrado en {1}.",
        'exists' => "No se encontró ningún registro con {0} igual a {1} en {2}.",
        'string' => "{0} debe ser una cadena de texto válida.",
        'date' => "{0} no tiene un formato de fecha válido. Formato esperado: {1}.",
        'time' => "{0} debe tener un formato de hora válido (HH:MM:SS).",
        'in' => "{0} debe ser uno de los siguientes valores: {1}.",
        'array' => "{0} debe ser un arreglo.",
        'json' => "{0} no es un JSON válido.",
        'boolean' => "{0} debe ser un valor booleano (true o false).",
        'optional' => "{0} es opcional.",
        'nullable' => "{0} puede estar vacío."
    ];





    public function __construct() {}

    public function getErrors()
    {
        return $this->errors;
    }

    // Método genérico para aplicar múltiples validaciones
    public function validated($data)
    {

        foreach ($this->rules as $field => $ruleSet) {

            foreach ($ruleSet as $rule => $params) {

                if (is_int($rule)) {
                    $rule = $params;
                    $params = [];
                }
                if (is_int($params)) {
                    $params = array($params);
                }

                if ($rule === 'nullable' && $this->nullable($data[$field] ?? null, $field)) {
                    // No ejecutar más validaciones para este campo
                    break;
                } else {
                    array_unshift($params, $data[$field] ?? null, $field);
                    call_user_func_array([$this, $rule], $params);
                }
            }
        }

        return empty($this->errors);
    }

    public function validate($rules, $data)
    {

        foreach ($rules as $field => $ruleSet) {
            foreach ($ruleSet as $rule => $params) {
                if (is_int($rule)) {
                    $rule = $params;
                    $params = [];
                }
                if (is_int($params)) {
                    $params = array($params);
                }
                if ($rule == 'nullable' && $this->nullable($data[$field] ?? null, $field)) {
                    // No ejecutar más validaciones para este campo
                    break;
                }
                array_unshift($params, $data[$field] ?? null, $field);
                call_user_func_array([$this, $rule], $params);
            }
        }

        return empty($this->errors);
    }

    // Verificar requerido
    private function required($value, $fieldName)
    {
        if ($value === '' || $value === null) {  // Comprobamos explícitamente si es una cadena vacía o null
            $this->setError($fieldName, 'required');
            return false;
        }
        return true;
    }

    // Validar que un valor sea un email
    private function email($value, $fieldName)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->setError($fieldName, 'email');
            return false;
        }
        return true;
    }

    // Validar que un valor sea un número
    private function number($value, $fieldName)
    {
        if (!is_numeric($value)) {
            $this->setError($fieldName, 'number');
            return false;
        }
        return true;
    }
    // Validar que un valor sea un número
    private function integer($value, $fieldName)
    {
        if (!is_numeric($value) || intval($value) != $value) {
            $this->setError($fieldName, 'integer');
            return false;
        }
        return true;
    }

    // Validar que un valor cumpla con una longitud mínima
    private function minLength($value, $fieldName, $length)
    {
        if (strlen($value ?? "") < $length) {
            $this->setError($fieldName, 'minLength', $length);
            return false;
        }
        return true;
    }

    // Validar que un valor no exceda una longitud máxima
    private function maxLength($value, $fieldName, $length)
    {
        if (strlen($value ?? "") > $length) {
            $this->setError($fieldName, 'maxLength', $length);
            return false;
        }
        return true;
    }


    // Validar unicidad en una columna específica de una tabla
    private function unique($value, $fieldName, $table, $column)
    {
        $db = Conexion::getConexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = :value");
        $stmt->execute(['value' => $value]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $this->setError($fieldName, 'unique', $table);
            return false;
        }
        return true;
    }

    // Validar unicidad exceptuando el propio registro actual en una operación de actualización
    private function uniqueExcept($value, $fieldName, $table, $column, $currentId, $idColumn = 'id')
    {
        $db = Conexion::getConexion();

        $stmt = $db->prepare("SELECT COUNT(*) FROM $table   WHERE $column = :value AND $idColumn != :currentId");
        $stmt->execute(['value' => $value, 'currentId' => $currentId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $this->setError($fieldName, 'uniqueExcept', $table);

            return false;
        }
        return true;
    }

    // Validar si existe en una tabla 
    private function exists($value, $fieldName, $table, $column)
    {
        $db = Conexion::getConexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = :value");
        $stmt->execute(['value' => $value]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {

            $this->setError($fieldName, 'exists', $value, $table);
            return false;
        }
        return true;
    }
    // Validar si es una cadena de texto
    private function string($value, $fieldName)
    {
        if (!is_string($value)) {
            $this->setError($fieldName, 'string');
            return false;
        }
        // Aplicar htmlspecialchars para prevenir XSS
        return true;
    }

    // Validar fecha con formato especifico
    private function date($value, $fieldName, $format)
    {


        // Intentar crear un objeto DateTime para verificar la validez de la fecha
        $date = \DateTime::createFromFormat($format, $value);
        if (!$date || $date->format($format) !== $value) {
            $this->setError($fieldName, 'date', $format);
            return false;
        }


        return true;
    }

    private function time($value, $fieldName)
    {
        // Regex para validar formato HH:MM:SS
        $pattern = '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/';

        if (!preg_match($pattern, $value)) {
            $this->setError($fieldName, 'time');
            return false;
        }
        return true;
    }

    // Validar un item dentro de un arreglo
    private function in($value, $fieldName, ...$values)
    {
        if (!in_array($value, $values, false)) {
            $this->setError($fieldName, 'in', implode(', ', $values));
            return false;
        }
        return true;
    }

    //validar un arreglo
    private function array($value, $fieldName)
    {
        if (!is_array($value)) {
            $this->setError($fieldName, 'array');
            return false;
        }
        return true;
    }

    private function json($value, $fieldName)
    {
        if (!is_string($value)) {

            $this->setError($fieldName, 'string');
            return false;
        }

        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setError($fieldName, 'json');
            return false;
        }
        return true;
    }


    private function boolean($value, $fieldName)
    {
        if (!is_bool($value)) {
            $this->setError($fieldName, 'boolean');
            return false;
        }
        return true;
    }
    private function optional($value, $fieldName)
    {
        // No hace nada si el campo es opcional y no está presente en los datos.
        // La lógica de validación debe manejarse en otras partes del código.
        return true;
    }
    private function nullable($value, $fieldName)
    {


        return !isset($value) || is_null($value) || trim($value) == '';
    }

    private function max($value, $fieldName, $maxValue)
    {

        if (!is_numeric($value)) {
            $this->setError($fieldName, 'number');
            return false;
        }
        if ($value > $maxValue) {
            $this->setError($fieldName, 'max', $maxValue);
            return false;
        }
        return true;
    }

    private function min($value, $fieldName, $minValue)
    {
        if (!is_numeric($value)) {
            $this->setError($fieldName, 'number');
            return false;
        }
        if ($value < $minValue) {
            $this->setError($fieldName, 'min', $minValue);
            return false;
        }
        return true;
    }

    private function setError($fieldName, ...$params)
    {

        $message = ($this->messages[$params[0]]) ?? $this->mess_default[$params[0]];;
        $field = ($this->fields[$fieldName]) ?? $fieldName;
        $message = str_replace("{0}", $field, $message);
        foreach ($params as $index => $value) {
            if ($index == 0) {
                continue;
            }
            $message = str_replace("{" . $index . "}", $value ?? "", $message);
        }
        if (!isset($this->errors[$fieldName]) || !in_array($message, $this->errors[$fieldName])) {
            $this->errors[$fieldName][] = $message;
        }
    }
}
