# Sistema de Trámite Documentario y Mesa de Partes — Municipalidad de Lurín

## Requisitos
- PHP 8.x con extensiones: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `gd`.
- Apache con `mod_rewrite` (o servidor equivalente).
- MySQL 8.x (o MariaDB compatible).
- Composer.

## Instalación
1. Clonar el repositorio.
2. Ejecutar `composer install`.
3. Importar `db.sql` en la base de datos.
4. Copiar `.env.example` a `.env` y completar las variables.
5. Configurar el virtual host apuntando a la carpeta del proyecto.
6. Asegurar permisos de escritura en `public/uploads/` y `storage/logs/`.

## Variables de entorno (.env)
- BD: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`, `DB_CHARSET`.
- `APP_ENV` (local/produccion), `APP_TIMEZONE`, `APP_URL`.
- Decolecta: `API_DECOLECTA_SERVER`, `API_DECOLECTA_TOKEN`.
- Correo (E13): `MAIL_HABILITADO`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`, `MAIL_MESA_PARTES`.

## Credenciales por defecto
Ver `db.sql` (usuarios de ejemplo). Cambiar las contraseñas en producción.

## Estructura
- `App/` — controladores, modelos, servicios, soporte.
- `routes/` — rutas por módulo.
- `view/` — vistas PHP planas.
- `config/` — configuración.
- `public/` — assets y uploads.
- `database/migrations/` — migraciones incrementales.
- `bin/` — scripts CLI (cola de correos).

## Cola de correos
Ejecutar cada 5 minutos (cron / Task Scheduler):
```
php bin/procesar-correos.php
```

## Seguridad
- Sin credenciales en el código (usar `.env`).
- Login con bloqueo por intentos (5 fallos / 15 min).
- Contraseñas: mín. 8 caracteres, mayúscula, número y carácter especial.
- CSRF en todos los POST.

## Documentación
- `spec.md`, `arquitectura.md`, `tareas.md`, `observaciones.md`.

## Despliegue en producción
- HTTPS obligatorio.
- Permisos: `public/uploads` y `storage/logs` escribibles por el servidor web.
- No versionar `.env`.