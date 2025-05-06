-- 14_crear_amigos.sql

-- Crear tabla amigos

USE gamedom_users;

CREATE TABLE IF NOT EXISTS `amistades` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `solicitante`     VARCHAR(50) NOT NULL, -- nombre de usuario que envía la solicitud
  `receptor`        VARCHAR(50) NOT NULL, -- nombre de usuario que recibe la solicitud
  `estado`          ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
  `fecha_solicitud` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_solicitante FOREIGN KEY (`solicitante`) REFERENCES `usuarios`(`usuario`) ON DELETE CASCADE,
  CONSTRAINT fk_receptor    FOREIGN KEY (`receptor`)    REFERENCES `usuarios`(`usuario`) ON DELETE CASCADE,

  UNIQUE KEY unique_solicitud (`solicitante`, `receptor`) -- evita solicitudes duplicadas
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;