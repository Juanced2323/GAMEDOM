<?php
session_start();  // Inicia la sesión

// Verifica si el usuario está autenticado (por ejemplo, si existe una variable de sesión "usuario")
if (!isset($_SESSION['usuario'])) {
    // Si no hay usuario autenticado, redirige a login
    header("Location: login.html");
    exit();
}

// Si llega hasta aquí, el usuario está autenticado
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GAMEDOM</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="#" class="nav-item">Biblioteca</a>
        <a href="#" class="nav-item">Comunidad</a>
        <a href="#" class="nav-item">Premios</a>
      </div>
      <div class="nav-right">
        <a href="#" class="nav-item">Perfil</a>
      </div>
    </nav>
  </header>
  
  <main>
    <aside class="filter-sidebar">
      <h3>Filtrar Juegos</h3>
      
      <div class="filter-group">
        <label for="search">Buscar:</label>
        <input type="text" id="search" placeholder="Buscar juegos...">
      </div>
      
      <div class="filter-group">
        <h4>Categoría</h4>
        <label><input type="checkbox"> Acción</label>
        <label><input type="checkbox"> Aventura</label>
        <label><input type="checkbox"> Estrategia</label>
        <label><input type="checkbox"> Deportes</label>
      </div>
      
      <div class="filter-group">
        <h4>Género</h4>
        <label><input type="checkbox"> RPG</label>
        <label><input type="checkbox"> Shooter</label>
        <label><input type="checkbox"> Puzzle</label>
        <label><input type="checkbox"> Simulación</label>
      </div>
      
      <div class="filter-group">
		<h4>Modo de Juego</h4>
		<label><input type="checkbox"> Un jugador</label>
		<label><input type="checkbox"> Multijugador</label>
		<label><input type="checkbox"> Ambos</label>
	  </div>

	  <div class="filter-group">
		<h4>Precio</h4>
		<select>
		<option value="all">Todos</option>
		<option value="free">Gratis</option>
		<option value="paid">De pago</option>
		<option value="discount">En oferta</option>
		</select>
	  </div>
    </aside>
    
    <section class="game-catalog">
      <h2>Catálogo de Juegos</h2>
      <div class="game-list">
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 1">
          <h4>Hundir la flota</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 2">
          <h4>Juego 2</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 3">
          <h4>Juego 3</h4>
        </div>
		<div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 1">
          <h4>Juego 4</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 2">
          <h4>Juego 5</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 3">
          <h4>Juego 6</h4>
        </div>
		<div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 1">
          <h4>Juego 7</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 2">
          <h4>Juego 8</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 3">
          <h4>Juego 9</h4>
        </div>
		<div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 1">
          <h4>Juego 10</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 2">
          <h4>Juego 11</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 3">
          <h4>Juego 12</h4>
        </div>
		<div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 1">
          <h4>Juego 13</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 2">
          <h4>Juego 14</h4>
        </div>
        <div class="game-card">
          <img src="images/juego-1.jpeg" alt="Juego 3">
          <h4>Juego 15</h4>
        </div>
      </div>
    </section>
  </main>
</body>
</html>