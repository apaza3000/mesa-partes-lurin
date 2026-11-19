# 🏛️ Guía de Arquitectura y Estándares de Programación — Mesa de Partes Lurín

> **Destinatarios:** Modelos de Inteligencia Artificial (LLMs) y Desarrolladores del Proyecto.  
> **Propósito:** Definir con precisión absoluta la estructura, convenciones de código, flujo de datos y patrones de diseño necesarios para implementar nuevas funcionalidades de forma coherente y sin romper el sistema existente.

---

## 📌 1. Principio Fundamental: Framework MVC Propio (Vanilla PHP 8)

> [!CAUTION]
> **NO ES LARAVEL, NI SYMFONY, NI CODEIGNITER.**  
> Este proyecto utiliza un **micro-framework MVC artesanal en PHP 8.x nativo**.
> - **NUNCA** uses Facades de Laravel (`Route::get`, `Auth::user`, `DB::table`).
> - **NUNCA** uses sintaxis de Blade (`@extends`, `@section`, `@if`). Todas las vistas son PHP plano (`.php`).
> - **NUNCA** asumas la existencia de un ORM (Eloquent o Doctrine). Toda interacción con la base de datos se realiza mediante **PDO nativo y consultas preparadas**.

---

## 📂 2. Estructura del Árbol de Directorios

```
mesa-partes-lurin/
│
├── index.php                      # Punto de entrada único (Front Controller)
├── autoload.php                   # Autoloader nativo PSR-0/4
├── .env                           # Variables de entorno (DB, APIs, Keys)
├── db.sql                         # Esquema oficial y vigente de la Base de Datos
│
├── App/                           # Capa de la Aplicación
│   ├── Controllers/               # Controladores (extienden de Src\Core\Controller)
│   ├── Models/                    # Modelos PDO (implementan InterfaceModel o métodos CRUD)
│   ├── Services/                  # Servicios e integraciones externas (APIs, lógica desacoplada)
│   └── Validators/                # Validadores de entrada (extienden de Src\Core\Validator o estáticos)
│
├── Src/Core/                      # Núcleo del Micro-Framework (NO MODIFICAR a menos que sea un bug)
│   ├── Conexion.php               # Singleton de PDO para Base de Datos
│   ├── Controller.php             # Controlador Base (render, view, json, redirect, input)
│   ├── Dotenv.php                 # Parser nativo de variables .env
│   ├── Path.php                   # Gestor de rutas de directorios absolutas
│   ├── Request.php                # Abstracción de HTTP Request, Files y Headers
│   ├── Router.php                 # Motor de enrutamiento y despacho regex
│   ├── Session.php                # Gestor de autenticación y sesiones seguras
│   └── Validator.php              # Motor de validación basado en reglas
│
├── routes/                        # Definición de Rutas por Módulo
│   ├── Router.php                 # Agregador central que despacha las rutas
│   ├── auth.php                   # Rutas de login, registro y logout
│   ├── dashboard.php              # Rutas del panel principal
│   ├── usuarios.php               # CRUD de usuarios
│   ├── personas.php               # CRUD de personas / remitentes
│   ├── areas.php                  # CRUD de dependencias municipales
│   ├── roles.php                  # CRUD de roles
│   ├── entrega.php                # Recepción virtual de expedientes
│   └── error.php                  # Páginas de error (404)
│
├── view/                          # Capa de Presentación (Vistas)
│   ├── includes/                  # Parciales del Layout maestro
│   │   ├── app.php                # Layout contenedor principal (AdminLTE 4)
│   │   ├── head.php               # Metadatos, fuentes y CSS
│   │   ├── header.php             # Barra de navegación superior
│   │   ├── sidebar.php            # Menú lateral con filtrado por roles
│   │   ├── footer.php             # Pie de página
│   │   └── scripts.php            # Scripts JS (Bootstrap, Chart.js, etc.)
│   └── pages/                     # Vistas específicas por módulo
│       ├── usuarios/              # index.php, crear.php, editar.php, show.php
│       ├── personas/              # index.php, create.php, edit.php, show.php
│       ├── areas/                 # index.php, crear.php, editar.php
│       ├── roles/                 # index.php, crear.php, editar.php
│       ├── dashboard/             # index.php y components/
│       ├── entrega/               # index.php (wizard) y exitoso.php
│       └── consulta/              # index.php y detalle.php
│
└── public/uploads/                # Archivos subidos por los usuarios (expedientes)
```

---

## ⚙️ 3. Componentes Clave y Cómo Utilizarlos

### 3.1 Base de Datos: `Src\Core\Conexion`
- Se obtiene la conexión mediante el Singleton:
  ```php
  use PDO;
  use Src\Core\Conexion;

  $db = Conexion::getConexion(); // Devuelve instancia PDO
  ```
