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

    function getQueryParam(name) {
        return new URLSearchParams(window.location.search).get(name);
    }

    function detectarIPLocalConRTC(callback) {
        const pc = new RTCPeerConnection({ iceServers: [] });
        pc.createDataChannel('');
        pc.createOffer().then(offer => pc.setLocalDescription(offer));

        pc.onicecandidate = (event) => {
            if (!event || !event.candidate) return;
            const candidate = event.candidate.candidate;
            const ipMatch = candidate.match(/([0-9]{1,3}(\.[0-9]{1,3}){3})/);
            if (ipMatch) {
                const ipLocal = ipMatch[1];

                // 🛡️ Validamos que no sea una IP inválida
                if (ipLocal === '127.0.0.1' || ipLocal === 'localhost') {
                    debugLog("⚠️ IP inválida detectada (localhost o 127.0.0.1), ignorando.");
                    return;
                }

                debugLog(`🌐 IP Local detectada por RTC: ${ipLocal}`);
                callback(ipLocal);
            }
        };
    }

    function redirigirSiNoTieneParametro(ipLocal) {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("api")) {
            debugLog(`🛠 Ya existe ?api=${urlParams.get("api")}`);
            mostrarDeviceID(urlParams.get("api"));
            return;
        }

        const nuevaURL = `${window.location.origin}${window.location.pathname}?api=${ipLocal}`;
        debugLog(`🔁 Redirigiendo a: ${nuevaURL}`);
        window.location.href = nuevaURL;
    }

    async function mostrarDeviceID(ip) {
        const url = `http://${ip}:3000/device`;
        debugLog(`🔍 Consultando: ${url}`);
        try {
            const res = await fetch(url);
            const json = await res.json();
            document.getElementById("deviceId").textContent = json.deviceid || "No encontrado";
            debugLog(`✅ Device ID: ${json.deviceid}`);
        } catch (e) {
            document.getElementById("deviceId").textContent = "Error";
            debugLog(`❌ Error al obtener device: ${e.message}`);
        }
    }

    // INICIO
    // 👇 Inicio automático
    const apiParam = getQueryParam("api");
    if (apiParam) {
        debugLog(`✅ ?api ya está presente: ${apiParam}`);
        mostrarDeviceID(apiParam);
    } else {
        debugLog("🕵️ Buscando IP local con WebRTC...");
        detectarIPLocalConRTC((ipLocal) => {
            if (ipLocal) redirigirSiNoTieneParametro(ipLocal);
            else debugLog("❌ No se pudo detectar una IP local válida.");
        });
    }
});