const express = require('express');
const http = require('http');
const socketIO = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIO(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const usuarios = {};

io.on('connection', (socket) => {
    console.log('Cliente conectado:', socket.id);

    socket.on('registrar_usuario', (data) => {
        usuarios[socket.id] = {
            nombre: data.nombre
        };
        console.log(`Usuario registrado: ${data.nombre}`);
    });

    socket.on('message', (data) => {
        const usuario = usuarios[socket.id];
        if (!usuario) {
            console.warn("Usuario no registrado aún");
            return;
        }

        console.log(`Mensaje de ${usuario.nombre}: ${data.message}`);

        io.emit('message', {
            nombre: usuario.nombre,
            message: data.message
        });
    });

    socket.on('disconnect', () => {
        console.log('Cliente desconectado:', socket.id);
        delete usuarios[socket.id];
    });
});

const PORT = 8080;
server.listen(PORT, () => {
    console.log(`Servidor WebSocket escuchando en puerto ${PORT}`);
});