- **Regla estricta:** SIEMPRE usar consultas preparadas (`prepare` + `execute`) con marcadores nombrados (`:id`, `:nombre`). NUNCA concatenar variables en la cadena SQL.

### 3.2 Controladores: `Src\Core\Controller`
Todo nuevo controlador debe crearse en `App\Controllers\` y extender `Src\Core\Controller`:

```php
<?php

namespace App\Controllers;

use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

class MiNuevoController extends Controller
{
    // Métodos disponibles heredados de Controller:
    // $this->view('modulo.vista', $datos, 'app'); -> Carga view/pages/modulo/vista.php envuelto en layout app.php
    // $this->json(['status' => true], 200);       -> Retorna respuesta JSON con cabeceras HTTP
    // $this->redirect('/ruta');                    -> Redirige vía header Location
    // $this->input('campo', 'defecto');            -> Obtiene datos sanitizados de $_POST o $_GET
    // $this->e($cadena);                           -> Escapa entidades HTML (htmlspecialchars)
}
```

### 3.3 Autenticación y Control de Acceso: `Src\Core\Session`
Para proteger rutas en el controlador:

```php
// 1. Verificar si está logueado:
if (!Session::isAuthenticated()) {
    return $this->redirect('/login');
}

// 2. Obtener usuario autenticado:
$user = Session::user();
// Estructura de $user:
// [
//    'id_usuario' => 1,
//    'username'   => 'admin',
//    'nombres'    => 'Juan',
//    'rol'        => 'ADMINISTRADOR',
//    'roles'      => ['ADMINISTRADOR'],
//    'id_area'    => 2
// ]

// 3. Proteger por rol:
if (!in_array('ADMINISTRADOR', $user['roles'] ?? [])) {
    return $this->redirect('/dashboard');
}
```

### 3.4 Modelos y Patrón de Acceso a Datos
Los modelos se ubican en `App\Models\`:
- Inyectan o inicializan `$this->db = Conexion::getConexion()`.
- Opcionalmente implementan `App\Models\InterfaceModel` (`getAll`, `getById`, `create`, `update`, `delete`).
- Para operaciones compuestas que involucren más de una tabla, usar transacciones PDO:
  ```php
  try {
      $this->db->beginTransaction();
      // ... queries de inserción / actualización ...
      $this->db->commit();
      return true;
  } catch (\PDOException $e) {
      $this->db->rollBack();
      error_log($e->getMessage());
      return false;
  }
  ```

### 3.5 Auditoría Obligatoria: `App\Models\Auditoria`
Toda acción de creación, actualización o eliminación debe ser auditada:
```php
use App\Models\Auditoria;

$auditoria = new Auditoria();
$auditoria->insertar(
    $idUsuario,                    // ID del usuario logueado
    'usuarios',                    // Módulo
    'crear',                       // Acción (crear, editar, eliminar, derivar, login)
    'usuarios',                    // Tabla afectada
    $idRegistro,                   // ID del registro modificado
    'Descripción clara del evento',// Descripción
    $_SERVER['REMOTE_ADDR'] ?? null// IP
);
```

### 3.6 Validación de Formularios
Se cuenta con `Src\Core\Validator` y validadores especializados en `App\Validators\`:
```php
use Src\Core\Validator;

$validator = new Validator();
$rules = [
    'nombre' => ['required', 'string', 'maxLength' => 100],
    'email'  => ['nullable', 'email'],
    'estado' => ['required', 'in' => ['Activo', 'Inactivo']],
    'id_rol' => ['required', 'exists' => ['roles', 'id_rol']]
];

