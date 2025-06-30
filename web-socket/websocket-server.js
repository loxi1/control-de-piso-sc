const WebSocket = require('ws')
const http = require('http')
const url = require('url')
const fetch = require('node-fetch')

const PORT = 3001

// Crear servidor HTTP base
const server = http.createServer()

// Crear el servidor WebSocket sobre el servidor HTTP
const wss = new WebSocket.Server({ server })

// Evento cuando un cliente se conecta
wss.on('connection', function connection(ws, req) {
    const location = url.parse(req.url, true)
    const query = location.query

    // Validar si se pasó un id de soporte
    const soporteId = query.id

    if (!soporteId) {
        ws.send(JSON.stringify({ error: 'Falta el parámetro ?id=XX en la URL de conexión' }))
        ws.close()
        return
    }

    console.log(`[WS] Cliente conectado para soporte ID ${soporteId}`)

    let lastMecanicoId = 0

    // Intervalo para consultar mecánico cada 5 segundos
    const interval = setInterval(async () => {
        try {
            // Primer fetch: obtener ID de mecánico
            const res1 = await fetch(`http://192.168.150.42:8092/scriptcase/app/eCorporativoM/get_mecanico/?id=${soporteId}`)
            const json1 = await res1.json()
            const mecaId = parseInt(json1?.data?.mecanico || 0)

            if (mecaId > 0 && mecaId !== lastMecanicoId) {
                // Segundo fetch: obtener nombre
                const res2 = await fetch(`http://192.168.150.42:8092/scriptcase/app/eCorporativoM/get_colaborador/?id=${mecaId}`)
                const json2 = await res2.json()
                const nombre = json2?.data?.mecanico || '(Sin nombre)'

                // Enviar al cliente
                ws.send(JSON.stringify({ mecanico_id: mecaId, nombre }))
                lastMecanicoId = mecaId
            }
        } catch (err) {
            console.error(`[WS] Error obteniendo datos:`, err)
            ws.send(JSON.stringify({ error: 'Error al consultar el servidor' }))
        }
    }, 5000)

    // Cierre de conexión
    ws.on('close', () => {
        clearInterval(interval)
        console.log(`[WS] Cliente desconectado para soporte ID ${soporteId}`)
    })
})

// Iniciar el servidor
server.listen(PORT, () => {
    console.log(`WebSocket server activo en ws://localhost:${PORT}`)
})
