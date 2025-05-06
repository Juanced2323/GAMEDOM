USE gamedom_users;


-- 2) Creamos la tabla de suscripciones premium
CREATE TABLE IF NOT EXISTS `premium_users` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `usuario`           VARCHAR(50)     NOT NULL,
  `fecha_alta`        DATE            NOT NULL DEFAULT (CURRENT_DATE()),
  `fecha_expiracion`  DATE            DEFAULT NULL,
  `estado`            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  CONSTRAINT `fk_premium_usuario` 
    FOREIGN KEY (`usuario`) REFERENCES `usuarios`(`usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `ux_premium_usuario` (`usuario`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

COMMIT;