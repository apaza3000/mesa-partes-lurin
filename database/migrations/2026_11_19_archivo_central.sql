-- ============================================================
-- TAREA E06-1: Archivo Central
-- Ubicación física del expediente para la bandeja de archivo.
--
-- Aplicar sobre bases ya creadas con una versión anterior de db.sql:
--   mysql -u USUARIO -p BASE < database/migrations/2026_11_19_archivo_central.sql
--
-- `db.sql` ya incluye estas columnas para instalaciones nuevas.
-- ============================================================

ALTER TABLE `expedientes`
  ADD COLUMN `caja` VARCHAR(50) NULL AFTER `fecha_archivado`,
  ADD COLUMN `archivador` VARCHAR(50) NULL AFTER `caja`,
  ADD COLUMN `estante` VARCHAR(50) NULL AFTER `archivador`;

CREATE INDEX `idx_expediente_archivado` ON `expedientes` (`fecha_archivado`);
CREATE INDEX `idx_expediente_ubicacion` ON `expedientes` (`caja`, `archivador`, `estante`);
