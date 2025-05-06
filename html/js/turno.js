document.addEventListener("DOMContentLoaded", async function () {
    const boton = document.getElementById("btn-finalizar-turno");
    if (!boton) return;
  
    const partidaId = window.PARTIDA_ID;
    const usuarioId = window.USUARIO_ID;
  
    console.log("PARTIDA_ID:", partidaId);
    console.log("USUARIO_ID:", usuarioId);
  
    if (!partidaId || !usuarioId) {
      console.error("Faltan PARTIDA_ID o USUARIO_ID.");
      boton.disabled = true;
      return;
    }
  
    // Verificar si es el turno del usuario actual
    try {
      const response = await fetch("../../php/obtener_turno.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ partidaId })
      });
  
      const data = await response.json();
      console.log("🔁 Respuesta de obtener_turno.php:", data);
  
      if (!data.success) {
        console.error("Error al obtener el turno:", data.message);
        boton.disabled = true;
        return;
      }
      
      const esMiTurno = data.turno === usuarioId;
      boton.disabled = !esMiTurno;
  
      if (!esMiTurno) {
        boton.title = "Espera tu turno";
      } else {
        boton.title = "Finaliza tu turno cuando termines";
      }
  
    } catch (err) {
      console.error("Error al verificar turno:", err);
      boton.disabled = true;
      return;
    }
  
    // Acción al hacer clic
    boton.addEventListener("click", async function () {
      if (boton.disabled) return;
  
      boton.disabled = true;
  
      try {
        const response = await fetch("../../php/finalizar_turno.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ partidaId, usuarioId }),
        });
  
        const data = await response.json();
  
        if (!data.success) {
          console.error("Error al finalizar turno:", data.message);
          boton.disabled = false;
        } else {
          console.log("Turno finalizado. Ahora es el turno de:", data.siguiente);
          boton.title = "Espera tu turno";
        }
      } catch (error) {
        console.error("Error de red:", error);
        boton.disabled = false;
      }
    });
  });
  