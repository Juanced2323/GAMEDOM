-- 17_create_forum_posts.sql
-- Crear tabla de mensajes del foro con soporte de imagen

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `id_post`      INT AUTO_INCREMENT PRIMARY KEY,
  `id_topic`     INT NOT NULL,                  -- clave foránea a forum_topics.id_topic
  `usuario`      VARCHAR(50) NOT NULL,          -- autor del mensaje
  `contenido`    TEXT NOT NULL,                 -- cuerpo del mensaje
  `imagen`       LONGBLOB DEFAULT NULL,         -- imagen adjunta (opcional)
  `fecha_creado` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_forum_posts_topic   FOREIGN KEY (`id_topic`)  REFERENCES `forum_topics`(`id_topic`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_forum_posts_usuario FOREIGN KEY (`usuario`)   REFERENCES `usuarios`(`usuario`)     ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;