document.addEventListener('DOMContentLoaded', () => {
    const urlapi = document.querySelector('input[name="api"]').value;

    // Variables para el temporizador
    let timer;
    let seconds = 0;
    let isRunning = false;
    const btnempezar = document.getElementById('btnempezar');
    const btnsalir = document.getElementById('btnsalir');
    const btnatras = document.getElementById('btnatras');
    const timerDisplay = document.getElementById('timerDisplay');
    const bgcontbtn = document.getElementById('btns');
    const usuario = document.querySelector('input[name="usuario"]').value;
    const nombre = document.getElementById("nombre_usuario").value;
    const costura = parseInt(document.getElementById("costura_id").value);
    const tiempo = parseFloat(document.getElementById("tiempo_estimado").value) || 0;

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
            iniciar();
        }
        let ciclo = parseInt(document.getElementById("ciclo_id").value);
        
        saveCiclo("save_ciclo", { costura, ciclo, usuario, nombre });
    });

    //Salir
    btnsalir.addEventListener('click', function () {
        handleExit('app_Login_costura');
    });
    
    // Atras
    btnatras.addEventListener('click', function () {
        handleExit('form_costura_operacion');
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

        const ciclo = parseInt(document.getElementById("ciclo_id").value);

        // Solo guardar el ciclo si es un número válido
        if (!isNaN(ciclo)) {
            let ciclo = parseInt(document.getElementById("ciclo_id").value);
            await saveCiclo("save_cerrar_ciclo", { costura, ciclo, usuario });
        }

        // Redirigir si se proporciona una URL
        if (url !== '') {
            window.top.location.href = `${urlapi}${url}/`;
        }
    }

    function iniciar() {
        isRunning = true;
        seconds = 0;
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

    // Función principal que maneja los eventos de los botones
    document.querySelectorAll('.event-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const tipo = parseInt(this.getAttribute('tipo'));
            let ciclo = parseInt(document.getElementById("ciclo_id").value);            
            
            const motivo = parseInt(this.getAttribute('motivoid'));  
            
            let estado = 1
            if (isNaN(tipo)) return;

            if (ciclo > 0 && !isNaN(ciclo)) {
                // Solo guardar el ciclo si es un número válido
                await saveCiclo("save_cerrar_ciclo",{ costura, ciclo, estado, tipo, usuario });
            }

            const result = await Swal.fire({ // Espera el resultado de la confirmación
                title: "¿Está seguro?",
                text: "Descartar operación Iniciada!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No, cancelar!"
            });
    
            if (result.isConfirmed) {
                await saveCicloEvento('save_evento_ciclo_normal', { costura, ciclo, motivo, nombre, usuario, tipo });
                Swal.fire({
                    title: "Eliminado!",
                    text: "Esta direccionando al soporte.",
                    icon: "success"
                });
            }
        });
    });

    // Función para guardar el ciclo
    async function saveCiclo(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`;
    
        try {
            const data = await postJSON(url, payload);
            if (data.code === 200) {
                if(metodo === "save_ciclo") {
                    document.getElementById("ciclo_id").value = data.data.ciclo;
                }
                console.log("Ciclo guardado:", data.data.ciclo);
            } else {
                document.getElementById("ciclo_id").value = 0;
                console.error("Error en la respuesta:", data);
            }
        } catch (error) {
            console.error("Error al guardar ciclo:", error);
        }
    }    
    
    // Función para guardar el evento ciclo
    async function saveCicloEvento(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`;

        try {
            const data = await postJSON(url, payload);

            if (data.code === 200) {
                const evento = parseInt(data.data.evento);
                console.log("Evento guardado:", evento);

                if (evento > 0) {
                    const sendUrl = buildRedirectionUrl(evento, payload.tipo);
                    window.top.location.href = sendUrl;
                }
            } else {
                console.error("Error en la respuesta:", data);
            }
        } catch (error) {
            console.error("Error al guardar evento ciclo:", error);
        }
    }

    // Función para construir la URL de redirección
    function buildRedirectionUrl(evento, tipo) {
        let pagina = "";
        if (tipo === 1) {
            pagina = "blank_evento_ciclo_normal";
        } else {
            pagina = "blank_soporte_ciclo";
        }
        return `${urlapi}${pagina}/?evento=${evento}`;
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

    actualizarTiempos();

    // Puedes repetirlo cada 5 minutos si deseas: setInterval(() => actualizarTiempos(usuarioid), 5 * 60 * 1000);
    async function actualizarTiempos() {
        const data = await getTiempoImproductivo('get_tiempo_improductivo',`usuario=${usuario}`);
        if (data) {
            // Actualizar los valores en el DOM
            document.getElementById("timp").textContent = data.timp;
            document.getElementById("pimp").textContent = data.pimp;
        }
    }
    
    // Ejecutar cada 3 minutos (180,000 ms)
    setInterval(actualizarEficiencia, 3 * 60 * 1000);

    // También puedes llamar una vez al cargar la página si es necesario
    actualizarEficiencia();

    async function actualizarEficiencia() {
        const data = await getTiempoImproductivo('get_eficiencia',`usuario=${usuario}&tiempo=${tiempo}`);
        if (data) {
            // Actualizar los valores en el DOM
            document.getElementById("eficienciaxcolaborador").textContent = data.eficiencia;
        }
    }

    // Función para obtener datos de un metodo
    async function getTiempoImproductivo(metodo, param) {
        const url = `${urlapi}${metodo}/?${param}&nmgp_outra_jan=true`;
        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.code === 200) {
                return data.data;
            } else {
                console.error("Error en la respuesta:", data);
                return null;
            }
        } catch (error) {
            console.error("Error al obtener el tiempo improductivo:", error);
            return null;
        }
    }
});