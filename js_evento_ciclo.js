document.addEventListener('DOMContentLoaded', () => {
    const urlapi = document.querySelector('input[name="api"]').value; 

    // Variables para el temporizador
    let timer;
    let isRunning = true;
    const btnempezar = document.getElementById('btnempezar');
    const timerDisplay = document.getElementById('timerDisplay');
    const bgcontbtn = document.getElementById('btns');

    const btnsalir = document.getElementById('btnsalir');
    const btnatras = document.getElementById('btnatras');

    let seconds = parseInt(document.getElementById('segundos').value) || 0;

    // Mostrar tiempo ya transcurrido al cargar

    iniciar();

    // Formatear tiempo
    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return [
            hours.toString().padStart(2, '0'),
            minutes.toString().padStart(2, '0'),
            seconds.toString().padStart(2, '0')
        ].join(':');
    }

    // Actualizar temporizador
    function updateTimer() {
        seconds++;
        timerDisplay.textContent = formatTime(seconds);
    }

    // Iniciar temporizador
    btnempezar.addEventListener('click', function () {
        if (!isRunning) {
            iniciar();
        } else {
            parar();
        }
        logoutScriptCase("blank_evento_normal")
    });

    function iniciar() {
        isRunning = true;
        seconds = segundos.value || 0; // Usar el valor de segundos del input
        timerDisplay.textContent = "00:00:00";
        timer = setInterval(updateTimer, 1000);
        btnempezar.textContent = 'FINALIZAR';
        bgcontbtn.classList.add('bg-finalizar');
        bgcontbtn.classList.remove('bg-inicio');
    }

    function parar() {
        isRunning = false;
        clearInterval(timer);
        btnempezar.textContent = 'INICIO';
        timerDisplay.textContent = "00:00:00";
        bgcontbtn.classList.add('bg-inicio');
        bgcontbtn.classList.remove('bg-finalizar');
    }

    // Limpiar al cerrar
    window.addEventListener('beforeunload', function () {
        if (isRunning) {
            clearInterval(timer);
        }
    });

    //Salir
    btnsalir.addEventListener('click', function () {
        handleExit('app_Login_costura');
    });
    
    // Atras
    btnatras.addEventListener('click', function () {
        handleExit('blank_evento_normal');
    });

    // Función principal para manejar la salida o redirección
    async function handleExit(url) {
        try {
            await logoutScriptCase(url);
        } catch (error) {
            console.error("Error al manejar la salida:", error);
        }
    }

    // Función para manejar el logout y redirección
    async function logoutScriptCase(url = '') {
        // Detener cualquier proceso relacionado
        parar();

        const evento = parseInt(document.getElementById("evento_id").value);
        const ciclo = parseInt(document.getElementById("ciclo_id").value);

        // Solo guardar el evento si es un número válido
        if (!isNaN(evento) && !isNaN(ciclo)) {
            await saveEvento("save_cerrar_evento_ciclo_normal", { evento, ciclo });
        }

        // Redirigir si se proporciona una URL
        if (url !== '') {
            window.top.location.href = `${urlapi}${url}/`;
        }
    }

    // Función para guardar el evento
    async function saveEvento(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`;        
        try {
            const data = await postJSON(url, payload);
            if (data.code === 200) {
                evento = data.data.evento;
            } else {
                document.getElementById("evento_id").value = 0;
                console.error("Error en la respuesta:", data);
            }
        } catch (error) {
            console.error("Error al guardar evento:", error);
        }
    }

    // Función para realizar la solicitud POST y manejar el JSON
    async function postJSON(url, data) {
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error("Error en la petición:", error);
            return { code: 500, msn: "Error en fetch", data: null };
        }
    }    
});