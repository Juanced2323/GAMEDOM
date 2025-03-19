USE gamedom_users;

-- Tabla juegos
CREATE TABLE IF NOT EXISTS `juegos` (
  `id_juego` INT AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `icono` LONGBLOB DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_juego`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla ranking
CREATE TABLE IF NOT EXISTS `ranking` (
  `id_ranking` INT AUTO_INCREMENT,
  `id_juego`   INT NOT NULL,
  `usuario`    VARCHAR(50) NOT NULL,
  `elo`        INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ranking`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  FOREIGN KEY (`usuario`) REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla torneos (opcional)
CREATE TABLE IF NOT EXISTS `torneos` (
  `id_torneo` INT AUTO_INCREMENT,
  `id_juego` INT NOT NULL,
  `nombre_torneo` VARCHAR(100) NOT NULL,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin` DATE DEFAULT NULL,
  `estado` VARCHAR(20) DEFAULT 'activo',
  PRIMARY KEY (`id_torneo`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE juegos ADD COLUMN ruta_index VARCHAR(255) DEFAULT NULL AFTER descripcion;
