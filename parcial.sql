CREATE DATABASE IF NOT EXISTS parcial_alveo
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE parcial_alveo;

SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS bi_perfiles_laborales;
DROP TRIGGER IF EXISTS bu_perfiles_laborales;

DROP TABLE IF EXISTS perfiles_laborales;
DROP TABLE IF EXISTS colaboradores;
DROP TABLE IF EXISTS cat_ocupaciones;
DROP TABLE IF EXISTS cat_tipos_planilla;
DROP TABLE IF EXISTS cat_tipos_empleado;
DROP TABLE IF EXISTS cat_rutas_colaborador;
DROP TABLE IF EXISTS cat_tipos_sangre;
DROP TABLE IF EXISTS inscriptor_temas;
DROP TABLE IF EXISTS inscriptores;
DROP TABLE IF EXISTS areas_interes;
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
('O+'),
('O-'),
('A+'),
('A-'),
('B+'),
('B-'),
('AB+'),
('AB-');

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
('Interino'),
('Temporal');

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
    UNIQUE KEY uk_ocupaciones_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cat_ocupaciones (nombre) VALUES
('Secretaria'),
('Albañil'),
('Ingeniero'),
('Analista'),
('Desarrollador'),
('Asistente'),
('Conductor'),
('Supervisor');

CREATE TABLE areas_interes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_areas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO areas_interes (nombre) VALUES
('Cloud Computing'),
('Big Data'),
('Desarrollo Móvil'),
('Ciberseguridad'),
('IoT (Internet de las Cosas)'),
('Machine Learning'),
('DevOps'),
('Python');

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
    motivo_baja VARCHAR(255) DEFAULT NULL,
    firma_integridad TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_perfiles_colaborador_inicio (colaborador_id, fecha_inicio),
    KEY idx_perfiles_colaborador (colaborador_id),
    KEY idx_perfiles_ocupacion (ocupacion_id),
    KEY idx_perfiles_planilla (planilla_id),
    KEY idx_perfiles_tipo_empleado (tipo_empleado_id),

    CONSTRAINT chk_perfiles_salario CHECK (salario >= 0),

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
    ELSE
        SET NEW.cargo_activo = IFNULL(NEW.cargo_activo, 1);
        SET NEW.empleado_activo = IFNULL(NEW.empleado_activo, 1);
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
    ELSE
        SET NEW.cargo_activo = IFNULL(NEW.cargo_activo, OLD.cargo_activo);
        SET NEW.empleado_activo = IFNULL(NEW.empleado_activo, OLD.empleado_activo);
    END IF;

    IF NEW.motivo_baja IS NOT NULL AND TRIM(NEW.motivo_baja) <> '' THEN
        SET NEW.empleado_activo = 0;
    END IF;
END$$

DELIMITER ;

CREATE TABLE inscriptores (
    id INT AUTO_INCREMENT PRIMARY KEY,

    identidad VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    sexo ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,

    pais_residencia_id INT NOT NULL,
    nacionalidad_id INT NOT NULL,

    correo VARCHAR(150) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    observaciones TEXT,

    firma_integridad TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_inscriptores_identidad (identidad),
    UNIQUE KEY uk_inscriptores_correo (correo),

    CONSTRAINT chk_inscriptores_edad CHECK (edad >= 1 AND edad <= 120),

    CONSTRAINT fk_inscriptores_pais_residencia
        FOREIGN KEY (pais_residencia_id)
        REFERENCES paises(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_inscriptores_nacionalidad
        FOREIGN KEY (nacionalidad_id)
        REFERENCES paises(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inscriptor_temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscriptor_id INT NOT NULL,
    area_interes_id INT NOT NULL,

    UNIQUE KEY uk_inscriptor_area (inscriptor_id, area_interes_id),

    CONSTRAINT fk_inscriptor_temas_inscriptor
        FOREIGN KEY (inscriptor_id)
        REFERENCES inscriptores(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_inscriptor_temas_area
        FOREIGN KEY (area_interes_id)
        REFERENCES areas_interes(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;