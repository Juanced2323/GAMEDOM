USE gamedom_users;

-- Relación N a N entre desarrolladores y juegos que publiquen
CREATE TABLE IF NOT EXISTS `desarrolladores_juegos` (
  `id_desarrollador` INT NOT NULL,
  `id_juego`         INT NOT NULL,
  PRIMARY KEY (`id_desarrollador`,`id_juego`),
  FOREIGN KEY (`id_desarrollador`)
    REFERENCES `desarrolladores`(`id_desarrollador`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_juego`)
    REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;