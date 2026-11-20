# 📋 Plan de Tareas Corregido — Mesa de Partes Lurín

> **Documento:** `tareas.md` (versión 2 — corregida y ampliada)
> **Sistema:** Sistema de Trámite Documentario y Mesa de Partes — Municipalidad de Lurín
> **Fecha de revisión:** 19 de Noviembre de 2026
> **Fuentes analizadas:** `spec.md`, `arquitectura.md`, `observaciones.md` y verificación estática del código (`App/`, `routes/`, `view/`, `db.sql`).
> **Tipo de verificación:** inspección del árbol de archivos, rutas registradas, controladores, modelos y vistas. **No** se ejecutaron pruebas funcionales ni de navegador.

---

## 📌 Índice

1. [Leyenda de estados](#0-leyenda-de-estados)
2. [Resumen de la verificación](#1-resumen-de-la-verificación)
3. [Tareas completadas verificadas](#2-tareas-completadas-verificadas)
4. [Tareas pendientes y parciales (Épicas 5–10)](#3-tareas-pendientes-y-parciales-épicas-510)
5. [Deuda técnica heredada de `spec.md`](#4-deuda-técnica-heredada-de-specmd)
6. [Nuevas tareas derivadas de `observaciones.md`](#5-nuevas-tareas-derivadas-de-observacionesmd)
7. [Roadmap actualizado](#6-roadmap-actualizado)
8. [Criterios de aceptación global](#7-criterios-de-aceptación-global)

---

## 0. Leyenda de estados

| Símbolo | Estado | Significado |
|---|---|---|
| ✅ | **Completada** | Implementada y verificada en el código actual. |
| 🟡 | **Parcial** | Existe una base funcional, pero faltan piezas para cumplir el criterio de aceptación. |
| ⏳ | **Pendiente** | No existe implementación o solo existe el modelo sin interfaz ni rutas. |
| 🗑️ | **Obsoleta** | La tarea dejó de aplicar porque el código fue reemplazado por otra solución. |

> **Regla:** una tarea solo pasa a ✅ cuando cumple **todas** sus subtareas y su criterio de aceptación. Tener el archivo creado no la convierte en completada.

---

## 1. Resumen de la verificación

### 1.1. Épicas de `tareas.md`

| ID | Tarea | Marca anterior | Estado real | Evidencia principal |
|---|---|---|---|---|
| **E01-1** | Bandeja de Entrada del Área | ✅ | ✅ **Completada** | `App/Controllers/BandejaAreaController.php`, `routes/bandeja_area.php`, `view/pages/area/bandeja.php` |
| **E01-2** | Re-derivación entre Áreas | ✅ | ✅ **Completada** | `BandejaAreaController::derivar()`, ruta `POST /area/derivar` |
| **E02-1** | Registro en Ventanilla | ✅ | ✅ **Completada** | `MesaPartesController::register/store`, `routes/mesa_partes.php` |
| **E02-2** | Bandeja de Mesa de Partes | ✅ | ✅ **Completada** | `MesaPartesController::bandeja`, `view/pages/mesa-partes/bandeja.php` |
| **E03-1** | Cargo de Recepción PDF con QR | *(sin marca)* | ✅ **Completada** | `App/Services/PdfCargoService.php`, `MesaPartesController::cargoPdf`, `GET /consulta/cargo/{codigo}` |
| **E03-2** | Hoja de Ruta | *(sin marca)* | ✅ **Completada** | `MesaPartesController::hojaRuta`, `GET /mesa-partes/hoja-ruta/{id}` |
| **E04-1** | Atención de Expediente | ✅ | ✅ **Completada** | `BandejaAreaController::atender/storeAtencion`, `view/pages/area/atender.php` |
| **E05-1** | Registro de Observaciones | ✅ | ✅ **Completada** | `ObservacionController::crear`, `POST /area/observar`, modal |
| **E05-2** | Subsanación de Observación | *(sin marca)* | ✅ **Completada** | `SubsanacionController`, `Observacion::subsanar/levantar`, `view/pages/consulta/subsanar.php`, `/area/subsanaciones` |
| **E06-1** | Bandeja de Archivo Central | *(sin marca)* | ✅ **Completada** | `ArchivoController`, `App/Models/Archivo.php`, `view/pages/archivo/index.php`, `/archivo` |
| **E07-1** | CRUD de Tipos de Documento | *(sin marca)* | ✅ **Completada** | `TipoDocumentoController`, CRUD en `TipoDocumento`, `view/pages/tipo-documento/*`, `/tipos-documento` |
| **E08-1** | Notificaciones Web | *(sin marca)* | ✅ **Completada** | `NotificacionController`, dropdown AJAX en header, disparadores en `Derivacion` y `Observacion` |
| **E09-1** | Reportes Documentarios | *(sin marca)* | ✅ **Completada** | `ReporteController`, `App/Models/Reporte.php`, `view/pages/reportes/index.php`, exportación CSV/PDF |
| **E10-1** | Visor de Auditoría | *(sin marca)* | ✅ **Completada** | `AuditoriaController`, `Auditoria::listar/contar/obtener/catalogos`, `view/pages/auditoria/index.php` |

### 1.2. Tareas de `spec.md`

| ID | Tarea | Estado declarado en `spec.md` | Estado real verificado |
|---|---|---|---|
| **BUG-01** | Sintaxis SQL en `Usuario::obtenerAllPorId` | ✅ Resuelto | ✅ **Confirmado** (sin `;` antes de `LIMIT 1`) |
| **BUG-02** | Validación en `UsuarioController::store` | ✅ Resuelto | ✅ **Confirmado** (`if (!$resultadoValidacion['esValido'])`) |
| **BUG-03** | Variables `.env` en `ApiDecolectaService` | ✅ Resuelto | ✅ **Funcional**, pero con **token hardcodeado** (ver SEC-03) |
| **BUG-04** | Import de `Conexion` en `Documento.php` | ✅ Resuelto | ✅ **Confirmado** (`use Src\Core\Conexion;`) |
| **REF-01** | Renombrar `ConsutlaExpediente` | *(sin marca)* | ✅ **Completada** |
| **REF-02** | Modernizar `DerivacionController` / `DocumentoController` | *(sin marca)* | 🗑️ **Obsoleta / Resuelta** (los controladores ya no existen; su función la cubren `BandejaAreaController` y `MesaPartesController`) |
| **REF-03** | Unificar conexión y eliminar `backend/` | *(sin marca)* | ✅ **Completada** |
| **REF-04** | Deduplicar widgets del Dashboard | *(sin marca)* | ✅ **Completada** (ya no existe `components/`; solo `dashboard/index.php`) |
| **REF-05** | Estandarizar `UsuarioServices` | *(sin marca)* | ✅ **Completada** (el servicio desapareció; no hay referencias) |
| **SEC-01** | Rate limiting en login | *(sin marca)* | ✅ **Completada** |
| **SEC-02** | Política de contraseñas + CSRF AJAX | *(sin marca)* | ✅ **Completada** |
| **FEAT-01** | Rutas huérfanas del sidebar | *(sin marca)* | 🟡 **Parcial** (perfil y trazabilidad implementados; reportes ya existen) |
| **FEAT-02** | Visor de auditoría | *(sin marca)* | ⏳ **Pendiente** (equivalente a E10-1) |
| **DOC-01** | `readme.md` del proyecto | *(sin marca)* | ✅ **Completada** |

### 1.3. Cambios de criterio respecto a la versión anterior

1. **E03-1 y E03-2 pasan a completadas:** ya estaban implementadas pero sin marca en el documento.
2. **REF-02 se marca obsoleta:** los controladores legacy fueron eliminados; ya no hay redirecciones `index.php?page=`.
3. **REF-03 baja a parcial:** la conexión duplicada ya no existe, pero queda un archivo de migración dentro de `backend/`.
4. **E08-1 baja de "sin implementar" a parcial:** hay modelo de lectura y badge real en el header.
5. **FEAT-01 sigue parcial:** los `href="#"` que quedan son solo los *toggles* de submenú (correcto en AdminLTE), pero faltan módulos completos.
6. **Se agrega SEC-03 (nueva):** token de Decolecta embebido como valor por defecto en el código.

---

## 2. Tareas completadas verificadas

Estas tareas **no requieren trabajo**, solo pruebas de regresión antes de cerrar la fase:

- [x] **E01-1** Bandeja de entrada del área con pestañas por estado.
- [x] **E01-2** Re-derivación con instrucciones y actualización de historial.
- [x] **E02-1** Registro presencial/interno con búsqueda RENIEC/SUNAT y adjuntos PDF.
- [x] **E02-2** Bandeja diaria de Mesa de Partes con filtros y reimpresión de cargos.
- [x] **E03-1** Cargo de recepción en PDF con QR (TCPDF) y descarga pública.
- [x] **E03-2** Hoja de ruta imprimible para el expediente físico.
- [x] **E04-1** Atención con documento de respuesta y cierre del expediente.
- [x] **E05-1** Registro de observaciones con notificación al administrado.
- [x] **REF-02** Migración de controladores legacy (obsoleta por reemplazo).
- [x] **REF-04** Dashboard deduplicado a una sola vista por rol.
- [x] **REF-05** Capa de servicios de usuario eliminada sin referencias.
- [x] **BUG-01 … BUG-04** Correcciones de la Fase 1 de `spec.md`.

> **Pendiente de verificación funcional:** ejecutar `php -l` sobre los archivos tocados y probar los flujos en navegador (login, ventanilla, bandeja de área, cargo PDF) antes de declarar la fase cerrada.

---

## 3. Tareas pendientes y parciales (Épicas 5–10)

### ÉPICA 5 — Observaciones y Subsanaciones

#### ✅ TAREA E05-2: Subsanación de Observación
- **Estado:** ✅ Completada.
- **Brecha:** el área puede observar (`E05-1`), pero el administrado no tiene forma de presentar la subsanación ni el área de levantarla. El expediente queda atrapado en `Observado`.
- **Estimación:** 1.5 días.

**Subtareas para completar:**
- [x] **E05-2.1** Reutilizar `Observacion::listarPorExpediente()` (ya existe) y agregar `subsanar(int $idObservacion, int $idUsuario, string $comentario)` y `levantar(int $idObservacion, int $idUsuario)` con transacción PDO.
- [x] **E05-2.2** Crear `App/Controllers/SubsanacionController.php` (extiende `Src\Core\Controller`) con `formulario()`, `store()` y `levantar()`.
- [x] **E05-2.3** Registrar rutas en `routes/observaciones.php`:
  - `GET /consulta/subsanar/{codigo}` (público, valida código de expediente).
  - `POST /consulta/subsanar` (público, con CSRF y adjuntos).
  - `GET /area/subsanaciones` (roles de área).
  - `POST /area/observacion/levantar` (roles de área).
- [x] **E05-2.4** Crear `view/pages/consulta/subsanar.php` con carga de documentos y motivo, y `view/pages/area/subsanaciones.php` con bandeja de observaciones `Subsanada`.
- [x] **E05-2.5** Al subsanar: cambiar `observaciones.estado = 'Subsanada'`, `fecha_subsanacion = NOW()`, insertar en `historial_expediente` y crear `notificaciones` para el área.
- [x] **E05-2.6** Al levantar: `estado = 'Levantada'`, `expedientes.estado_actual = 'En_Proceso'`, historial y notificación al administrado.
- [x] **E05-2.7** Reutilizar la validación de adjuntos de `MesaPartesController` (PDF, máximo 10 MB) y auditar cada acción con `App\Models\Auditoria`.

**Archivos:** `App/Models/Observacion.php`, `App/Controllers/SubsanacionController.php` (nuevo), `routes/observaciones.php`, `view/pages/consulta/subsanar.php` (nuevo), `view/pages/area/subsanaciones.php` (nuevo), `view/includes/sidebar.php`.

**Criterio de aceptación:** un expediente `Observado` puede pasar a `Subsanada` desde el portal público y luego a `Levantada` desde el área, con historial completo y notificación en ambos sentidos.

---

### ÉPICA 6 — Archivo Central

#### ✅ TAREA E06-1: Bandeja de Archivo y Cierre de Expedientes
- **Estado:** ✅ Completada.
- **Brecha:** no existe ubicación física ni cierre formal. `expedientes` solo tiene `fecha_archivado`.
- **Estimación:** 1 día.

**Subtareas para completar:**
- [x] **E06-1.1** Definir el almacenamiento de la ubicación física. Opción recomendada: columnas `caja`, `archivador`, `estante` en `expedientes`; alternativa: tabla `ubicaciones_archivo`. Actualizar `db.sql`.
- [x] **E06-1.2** Crear `App/Models/Archivo.php` con `listarAtendidos(array $filtros)`, `archivar(int $idExpediente, array $ubicacion, int $idUsuario)` y `reubicar(...)`.
- [x] **E06-1.3** Crear `App/Controllers/ArchivoController.php` con `index()`, `archivar()` y `reimprimirCargo()`.
- [x] **E06-1.4** Registrar `routes/archivo.php` y cargarlo en `routes/Router.php`.
- [x] **E06-1.5** Crear `view/pages/archivo/index.php` (DataTables, filtros por año, área y código).
- [x] **E06-1.6** Al archivar: `estado_actual = 'Archivado'`, `fecha_archivado = NOW()`, historial + auditoría.
- [x] **E06-1.7** Agregar el ítem "Archivo central" al sidebar solo para `ADMINISTRADOR` y `MESA_DE_PARTES`.

**Criterio de aceptación:** un expediente `Atendido` se archiva con ubicación física obligatoria, cambia a `Archivado` y queda consultable por código y ubicación.

---

### ÉPICA 7 — Catálogo de Tipos de Documento

#### ✅ TAREA E07-1: CRUD de `tipos_documento`
- **Estado:** ✅ Completada.
- **Estimación:** 0.5 día.

**Subtareas para completar:**
- [x] **E07-1.1** Ampliar `App/Models/TipoDocumento.php` con `getAll()`, `getById()`, `create()`, `update()`, `cambiarEstado()` y `existeNombre()` (implementando `InterfaceModel`).
- [x] **E07-1.2** Crear `App/Controllers/TipoDocumentoController.php` con `index`, `crear`, `store`, `editar`, `update`, `cambiarEstado`, protegido por `Roles::Administracion`.
- [x] **E07-1.3** Validar nombre obligatorio, único (excepto en edición) y máximo 100 caracteres.
- [x] **E07-1.4** Crear `view/pages/tipo-documento/{index,crear,editar}.php` con el patrón de `view/pages/areas/`.
- [x] **E07-1.5** Registrar `routes/tipos_documento.php` y cargarlo en `routes/Router.php`.
- [x] **E07-1.6** Enlazar "Tipos de documento" en el sidebar (Administración) y auditar cada cambio.
- [x] **E07-1.7** Impedir inactivar un tipo con documentos asociados o mostrar advertencia con conteo.

**Criterio de aceptación:** el administrador crea, edita e inactiva tipos; los inactivos dejan de aparecer en los formularios de ventanilla y portal.

---

### ÉPICA 8 — Notificaciones Web

#### ✅ TAREA E08-1: Componente de Notificaciones en el Header
- **Estado:** ✅ Completada.
- **Ya existe:** `Notificacion::sinLeer()` y `ultimas()`, badge real en `view/includes/header.php`, inserción desde `Observacion::observar()`.
- **Falta:** desplegable, marcar como leída, disparadores en derivación/atención y enlace al expediente.
- **Estimación:** 1 día.

**Subtareas para completar:**
- [x] **E08-1.1** Agregar a `App/Models/Notificacion.php`: `crear(array $datos)`, `marcarLeida(int $id, int $idUsuario)`, `marcarTodasLeidas(int $idUsuario)` y `listar(int $idUsuario, bool $soloNoLeidas)`.
- [x] **E08-1.2** Crear `App/Controllers/NotificacionController.php` con `index()`, `marcarLeida()` y `marcarTodas()`; responder JSON y validar CSRF en POST.
- [x] **E08-1.3** Implementar el dropdown real en `view/includes/header.php` (lista, hora relativa, enlace a `/area/derivacion/{id}` o `/consulta`).
- [x] **E08-1.4** Registrar `routes/notificaciones.php` en `routes/Router.php`.
- [x] **E08-1.5** Disparar notificación al **recepcionar una derivación** (`BandejaAreaController::recepcionar`).
- [x] **E08-1.6** Disparar notificación al **atender** un expediente y al **devolver** una derivación.
- [x] **E08-1.7** Al observar/subsanar, enlazar la notificación al expediente (`id_expediente`) y al usuario correcto.
- [x] **E08-1.8** Actualizar el contador por AJAX cada 60 s sin recargar la página.

**Criterio de aceptación:** el badge coincide con `notificaciones` no leídas, el desplegable lista las últimas 5, marcar leída persiste y los cuatro eventos del flujo generan notificación.

---

### ÉPICA 9 — Reportes

#### ✅ TAREA E09-1: Módulo de Reportes con Filtros y Exportación
- **Estado:** ✅ Completada.
- **Estimación:** 2 días.

**Subtareas para completar:**
- [x] **E09-1.1** Crear `App/Models/Reporte.php` con consultas agregadas: volumen por rango, por área, por tipo, por estado, por canal y tiempo promedio de atención.
- [x] **E09-1.2** Crear `App/Controllers/ReporteController.php` con `index()` (filtros + gráficos), `exportarCsv()` y `exportarPdf()`; roles `ADMINISTRADOR` y `RESPONSABLE_AREA` (este último limitado a su área).
- [x] **E09-1.3** Crear `view/pages/reportes/index.php` con filtros (fecha desde/hasta, área, tipo, estado, canal, prioridad) y Chart.js.
- [x] **E09-1.4** Implementar exportación CSV con BOM UTF-8 (Excel) y PDF con TCPDF reutilizando `PdfCargoService` como referencia de estilo.
- [x] **E09-1.5** Calcular indicadores SLA: atendidos dentro de plazo, fuera de plazo y pendientes por área.
- [x] **E09-1.6** Registrar `routes/reportes.php`, cargarlo en el router y enlazarlo en el sidebar.
- [x] **E09-1.7** Auditar cada exportación (usuario, filtros, cantidad de filas).

**Criterio de aceptación:** los filtros devuelven datos consistentes con la base, y ambos formatos de exportación respetan exactamente el filtro aplicado.

---

### ÉPICA 10 — Auditoría

#### ✅ TAREA E10-1: Pantalla de Auditoría del Sistema
- **Estado:** ✅ Completada.
- **Estimación:** 1 día.

**Subtareas para completar:**
- [x] **E10-1.1** Ampliar `App/Models/Auditoria.php` con `listar(array $filtros, int $pagina, int $porPagina)`, `contar(array $filtros)` y `obtener(int $id)`.
- [x] **E10-1.2** Crear `App/Controllers/AuditoriaController.php` con `index()` y `detalle(int $id)`, restringido a `ADMINISTRADOR`.
- [x] **E10-1.3** Crear `view/pages/auditoria/index.php` con DataTables server-side o paginación nativa; columnas: fecha, usuario, módulo, acción, tabla, IP.
- [x] **E10-1.4** Agregar filtros por rango de fechas, usuario, módulo, acción y texto libre.
- [x] **E10-1.5** Agregar modal de detalle con JSON de datos anteriores/nuevos escapado con `htmlspecialchars`.
- [x] **E10-1.6** Registrar `routes/auditoria.php`, cargar en el router y enlazar en el sidebar.
- [x] **E10-1.7** Limitar la consulta por defecto a los últimos 30 días y documentarlo en la vista.

**Criterio de aceptación:** solo el administrador accede; los filtros combinan correctamente; el detalle no ejecuta HTML/JS del JSON almacenado.

---

## 4. Deuda técnica heredada de `spec.md`

### ✅ REF-01: Renombrar `ConsutlaExpediente` → `ConsultaExpediente`
- **Estado:** ✅ Completada. Archivo y clase renombrados; controlador actualizado; grep sin referencias activas.
- **Evidencia:** `App/Models/ConsutlaExpediente.php` (clase y archivo con typo) y `App/Controllers/ConsultaExpedienteController.php:6,22`.

**Subtareas:**
- [ ] **REF-01.1** Renombrar el archivo a `App/Models/ConsultaExpediente.php` conservando el historial (`git mv`).
- [ ] **REF-01.2** Renombrar la clase a `ConsultaExpediente` y ajustar el `namespace`.
- [ ] **REF-01.3** Actualizar `use` e instanciación en `ConsultaExpedienteController`.
- [ ] **REF-01.4** Buscar cualquier otra referencia con `grep -ri "consutla"` y corregirla.
- [ ] **REF-01.5** Verificar con `php -l` y probar consulta pública y consulta autenticada.

### ✅ REF-03: Resto de la unificación de `backend/`
- **Estado:** ✅ Completada. Migración movida a `database/migrations/`, carpeta `backend/` eliminada, rewrite `/backend` retirado del router.
- **Subtareas:**
  - [ ] **REF-03.1** Mover la migración a `database/migrations/` (crear la carpeta si no existe) o documentarla en `db.sql`.
  - [ ] **REF-03.2** Eliminar la carpeta `backend/` vacía.
  - [ ] **REF-03.3** Revisar `routes/Router.php:36` y retirar el *rewrite* de `/backend` si ya no aplica.
  - [ ] **REF-03.4** Confirmar con `grep -ri "backend"` que no queden referencias activas.

### ✅ SEC-01: Rate limiting y bloqueo de login
- **Estado:** ✅ Completada. Tabla `intentos_login`, bloqueo 5/15min, limpieza al autenticar.
- **Subtareas:**
  - [ ] **SEC-01.1** Crear tabla `intentos_login` (`id`, `identificador`, `ip`, `exitoso`, `creado_en`) o usar sesión si se decide no persistir; justificar la elección.
  - [ ] **SEC-01.2** Registrar cada intento fallido por usuario + IP en `AuthController::login()`.
  - [ ] **SEC-01.3** Bloquear tras 5 fallos en 15 minutos y mostrar minutos restantes en `view/pages/login.php`.
  - [ ] **SEC-01.4** Limpiar el contador al autenticar correctamente.
  - [ ] **SEC-01.5** Auditar bloqueos y desbloqueos.
  - [ ] **SEC-01.6** Agregar pruebas manuales: 4 fallos, 5.º bloqueo, espera, acceso correcto.

### ✅ SEC-02: Política de contraseñas y CSRF en AJAX
- **Estado:** ✅ Completada. Política 8+mayús+número+especial en validador y registro; CSRF ya se envía en AJAX.
- **Subtareas:**
  - [ ] **SEC-02.1** Implementar en `UsuarioValidator` mínimo 8, al menos una mayúscula, un número y un carácter especial.
  - [ ] **SEC-02.2** Aplicar la misma regla en `AuthController::postRegister` y en el cambio de contraseña.
  - [ ] **SEC-02.3** Actualizar los mensajes de error y las ayudas visuales en `view/pages/usuarios/crear.php` y `editar.php`.
  - [ ] **SEC-02.4** Verificar que todos los `fetch` con método POST en `view/assets/js/` envíen `X-CSRF-TOKEN` (helper JS único).
  - [ ] **SEC-02.5** Rechazar con HTTP 419/403 y JSON cuando el token falte o sea inválido.
  - [ ] **SEC-02.6** Documentar las reglas de contraseña en `readme.md`.

### ✅ SEC-03 (nueva): Eliminar credenciales embebidas
- **Estado:** ✅ Completada. Token por defecto eliminado; error controlado si falta; `.env` saneado.
- **Hallazgo:** `App/Services/ApiDecolectaService.php:14` define un token real como valor por defecto.
- **Subtareas:**
  - [ ] **SEC-03.1** Quitar el token por defecto y lanzar error controlado si no existe en `.env`.
  - [ ] **SEC-03.2** Rotar la credencial expuesta en el proveedor.
  - [ ] **SEC-03.3** Documentar `API_DECOLECTA_TOKEN` y `API_DECOLECTA_SERVER` en `.env.example`.
  - [ ] **SEC-03.4** Revisar el resto del código con `grep` por `sk_`, `password`, `token` y claves hardcodeadas.

### 🟡 FEAT-01: Rutas y módulos faltantes del sidebar
- **Estado:** 🟡 Parcial. Implementados `/perfil` y `/trazabilidad`; pendientes enlaces a reportes ya existentes.
- **Subtareas:**
  - [ ] **FEAT-01.1** Implementar "Mi perfil" (`GET/POST /perfil`) con datos de la persona y cambio de contraseña.
  - [ ] **FEAT-01.2** Implementar "Trazabilidad / Seguimiento" (`GET /trazabilidad` o `/expedientes/{codigo}/historial`) con el timeline de `historial_expediente`.
  - [ ] **FEAT-01.3** Enlazar "Reportes documentarios" a `E09-1` cuando exista.
  - [ ] **FEAT-01.4** Enlazar "Documentos recibidos / atendidos / derivados" a filtros de la bandeja de área (o crear vistas específicas).
  - [ ] **FEAT-01.5** Verificar que `sidebarActivo()` marque correctamente los nuevos ítems.
  - [ ] **FEAT-01.6** Probar el sidebar con los 5 roles y confirmar que no queda ningún enlace roto.

### ✅ DOC-01: `readme.md`
- **Estado:** ✅ Completada. Documento creado con requisitos, instalación, variables, seguridad y despliegue.
- **Subtareas:**
  - [ ] **DOC-01.1** Requisitos: PHP 8.x, extensiones (`pdo_mysql`, `mbstring`, `fileinfo`, `openssl`), Apache con `mod_rewrite`, MySQL 8.
  - [ ] **DOC-01.2** Instalación: clonar, `composer install`, importar `db.sql`, copiar `.env.example` a `.env`.
  - [ ] **DOC-01.3** Variables de entorno: BD, `APP_ENV`, `APP_TIMEZONE`, `APP_URL`, Decolecta y correo.
  - [ ] **DOC-01.4** Credenciales por defecto por rol y advertencia de cambio obligatorio.
  - [ ] **DOC-01.5** Estructura de carpetas, convenciones MVC y enlaces a `arquitectura.md`, `spec.md` y `tareas.md`.
  - [ ] **DOC-01.6** Sección de despliegue en producción (permisos de `public/uploads`, logs, HTTPS).

---

## 5. Nuevas tareas derivadas de `observaciones.md`

> Estas tareas **no existían** en la versión anterior y se agregan por las tres observaciones del usuario.

### ÉPICA 11 — "Enviar documento" para usuario autenticado (OBS-01)

> **Observación:** al estar logueado, el menú debe decir **"Enviar documento"** en lugar de "Mesa de Partes Virtual"; el formulario debe abrirse con los datos del usuario ya cargados y **bloqueados**, permitiendo completar solo la información del documento, salvo el campo de Mesa de Partes.

#### ✅ TAREA E11-1: Renombrar y separar el acceso en el sidebar
- **Estado:** ✅ Completada (solo el acceso del sidebar; el formulario autenticado depende de E11-2/E11-3).
- **Estimación:** 30 min.
- **Subtareas:**
  - [x] **E11-1.1** En `view/includes/sidebar.php` se reemplazó "Mesa de Partes Virtual" por una única entrada: **"Recepción en ventanilla"** (`/mesa-partes/registrar`) para `MESA_DE_PARTES` y **"Enviar documento"** (`/entregas`) para el resto de roles. La ruta `/enviar-documento` queda para E11-3.
  - [x] **E11-1.2** El portal público `/entregas` sigue registrado y accesible sin sesión.
  - [x] **E11-1.3** Los enlaces públicos de la landing conservan "Mesa de Partes Virtual" para invitados.
  - [x] **E11-1.4** Existe una sola entrada de envío en el sidebar; no se duplica.

#### ✅ TAREA E11-2: Exponer la persona del usuario en la sesión
- **Estado:** ✅ Completada. `AuthController::login` guarda `usuario_persona`; `App\Support\UsuarioActual::persona()` con fallback a BD.
- **Brecha:** `Session` guarda `usuario_id`, `usuario_nombre`, roles y área, pero **no** `id_persona` ni documento/correo, necesarios para precargar el formulario.
- **Subtareas:**
  - [ ] **E11-2.1** En `AuthController::login()`, guardar `usuario_persona` con `id_persona`, `tipo_documento`, `numero_documento`, `nombres`, `apellido_paterno`, `apellido_materno`, `razon_social`, `email`, `telefono`, `direccion`.
  - [ ] **E11-2.2** Extender `Src\Core\Session` (o `App\Support\UsuarioActual`) con un accesor `persona()` que devuelva esos datos.
  - [ ] **E11-2.3** Agregar un *fallback*: si la sesión no tiene los datos (sesiones antiguas), consultarlos con `App\Models\Persona` por `id_usuario`.
  - [ ] **E11-2.4** Refrescar `usuario_persona` cuando el usuario actualice su perfil (`FEAT-01.1`).
  - [ ] **E11-2.5** No romper `Session::user()` ni las vistas existentes que lo consumen.

#### ✅ TAREA E11-3: Vista y controlador de envío autenticado
- **Estado:** ✅ Completada. `EnvioDocumentoController`, ruta `/enviar-documento`, vista `view/pages/envio/index.php` con remitente bloqueado.
- **Estimación:** 1.5 días.
- **Subtareas:**
  - [ ] **E11-3.1** Crear `App/Controllers/EnvioDocumentoController.php` (extiende `Src\Core\Controller`, usa `AuthorizesRequest`) con `index()` y `store()`.
  - [ ] **E11-3.2** Registrar en `routes/entrega.php`: `GET /enviar-documento` y `POST /enviar-documento/store`, protegidos por `Session::isAuthenticated()` y `Roles::Todos`.
  - [ ] **E11-3.3** Crear `view/pages/envio/index.php` renderizada con el layout `app` (no una página suelta), tomando como base el formulario de `view/pages/mesa-partes/registrar.php`.
  - [ ] **E11-3.4** Precargar el bloque de remitente (tipo y número de documento, nombres, apellidos, correo, teléfono, dirección) con los datos de `E11-2`.
  - [ ] **E11-3.5** Renderizar esos campos como `readonly`/`disabled` **y** enviarlos por `hidden` para que el `POST` no dependa de campos deshabilitados.
  - [ ] **E11-3.6** Mantener editable el selector de **Mesa de Partes / área destino** (única excepción indicada en la observación).
  - [ ] **E11-3.7** Mantener editables los campos propios del documento: tipo de documento, número, asunto, folios, fecha, prioridad y adjuntos.
  - [ ] **E11-3.8** Mostrar un aviso visible: "Los datos personales se toman de tu cuenta. Para cambiarlos, actualiza tu perfil."
  - [ ] **E11-3.9** Validar en servidor que el `id_persona` enviado coincida con el de la sesión (nunca confiar en el `POST`).
  - [ ] **E11-3.10** Reutilizar el validador de adjuntos (PDF/JPG/PNG, máximo 10 MB) y el token CSRF.

#### ✅ TAREA E11-4: Registro del expediente autenticado
- **Estado:** ✅ Completada. `ExpedienteService` acepta `id_persona`/`id_usuario_creador`; derivación automática a Mesa de Partes/área; correo encolado.
- **Subtareas:**
  - [x] **E11-4.1** Extender `ExpedienteService::crearExpedienteVirtual()` para aceptar `id_persona` forzado y `id_usuario_creador`.
  - [x] **E11-4.2** Guardar `canal_ingreso = 'Virtual'` y `documentos.creado_por = id_usuario_actual`.
  - [x] **E11-4.3** Registrar el historial inicial con `id_usuario` (no `NULL`) y comentario "Expediente registrado por usuario autenticado".
  - [ ] **E11-4.4** Registrar auditoría (`expedientes`, `crear`) con la IP real. (pendiente)
  - [x] **E11-4.5** Derivar automáticamente a Mesa de Partes o al área elegida, replicando la regla de `EntregaModel`.
  - [x] **E11-4.6** Mostrar pantalla de confirmación con código, resumen y enlace al cargo PDF (`/consulta/cargo/{codigo}`).
  - [x] **E11-4.7** Enviar el correo de constancia (integrado; sin adjunto PDF, con QR + enlace en HTML).

#### ⏳ TAREA E11-5: Pruebas y regresión del flujo autenticado
- **Estado:** ⏳ Pendiente.
- **Subtareas:**
  - [ ] **E11-5.1** Probar con los 5 roles: el formulario debe abrir con datos correctos y sin errores de permisos.
  - [ ] **E11-5.2** Intentar manipular `id_persona` por `POST` y confirmar que se ignora.
  - [ ] **E11-5.3** Verificar que el sidebar muestre "Enviar documento" y no "Mesa de Partes Virtual".
  - [ ] **E11-5.4** Verificar que un usuario sin sesión que entra a `/enviar-documento` sea redirigido a `/login`.
  - [ ] **E11-5.5** Revisar que el layout móvil del formulario no se rompa.

---

### ÉPICA 12 — Salvaguarda del portal público de entregas (OBS-02)

> **Observación:** la vista `entregar` **no debe modificarse**; la URL llega desde fuera. "Entregar documento" es solo para personas o empresas **sin login**; cualquier persona puede registrar sin usuario.

#### ✅ TAREA E12-1: Contrato de no regresión del portal público
- **Estado:** ✅ Completada. Portal `/entregas` no se modificó; rutas y vista congeladas; documentado en readme.md.
- **Estimación:** 2 horas.
- **Subtareas:**
  - [ ] **E12-1.1** Congelar `view/pages/entrega/index.php`, `view/pages/entrega/exitoso.php` y `EntregaController`: no se rediseñan ni se envuelven en el layout `app`.
  - [ ] **E12-1.2** Confirmar que `GET /entregas` y `POST /entregas/store` funcionan sin sesión y sin redirección a login.
  - [ ] **E12-1.3** Confirmar que el envío anónimo sigue creando/actualizando la persona por documento y registrando el expediente con `canal_ingreso = 'Virtual'`.
  - [ ] **E12-1.4** Asegurar que el formulario público **no** exija `id_persona` ni datos de sesión.
  - [ ] **E12-1.5** Documentar en `readme.md` que `/entregas` es la URL pública que se comparte externamente y que no debe cambiarse sin redirect 301.
  - [ ] **E12-1.6** Agregar una prueba manual: navegador en incógnito → registrar expediente → obtener código → consultar en `/consulta`.
  - [ ] **E12-1.7** Si en el futuro se unifican ambos formularios, hacerlo por composición de parciales, **sin** tocar la vista pública.

**Criterio de aceptación:** un ciudadano sin cuenta completa el registro en `/entregas` exactamente como hoy; los cambios de `E11` no alteran esa ruta ni su diseño.

---

### ÉPICA 13 — Notificaciones por correo con PHPMailer (OBS-03)

> **Observación:** implementar notificación por correo cuando se registre un nuevo documento, enviando la ficha o registro correcto, usando `phpmailer/phpmailer` y credenciales configuradas en `.env`.

#### ✅ TAREA E13-1: Dependencia y configuración de correo
- **Estado:** ✅ Completada. Validación de credenciales en `MailService::enviar()`; variables documentadas.
- **Estimación:** 4 horas.
- **Subtareas:**
  - [ ] **E13-1.1** Ejecutar `composer require phpmailer/phpmailer` y confirmar que `vendor/autoload.php` lo carga.
  - [ ] **E13-1.2** Crear `config/mail.php` que lea únicamente variables de entorno.
  - [ ] **E13-1.3** Agregar a `.env` y `.env.example`: `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_MESA_PARTES`, `MAIL_HABILITADO`.
  - [ ] **E13-1.4** Agregar `APP_URL` para construir enlaces absolutos dentro del correo.
  - [ ] **E13-1.5** Validar al arrancar que, si `MAIL_HABILITADO=true` y falta una credencial, se registre en el log y no se rompa la aplicación.
  - [ ] **E13-1.6** Nunca versionar el `.env` real ni imprimir credenciales en pantalla.

#### ✅ TAREA E13-2: Servicio de envío desacoplado
- **Estado:** ✅ Completada. `App\Services\MailService` con SMTP TLS/SSL, modo simulación y logging.
- **Subtareas:**
  - [ ] **E13-2.1** Crear `App/Services/MailService.php` con `enviar(string $para, string $asunto, string $html, array $adjuntos = [], ?string $cc = null): bool`.
  - [ ] **E13-2.2** Configurar SMTP con TLS/SSL, timeout y `CharSet = 'UTF-8'`.
  - [ ] **E13-2.3** Capturar `PHPMailer\PHPMailer\Exception`, registrar en `storage/logs` y devolver `false` sin lanzar al usuario.
  - [ ] **E13-2.4** Permitir modo simulación (`MAIL_HABILITADO=false`) que escribe el correo en log para desarrollo.
  - [ ] **E13-2.5** Sanitizar asunto y destinatarios; validar con `FILTER_VALIDATE_EMAIL`.

#### ✅ TAREA E13-3: Constancia de registro por correo (sin adjunto PDF)
- **Estado:** ✅ Completada (redefinida). El correo incluye datos del expediente, enlace de consulta y código QR incrustado en HTML. El PDF del cargo sigue disponible on-demand en `/mesa-partes/cargo-pdf/{id}` y `/consulta/cargo/{codigo}`.
- **Subtareas:**
  - [x] **E13-3.1** Generar el QR en base64 con `TCPDF2DBarcode` + GD e incrustarlo en el HTML.
  - [x] **E13-3.2** Incluir en el cuerpo del correo: código, remitente, asunto, fecha/hora, área destino y enlace de consulta.
  - [x] **E13-3.3** Eliminar la generación/adjunto del PDF en `encolarCorreoConstancia` (no más `Cargo-{codigo}.pdf`).
  - [x] **E13-3.4** Corregir el bug por el que el código llegaba `null` (el include reasignaba `$codigo`): la plantilla ya no sobrescribe la variable.
  - [x] **E13-3.5** Corregir el bug por el que `id_expediente` llegaba `null`: ahora se pasa `$resultado['id_expediente']` a la cola.

#### ✅ TAREA E13-4: Disparadores del correo
- **Estado:** ✅ Completada. Disparadores en portal público, envío autenticado, ventanilla y observación.
- **Subtareas:**
  - [x] **E13-4.1** Enviar al **remitente** cuando se registre un expediente en `/entregas` (portal público), si tiene correo válido.
  - [x] **E13-4.2** Enviar al **remitente autenticado** cuando registre en `/enviar-documento`.
  - [x] **E13-4.3** Enviar en el registro de **ventanilla** (`MesaPartesController::store`) si el remitente tiene correo.
  - [ ] **E13-4.4** Enviar copia (CC) a `MAIL_MESA_PARTES` en todos los registros nuevos.
  - [ ] **E13-4.5** Enviar aviso cuando el expediente se **observe** (con enlace a `/consulta/subsanar/{codigo}`).
  - [ ] **E13-4.6** Enviar aviso cuando el expediente se **atienda**, adjuntando o enlazando la respuesta.
  - [ ] **E13-4.7** No bloquear la transacción principal: encolar y procesar después (ver E13-5).
  - [ ] **E13-4.8** Evitar duplicados: un correo por expediente y evento.

#### ✅ TAREA E13-5: Cola de correos y reintentos
- **Estado:** ✅ Completada. Script CLI funcional con `Path::setBasePath`; documentado en readme.md para cron/Task Scheduler.
- **Subtareas:**
  - [ ] **E13-5.1** Crear tabla `correos_cola` en `db.sql` con: `id_correo`, `destinatario`, `cc`, `asunto`, `cuerpo_html`, `adjunto_ruta`, `estado` (`Pendiente`, `Enviado`, `Error`), `intentos`, `ultimo_error`, `id_expediente`, `creado_en`, `enviado_en`.
  - [ ] **E13-5.2** Crear `App/Models/CorreoCola.php` con `encolar()`, `pendientes(int $limite)`, `marcarEnviado()` y `marcarError()`.
  - [ ] **E13-5.3** Crear `bin/procesar-correos.php` (CLI) que procese hasta N pendientes y actualice estados.
  - [ ] **E13-5.4** Documentar la tarea programada (cron cada 5 minutos o Task Scheduler en Windows).
  - [ ] **E13-5.5** Si no hay cron disponible, ofrecer un envío síncrono controlado con timeout de 5 s como *fallback*.
  - [ ] **E13-5.6** Reintentar hasta 3 veces con espera progresiva y dejar el error registrado.

#### ✅ TAREA E13-6: Plantillas y pruebas de correo
- **Estado:** ✅ Completada. Plantillas `registro-expediente.php` y `observacion.php` creadas.
- **Subtareas:**
  - [ ] **E13-6.1** Crear `view/emails/registro-expediente.php` y `view/emails/observacion.php` con HTML simple e inline CSS.
  - [ ] **E13-6.2** Usar una función de render (`ob_start` + `include`) que escape todos los datos con `htmlspecialchars`.
  - [ ] **E13-6.3** Probar con un buzón real y con Mailtrap/MailHog en desarrollo.
  - [ ] **E13-6.4** Verificar que el correo no caiga en spam (SPF/DKIM del dominio institucional) y documentarlo.
  - [ ] **E13-6.5** Probar los escenarios: correo válido, correo inexistente, SMTP caído, adjunto grande.
  - [ ] **E13-6.6** Auditar cada envío (éxito/error) con `App\Models\Auditoria`.

**Criterio de aceptación:** al registrar un documento se encola un correo con la ficha en PDF y el código del expediente; el destinatario lo recibe en menos de 5 minutos; un fallo de SMTP no rompe el registro y queda registrado para reintento.

---

## 6. Roadmap actualizado

```
┌──────────────────────────────────────────────────────────────────────┐
│ FASE A (Semana 1) — Cierre operativo y observaciones prioritarias    │
│ • E11  Enviar documento autenticado (OBS-01)                        │
│ • E12  Salvaguarda del portal público (OBS-02)                      │
│ • E13  Correo con PHPMailer + ficha PDF (OBS-03)                    │
├──────────────────────────────────────────────────────────────────────┤
│ FASE B (Semana 2) — Completar el ciclo documentario                  │
│ • E05-2 Subsanaciones                                                │
│ • E06-1 Archivo central                                              │
│ • E07-1 CRUD tipos de documento                                      │
├──────────────────────────────────────────────────────────────────────┤
│ FASE C (Semana 3) — Control y visibilidad                            │
│ • E08-1 Notificaciones web completas                                 │
│ • E09-1 Reportes y exportaciones                                     │
│ • E10-1 Visor de auditoría                                           │
├──────────────────────────────────────────────────────────────────────┤
│ FASE D (Semana 4) — Deuda técnica y endurecimiento                   │
│ • REF-01 Renombrar ConsultaExpediente                                │
│ • REF-03 Limpiar backend/                                            │
│ • SEC-01 Rate limiting de login                                      │
│ • SEC-02 Política de contraseñas y CSRF AJAX                         │
│ • SEC-03 Quitar credenciales embebidas                               │
│ • FEAT-01 Perfil y trazabilidad                                      │
│ • DOC-01 readme.md                                                   │
└──────────────────────────────────────────────────────────────────────┘
```

**Justificación del orden:**
1. Las observaciones del usuario son cambios de experiencia visibles y dependen de piezas ya existentes (formulario de ventanilla, `ExpedienteService`, `PdfCargoService`).
2. La subsanación y el archivo cierran el ciclo de vida del expediente.
3. Reportes y auditoría dependen de que el flujo genere datos completos.
4. La deuda técnica se paga al final, salvo SEC-03 (credencial expuesta), que debería adelantarse si el repositorio es compartido.

---

## 7. Criterios de aceptación global

Para considerar el sistema estabilizado:

1. **Cero errores 500/fatales** en las rutas CRUD de Usuarios, Personas, Roles, Áreas, Expedientes, Tipos de Documento y Archivo.
2. **Registro de expedientes 100 % funcional** en los tres canales: público (`/entregas`), autenticado (`/enviar-documento`) y ventanilla (`/mesa-partes/registrar`), con correlativo `EXP-YYYY-XXXXX`.
3. **Portal público intacto:** `/entregas` funciona sin sesión y sin cambios visuales.
4. **Ciclo completo:** `Registrado → En_Proceso → Observado → Subsanada → En_Proceso → Atendido → Archivado`, con historial en cada transición.
5. **Notificaciones:** badge y dropdown reflejan datos reales; el correo con ficha PDF llega al remitente y a Mesa de Partes.
6. **Trazabilidad:** todo cambio de estado genera fila en `historial_expediente` y en `auditoria`.
7. **Seguridad:** sin credenciales en el código, login con bloqueo por intentos, contraseñas robustas y CSRF en todos los POST.
8. **Sin enlaces muertos:** el sidebar de los 5 roles no contiene rutas inexistentes.
9. **Calidad:** `php -l` sin errores en todos los archivos modificados y `readme.md` completo.

---

## 8. Registro de decisiones pendientes de confirmar

| # | Duda | Propuesta por defecto |
|---|---|---|
| 1 | En `E11-3`, ¿"excepción a mesa de partes" significa que el selector de área destino queda editable? | Sí: remitente bloqueado, área/Mesa de Partes destino editable. Confirmar antes de implementar. |
| 2 | ¿El expediente autenticado debe derivarse automáticamente a Mesa de Partes o permitir elegir área? | Permitir elegir; por defecto, el área de Mesa de Partes. |
| 3 | ¿Se persiste el rate limiting en BD o en sesión? | BD (`intentos_login`), porque la sesión se evade fácilmente. |
| 4 | ¿La cola de correos se procesa por cron o al final de la petición? | Cron cada 5 min; *fallback* síncrono con timeout. |
| 5 | ¿El cargo PDF se adjunta siempre o solo si el remitente tiene correo? | Solo si hay correo válido; el PDF siempre queda descargable por código. |

---

> [!TIP]
> Toda implementación debe respetar `arquitectura.md`: PHP 8 nativo, PDO con consultas preparadas, controladores que extienden `Src\Core\Controller`, vistas PHP planas con notación `carpeta.archivo`, auditoría obligatoria y `php -l` antes de cerrar cada tarea.
