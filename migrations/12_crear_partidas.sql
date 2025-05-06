-- 12_crear_partidas.sql

USE gamedom_users;

-- Tabla de partidas
CREATE TABLE IF NOT EXISTS partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,                -- ID de la partida
    juego_id INT NOT NULL,                             -- ID del juego asociado
    estado ENUM('en_progreso', 'terminada') DEFAULT 'en_progreso', -- Estado de la partida
    turno_actual_usuario_id VARCHAR(50) NOT NULL,      -- Usuario que tiene el turno actualmente
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación de la partida
    FOREIGN KEY (juego_id) REFERENCES juegos(id_juego)      -- Relación con la tabla 'juegos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;