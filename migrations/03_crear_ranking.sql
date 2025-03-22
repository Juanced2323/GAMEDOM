-- 03_create_ranking.sql

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `ranking` (
  `id_ranking` INT AUTO_INCREMENT,
  `id_juego`   INT NOT NULL,
  `usuario`    VARCHAR(50) NOT NULL,
  `elo`        INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ranking`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`usuario`) REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
