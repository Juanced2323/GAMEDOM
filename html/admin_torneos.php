<?php
session_start();
require_once "php/db_connect.php";

// Obtener juegos para el selector
$juegosQuery = "SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC";
$juegosResult = $conn->query($juegosQuery);

// Obtener todos los torneos para la tabla
$torneosQuery = "
    SELECT t.*, j.nombre AS nombre_juego
    FROM torneos t
    JOIN juegos j ON t.id_juego = j.id_juego
    ORDER BY t.fecha_inicio DESC
";
$torneosResult = $conn->query($torneosQuery);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Administrar Torneos - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_torneos.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item">Inicio</a>
        <a href="torneos.php" class="nav-item">Torneos</a>
        <a href="admin_torneos.php" class="nav-item active">Admin Torneos</a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" class="nav-item">Perfil</a>
        <?php else: ?>
          <a href="login.html" class="nav-item">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="admin-panel">
    <div class="admin-column">
      <section class="torneos-section">
        <h2>Crear nuevo torneo</h2>
        <form action="php/add_torneo.php" method="POST">
          <label for="nombre">Nombre del torneo:</label><br>
          <input type="text" id="nombre" name="nombre" required><br><br>

          <label for="juego">Juego:</label><br>
          <select id="juego" name="id_juego" required>
            <option value="">Seleccione un juego</option>
            <?php while($juego = $juegosResult->fetch_assoc()): ?>
              <option value="<?php echo $juego['id_juego']; ?>">
                <?php echo htmlspecialchars($juego['nombre']); ?>
              </option>
            <?php endwhile; ?>
          </select><br><br>

          <label for="descripcion">Descripción del torneo:</label><br>
          <textarea id="descripcion" name="descripcion" rows="5" cols="50" placeholder="Explica en qué consiste el torneo..."></textarea><br><br>

          <label for="fecha_inicio">Fecha de inicio:</label><br>
          <input type="date" id="fecha_inicio" name="fecha_inicio" required><br><br>

          <label for="fecha_fin">Fecha de fin:</label><br>
          <input type="date" id="fecha_fin" name="fecha_fin" required><br><br>

          <label for="estado">Estado:</label><br>
          <select id="estado" name="estado" required>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select><br><br>

          <label for="elo_minimo">Elo mínimo requerido:</label><br>
          <input type="number" id="elo_minimo" name="elo_minimo" min="0" value="0" required><br><br>

          <label for="max_jugadores">Máximo de jugadores:</label><br>
          <input type="number" id="max_jugadores" name="max_jugadores" min="2" max="6" value="6" required><br><br>

          <button type="submit" class="participar-btn">Crear Torneo</button>
        </form>
      </section>
    </div>

    <div class="admin-column tabla-scroll">
      <section class="torneos-section">
        <h2>Lista de torneos existentes</h2>
        <div class="tabla-contenedor">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Juego</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Elo mín.</th>
                <th>Acciones</th>
                <th>Cupos</th>
              </tr>
            </thead>
            <tbody>
              <?php while($torneo = $torneosResult->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $torneo['id_torneo']; ?></td>
                  <td><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></td>
                  <td><?php echo htmlspecialchars($torneo['nombre_juego']); ?></td>
                  <td><?php echo $torneo['fecha_inicio']; ?></td>
                  <td><?php echo $torneo['fecha_fin']; ?></td>
                  <td><?php echo $torneo['estado']; ?></td>
                  <td><?php echo $torneo['elo_minimo']; ?></td>
                  <td><?php echo $torneo['jugadores_actuales'] . "/" . $torneo['max_jugadores']; ?></td>
                  <td>
                    <button class="action-btn edit-btn" onclick="alert('Función de edición próximamente')">Editar</button>
                    <form action="php/delete_torneo.php" method="POST" style="display:inline;">
                      <input type="hidden" name="id_torneo" value="<?php echo $torneo['id_torneo']; ?>">
                      <button type="submit" class="action-btn delete-btn" onclick="return confirm('¿Seguro que deseas eliminar este torneo?')">Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2025 GAMEDOM. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
