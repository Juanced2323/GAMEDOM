-- 04_create_torneos.sql

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `torneos` (
  `id_torneo`     INT AUTO_INCREMENT,
  `id_juego`      INT NOT NULL,
  `nombre_torneo` VARCHAR(100) NOT NULL,
  `fecha_inicio`  DATE DEFAULT NULL,
  `fecha_fin`     DATE DEFAULT NULL,
  `estado`        VARCHAR(20) DEFAULT 'activo',
  PRIMARY KEY (`id_torneo`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
