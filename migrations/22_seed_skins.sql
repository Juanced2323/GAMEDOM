-- 2) Insertamos 5 skins de ejemplo

USE gamedom_users;

INSERT INTO premium_skins
  (nombre, descripcion, imagen, archivo_skin, rareza)
VALUES
  ('Wallpaper Neon',
   'Un fondo de neón vibrante para tu perfil.',
   LOAD_FILE('/var/www/html/images/wallpaper3.png'),
   'images/wallpaper3.png',
   'épica'),

  ('Amanecer Pixelado',
   'Pixel-art retro con tonos cálidos.',
   LOAD_FILE('/var/www/html/images/wallpaper4 (1).jpg'),
   'images/wallpaper4 (1).jpg',
   'común'),

  ('Ciber-Océano',
   'Un mar digital lleno de bits y luz.',
   LOAD_FILE('/var/www/html/images/wallpaper4 (2).jpg'),
   'images/wallpaper4 (2).jpg',
   'rara'),

  ('Bosque Encantado',
   'Dicen que esconde logros secretos…',
   LOAD_FILE('/var/www/html/images/wallpaper4 (3).jpg'),
   'images/wallpaper4 (3).jpg',
   'legendaria'),

  ('Horizonte Urbano',
   'Skyline nocturno con estética synth-wave.',
   LOAD_FILE('/var/www/html/images/wallpaper4 (4).jpg'),
   'images/wallpaper4 (4).jpg',
   'épica');
