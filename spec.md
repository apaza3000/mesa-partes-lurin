# 📋 Especificación Técnica de Tareas Pendientes (Spec) — Mesa de Partes Lurín

> **Documento:** `spec.md`  
> **Sistema:** Mesa de Partes Virtual y Gestión Documental — Municipalidad de Lurín  
> **Fecha:** 04 de Noviembre de 2026  
> **Estado:** Borrador de tareas priorizadas para desarrollo

---

## 📌 Índice
1. [Resumen y Alcance](#1-resumen-y-alcance)
2. [Matriz de Priorización](#2-matriz-de-priorización)
3. [Fase 1: Corrección de Errores Críticos y Bugs (Hotfixes)](#3-fase-1-corrección-de-errores-críticos-y-bugs-hotfixes)
4. [Fase 2: Limpieza Arquitectónica y Estandarización](#4-fase-2-limpieza-arquitectónica-y-estandarización)
5. [Fase 3: Seguridad y Robustez](#5-fase-3-seguridad-y-robustez)
6. [Fase 4: Completitud Funcional y Rutas Pendientes](#6-fase-4-completitud-funcional-y-rutas-pendientes)
7. [Fase 5: Documentación y Testing](#7-fase-5-documentación-y-testing)
8. [Criterios de Aceptación Global](#8-criterios-de-aceptación-global)

---

## 1. Resumen y Alcance

Tras el análisis estático y funcional del sistema **Mesa de Partes Lurín**, se han detectado inconsistencias sintácticas, código redundante/huérfano, duplicidad de configuraciones de base de datos, módulos con rutas pendientes (`#`) y oportunidades de fortalecimiento de seguridad.

Este documento desglosa todas las tareas técnicas necesarias para llevar el proyecto a un estado estable, escalable y listo para producción.

---

## 2. Matriz de Priorización

| ID | Tarea | Prioridad | Módulo / Componente | Esfuerzo Estimado | Estado |
|---|---|---|---|---|---|
| **BUG-01** | Corrección de sintaxis SQL en `Usuario::obtenerAllPorId` | 🔴 Alta | `App\Models\Usuario` | 15 min | ✅ **Resuelto** |
| **BUG-02** | Activación de validación omitida en `UsuarioController::store` | 🔴 Alta | `App\Controllers\UsuarioController` | 30 min | ✅ **Resuelto** |
| **BUG-03** | Corrección de variables de entorno en `ApiDecolectaService` | 🔴 Alta | `App\Services\ApiDecolectaService` | 20 min | ✅ **Resuelto** |
| **BUG-04** | Importación faltante de namespace en `Documento.php` | 🔴 Alta | `App\Models\Documento` | 15 min | ✅ **Resuelto** |
| **REF-01** | Renombrar clase y archivo `ConsutlaExpediente` (Typo) | 🟠 Media | `App\Models`, `Controllers`, `routes` | 30 min |
| **REF-02** | Modernizar `DerivacionController` y `DocumentoController` a MVC Core | 🟠 Media | `App\Controllers`, `routes` | 1.5 horas |
| **REF-03** | Unificar conexión a Base de Datos (eliminar carpeta duplicada `backend/`) | 🟠 Media | `Src\Core`, `backend/`, `config` | 1 hora |
| **REF-04** | Deduplicar widgets del Dashboard (~114KB x 4) | 🟡 Media | `view/pages/dashboard/components/` | 2 horas |
| **REF-05** | Estandarizar capa de servicios (`UsuarioServices` vs `Usuario`) | 🟡 Baja | `App\Services`, `App\Controllers` | 1.5 horas |
| **SEC-01** | Implementar Rate Limiting / Bloqueo en Login | 🟠 Media | `App\Controllers\AuthController` | 2 horas |
| **SEC-02** | Fortalecer políticas de contraseña y CSRF en AJAX | 🟠 Media | `App\Validators`, `Src\Core` | 1.5 horas |
| **FEAT-01** | Implementación de Rutas Huérfanas del Sidebar (Trazabilidad y Reportes) | 🟡 Media | `view/includes/sidebar.php`, `routes` | 4 horas |
| **FEAT-02** | Módulo de Visualización de Auditoría del Sistema | 🟡 Media | `App\Controllers\AuditoriaController` | 3 horas |
| **DOC-01** | Redacción del `readme.md` del proyecto y guía de despliegue | 🟢 Baja | Raíz del proyecto | 45 min |

---

## 3. Fase 1: Corrección de Errores Críticos y Bugs (Hotfixes)

### 📌 BUG-01: Corrección de sintaxis SQL en `Usuario::obtenerAllPorId` ✅ [RESUELTO]
- **Problema:** En [Usuario.php](file:///c:/laragon/www/mesa-partes-lurin/App/Models/Usuario.php), el método `obtenerAllPorId` contiene un punto y coma antes de `LIMIT 1`:
  ```sql
  WHERE u.id_usuario = :id_usuario; LIMIT 1
  ```
  Esto provoca un error fatal de sintaxis PDO al intentar ver el detalle de un usuario (`/usuarios/show/{id}`).
- **Archivos afectados:**
  - `App/Models/Usuario.php`
- **Criterio de aceptación:**
  - Remover el punto y coma intermedio en la consulta SQL.
  - La ruta `/usuarios/show/{id}` debe renderizar correctamente los datos completos del usuario sin lanzar excepción PDO.

---

### 📌 BUG-02: Activación de validación omitida en `UsuarioController::store` ✅ [RESUELTO]
- **Problema:** En [UsuarioController.php](file:///c:/laragon/www/mesa-partes-lurin/App/Controllers/UsuarioController.php), en el método `store()`, se invoca la validación:
  ```php
  $resultadoValidacion = UsuarioValidator::validar($_POST);
  ```
  Sin embargo, el resultado `$resultadoValidacion['esValido']` nunca se evalúa antes de proceder con el registro en base de datos.
- **Archivos afectados:**
  - `App/Controllers/UsuarioController.php`
- **Criterio de aceptación:**
  - Si `$resultadoValidacion['esValido'] === false`, debe interrumpirse la ejecución, establecer mensajes flash de error y redirigir con `redirect('/usuarios/crear')`.
  - Los errores deben mostrarse en la vista del formulario.

---

### 📌 BUG-03: Corrección de variables de entorno en `ApiDecolectaService` ✅ [RESUELTO]
- **Problema:** En [ApiDecolectaService.php](file:///c:/laragon/www/mesa-partes-lurin/App/Services/ApiDecolectaService.php), el constructor tiene invertidas o mal mapeadas las variables `.env`:
  - `API_DECOLECTA_TOKEN` vs `API_DECOLECTA_SERVER`.
- **Archivos afectados:**
  - `App/Services/ApiDecolectaService.php`
  - `.env.example` / `.env`
- **Criterio de aceptación:**
  - El token debe asignarse a `$this->token` y la URL base a `$this->baseUrl`.
  - La consulta a DNI (8 dígitos) y RUC (11 dígitos) debe enviar la cabecera `Authorization: Bearer <TOKEN>` a la URL correcta.

---

### 📌 BUG-04: Namespace y conexión en `Documento.php` ✅ [RESUELTO]
- **Problema:** En [Documento.php](file:///c:/laragon/www/mesa-partes-lurin/App/Models/Documento.php), se invoca `Conexion::getConexion()` sin tener el `use Src\Core\Conexion;` importado en el namespace `App\Models`.
- **Archivos afectados:**
  - `App/Models/Documento.php`
- **Criterio de aceptación:**
  - Agregar `use Src\Core\Conexion;` o calificar como `\Src\Core\Conexion::getConexion()`.
  - Evitar errores de tipo `Class 'App\Models\Conexion' not found`.

---

## 4. Fase 2: Limpieza Arquitectónica y Estandarización

### 📌 REF-01: Renombrar clase y modelo `ConsutlaExpediente`
- **Problema:** El archivo [ConsutlaExpediente.php](file:///c:/laragon/www/mesa-partes-lurin/App/Models/ConsutlaExpediente.php) contiene una errata ortográfica en el nombre de archivo y en la definición de la clase (`ConsutlaExpediente`).
- **Archivos afectados:**
  - `App/Models/ConsutlaExpediente.php` ➔ Renombrar a `App/Models/ConsultaExpediente.php`
  - `App/Controllers/ConsultaExpedienteController.php` (actualizar llamada e instancia)
- **Criterio de aceptación:**
  - Clase renombrada a `ConsultaExpediente`.
  - Las consultas públicas y privadas de expedientes deben funcionar de forma transparente.

---

### 📌 REF-02: Migrar `DerivacionController` y `DocumentoController` a la arquitectura MVC base
- **Problema:** Ambos controladores no heredan de `Src\Core\Controller` y manejan redirecciones obsoletas estilo `index.php?page=derivaciones` en lugar de utilizar el router centralizado (`Src\Core\Router` y `routes/web.php` o archivos dedicados en `routes/`).
- **Archivos afectados:**
  - `App/Controllers/DerivacionController.php`
  - `App/Controllers/DocumentoController.php`
  - `routes/web.php` / `routes/Router.php`
- **Criterio de aceptación:**
  - Ambos controladores deben extender `Src\Core\Controller`.
  - Utilizar `$this->redirect(...)`, `$this->json(...)`, `$this->view(...)`.
  - Registrar formalmente sus rutas RESTful en el sistema de enrutamiento.

---

### 📌 REF-03: Unificar conexión y configuración de base de datos
- **Problema:** Coexisten dos subsistemas de conexión:
  1. `Src/Core/Conexion.php` + `config/database.php` (usado por la app principal).
  2. `backend/src/core/conexion.php` + `backend/config/database.php` (duplicado/legacy).
- **Archivos afectados:**
  - Directorio `backend/`
  - `autoload.php`
- **Criterio de aceptación:**
  - Eliminar o archivar la carpeta legacy `backend/` si no aporta funcionalidad única.
  - Asegurar que todo el código use de forma exclusiva `Src\Core\Conexion`.

---

### 📌 REF-04: Deduplicar y optimizar componentes del Dashboard
- **Problema:** Los archivos en `view/pages/dashboard/components/`:
  - `admin_widgets.php` (~114KB)
  - `mesa_parte_widgets.php` (~114KB)
  - `jefe_area_widgets.php` (~114KB)
  - `consultor_widgets.php` (~114KB)  
  Son prácticamente idénticos en un 95%, lo que genera redundancia masiva de código HTML/JS y sobrecarga de mantenimiento.
- **Archivos afectados:**
  - `view/pages/dashboard/components/*.php`
  - `view/pages/dashboard/index.php`
- **Criterio de aceptación:**
  - Extraer los elementos comunes (gráficos Chart.js, tarjetas de resumen, estados de conexión) a componentes parciales reutilizables.
  - Reducir cada archivo de rol a únicamente los widgets específicos de su competencia.

---

### 📌 REF-05: Estandarizar capa de servicios (`UsuarioServices` vs `Usuario`)
- **Problema:** Existe `App\Services\usuarioServices.php` (con minúscula inicial en el nombre de archivo) que duplica transacciones que `UsuarioController` ya ejecuta directamente con el modelo `Usuario`.
- **Archivos afectados:**
  - `App/Services/usuarioServices.php` ➔ `App/Services/UsuarioService.php`
  - `App/Controllers/UsuarioController.php`
- **Criterio de aceptación:**
  - Decidir y homologar: o el controlador orquesta directamente con el modelo (patrón Active Record actual), o delegar la lógica de negocio al servicio de forma coherente. Renombrar a PascalCase.

---

## 5. Fase 3: Seguridad y Robustez

### 📌 SEC-01: Rate Limiting y Bloqueo de Intentos Fallidos en Login
- **Problema:** El endpoint de autenticación no restringe intentos consecutivos, siendo vulnerable a ataques de fuerza bruta.
- **Archivos afectados:**
  - `App/Controllers/AuthController.php`
  - `Src\Core\Session.php`
- **Criterio de aceptación:**
  - Registrar intentos fallidos de login por IP/usuario en sesión o base de datos.
  - Bloquear temporalmente el acceso (ej. 5 intentos = bloqueo de 15 minutos).
  - Notificar al usuario el tiempo restante de bloqueo.

---

### 📌 SEC-02: Política de Contraseñas y Validación CSRF en AJAX
- **Problema:**
  - La contraseña actual solo exige `min:6`.
  - Algunas peticiones AJAX (como búsqueda de personas o consultas rápidas) no validan cabecera o token CSRF.
- **Archivos afectados:**
  - `App/Validators/UsuarioValidator.php`
  - `Src/Core/Controller.php`
  - Vistas y scripts JS (`view/assets/js/`)
- **Criterio de aceptación:**
  - Exigir mínimo 8 caracteres con al menos una mayúscula, un número y un caracter especial para usuarios administrativos.
  - Incorporar validación de token CSRF (`X-CSRF-TOKEN` o `_csrf`) en todas las peticiones POST/PUT/DELETE vía fetch/AJAX.

---

## 6. Fase 4: Completitud Funcional y Rutas Pendientes

### 📌 FEAT-01: Activación de Enlaces Huérfanos en `sidebar.php`
- **Problema:** En [sidebar.php](file:///c:/laragon/www/mesa-partes-lurin/view/includes/sidebar.php), múltiples opciones del menú tienen enlace `href="#"`:
  - *Trazabilidad documentaria / Seguimiento:* `#`
  - *Reportes documentarios:* `#`
  - *Documentos recibidos / atendidos / derivados:* `#`
  - *Mi perfil / Configuración:* `#`
- **Archivos afectados:**
  - `view/includes/sidebar.php`
  - `routes/web.php`
  - Controladores y vistas correspondientes
- **Criterio de aceptación:**
  - Enlazar cada item con su ruta real existente o crear vistas informativas/funcionales para evitar links rotos en la interfaz del usuario.

---

### 📌 FEAT-02: Módulo Visualizador de Auditoría del Sistema
- **Problema:** La tabla `auditoria` registra actividad correctamente en base de datos, pero no existe una vista administrativa para que el rol `ADMINISTRADOR` pueda auditar los registros y eventos del sistema.
- **Archivos afectados:**
  - `App/Controllers/AuditoriaController.php` (nuevo)
  - `view/pages/auditoria/index.php` (nuevo)
  - `routes/web.php`
- **Criterio de aceptación:**
  - Tabla interactiva con DataTables mostrando: Fecha, Usuario, Módulo, Acción, Tabla, IP, y visualizador de datos anteriores/nuevos (JSON).
  - Filtro por fecha, usuario y módulo.

---

## 7. Fase 5: Documentación y Testing

### 📌 DOC-01: Documentación Principal en `readme.md`
- **Problema:** El archivo [readme.md](file:///c:/laragon/www/mesa-partes-lurin/readme.md) en la raíz se encuentra completamente vacío (0 bytes).
- **Archivos afectados:**
  - `readme.md`
- **Criterio de aceptación:**
  - Guía completa de instalación y requisitos (PHP 8.x, Apache mod_rewrite, MySQL).
  - Instrucciones para clonar, `composer install`, importar `db.sql` y configurar `.env`.
  - Credenciales de acceso por defecto de los roles y advertencia de seguridad para cambiarlas.
  - Estructura general de carpetas y enlaces al prospecto y spec.

---

## 8. Criterios de Aceptación Global

Para dar por concluida la estabilización del sistema:
1. **Cero Errores 500 / Fatales:** Todas las rutas CRUD de Usuarios, Personas, Roles, Áreas y Expedientes deben responder exitosamente.
2. **Registro de Expedientes 100% Funcional:** El wizard de 4 pasos de Mesa de Partes Virtual debe completar la transacción y generar el correlativo `EXP-YYYY-XXXXX`.
3. **Consulta de Expedientes:** El ciudadano debe poder consultar el estado y timeline de su expediente en `/consulta` mediante código o documento.
4. **Validaciones Activas:** Ningún formulario debe procesar entradas maliciosas o vacías sin emitir la advertencia correspondiente.

---
*Fin de la especificación técnica.*

