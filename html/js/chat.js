const socket = io("http://localhost:8090");
const chatBox = document.getElementById("chat-box");
const chatInput = document.getElementById("chat-input");
const sendButton = document.querySelector("button");

let userId = '';
let isChatFocused = false;

// Recibir el nombre de usuario del servidor
socket.on('user_id', (id) => {
  userId = id;
});

// Evento cuando se recibe un mensaje
socket.on("message", function(data) {
  if (data.userId !== userId) { // Verificar si el mensaje no es del usuario actual
    const messageDiv = document.createElement("div");
    messageDiv.classList.add("message", "other");
    messageDiv.textContent = `${data.userId}: ${data.message}`;
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
  }
});

sendButton.addEventListener("click", sendMessage);

chatInput.addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    sendMessage();
  }
});

chatInput.addEventListener("focus", function() {
  isChatFocused = true;
});

chatInput.addEventListener("blur", function() {
  isChatFocused = false;
});

function sendMessage() {
  const message = chatInput.value.trim();

  if (message) {
    // Enviar mensaje al servidor Socket.IO
    socket.emit("message", { userId: userId, message: message });

    // Mostrar el mensaje del usuario a la derecha y en azul
    const userMessage = document.createElement("div");
    userMessage.classList.add("message", "user");
    userMessage.textContent = `Tú: ${message}`; // Mostrar "Tú" en lugar del userId
    chatBox.appendChild(userMessage);
    chatBox.scrollTop = chatBox.scrollHeight;

    chatInput.value = "";
  }
}

// Mantener el enfoque en el campo de entrada si alguna vez fue enfocado
setInterval(() => {
  if (isChatFocused) {
    chatInput.focus();
  }
}, 100);

document.getElementById("chat-input").addEventListener("keydown", (event) => {
  console.log("Input detected:", event.key);
});