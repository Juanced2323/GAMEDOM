const express = require('express');
const http = require('http');
const socketIO = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIO(server, {
    cors: {
        origin: "http://localhost:8080", // Permite conexiones desde tu aplicación
        methods: ["GET", "POST"]
    }
});

const users = {}; // Objeto para almacenar usuarios y sus sockets

io.on('connection', (socket) => {
    console.log('Nuevo cliente conectado');

    // Asignar un nombre de usuario único al cliente
    const userId = `user_${Date.now()}`;
    users[userId] = socket;

    // Enviar el nombre de usuario al cliente
    socket.emit('user_id', userId);

    // Manejar mensajes del cliente
    socket.on('message', (data) => {
        console.log(`Mensaje de ${data.userId}: ${data.message}`);

        // Enviar el mensaje a todos los clientes conectados como un objeto
        io.emit('message', { userId: data.userId, message: data.message });
    });

    // Manejar desconexiones
    socket.on('disconnect', () => {
        console.log(`Cliente ${userId} desconectado`);
        delete users[userId];
    });
});

const PORT = 8080;
server.listen(PORT, () => {
    console.log(`Servidor Socket.IO iniciado en el puerto ${PORT}`);
});
