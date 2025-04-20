const express = require('express');
const http = require('http');
const socketIO = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIO(server, {
    cors: {
        origin: "*", // Asegúrate que permite conexión desde tu frontend
        methods: ["GET", "POST"]
    }
});

// Almacenamos info de usuario por socket.id
const usuarios = {};

io.on('connection', (socket) => {
    console.log('Cliente conectado:', socket.id);

    // Escuchar evento de registro
    socket.on('registrar_usuario', (data) => {
        usuarios[socket.id] = {
            correo: data.correo,
            nombre: data.nombre
        };
        console.log(`Usuario registrado: ${data.nombre} (${data.correo})`);
    });

    // Escuchar mensajes
    socket.on('message', (data) => {
        const usuario = usuarios[socket.id];

        if (!usuario) {
            console.warn("Usuario no registrado aún");
            return;
        }

        console.log(`Mensaje de ${usuario.correo}: ${data.message}`);

        // Emitir a todos los clientes incluyendo nombre y correo reales
        io.emit('message', {
            correo: usuario.correo,
            nombre: usuario.nombre,
            message: data.message
        });
    });

    // Desconexión
    socket.on('disconnect', () => {
        console.log('Cliente desconectado:', socket.id);
        delete usuarios[socket.id];
    });
});

const PORT = 8080;
server.listen(PORT, () => {
    console.log(`Servidor WebSocket escuchando en puerto ${PORT}`);
});