if (!$validator->validate($rules, $data)) {
    $errores = $validator->getErrors();
    // Renderizar vista de vuelta pasando los errores
}
```

Reglas soportadas por `Validator`:
`required`, `email`, `number`, `integer`, `string`, `boolean`, `json`, `array`, `optional`, `nullable`, `time`, `date`, `minLength`, `maxLength`, `min`, `max`, `unique`, `uniqueExcept`, `exists`, `in`.

---

## 🛣️ 4. Sistema de Enrutamiento

Las rutas se registran usando `Src\Core\Router`:

1. Se dividen en archivos semánticos dentro de `routes/` (ej. `routes/expedientes.php`).
2. Se definen con la sintaxis:
   ```php
   use Src\Core\Router;
   use App\Controllers\ExpedienteController;

   Router::get('/expedientes', [ExpedienteController::class, 'index']);
   Router::get('/expedientes/crear', [ExpedienteController::class, 'create']);
   Router::post('/expedientes/store', [ExpedienteController::class, 'store']);
   Router::get('/expedientes/show/{id}', [ExpedienteController::class, 'show']);
   Router::get('/expedientes/editar/{id}', [ExpedienteController::class, 'edit']);
   Router::post('/expedientes/update/{id}', [ExpedienteController::class, 'update']);
   Router::post('/expedientes/delete/{id}', [ExpedienteController::class, 'delete']);
   ```
3. El archivo `routes/Router.php` debe hacer `require_once` del archivo de rutas creado:
   ```php
   require_once __DIR__ . '/expedientes.php';
   ```

---

## 🎨 5. Estándar de Vistas y UI (AdminLTE 4)

- **Plantilla Base:** AdminLTE v4 (basado en Bootstrap 5.3).
- **Iconografía:** Usar Bootstrap Icons (`<i class="bi bi-folder2-open"></i>`) o Font Awesome 6 (`<i class="fas fa-file-alt"></i>`).
- **Renderizado desde Controlador:**
  ```php
  // Carga view/pages/expedientes/index.php dentro de view/includes/app.php
  $this->view('expedientes.index', [
      'expedientes' => $listaExpedientes,
      'titulo'      => 'Lista de Expedientes'
  ], 'app');
  ```
- **Manejo de Errores y Alertas en Formularios:**
  Toda vista de formulario debe incluir el contenedor de feedback de errores:
  ```php
  <?php if (!empty($errors)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <ul class="mb-0 ps-3">
              <?php foreach ((array)$errors as $err): ?>
                  <li><?= htmlspecialchars(is_array($err) ? implode(', ', $err) : $err, ENT_QUOTES, 'UTF-8') ?></li>
              <?php endforeach; ?>
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  <?php endif; ?>
  ```
- **Protección CSRF:** Incluir siempre el token en formularios POST:
  ```html
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
  ```

---

## 🚀 6. Guía Paso a Paso: Cómo Agregar un Nuevo Módulo

Sigue estrictamente esta receta para programar cualquier nueva funcionalidad:

### Paso 1: Base de Datos (`db.sql`)
Verificar o definir la tabla en MySQL con sus claves foráneas, restricciones e índices. Mantener tipos estándar (`INT AUTO_INCREMENT`, `VARCHAR`, `TIMESTAMP`, `ENUM`).

### Paso 2: Crear el Modelo (`App/Models/`)
Crear `App/Models/Reporte.php`:
- Declarar namespace `namespace App\Models;`.
- Importar `use PDO;` y `use Src\Core\Conexion;`.
- Implementar consultas SQL con `prepare()`.

### Paso 3: Crear el Validador (si aplica) (`App/Validators/`)
Crear `App/Validators/ReporteValidator.php` para validar entradas complejas.

### Paso 4: Crear el Controlador (`App/Controllers/`)
Crear `App/Controllers/ReporteController.php`:
- Extender `Src\Core\Controller`.
- Proteger métodos con `Session::isAuthenticated()`.
- Capturar datos con `(new Request())->all()` o `$this->input()`.
- Validar datos.
- Invocar modelo y registrar auditoría.
- Renderizar vista con `$this->view('reportes.index', [...], 'app')`.

### Paso 5: Registrar las Rutas (`routes/`)
- Crear o editar el archivo correspondiente en `routes/`.
- Conectar cada URL con su acción `[ReporteController::class, 'metodo']`.
- Asegurar que `routes/Router.php` lo cargue.

### Paso 6: Crear las Vistas (`view/pages/`)
Crear la carpeta `view/pages/reportes/` y los archivos `index.php`, `crear.php`, etc., respetando el maquetado de AdminLTE 4.

### Paso 7: Integrar en el Menú (`view/includes/sidebar.php`)
Agregar la opción en el sidebar bajo la sección adecuada, condicionando su visibilidad según el rol correspondiente:
```php
<?php if (in_array('ADMINISTRADOR', $rolesUsuario)): ?>
    <li class="nav-item">
        <a href="/reportes" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/reportes') !== false ? 'active' : '' ?>">
            <i class="nav-icon bi bi-bar-chart-fill"></i>
            <p>Reportes Documentarios</p>
        </a>
    </li>
<?php endif; ?>
```

---

## ⚠️ 7. Reglas de Oro para Asistentes de IA (Golden Rules)

1. **NO reinventar la conexión a DB:** Utiliza SIEMPRE `Src\Core\Conexion::getConexion()`.
2. **NO romper la convención de vistas:** La notación es con punto `'carpeta.archivo'` que traduce a `view/pages/carpeta/archivo.php`.
3. **NO olvidar los tipos de datos en parámetros:** El proyecto usa PHP 8.x con tipado estricto (`int $id`, `string $codigo`, `array $datos`, `: bool`, `: array`).
4. **NO dejar rutas con `href="#"`:** Toda funcionalidad programada debe tener su enlace real y su controlador asociado.
5. **Sanitizar SIEMPRE la salida HTML:** Usar `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')` o el helper `$this->e($valor)`.
6. **Verificar sintaxis antes de finalizar:** Comprobar siempre los archivos PHP con `php -l` para asegurar 0 errores de sintaxis.

