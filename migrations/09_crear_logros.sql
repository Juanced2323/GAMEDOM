USE gamedom_users;

CREATE TABLE IF NOT EXISTS logros (
    id_logro INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen LONGBLOB -- Icono del logro
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;