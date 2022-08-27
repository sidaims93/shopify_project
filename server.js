/**
 * npm install
 * npm install express
 * npm install socket.io
 */

const express = require('express');
const app = express();

const server = require('http').createServer(app);

const io = require('socket.io')(server, {
    cors: { origin: "http://shopify_project.test" }
});

io.on('connection', (socket) => {
    console.log('connection');

    socket.on('sendNotificationToUser', (obj) => {
        console.log(obj);
        socket.broadcast.emit('receiveNotificationToUser_'+obj.user, obj.message); 
    });

    socket.on('disconnect', (socket) => {
        //console.log('Disconnect');
    });
});
const port = 3000;

server.listen(port, () => {
    console.log('Server is running. Port: '+port);
});