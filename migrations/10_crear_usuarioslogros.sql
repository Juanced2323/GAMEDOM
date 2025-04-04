USE gamedom_users;

CREATE TABLE IF NOT EXISTS usuarios_logros (
    id_usuario_logro INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    id_logro INT NOT NULL,
    fecha_obtenido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario) REFERENCES usuarios(usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_logro) REFERENCES logros(id_logro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;