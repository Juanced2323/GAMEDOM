CREATE TABLE inscripciones_torneo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_torneo INT NOT NULL,
    usuario VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_torneo) REFERENCES torneos(id_torneo) ON DELETE CASCADE,
    FOREIGN KEY (usuario) REFERENCES usuarios(usuario) ON DELETE CASCADE,
    UNIQUE (id_torneo, usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
