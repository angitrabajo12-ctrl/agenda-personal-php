CREATE DATABASE IF NOT EXISTS agenda_personal
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE agenda_personal;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de contactos
CREATE TABLE contactos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,

    CONSTRAINT fk_contactos_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;