# Manual de usuario — Sistema de Trámite Documentario y Mesa de Partes

**Municipalidad de Lurín** · Versión 2026

Manual general aplicable a todos los roles. Los manuales específicos por rol complementan este documento.

---

## 1. Acceso al sistema

### 1.1 Ingreso
1. Abra el navegador y acceda a la URL del sistema (ej. `http://localhost/mesa-partes-lurin`).
2. Ingrese su **usuario** y **contraseña** y pulse **Iniciar sesión**.

![Pantalla de login](capturas/00-login.png)

> Si olvidó su contraseña, use el enlace **"¿Olvidaste tu contraseña?"** en la misma pantalla. Recibirá un correo con un enlace para restablecerla (válido por 1 hora).

### 1.2 Recuperar contraseña
1. Pulse **"¿Olvidaste tu contraseña?"**.
2. Ingrese el correo asociado a su cuenta.
3. Revise su bandeja de entrada y abra el enlace recibido.
4. Ingrese la nueva contraseña (mín. 8 caracteres, mayúscula, número y carácter especial) y confírmela.

![Recuperar contraseña](capturas/00-recuperar.png)
![Restablecer contraseña](capturas/00-restablecer.png)

### 1.3 Cerrar sesión
Use la opción **Cerrar sesión** al final del menú lateral (se envía por POST con token de seguridad).

---

## 2. Navegación general

El menú lateral se adapta al rol del usuario. Solo se muestran las opciones permitidas. Secciones comunes:

- **PANEL → Inicio**: dashboard con indicadores.
- **CONSULTAS → Buscar expediente**: consulta por código o número de documento.
- **CONSULTAS → Trazabilidad**: historial completo del expediente.
- **SISTEMA → Mi perfil**: datos personales y cambio de contraseña.
- **SISTEMA → Cerrar sesión**.

![Menú lateral](capturas/00-sidebar.png)

---

## 3. Mi perfil

Acceda desde **SISTEMA → Mi perfil** para ver sus datos y cambiar la contraseña.

![Perfil](capturas/00-perfil.png)

---

## 4. Consultar un expediente

1. Vaya a **CONSULTAS → Buscar expediente**.
2. Ingrese el **código de expediente** (ej. `EXP-2026-00001`) o el **número de documento**.
3. Pulse **Buscar**. Se mostrarán los datos del expediente y su estado actual.

![Consulta de expediente](capturas/00-consulta.png)

También puede consultar escaneando el **código QR** del cargo de recepción, que abre directamente `/consulta/cargo/{codigo}`.

---

## 5. Trazabilidad

1. Vaya a **CONSULTAS → Trazabilidad**.
2. Ingrese el código del expediente.
3. Se mostrará la línea de tiempo con cada movimiento: registro, derivaciones, recepciones, atenciones, observaciones y archivo.

![Trazabilidad](capturas/00-trazabilidad.png)

---

## 6. Notificaciones

El ícono de campana en la barra superior muestra las notificaciones pendientes. Pulse para ver el detalle y marcarlas como leídas.

![Notificaciones](capturas/00-notificaciones.png)

---

## 7. Instalación inicial (solo primera vez)

Si el sistema no está instalado, al acceder se muestra el asistente de instalación:

1. **Requisitos**: verificación de PHP, extensiones y permisos.
2. **Base de datos**: host, nombre, usuario y contraseña; opción de crear la BD.
3. **Entorno**: URL de la aplicación, zona horaria, correo y token Decolecta.
4. **Administrador**: creación del primer usuario con rol Administrador.

![Asistente de instalación](capturas/00-install.png)

Una vez finalizado, las rutas `/install` quedan deshabilitadas automáticamente.

---

## 8. Consideraciones de seguridad
- No comparta su contraseña.
- Cierre sesión al terminar, especialmente en equipos compartidos.
- La sesión se bloquea tras 5 intentos fallidos por 15 minutos.