document.addEventListener("DOMContentLoaded", function () {
    const chatBox = document.getElementById("chat-box");

    const mensajes = [
        { tipo: 'other', texto: 'user_1743971457913:¡Hola! ¿Estás listo para jugar?' },
        { tipo: 'user', texto: '¡Sí, estoy preparado!' },
        { tipo: 'other', texto: 'user_1743971457913: Perfecto, buena suerte.' },
        { tipo: 'user', texto: 'Vamos a ver si puedes ganarme!' },
        { tipo: 'other', texto: 'user_1743971457913: 😎 Que comience la batalla.' },
    ];

    let indice = 0;

    function mostrarSiguienteMensaje() {
        if (indice >= mensajes.length) return;

        const mensaje = mensajes[indice];
        const nuevoMensaje = document.createElement("div");
        nuevoMensaje.classList.add("message", mensaje.tipo);
        nuevoMensaje.textContent = mensaje.texto;

        chatBox.appendChild(nuevoMensaje);
        chatBox.scrollTop = chatBox.scrollHeight;

        indice++;

        const intervalo = 4000 + Math.random() * 1000; // Entre 2 y 3 segundos
        setTimeout(mostrarSiguienteMensaje, intervalo);
    }

    mostrarSiguienteMensaje();
});
