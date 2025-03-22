-- 06_create_juegos_categorias.sql

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `juegos_categorias` (
  `id_juego`     INT NOT NULL,
  `id_categoria` INT NOT NULL,
  PRIMARY KEY (`id_juego`, `id_categoria`),
  FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id_categoria`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
