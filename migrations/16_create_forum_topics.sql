-- 16_create_forum_topics.sql
-- Crear tabla de temas del foro

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `forum_topics` (
  `id_topic`     INT AUTO_INCREMENT PRIMARY KEY,
  `id_juego`     INT NOT NULL,                  -- clave foránea a juegos.id_juego
  `usuario`      VARCHAR(50) NOT NULL,          -- autor del tema
  `titulo`       VARCHAR(255) NOT NULL,         -- título del tema
  `contenido`    TEXT NOT NULL,                 -- cuerpo del tema
  `fecha_creado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_forum_topics_juego   FOREIGN KEY (`id_juego`)  REFERENCES `juegos`(`id_juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_forum_topics_usuario FOREIGN KEY (`usuario`)   REFERENCES `usuarios`(`usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;