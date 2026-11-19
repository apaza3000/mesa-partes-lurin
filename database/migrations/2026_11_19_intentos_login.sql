-- ============================================================
-- TAREA SEC-01: Rate limiting y bloqueo de login
-- ============================================================
CREATE TABLE IF NOT EXISTS intentos_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    exitoso TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_intentos_ident_ip (identificador, ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;