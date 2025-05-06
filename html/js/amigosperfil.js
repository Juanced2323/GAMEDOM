document.addEventListener("DOMContentLoaded", () => {
    fetch("php/obtener_amigos.php")
      .then(res => res.json())
      .then(amigos => {
        const contenedor = document.getElementById("amigos-lista");
        contenedor.innerHTML = "";
  
        if (!Array.isArray(amigos) || amigos.length === 0) {
          contenedor.innerHTML = "<p>No tienes amigos aún.</p>";
          return;
        }
  
        amigos.forEach(amigo => {
          const div = document.createElement("div");
          div.className = "amigo-item";
          div.innerHTML = `
            <img src="${amigo.imagen}" alt="${amigo.usuario}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
            <strong>${amigo.usuario}</strong>
          `;
          contenedor.appendChild(div);
        });
      })
      .catch(err => {
        console.error("Error al cargar amigos del perfil:", err);
        document.getElementById("amigos-lista").innerHTML = "<p>Error al cargar amigos.</p>";
      });
  });
  