-- 13_crear_partidas_usuarios.sql

-- Crear tabla partidas_usuarios

USE gamedom_users;

CREATE TABLE IF NOT EXISTS partidas_usuarios (
    partida_id INT NOT NULL,                           -- ID de la partida
    usuario_id VARCHAR(50) NOT NULL,                    -- ID del usuario
    orden_turno INT NOT NULL,                          -- Orden de turno en la partida
    PRIMARY KEY (partida_id, usuario_id),              -- Clave primaria compuesta
    FOREIGN KEY (partida_id) REFERENCES partidas(id),  -- Relación con la tabla 'partidas'
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario) -- Relación con la tabla 'usuarios'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;