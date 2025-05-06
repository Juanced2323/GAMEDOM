USE `gamedom_users`;

-- --------------------------------------------------------
-- Tabla: premium_skins
--  ► Skins exclusivas que estarán disponibles
--    para los usuarios con suscripción Premium
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `premium_skins` (
  `id_skin`        INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(80)  NOT NULL,
  `descripcion`    TEXT         DEFAULT NULL,
  `imagen`         LONGBLOB     DEFAULT NULL,          -- miniatura / icono
  `archivo_skin`   VARCHAR(255) DEFAULT NULL,          -- ruta o URL al .png / .zip
  `rareza`         ENUM('común','rara','épica','legendaria') NOT NULL DEFAULT 'común',
  `fecha_creado`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo`         TINYINT(1)   NOT NULL DEFAULT 1,

  PRIMARY KEY (`id_skin`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


COMMIT;

