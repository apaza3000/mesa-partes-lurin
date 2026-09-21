CREATE TABLE `personas` (
  `id_persona` INT PRIMARY KEY AUTO_INCREMENT,
  `tipo_persona` ENUM ('Natural', 'Juridica') NOT NULL DEFAULT 'Natural',
  `tipo_documento` ENUM ('DNI', 'CE', 'RUC', 'Pasaporte', 'Otro') NOT NULL DEFAULT 'DNI',
  `numero_documento` VARCHAR(20) NOT NULL,
  `nombres` VARCHAR(100),
  `apellido_paterno` VARCHAR(100),
  `apellido_materno` VARCHAR(100),
  `razon_social` VARCHAR(200),
  `email` VARCHAR(150),
  `telefono` VARCHAR(30),
  `direccion` VARCHAR(255),
  `estado` ENUM ('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `areas` (
  `id_area` INT PRIMARY KEY AUTO_INCREMENT,
  `id_area_padre` INT,
  `nombre` VARCHAR(100) NOT NULL,
  `siglas` VARCHAR(20) NOT NULL,
  `estado` ENUM ('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `roles` (
  `id_rol` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255),
  `estado` ENUM ('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `usuarios` (
  `id_usuario` INT PRIMARY KEY AUTO_INCREMENT,
  `id_persona` INT NOT NULL,
  `id_area` INT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'assets/img/default-user.png',
  `estado` ENUM ('Activo', 'Inactivo', 'Suspendido') NOT NULL DEFAULT 'Activo',
  `ultimo_acceso` DATETIME,
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `usuario_roles` (
  `id_usuario` INT NOT NULL,
  `id_rol` INT NOT NULL,
  PRIMARY KEY (`id_usuario`, `id_rol`)
);

CREATE TABLE `tipos_documento` (
  `id_tipo_doc` INT PRIMARY KEY AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255),
  `estado` ENUM ('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `expedientes` (
  `id_expediente` INT PRIMARY KEY AUTO_INCREMENT,
  `codigo_expediente` VARCHAR(30) NOT NULL,
  `id_remitente` INT NOT NULL,
  `asunto` VARCHAR(500) NOT NULL,
  `prioridad` ENUM ('Normal', 'Urgente') NOT NULL DEFAULT 'Normal',
  `canal_ingreso` ENUM ('Virtual', 'Presencial', 'Interno') NOT NULL DEFAULT 'Virtual',
  `estado_actual` ENUM ('Registrado', 'En_Proceso', 'Observado', 'Atendido', 'Archivado') NOT NULL DEFAULT 'Registrado',
  `fecha_registro` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `fecha_atencion` DATETIME,
  `fecha_archivado` DATETIME,
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `documentos` (
  `id_documento` INT PRIMARY KEY AUTO_INCREMENT,
  `id_expediente` INT NOT NULL,
  `id_tipo_doc` INT NOT NULL,
  `numero_documento` VARCHAR(100),
  `asunto` VARCHAR(500) NOT NULL,
  `folios` INT NOT NULL DEFAULT 1,
  `fecha_documento` DATE,
  `tipo_documento_registro` ENUM ('Ingresado', 'Generado', 'Respuesta') NOT NULL DEFAULT 'Ingresado',
  `es_principal` BOOLEAN NOT NULL DEFAULT false,
  `creado_por` INT,
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `actualizado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `archivos_adjuntos` (
  `id_archivo` INT PRIMARY KEY AUTO_INCREMENT,
  `id_documento` INT NOT NULL,
  `nombre_original` VARCHAR(255) NOT NULL,
  `nombre_archivo` VARCHAR(255) NOT NULL,
  `ruta_archivo` VARCHAR(500) NOT NULL,
  `extension` VARCHAR(20),
  `mime_type` VARCHAR(100),
  `tamanio` BIGINT,
  `hash_archivo` VARCHAR(64),
  `creado_por` INT,
  `creado_en` TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `derivaciones` (
  `id_derivacion` INT PRIMARY KEY AUTO_INCREMENT,
  `id_expediente` INT NOT NULL,
  `id_area_origen` INT NOT NULL,
  `id_area_destino` INT NOT NULL,
  `id_usuario_envia` INT NOT NULL,
  `id_usuario_recibe` INT,
  `observaciones` VARCHAR(1000),
  `estado` ENUM ('Pendiente', 'Recibido', 'En_Atencion', 'Atendido', 'Devuelto') NOT NULL DEFAULT 'Pendiente',
  `fecha_envio` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `fecha_recepcion` DATETIME,
  `fecha_atencion` DATETIME
);

CREATE TABLE `historial_expediente` (
  `id_historial` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `id_expediente` INT NOT NULL,
  `estado_anterior` VARCHAR(30),
  `estado_nuevo` VARCHAR(30) NOT NULL,
  `id_usuario` INT,
  `comentario` VARCHAR(1000),
  `fecha` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `observaciones` (
  `id_observacion` INT PRIMARY KEY AUTO_INCREMENT,
  `id_expediente` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `descripcion` VARCHAR(1000) NOT NULL,
  `estado` ENUM ('Pendiente', 'Subsanada', 'Levantada') NOT NULL DEFAULT 'Pendiente',
  `fecha_registro` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  `fecha_subsanacion` DATETIME
);

CREATE TABLE `notificaciones` (
  `id_notificacion` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` INT,
  `id_persona` INT,
  `id_expediente` INT,
  `titulo` VARCHAR(200) NOT NULL,
  `mensaje` VARCHAR(1000) NOT NULL,
  `leida` BOOLEAN NOT NULL DEFAULT false,
  `fecha_lectura` DATETIME,
  `creado_en` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `numeradores` (
  `id_numerador` INT PRIMARY KEY AUTO_INCREMENT,
  `tipo` VARCHAR(50) NOT NULL,
  `anio` YEAR NOT NULL,
  `ultimo_numero` INT NOT NULL DEFAULT 0
);

CREATE TABLE `auditoria` (
  `id_auditoria` BIGINT PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` INT,
  `modulo` VARCHAR(100) NOT NULL,
  `accion` VARCHAR(50) NOT NULL,
  `tabla_afectada` VARCHAR(100),
  `id_registro` INT,
  `descripcion` VARCHAR(1000),
  `ip` VARCHAR(45),
  `fecha` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP)
);

CREATE UNIQUE INDEX `uk_persona_documento` ON `personas` (`tipo_documento`, `numero_documento`);

CREATE INDEX `idx_personas_email` ON `personas` (`email`);

CREATE UNIQUE INDEX `uk_area_nombre` ON `areas` (`nombre`);

CREATE UNIQUE INDEX `uk_area_siglas` ON `areas` (`siglas`);

CREATE UNIQUE INDEX `uk_rol_nombre` ON `roles` (`nombre`);

CREATE UNIQUE INDEX `uk_usuario_username` ON `usuarios` (`username`);

CREATE UNIQUE INDEX `uk_tipo_documento_nombre` ON `tipos_documento` (`nombre`);

CREATE UNIQUE INDEX `uk_codigo_expediente` ON `expedientes` (`codigo_expediente`);

CREATE INDEX `idx_expediente_remitente` ON `expedientes` (`id_remitente`);

CREATE INDEX `idx_expediente_estado` ON `expedientes` (`estado_actual`);

CREATE INDEX `idx_expediente_fecha` ON `expedientes` (`fecha_registro`);

CREATE INDEX `idx_documento_expediente` ON `documentos` (`id_expediente`);

CREATE INDEX `idx_documento_tipo` ON `documentos` (`id_tipo_doc`);

CREATE INDEX `idx_archivo_documento` ON `archivos_adjuntos` (`id_documento`);

CREATE INDEX `idx_derivacion_expediente` ON `derivaciones` (`id_expediente`);

CREATE INDEX `idx_derivacion_destino` ON `derivaciones` (`id_area_destino`);

CREATE INDEX `idx_derivacion_estado` ON `derivaciones` (`estado`);

CREATE INDEX `idx_historial_expediente` ON `historial_expediente` (`id_expediente`);

CREATE INDEX `idx_historial_fecha` ON `historial_expediente` (`fecha`);

CREATE INDEX `idx_observacion_expediente` ON `observaciones` (`id_expediente`);

CREATE INDEX `idx_observacion_estado` ON `observaciones` (`estado`);

CREATE INDEX `idx_notificacion_usuario` ON `notificaciones` (`id_usuario`);

CREATE INDEX `idx_notificacion_persona` ON `notificaciones` (`id_persona`);

CREATE INDEX `idx_notificacion_expediente` ON `notificaciones` (`id_expediente`);

CREATE UNIQUE INDEX `uk_numerador` ON `numeradores` (`tipo`, `anio`);

CREATE INDEX `idx_auditoria_usuario` ON `auditoria` (`id_usuario`);

CREATE INDEX `idx_auditoria_fecha` ON `auditoria` (`fecha`);

CREATE INDEX `idx_auditoria_modulo` ON `auditoria` (`modulo`);

ALTER TABLE `areas` ADD FOREIGN KEY (`id_area_padre`) REFERENCES `areas` (`id_area`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `usuario_roles` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `usuario_roles` ADD FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `expedientes` ADD FOREIGN KEY (`id_remitente`) REFERENCES `personas` (`id_persona`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_tipo_doc`) REFERENCES `tipos_documento` (`id_tipo_doc`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `documentos` ADD FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `archivos_adjuntos` ADD FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`id_documento`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `archivos_adjuntos` ADD FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_area_origen`) REFERENCES `areas` (`id_area`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_area_destino`) REFERENCES `areas` (`id_area`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_usuario_envia`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_usuario_recibe`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `historial_expediente` ADD FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `historial_expediente` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `observaciones` ADD FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `observaciones` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `notificaciones` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notificaciones` ADD FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notificaciones` ADD FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `auditoria` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Disable foreign key checks for INSERT
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `roles` (`nombre`, `descripcion`)
VALUES
  ('ADMINISTRADOR', 'Administración general del sistema'),
  ('MESA_DE_PARTES', 'Registro y recepción de expedientes'),
  ('RESPONSABLE_AREA', 'Responsable de un área institucional'),
  ('USUARIO_AREA', 'Usuario encargado de atender expedientes'),
  ('CONSULTA', 'Usuario con permisos de consulta');
INSERT INTO `tipos_documento` (`nombre`, `descripcion`)
VALUES
  ('Solicitud', 'Solicitud presentada por el administrado'),
  ('Oficio', 'Oficio institucional'),
  ('Carta', 'Carta'),
  ('Informe', 'Informe técnico o administrativo'),
  ('Memorando', 'Memorando interno'),
  ('Resolución', 'Resolución institucional'),
  ('Otros', 'Otros tipos de documentos');
INSERT INTO `numeradores` (`tipo`, `anio`, `ultimo_numero`)
VALUES
  ('EXPEDIENTE', CAST('2026' AS YEAR), 0);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;