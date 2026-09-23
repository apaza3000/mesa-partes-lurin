CREATE TABLE IF NOT EXISTS `tipos_documento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(20) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL,
  `requiere_archivo` TINYINT(1) NOT NULL DEFAULT 1,
  `estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
  `deleted_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `tabla` VARCHAR(50) NOT NULL,
  `registro_id` INT NOT NULL,
  `accion` ENUM('CREAR', 'EDITAR', 'CAMBIAR_ESTADO', 'ELIMINAR') NOT NULL,
  `detalles` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=UTF8MB4_UNICODE_CI;

INSERT INTO `tipos_documento` (`codigo`, `nombre`, `descripcion`, `requiere_archivo`, `estado`) VALUES
('SOL', 'SOLICITUD', 'Solicitud general dirigida a la institución', 1, 1),
('OF', 'OFICIO', 'Documento oficial entre entidades', 1, 1),
('FUT', 'FORMULARIO ÚNICO DE TRÁMITE', 'Formato estándar de atención al ciudadano', 0, 1),
('MEMO', 'MEMORÁNDUM', 'Comunicación interna administrativa', 0, 1);
