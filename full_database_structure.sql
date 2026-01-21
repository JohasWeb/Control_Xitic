-- Archivo: full_database_structure.sql
-- Descripción: Estructura COMPLETA del sistema (Core + Módulo Encuestas)
-- INCLUYE: Creación de BD desde cero y Usuario Verificado.

DROP DATABASE IF EXISTS `appXitic_ControlMaster`;
CREATE DATABASE `appXitic_ControlMaster` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `appXitic_ControlMaster`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ==============================================================================
-- MÓDULO CORE: CLIENTES, MARCAS, USUARIOS Y SESIONES
-- ==============================================================================

-- -----------------------------------------------------
-- Tabla: clientes
-- Empresas que contratan el servicio
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_comercial` VARCHAR(100) NOT NULL,
  `razon_social` VARCHAR(150) DEFAULT NULL,
  `rfc_tax_id` VARCHAR(50) DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: marcas
-- Marcas pertenecientes a un cliente (Ej: Restaurante A, Restaurante B)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_marca_cliente` (`cliente_id`),
  CONSTRAINT `fk_marca_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `clientes` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: sucursales
-- Tiendas físicas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `marca_id` INT(11) DEFAULT NULL,
  `region` VARCHAR(50) DEFAULT NULL,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre de sucursal',
  `direccion` TEXT DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `idx_sucursal_cliente` (`cliente_id`),
  CONSTRAINT `fk_sucursal_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `clientes` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: usuarios
-- Usuarios con acceso al panel administrativo
-- CAMBIO: 'usuario' ahora es 'email'
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) DEFAULT NULL COMMENT 'NULL para AdminMaster (Superadmin)',
  `email` VARCHAR(100) NOT NULL COMMENT 'Correo electrónico para login',
  `contrasena_hash` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) DEFAULT NULL,
  `rol` ENUM('AdminMaster', 'ClienteAdmin', 'Regional', 'Gerente', 'AdminMarca') NOT NULL DEFAULT 'Gerente',
  `estado` ENUM('Activo', 'Inactivo', 'Suspendido') DEFAULT 'Activo',
  `ultimo_acceso` DATETIME DEFAULT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_email_unico` (`email`),
  INDEX `idx_usuario_cliente` (`cliente_id`),
  CONSTRAINT `fk_usuario_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `clientes` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: sesiones
