document.addEventListener('DOMContentLoaded', () => {
    const logArea = document.getElementById("debuglog");

    const debugLog = (msg) => {
        const timestamp = new Date().toLocaleTimeString();
        if (logArea) {
            logArea.innerHTML += `<div>[${timestamp}] ${msg}</div>`;
            logArea.scrollTop = logArea.scrollHeight;
        }
        console.log(`[${timestamp}] ${msg}`);
    };

    detectarIPLocalConRTC((ipLocal) => {
        if (ipLocal) {
            debugLog(`🌐 IP local detectada: ${ipLocal}`);
            showdevice(ipLocal);
        } else {
            document.getElementById("deviceId").textContent = "(IP no detectada)";
            debugLog("❌ No se pudo detectar la IP local.");
        }
    });

    // Detecta la IP local usando WebRTC
    function detectarIPLocalConRTC(callback) {
        const pc = new RTCPeerConnection({ iceServers: [] });
        pc.createDataChannel('');
        pc.createOffer().then(offer => pc.setLocalDescription(offer));
        pc.onicecandidate = (event) => {
            if (!event || !event.candidate) return;
            const candidate = event.candidate.candidate;
            const ipMatch = candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3})/);
            if (ipMatch) {
                debugLog(`🕵️ IP encontrada en candidato ICE: ${candidate}`);
                callback(ipMatch[1]);
            }
        };
    }

    async function showdevice(ip) {
        const deviceid = await metodoGet(ip);
        document.getElementById("deviceId").textContent = deviceid;
        debugLog(`✅ Device ID: ${deviceid}`);
    }

    async function metodoGet(ip) {
        const url = `http://${ip}:3000/device`;
        debugLog(`🔍 Consultando endpoint: ${url}`);
        try {
            const response = await fetch(url);
            const data = await response.json();
            debugLog(`📦 Respuesta del servidor: ${JSON.stringify(data)}`);
            return (!data || typeof data.deviceid === 'undefined' || data.deviceid === null)
                ? "(Sin Asignar Maquina)"
                : data.deviceid;
        } catch (error) {
            debugLog(`❌ Error al obtener el deviceid: ${error.message}`);
            return "(Sin Servicio)";
        }
    }
});