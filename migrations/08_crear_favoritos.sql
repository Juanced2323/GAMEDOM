USE gamedom_users;

CREATE TABLE IF NOT EXISTS `favoritos` (
  `id_favorito` INT AUTO_INCREMENT,
  `usuario`     VARCHAR(50) NOT NULL,
  `id_juego`    INT NOT NULL,
  PRIMARY KEY (`id_favorito`),
  CONSTRAINT fk_fav_user
    FOREIGN KEY (`usuario`) REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_fav_juego
    FOREIGN KEY (`id_juego`) REFERENCES `juegos`(`id_juego`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;;

COMMIT;