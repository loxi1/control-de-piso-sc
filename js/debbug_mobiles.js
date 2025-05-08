document.addEventListener('DOMContentLoaded', () => {    
    function debugLog(mensaje) {
        const logArea = document.getElementById("debuglog");
        if (!logArea) return;
        const timestamp = new Date().toLocaleTimeString();
        logArea.innerHTML += `<div>[${timestamp}] ${mensaje}</div>`;
        logArea.scrollTop = logArea.scrollHeight;
    }

    debugLog("📱 Debug visible desde móvil");

    async function getMecanico() {
        if (soporteid < 1) {
            debugLog("❌ soporteid no válido");
            return null;
        }

        debugLog("🔍 Buscando mecánico...");

        const data = await metodoGet('get_mecanico', `id=${soporteid}`, false);

        if (!data || typeof data.mecanico === 'undefined' || data.mecanico === null) {
            debugLog("⚠️ No se obtuvo nombre del mecánico.");
            return null;
        }

        debugLog(`✅ Mecánico recibido: ${data.mecanico}`);
        return data.mecanico;
    }

    async function actualizarMecanico() {
        debugLog("⏳ Ejecutando actualizarMecanico()");
        const mecanico = await getMecanico();
        if (mecanico) {
            document.querySelector("#nombre_mecanico").innerText = mecanico;
            debugLog("🟢 nombre_mecanico actualizado.");
        }
    }

    function actualizarSiVisible() {
        debugLog(`👁️ Estado visibilidad: ${document.visibilityState}`);
        if (document.visibilityState === "visible") {
            actualizarMecanico();
        }
    }

    // Configuración inicial
    document.addEventListener("visibilitychange", actualizarSiVisible);
    actualizarMecanico();
    setInterval(actualizarSiVisible, 5000);



    // Función para obtener datos de un metodo
    async function metodoGet(metodo, param, mostrarLoader = true) {
        const url = `${urlapi}${metodo}/?${param}`
        try {
            if (mostrarLoader) loadingData(true)
            const response = await fetch(url)
            const data = await response.json()

            return data.code === 200 ? data.data : null
        } catch (error) {
            return null
        } finally {
            if (mostrarLoader) loadingData(false)
        }
    }

})