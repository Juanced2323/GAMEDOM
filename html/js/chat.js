const socket = io("http://localhost:8090");

const chatBox = document.getElementById("chat-box");
const chatInput = document.getElementById("chat-input");
const sendButton = document.querySelector("#chat-input-container button");
const toggleBtn = document.getElementById("chat-toggle-btn");
const chatContainer = document.getElementById("chat-container");
const userCorreo = document.body.dataset.userCorreo;
const userNombre = document.body.dataset.userNombre;

let isChatFocused = false;

// Registrar identidad del usuario
socket.emit("registrar_usuario", {
  correo: userCorreo,
  nombre: userNombre,
});

// Mostrar mensajes entrantes
socket.on("message", function (data) {
  if (!chatBox || !data.message) return;

  const isOwn = data.correo === userCorreo;
  const messageDiv = document.createElement("div");
  messageDiv.classList.add("message", isOwn ? "user" : "other");

  const span = document.createElement("span");
  span.classList.add("username");
  span.classList.add(isOwn ? "username-user" : "username-other");
  span.textContent = `${data.nombre} (${data.correo}): `;
  span.dataset.userid = data.correo;

  const msg = document.createTextNode(data.message);

  messageDiv.appendChild(span);
  messageDiv.appendChild(msg);

  chatBox.appendChild(messageDiv);
  chatBox.scrollTop = chatBox.scrollHeight;
});

// Enviar mensaje
function sendMessage() {
  const message = chatInput.value.trim();
  if (!message) return;

  // Emitir mensaje al servidor (será reenviado para todos, incluido el emisor)
  socket.emit("message", { message });
  chatInput.value = "";
}

sendButton?.addEventListener("click", sendMessage);
chatInput?.addEventListener("keydown", function (e) {
  if (e.key === "Enter") {
    sendMessage();
  }
});

chatInput?.addEventListener("focus", () => isChatFocused = true);
chatInput?.addEventListener("blur", () => isChatFocused = false);

setInterval(() => {
  if (isChatFocused && chatInput) chatInput.focus();
}, 100);

toggleBtn?.addEventListener("click", () => {
  chatContainer?.classList.toggle("hidden");
});

//Menu del chat
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  sidebar.classList.toggle("hidden");
}

//Añadir amigo

function mostrarFormularioAmigo() {
  const form = document.getElementById("form-solicitud-amigo");
  form.classList.remove("hidden");
}

function cerrarFormularioAmigo() {
  const form = document.getElementById("form-solicitud-amigo");
  form.classList.add("hidden");
}

function enviarSolicitudAmistad() {
  const correo = document.getElementById("correo-amigo").value.trim();
  if (!correo) return alert("Introduce un correo válido");

  fetch("php/add_amistad.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ correo })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert("Solicitud enviada correctamente");
      } else {
        alert(data.error || "Error al enviar solicitud");
      }
    })
    .catch(err => {
      console.error("Error:", err);
      alert("Error de conexión con el servidor");
    });

  document.getElementById("correo-amigo").value = "";
  cerrarFormularioAmigo();
}
