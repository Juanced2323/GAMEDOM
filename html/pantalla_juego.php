<?php
/*****************************************************************
 *  PANTALLA DEL JUEGO — BACK-END
 *****************************************************************/
session_start();
require_once "php/db_connect.php";

/* 1.  id del juego ------------------------------------------------*/
if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id_juego = (int)$_GET['id'];

/* 2.  Datos del juego --------------------------------------------*/
$stmt = $conn->prepare("SELECT * FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i",$id_juego); $stmt->execute();
$game = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$game){ echo "Juego no encontrado."; exit(); }

$iconoBase64    = $game['icono']    ? "data:image/jpeg;base64,".base64_encode($game['icono'])    : "images/default-game.png";
$capturesBase64 = $game['capturas'] ? "data:image/jpeg;base64,".base64_encode($game['capturas']) : null;

/* 3.  Categorías --------------------------------------------------*/
$categories=[];
$stmt=$conn->prepare("
  SELECT c.nombre
    FROM juegos_categorias jc
    JOIN categorias c ON c.id_categoria = jc.id_categoria
   WHERE jc.id_juego=?");
$stmt->bind_param("i",$id_juego); $stmt->execute();
$r=$stmt->get_result(); while($row=$r->fetch_assoc()) $categories[]=$row['nombre'];
$stmt->close();

/* 4.  Ranking top-3 ---------------------------------------------*/
$ranking=[];
$stmt=$conn->prepare("SELECT usuario, elo FROM ranking WHERE id_juego=? ORDER BY elo DESC LIMIT 3");
$stmt->bind_param("i",$id_juego); $stmt->execute(); $r=$stmt->get_result();
while($row=$r->fetch_assoc()) $ranking[]=$row; $stmt->close();

/* 5.  Torneos del juego -----------------------------------------*/
$torneos=[];
$stmt=$conn->prepare("
  SELECT nombre_torneo, fecha_inicio, fecha_fin, estado
    FROM torneos
   WHERE id_juego=? ORDER BY fecha_inicio DESC");
$stmt->bind_param("i",$id_juego); $stmt->execute(); $r=$stmt->get_result();
while($row=$r->fetch_assoc()) $torneos[]=$row; $stmt->close();

/* 6.  ¿Favorito? -------------------------------------------------*/
$isFavorite=false;
if (isset($_SESSION['usuario'])){
  $u=$_SESSION['usuario'];
  $stmt=$conn->prepare("SELECT 1 FROM favoritos WHERE usuario=? AND id_juego=?");
  $stmt->bind_param("si",$u,$id_juego); $stmt->execute();
  $isFavorite=$stmt->get_result()->num_rows>0; $stmt->close();
}
$conn->close();
/*****************************************************************/
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($game['nombre']) ?> – GAMEDOM</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!--  estilos generales del sitio  -->
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/catalogo.css">
  <link rel="stylesheet" href="css/achievement.css">
  <!--  iconos (campana, etc.)  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>

  <style>
    /* ─── Ajustes visuales sólo para esta página ─── */
    body            { background:#F9DFBC; }
    .game-wrapper   { max-width:1100px; margin:30px auto; padding:25px;
                      background:#fff; border-radius:8px;
                      box-shadow:0 4px 10px rgba(0,0,0,.1); }
    .header-box     { display:flex; flex-wrap:wrap; gap:25px; align-items:center; }
    .game-icon      { width:180px; height:180px; border-radius:16px; object-fit:cover;
                      box-shadow:0 3px 6px rgba(0,0,0,.25); }
    .game-title     { font-size:2.2rem; color:#7d110d; font-weight:bold; }
    .chip           { display:inline-block; background:#7d110d; color:#F9DFBC;
                      padding:4px 10px; border-radius:20px; font-size:.8rem; margin:4px 6px 0 0; }
    .favorite-container{ margin-top:14px; cursor:pointer; display:flex; align-items:center; }
    .favorite-container img{ width:28px;height:28px;margin-right:8px; }
    .section-block  { margin-top:30px; }
    .section-block h3{ color:#7d110d; margin-bottom:10px; font-size:1.3rem; }
    .ranking p, .tournaments p { margin:4px 0; }

    .manual-button{ background:#36b9cc;color:#fff;border:none;border-radius:6px;
                    padding:8px 25px;font-size:.9rem;cursor:pointer;
                    transition:.25s;margin-top:10px; }
    .manual-button:hover{ background:#2892a3; }

    .play-button { display:block;margin:35px auto 0;
                   background:#f0932b;color:#fff;border:none;border-radius:6px;
                   font-size:1.2rem;padding:12px 40px;cursor:pointer;transition:.25s; }
    .play-button:hover{ background:#d77e16; }

    .screenshots img{ max-width:100%; border-radius:8px;
                      box-shadow:0 2px 6px rgba(0,0,0,.2); }
  </style>
</head>
<body>
<!--──────────  MENÚ SUPERIOR  ──────────-->
<header class="menu-superior">
  <div class="nav-left">
    <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
  </div>
  <div class="nav-right">
    <a href="index.php"      class="nav-item">Inicio</a>
    <a href="biblioteca.php" class="nav-item">Biblioteca</a>
    <a href="comunidad.php"  class="nav-item">Comunidad</a>
    <a href="premios.php"    class="nav-item">Premios</a>
    <?php if(isset($_SESSION['usuario'])):?>
      <a href="perfil.php"   class="nav-item">Perfil</a>
    <?php else:?>
      <a href="login.html"   class="nav-item">Iniciar Sesión</a>
    <?php endif;?>
  </div>
</header>

<!--──────────  CONTENIDO  ──────────-->
<main>
  <div class="game-wrapper">
    <!-- CABECERA -->
    <div class="header-box">
      <img src="<?= $iconoBase64 ?>" class="game-icon" alt="icono">
      <div>
        <div class="game-title"><?= htmlspecialchars($game['nombre']) ?></div>
        <?php foreach($categories as $c): ?>
          <span class="chip"><?= htmlspecialchars($c) ?></span>
        <?php endforeach; ?>

        <?php if(isset($_SESSION['usuario'])): ?>
        <div class="favorite-container" onclick="toggleFavorite(<?= $id_juego ?>)">
          <img id="favoriteIcon"
               src="<?= $isFavorite?'images/star-filled.png':'images/star-outline.png' ?>"
               alt="favorito">
          <span id="favoriteText"><?= $isFavorite?'Quitar de Biblioteca':'Añadir a Biblioteca' ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- DESCRIPCIÓN -->
    <div class="section-block">
      <h3>Descripción</h3>
      <p style="text-align:justify"><?= nl2br(htmlspecialchars($game['descripcion'])) ?></p>
    </div>

    <!-- CAPTURA -->
    <div class="section-block screenshots">
      <h3>Captura</h3>
      <?= $capturesBase64 ? "<img src='$capturesBase64' alt='captura'>" : "<p>No hay capturas disponibles.</p>" ?>
    </div>

    <!-- RANKING -->
    <div class="section-block ranking">
      <h3>Top jugadores</h3>
      <?php if($ranking): foreach($ranking as $k=>$r): ?>
        <p>#<?= $k+1 ?> <strong><?= htmlspecialchars($r['usuario']) ?></strong> – Elo <?= $r['elo'] ?></p>
      <?php endforeach; else: ?>
        <p>No hay ranking disponible para este juego.</p>
      <?php endif; ?>
    </div>

    <!-- TORNEOS -->
    <div class="section-block tournaments">
      <h3>Torneos</h3>
      <?php if($torneos): foreach($torneos as $t): ?>
        <p><?= htmlspecialchars($t['nombre_torneo']) ?> |
           <?= $t['fecha_inicio']??'N/A' ?> – <?= $t['fecha_fin']??'N/A' ?> |
           Estado: <?= htmlspecialchars($t['estado']) ?></p>
      <?php endforeach; else: ?>
        <p>No hay torneos para este juego.</p>
      <?php endif; ?>
    </div>

    <!-- BOTONES -->
    <button class="manual-button"
            onclick="window.open('Manuales/<?= rawurlencode($game['manual']??'manual.pdf') ?>','_blank')">
      Ver manual
    </button>

    <button class="play-button"
            onclick="playGame(<?= $id_juego ?>,'<?= htmlspecialchars($game['ruta_index']) ?>')">
      Jugar Ahora
    </button>
  </div>
</main>

<!--──────────  FOOTER  ──────────-->
<footer class="footer">
  <p>© 2025 GAMEDOM. Todos los derechos reservados.</p>
  <nav>
    <a href="index.php">Inicio</a> |
    <a href="biblioteca.php">Biblioteca</a> |
    <a href="comunidad.php">Comunidad</a> |
    <a href="premios.php">Premios</a> |
    <a href="perfil.php">Perfil</a>
  </nav>
</footer>

<!--──────────  SCRIPTS  ──────────-->
<script>
/* ============  NOTIFICACIONES DE LOGRO  ============ */
function showAchievementNotification(a){
  let c=document.getElementById('achievementContainer');
  if(!c){
    c=document.createElement('div'); c.id='achievementContainer';
    Object.assign(c.style,{position:'fixed',bottom:'20px',right:'20px',zIndex:10000});
    document.body.appendChild(c);
  }
  const n=document.createElement('div');
  Object.assign(n.style,{display:'flex',alignItems:'center',background:'rgba(0,0,0,.9)',
                         color:'#fff',padding:'10px 15px',borderRadius:'8px',
                         marginTop:'10px',opacity:'0',transition:'opacity 1s'});
  n.innerHTML=`<img src="${a.imagen}" style="width:50px;height:50px;border-radius:5px;margin-right:10px;">
               <div><strong>${a.nombre}</strong><br><small>${a.descripcion}</small></div>`;
  c.appendChild(n); requestAnimationFrame(()=>n.style.opacity='1');
  setTimeout(()=>{n.style.opacity='0'; setTimeout(()=>n.remove(),1000)},5000);
}

/* ============  FAVORITOS  ============ */
function toggleFavorite(id){
  const fd=new FormData(); fd.append('id_juego',id);
  fetch('php/toggle_favorite.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>{
      const ic=document.getElementById('favoriteIcon');
      const tx=document.getElementById('favoriteText');
      if(d.status==='added'){
        ic.src='images/star-filled.png'; tx.innerText='Quitar de Biblioteca';
      }else if(d.status==='removed'){
        ic.src='images/star-outline.png'; tx.innerText='Añadir a Biblioteca';
      }
    })
    .catch(()=>alert('Error al actualizar favoritos'));
}

/* ============  JUGAR  ============ */
function playGame(idJuego,rutaIndex){
  const fd=new FormData(); fd.append('juego_id',idJuego);

  /* Paso 1: ranking + logros */
  fetch('php/update_ranking.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      const continuar = () =>{
        /* Paso 2: gestor de partidas */
        fetch('php/gestor_partidas.php',{method:'POST',body:fd})
          .then(r=>r.json())
          .then(p=>{
            if(p.status==='success'){ window.location.href=p.redirect; }
            else{ alert('Error al crear/unirse a partida'); }
          })
          .catch(()=>alert('Error de conexión con gestor de partidas'));
      };

      if(data.status==='success' && data.achievements?.length){
        data.achievements.forEach(showAchievementNotification);
        setTimeout(continuar,6000);
      }else if(data.status==='success'){ continuar(); }
      else{
        alert('Error al actualizar ranking: '+data.message);
        window.location.href=rutaIndex;
      }
    })
    .catch(()=>window.location.href=rutaIndex);
}
</script>
</body>
</html>
