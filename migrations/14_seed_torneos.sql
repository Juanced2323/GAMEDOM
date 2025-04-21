-- Archivo: 05_insert_risk_torneos.sql
USE gamedom_users;

-- Obtenemos el id del juego "Risk" y lo asignamos a una variable
SET @risk_id = (SELECT id_juego FROM juegos WHERE nombre = 'Risk' LIMIT 1);

-- Insertamos un único torneo activo para el juego Risk
INSERT INTO torneos (id_juego, nombre_torneo, fecha_inicio, fecha_fin, estado, descripcion, elo_minimo)
VALUES 
(@risk_id, 'Torneo de Risk - Edición Activa', '2025-10-01', '2025-10-08', 'activo', 'Torneo activo de Risk', 0);

COMMIT;
