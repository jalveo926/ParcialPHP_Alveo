CREATE DATABASE IF NOT EXISTS parcial_itech
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE parcial_alveo;

CREATE TABLE IF NOT EXISTS paises (
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

CREATE TABLE IF NOT EXISTS areas_interes (
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

CREATE TABLE IF NOT EXISTS inscriptores (
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

    CONSTRAINT chk_edad CHECK (edad >= 1 AND edad <= 120),

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

CREATE TABLE IF NOT EXISTS inscriptor_temas (
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