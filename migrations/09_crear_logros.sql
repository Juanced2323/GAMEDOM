USE gamedom_users;

CREATE TABLE IF NOT EXISTS logros (
    id_logro INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen LONGBLOB -- Icono del logro
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE logros 
ADD COLUMN tipo ENUM('global', 'juego') DEFAULT 'global',
ADD COLUMN id_juego INT DEFAULT NULL,
ADD FOREIGN KEY (id_juego) REFERENCES juegos(id_juego) ON DELETE SET NULL;


COMMIT;