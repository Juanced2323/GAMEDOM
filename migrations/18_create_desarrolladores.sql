USE gamedom_users;

-- Perfil de desarrollador vinculado al usuario existente
CREATE TABLE IF NOT EXISTS `desarrolladores` (
  `id_desarrollador` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario`         VARCHAR(50)  NOT NULL UNIQUE,
  `nombre_empresa`  VARCHAR(100) NOT NULL,
  `sitio_web`       VARCHAR(255) DEFAULT NULL,
  `descripcion`     TEXT         DEFAULT NULL,
  `fecha_registro`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario`)
    REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;