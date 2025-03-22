-- 05_create_categorias.sql

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` INT AUTO_INCREMENT,
  `nombre`       VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
