# Sintaxis de las Reglas de Validación

Las reglas de validación se definen dentro de un arreglo asociativo, donde cada clave representa un campo y su valor es un arreglo con las validaciones que se aplicarán.

## Reglas sin parámetros

Estas reglas solo requieren indicar el nombre de la validación.

| Regla    | Sintaxis     |
| -------- | ------------ |
| required | `'required'` |
| email    | `'email'`    |
| number   | `'number'`   |
| integer  | `'integer'`  |
| string   | `'string'`   |
| boolean  | `'boolean'`  |
| json     | `'json'`     |
| array    | `'array'`    |
| optional | `'optional'` |
| nullable | `'nullable'` |
| time     | `'time'`     |

---

## Reglas con un parámetro

Estas reglas reciben un único parámetro y deben declararse utilizando el nombre de la regla como clave.

| Regla     | Sintaxis              |
| --------- | --------------------- |
| minLength | `'minLength' => 5`    |
| maxLength | `'maxLength' => 50`   |
| min       | `'min' => 1`          |
| max       | `'max' => 100`        |
| date      | `'date' => ['Y-m-d']` |

> **Nota:** La regla `date` acepta cualquier formato compatible con `DateTime::createFromFormat()`.

---

## Reglas con múltiples parámetros

Estas reglas reciben varios parámetros y deben declararse como un arreglo.

| Regla        | Sintaxis                                             |
| ------------ | ---------------------------------------------------- |
| unique       | `'unique' => ['tabla', 'columna']`                   |
| uniqueExcept | `'uniqueExcept' => ['tabla', 'columna', $currentId]` |
| exists       | `'exists' => ['tabla', 'columna']`                   |
| in           | `'in' => ['Valor1', 'Valor2', 'Valor3']`             |

---

## Ejemplo de definición de reglas

```php
protected $rules = [

    'nombre' => [
        'required',
        'string',
        'minLength' => 3,
        'maxLength' => 100
    ],

    'correo' => [
        'required',
        'email',
        'unique' => [
            'usuarios',
            'correo'
        ]
    ],

    'edad' => [
        'required',
        'integer',
        'min' => 18,
        'max' => 60
    ],

    'estado' => [
        'required',
        'in' => [
            'Activo',
            'Inactivo'
        ]
    ],

    'fecha_nacimiento' => [
        'nullable',
        'date' => 'Y-m-d'
    ]

];
```
