<?php
namespace App\Validators;

use PDO;

class TipoDocumentoValidator {
    /**
     * @var PDO
     */
    private $db;

    /**
     * @param PDO $db
     */
    public function __construct(PDO $db) {
        $this->db =$db;
    }

    /**
     * @param int|string|null $currentId
     * @return array
     */
    public function getRules($currentId = null) {
        $codigoRule =$currentId 
            ? ['uniqueExcept' => ['tipos_documento', 'codigo', $currentId]]
            : ['unique' => ['tipos_documento', 'codigo']];

        $nombreRule =$currentId 
            ? ['uniqueExcept' => ['tipos_documento', 'nombre', $currentId]]
            : ['unique' => ['tipos_documento', 'nombre']];

        return [
            'codigo' => array_merge([
                'required',
                'string',
                'minLength' => 2,
                'maxLength' => 20
            ], $codigoRule),

            'nombre' => array_merge([
                'required',
                'string',
                'minLength' => 3,
                'maxLength' => 100
            ], $nombreRule),

            'descripcion' => [
                'optional',
                'string',
                'maxLength' => 255
            ],

            'requiere_archivo' => [
                'required',
                'boolean'
            ]
        ];
    }

    /**
     * @param array $data
     * @param int|string|null $currentId
     * @return array
     */
    public function validate(array $data,$currentId = null) {
        $rules =$this->getRules($currentId);$errors = [];

        foreach ($rules as$field => $fieldRules) {$value = $data[$field] ?? null;

            foreach ($fieldRules as $key =>$ruleParam) {
                $rule = is_int($key) ? $ruleParam :$key;
                $param = is_int($key) ? null : $ruleParam;

                if ($rule === 'required' && (is_null($value) || trim((string)$value) === '')) {
                    $errors[$field] = "El campo {$field} es obligatorio.";
                    break;
                }
                if ($rule === 'optional' && (is_null($value) || trim((string)$value) === '')) {
                    break;
                }
                if ($rule === 'boolean' && !is_null($value)) {
                    if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                        $errors[$field] = "El campo {$field} debe ser booleano.";
                    }
                }
                if ($rule === 'minLength' && strlen((string)$value) <$param) {
                    $errors[$field] = "El campo {$field} debe tener al menos {$param} caracteres.";
                }
                if ($rule === 'maxLength' && strlen((string)$value) >$param) {
                    $errors[$field] = "El campo {$field} no debe superar los {$param} caracteres.";
                }
                if ($rule === 'unique' && is_array($param)) {
                    [$tabla, $columna] =$param;
                    if ($this->checkExists($tabla, $columna,$value)) {
                        $errors[$field] = "El valor ingresado ya esta registrado.";
                    }
                }
                if ($rule === 'uniqueExcept' && is_array($param)) {
                    [$tabla,$columna, $exceptId] =$param;
                    if ($this->checkExists($tabla,$columna, $value,$exceptId)) {
                        $errors[$field] = "El valor ingresado ya esta registrado en otro tipo.";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param int|string|null $exceptId
     * @return bool
     */
    private function checkExists($table, $column,$value, $exceptId = null) {$sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val AND deleted_at IS NULL";
        $params = [':val' =>$value];
        if ($exceptId) {$sql .= " AND id != :exceptId";
            $params[':exceptId'] =$exceptId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
