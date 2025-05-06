-- 04_create_torneos.sql

USE gamedom_users;

-- (Opcional) Eliminar la tabla torneos si ya existe para recrearla
DROP TABLE IF EXISTS `torneos`;

CREATE TABLE `torneos` (
  `id_torneo`     INT AUTO_INCREMENT,
  `id_juego`      INT NOT NULL,
  `nombre_torneo` VARCHAR(100) NOT NULL,
  `fecha_inicio`  DATE DEFAULT NULL,
  `fecha_fin`     DATE DEFAULT NULL,
  `estado`        VARCHAR(20) DEFAULT 'activo',
  `descripcion`   TEXT DEFAULT NULL,
  `elo_minimo`    INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_torneo`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
