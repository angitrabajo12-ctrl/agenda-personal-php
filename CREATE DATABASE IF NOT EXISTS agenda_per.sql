CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nombre)
VALUES
    ('Familia'),
    ('Amigos'),
    ('Trabajo'),
    ('Universidad'),
    ('Otro')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Agregar categoría si todavía no existe.
SET @existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'contactos'
      AND COLUMN_NAME = 'id_categoria'
);

SET @sentencia = IF(
    @existe = 0,
    'ALTER TABLE contactos ADD COLUMN id_categoria INT UNSIGNED NULL',
    'SELECT 1'
);

PREPARE migracion FROM @sentencia;
EXECUTE migracion;
DEALLOCATE PREPARE migracion;

-- Agregar favorito si todavía no existe.
SET @existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'contactos'
      AND COLUMN_NAME = 'favorito'
);

SET @sentencia = IF(
    @existe = 0,
    'ALTER TABLE contactos ADD COLUMN favorito TINYINT UNSIGNED NOT NULL DEFAULT 0',
    'SELECT 1'
);

PREPARE migracion FROM @sentencia;
EXECUTE migracion;
DEALLOCATE PREPARE migracion;

-- Agregar fecha si todavía no existe.
SET @existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'contactos'
      AND COLUMN_NAME = 'fecha_creacion'
);

SET @sentencia = IF(
    @existe = 0,
    'ALTER TABLE contactos ADD COLUMN fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    'SELECT 1'
);

PREPARE migracion FROM @sentencia;
EXECUTE migracion;
DEALLOCATE PREPARE migracion;

-- Asignar categoría a los contactos anteriores.
UPDATE contactos
SET id_categoria = (
    SELECT id FROM categorias WHERE nombre = 'Otro'
)
WHERE id_categoria IS NULL;

ALTER TABLE contactos
MODIFY COLUMN id_categoria INT UNSIGNED NOT NULL;

-- Relacionar contactos con categorías.
SET @existe = (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'contactos'
      AND COLUMN_NAME = 'id_categoria'
      AND REFERENCED_TABLE_NAME = 'categorias'
);

SET @sentencia = IF(
    @existe = 0,
    'ALTER TABLE contactos ADD CONSTRAINT fk_contactos_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id)',
    'SELECT 1'
);

PREPARE migracion FROM @sentencia;
EXECUTE migracion;
DEALLOCATE PREPARE migracion;
