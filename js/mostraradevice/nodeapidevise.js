const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;

// Habilitar CORS para que cualquier cliente pueda hacer peticiones
app.use((req, res, next) => {
    res.setHeader("Access-Control-Allow-Origin", "*"); // Cambiar "*" por dominio específico si es necesario
    res.setHeader("Access-Control-Allow-Methods", "GET, OPTIONS");
    res.setHeader("Access-Control-Allow-Headers", "Content-Type");
    next();
});

app.get('/device', (req, res) => {
    const filePath = path.join('/storage/emulated/0/Download/', 'device.json');

    fs.readFile(filePath, 'utf8', (err, data) => {
        if (err) {
            return res.status(500).json({ error: 'Error al leer el archivo' });
        }
        res.json(JSON.parse(data));
    });
});

app.get('/ip', (req, res) => {
    const ip = req.socket.localAddress.replace(/^.*:/, '');
    res.json({ ip });
});

app.get('/loca', (req, res) => {
    const ip = req.ip || req.connection.remoteAddress;
    res.send(`Tu IP interna es: ${ip}`);
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`Servidor corriendo en http://localhost:${PORT}/device`);
});