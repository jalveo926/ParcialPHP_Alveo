CREATE DATABASE IF NOT EXISTS parcial_alveo
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE parcial_alveo;

SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS bi_perfiles_laborales;
DROP TRIGGER IF EXISTS bu_perfiles_laborales;

DROP TABLE IF EXISTS perfiles_laborales;
DROP TABLE IF EXISTS colaboradores;
DROP TABLE IF EXISTS cat_motivos_terminacion;
DROP TABLE IF EXISTS cat_ocupaciones;
DROP TABLE IF EXISTS cat_tipos_planilla;
DROP TABLE IF EXISTS cat_tipos_empleado;
DROP TABLE IF EXISTS cat_rutas_colaborador;
DROP TABLE IF EXISTS cat_tipos_sangre;
DROP TABLE IF EXISTS paises;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE paises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_paises_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO paises (nombre) VALUES
('Panamá'),
('Colombia'),
('Costa Rica'),
('México'),
('Estados Unidos'),
('España'),
('Argentina'),
('Chile'),
('Perú'),
('Venezuela');

CREATE TABLE cat_tipos_sangre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) NOT NULL,
    UNIQUE KEY uk_tipos_sangre_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_tipos_sangre (nombre) VALUES
('O+'), ('O-'), ('A+'), ('A-'), ('B+'), ('B-'), ('AB+'), ('AB-');

CREATE TABLE cat_rutas_colaborador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    UNIQUE KEY uk_rutas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_rutas_colaborador (nombre) VALUES
('Panamá Este'),
('Panamá Oeste'),
('Panamá Norte');

CREATE TABLE cat_tipos_planilla (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    UNIQUE KEY uk_planilla_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_tipos_planilla (nombre) VALUES
('Permanente'),
('Eventual'),
('Interino');

CREATE TABLE cat_tipos_empleado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    UNIQUE KEY uk_tipo_empleado_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_tipos_empleado (nombre) VALUES
('Administrativo'),
('Operativo'),
('Técnico'),
('Gerencial');

CREATE TABLE cat_ocupaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_ocupaciones_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_ocupaciones (nombre, activo) VALUES
('Secretaria', 1),
('Albañil', 1),
('Ingeniero', 1),
('Analista', 1),
('Desarrollador', 1),
('Asistente', 1),
('Conductor', 1),
('Supervisor', 1);

CREATE TABLE cat_motivos_terminacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    UNIQUE KEY uk_motivo_terminacion_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_motivos_terminacion (nombre) VALUES
('Renuncia'),
('Terminación de contrato'),
('Destitución'),
('Jubilación'),
('Abandono del cargo'),
('Evaluación insatisfactoria'),
('Otro');

CREATE TABLE colaboradores (
    codigo_empleado INT AUTO_INCREMENT PRIMARY KEY,
    identidad VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    tipo_sangre_id INT NOT NULL,
    sexo ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    nacionalidad_id INT NOT NULL,
    ruta_colaborador_id INT NOT NULL,
    correo VARCHAR(150) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_colaboradores_identidad (identidad),
    UNIQUE KEY uk_colaboradores_correo (correo),
    UNIQUE KEY uk_colaboradores_celular (celular),

    CONSTRAINT chk_colaboradores_edad CHECK (edad >= 1 AND edad <= 120),
    CONSTRAINT chk_colaboradores_correo CHECK (correo LIKE '%@%.%'),
    CONSTRAINT chk_colaboradores_celular CHECK (CHAR_LENGTH(celular) >= 8),

    CONSTRAINT fk_colaboradores_tipo_sangre
        FOREIGN KEY (tipo_sangre_id)
        REFERENCES cat_tipos_sangre(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_colaboradores_nacionalidad
        FOREIGN KEY (nacionalidad_id)
        REFERENCES paises(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_colaboradores_ruta
        FOREIGN KEY (ruta_colaborador_id)
        REFERENCES cat_rutas_colaborador(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE perfiles_laborales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    tipo_empleado_id INT NOT NULL,
    planilla_id INT NOT NULL,
    ocupacion_id INT NOT NULL,
    salario DECIMAL(12,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    cargo_activo TINYINT(1) NOT NULL DEFAULT 1,
    empleado_activo TINYINT(1) NOT NULL DEFAULT 1,
    motivo_terminacion_id INT DEFAULT NULL,
    motivo_baja VARCHAR(255) DEFAULT NULL,
    firma_integridad TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_perfiles_colaborador_inicio (colaborador_id, fecha_inicio),

    CONSTRAINT chk_perfiles_salario CHECK (salario >= 0),
    CONSTRAINT chk_perfiles_fechas CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),

    CONSTRAINT fk_perfiles_colaborador
        FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores(codigo_empleado)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_perfiles_tipo_empleado
        FOREIGN KEY (tipo_empleado_id)
        REFERENCES cat_tipos_empleado(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_perfiles_planilla
        FOREIGN KEY (planilla_id)
        REFERENCES cat_tipos_planilla(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_perfiles_ocupacion
        FOREIGN KEY (ocupacion_id)
        REFERENCES cat_ocupaciones(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_perfiles_motivo_terminacion
        FOREIGN KEY (motivo_terminacion_id)
        REFERENCES cat_motivos_terminacion(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$

CREATE TRIGGER bi_perfiles_laborales
BEFORE INSERT ON perfiles_laborales
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin IS NOT NULL THEN
        SET NEW.cargo_activo = 0;
        SET NEW.empleado_activo = 0;
    END IF;

    IF NEW.motivo_baja IS NOT NULL AND TRIM(NEW.motivo_baja) <> '' THEN
        SET NEW.empleado_activo = 0;
    END IF;
END$$

CREATE TRIGGER bu_perfiles_laborales
BEFORE UPDATE ON perfiles_laborales
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin IS NOT NULL THEN
        SET NEW.cargo_activo = 0;
        SET NEW.empleado_activo = 0;
    END IF;

    IF NEW.motivo_baja IS NOT NULL AND TRIM(NEW.motivo_baja) <> '' THEN
        SET NEW.empleado_activo = 0;
    END IF;
END$$

DELIMITER ;