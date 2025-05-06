document.addEventListener("DOMContentLoaded", () => {
    fetch("php/obtener_partidas.php")
      .then(response => response.json())
      .then(partidas => {
        const contenedor = document.getElementById("partidas-lista");
        contenedor.innerHTML = ""; // Limpia el mensaje "Cargando..."
  
        if (partidas.length === 0) {
          contenedor.innerHTML = "<p>No tienes partidas activas.</p>";
          return;
        }
  
        partidas.forEach(p => {
          const div = document.createElement("div");
          div.className = "partida-item";
  
          const usuario = window.USUARIO_ID ?? "usuario";
  
          div.innerHTML = `
            <strong>${p.nombre_juego}</strong><br>
            Partida #${p.partida_id} – ¿Es tu turno?: 
            <span style="color:${p.es_tu_turno === 'Sí' ? 'green' : 'gray'}">${p.es_tu_turno}</span><br>
            <a href="games/${p.nombre_juego.toLowerCase()}/index.html?partida_id=${p.partida_id}&usuario_id=${encodeURIComponent(usuario)}"
               class="btn btn-secundario" style="margin-top: 5px;">
              Reanudar
            </a>
          `;
          contenedor.appendChild(div);
        });
      })
      .catch(error => {
        console.error("Error al obtener partidas:", error);
        document.getElementById("partidas-lista").innerHTML = "<p>Error al cargar partidas.</p>";
      });
  });
  