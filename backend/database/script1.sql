CREATE TABLE `areas` (
  `id_area` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `siglas` varchar(20) NOT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `creado_en` timestamp DEFAULT (now())
);

CREATE TABLE `roles` (
  `id_rol` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255)
);

CREATE TABLE `usuarios` (
  `id_usuario` int PRIMARY KEY AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `id_area` int NOT NULL,
  `id_persona` int NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'assets/img/default-user.png',
  `estado` ENUM ('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  `creado_en` timestamp DEFAULT (now()),
  `update_at` timestamp DEFAULT (now())
);

CREATE TABLE `tipos_documento` (
  `id_tipo_doc` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `estado` varchar(20) DEFAULT 'Activo'
);

CREATE TABLE `documentos` (
  `id_documento` int PRIMARY KEY AUTO_INCREMENT,
  `codigo_unico` varchar(30) UNIQUE NOT NULL COMMENT 'Ej: EXP-2026-0001',
  `id_tipo_doc` int NOT NULL,
  `numero_documento` varchar(100) NOT NULL COMMENT 'Ej: OFICIO N° 012-2026-IESTPL',
  `asunto` varchar(255) NOT NULL,
  `folios` int DEFAULT 1,
  `estado_actual` ENUM ('Registrado', 'En_Proceso', 'Atendido', 'Observado', 'Archivado') DEFAULT 'Registrado',
  `id_persona` int NOT NULL,
  `creado_en` timestamp DEFAULT (now())
);

CREATE TABLE `archivos_adjuntos` (
  `id_archivo` int PRIMARY KEY AUTO_INCREMENT,
  `id_documento` int NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `creado_en` timestamp DEFAULT (now())
);

CREATE TABLE `derivaciones` (
  `id_derivacion` int PRIMARY KEY AUTO_INCREMENT,
  `id_documento` int NOT NULL,
  `id_area_origen` int NOT NULL,
  `id_area_destino` int NOT NULL,
  `id_usuario_envia` int NOT NULL,
  `id_usuario_recibe` int,
  `observaciones` varchar(500),
  `estado_derivacion` varchar(20) DEFAULT 'Pendiente',
  `fecha_envio` timestamp DEFAULT (now()),
  `fecha_recepcion` datetime
);

CREATE TABLE `persona` (
  `id_persoan` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido_P` varchar(100) NOT NULL,
  `apellido_M` varchar(100) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `estado` ENUM ('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  `creado_en` timestamp DEFAULT (now()),
  `update_at` timestamp DEFAULT (now())
);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`);

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_tipo_doc`) REFERENCES `tipos_documento` (`id_tipo_doc`);

ALTER TABLE `archivos_adjuntos` ADD FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`id_documento`);

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`id_documento`);

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_area_origen`) REFERENCES `areas` (`id_area`);

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_area_destino`) REFERENCES `areas` (`id_area`);

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_usuario_envia`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `derivaciones` ADD FOREIGN KEY (`id_usuario_recibe`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persoan`);

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persoan`);
