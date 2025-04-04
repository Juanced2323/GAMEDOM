-- 07_create_historial_juegos.sql

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `historial_juegos` (
  `id_historial` INT AUTO_INCREMENT,
  `usuario`      VARCHAR(50) NOT NULL,
  `id_juego`     INT NOT NULL,
  `fecha`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historial`),
  FOREIGN KEY (`usuario`) REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

ALTER TABLE historial_juegos 
ADD COLUMN resultado ENUM('victoria','derrota','jugado') NOT NULL DEFAULT 'jugado';
