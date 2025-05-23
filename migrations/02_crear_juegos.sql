USE gamedom_users;

CREATE TABLE IF NOT EXISTS `juegos` (
  `id_juego`    INT AUTO_INCREMENT,
  `nombre`      VARCHAR(100) NOT NULL,
  `icono`       LONGBLOB DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `modo_juego`  VARCHAR(50) DEFAULT NULL,
  `capturas`    LONGBLOB DEFAULT NULL,
  `ruta_index`  VARCHAR(255) DEFAULT NULL,
  `max_jugadores` INT NOT NULL DEFAULT 2, 
  PRIMARY KEY (`id_juego`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
