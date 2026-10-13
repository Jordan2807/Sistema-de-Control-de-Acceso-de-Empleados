
CREATE DATABASE IF NOT EXISTS control_asistencia
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE control_asistencia;

-- Usuarios administrativos
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    estado ENUM('Activo', 'Inactivo')
        NOT NULL DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Empleados de la empresa
CREATE TABLE empleados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(25) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(25) DEFAULT NULL,
    codigo_acceso VARCHAR(6)
        CHARACTER SET ascii COLLATE ascii_bin
        NOT NULL UNIQUE,
    estado ENUM('Activo', 'Inactivo')
        NOT NULL DEFAULT 'Activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Registros de entrada y salida
CREATE TABLE asistencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME DEFAULT NULL,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asistencias_empleado
        FOREIGN KEY (empleado_id)
        REFERENCES empleados(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_asistencia_empleado_fecha
        UNIQUE (empleado_id, fecha),

    INDEX idx_asistencia_fecha (fecha),
    INDEX idx_asistencia_empleado (empleado_id)
) ENGINE=InnoDB;