-- Registro de actividad y seguridad (LoginModel)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sesiones` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) DEFAULT NULL,
  `email_texto` VARCHAR(100) DEFAULT NULL COMMENT 'Email intentado',
  `tipo_evento` VARCHAR(20) DEFAULT 'LOGIN',
  `exito_login` TINYINT(1) DEFAULT 0,
  `motivo` TEXT DEFAULT NULL,
  `intentos_fallidos_consecutivos` INT(11) DEFAULT 0,
  `bloqueo_activo` TINYINT(1) DEFAULT 0,
  `fecha_bloqueo` DATETIME DEFAULT NULL,
  `bloqueo_motivo` VARCHAR(100) DEFAULT NULL,
  `ip_remota` VARCHAR(45) DEFAULT NULL,
  `ip_reenviada` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `host` VARCHAR(100) DEFAULT NULL,
  `uri` TEXT DEFAULT NULL,
  `metodo` VARCHAR(10) DEFAULT NULL,
  `protocolo` VARCHAR(10) DEFAULT NULL,
  `https` TINYINT(1) DEFAULT 0,
  `puerto` INT(11) DEFAULT NULL,
  `sesion_php` VARCHAR(100) DEFAULT NULL,
  `huella_sha256` VARCHAR(64) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_sesiones_email_ip` (`email_texto`, `ip_remota`),
  INDEX `idx_fecha_registro` (`fecha_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- MÓDULO ENCUESTAS (QR PÚBLICO)
-- ==============================================================================

-- -----------------------------------------------------
-- Tabla: encuestas
-- Configuración de la encuesta
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `encuestas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `marca_id` INT(11) DEFAULT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `fecha_inicio` DATETIME DEFAULT NULL,
  `fecha_fin` DATETIME DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT 0 COMMENT '0=Borrador, 1=Activa, 2=Cerrada',
  `creado_por` INT(11) DEFAULT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_encuesta_cliente`
    FOREIGN KEY (`cliente_id`)
    REFERENCES `clientes` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: encuestas_preguntas
-- Estructura de la encuesta
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `encuestas_preguntas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `encuesta_id` INT(11) NOT NULL,
  `texto_pregunta` TEXT NOT NULL,
  `tipo_pregunta` ENUM('texto', 'numero', 'calificacion_5', 'si_no', 'opcion_unica', 'opcion_multiple') NOT NULL DEFAULT 'texto',
  `orden` INT(11) DEFAULT 0,
  `requerido` TINYINT(1) DEFAULT 0,
  `opciones_json` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_preguntas_encuesta`
    FOREIGN KEY (`encuesta_id`)
    REFERENCES `encuestas` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: sucursales_qr
-- Tokens de acceso para comensales
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursales_qr` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sucursal_id` INT(11) NOT NULL,
  `mesa` VARCHAR(50) DEFAULT NULL,
  `token_unico` VARCHAR(64) NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_token_unico` (`token_unico`),
  CONSTRAINT `fk_qr_sucursal`
    FOREIGN KEY (`sucursal_id`)
    REFERENCES `sucursales` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: encuestas_respuestas
-- Resultados (Header)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `encuestas_respuestas` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `encuesta_id` INT(11) NOT NULL,
  `sucursal_qr_id` INT(11) NOT NULL,
  `fecha_respuesta` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ip_cliente` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `dispositivo_tipo` VARCHAR(50) DEFAULT NULL,
  `latitud` DECIMAL(10, 8) DEFAULT NULL,
  `longitud` DECIMAL(11, 8) DEFAULT NULL,
  `contacto_opcional` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_respuestas_encuesta`
    FOREIGN KEY (`encuesta_id`)
    REFERENCES `encuestas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_respuestas_qr`
    FOREIGN KEY (`sucursal_qr_id`)
    REFERENCES `sucursales_qr` (`id`)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: encuestas_respuestas_detalle
-- Resultados (Detalle)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `encuestas_respuestas_detalle` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `respuesta_id` BIGINT(20) NOT NULL,
  `pregunta_id` INT(11) NOT NULL,
  `valor_respuesta` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_detalle_respuesta`
    FOREIGN KEY (`respuesta_id`)
    REFERENCES `encuestas_respuestas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_pregunta`
    FOREIGN KEY (`pregunta_id`)
    REFERENCES `encuestas_preguntas` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- DATOS INICIALES (SEMILLA)
-- ==============================================================================

-- 1. Cliente por defecto (Demo)
INSERT INTO `clientes` (`id`, `nombre_comercial`, `razon_social`) VALUES
(1, 'Empresa Demo', 'Demo SA de CV');

-- 2. Sucursal Demo
INSERT INTO `sucursales` (`id`, `cliente_id`, `nombre`, `region`) VALUES
(1, 1, 'Sucursal Centro', 'Norte');

-- 3. Usuario AdminMaster (AHORA CON EMAIL)
-- Email: admin@xitic.com
-- Contraseña: password123 (hash bcrypt REAL: $2y$12$7glvVeWJ5GTKeUAhlUGyme33oC0pLDoHaHWZteGfNsj4LvUAE1U3e)

INSERT INTO `usuarios` (`id`, `cliente_id`, `email`, `contrasena_hash`, `nombre`, `apellido`, `rol`, `estado`) VALUES
(1, NULL, 'admin@xitic.com', '$2y$12$7glvVeWJ5GTKeUAhlUGyme33oC0pLDoHaHWZteGfNsj4LvUAE1U3e', 'Administrador', 'Sistema', 'AdminMaster', 'Activo');

SET FOREIGN_KEY_CHECKS = 1;
