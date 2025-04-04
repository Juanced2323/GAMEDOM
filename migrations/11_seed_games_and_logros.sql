USE gamedom_users;

-- Insertar los 2 juegos (sin imágenes, se usan NULL o cadenas vacías para capturas)
INSERT INTO juegos (nombre, icono, descripcion, capturas, ruta_index, modo_juego)
VALUES 
  ('Risk', NULL, 'Juego de estrategia y conquista.', NULL, 'games/Risk/index.html', 'Multijugador'),
  ('Hundir la Flota', NULL, 'Reinvención del clásico juego de estrategia naval.', NULL, 'games/HundirLaFlota/index.html', 'Individual');

-- Suponiendo que el primer juego insertado tiene id_juego = 1 y el segundo = 2

-- Insertar los logros globales
INSERT INTO logros (nombre, descripcion, imagen, tipo, id_juego)
VALUES
  ('Primeros Pasos', 'Juega tu primera partida en GAMEDOM', NULL, 'global', NULL),
  ('Milenario', 'Alcanza 1000 puntos de ELO en cualquier juego', NULL, 'global', NULL);

-- Insertar los logros específicos para Risk (id_juego = 1)
INSERT INTO logros (nombre, descripcion, imagen, tipo, id_juego)
VALUES
  ('Novato en Risk', 'Juega tu primera partida en Risk', NULL, 'juego', 1),
  ('Primera Victoria en Risk', 'Gana tu primera partida en Risk', NULL, 'juego', 1);

-- Insertar los logros específicos para Hundir la Flota (id_juego = 2)
INSERT INTO logros (nombre, descripcion, imagen, tipo, id_juego)
VALUES
  ('Novato Naval', 'Juega tu primera partida en Hundir la Flota', NULL, 'juego', 2),
  ('Primera Victoria Naval', 'Gana tu primera partida en Hundir la Flota', NULL, 'juego', 2);
