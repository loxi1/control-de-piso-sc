document.addEventListener('DOMContentLoaded', () => {
    //Obtener la url del API
    const urlapi = document.querySelector('input[name="api"]').value
    
    //Funcion para activar y desactivar el preload
    const loadingData = (estado) => {
        const loading = document.querySelector("#preloader")
        document.body.style.overflow = estado ? "hidden" : "auto"

        if (estado) 
            loading.classList.remove("d-none")
        else
            loading.classList.add("d-none")        
    }

    // Variables para el temporizador
    let timer
    let isRunning = true
    let seconds = parseInt(document.getElementById('segundos').value) || 0

    //Elementos
    const btnempezar = document.getElementById('btnempezar')
    const timerDisplay = document.getElementById('timerDisplay')
    const bgcontbtn = document.getElementById('btns')

    //Variables necesarias
    const usuario = document.querySelector('input[name="usuario"]').value
    const evento = document.querySelector('input[name="evento_id"]').value
    const ciclo = document.querySelector('input[name="ciclo_id"]').value
    const costura = parseInt(document.getElementById("costura_id").value) || 0
    const nombre = document.querySelector('input[name="nombre_usuario"]').value
    const motivo_tipo = document.querySelector('input[name="motivo_tipo"]').value || 0
    const idingreso = parseInt(document.getElementById("idingreso").value) || 0

    //Inicia el cronometro
    iniciar()

    // Formatear tiempo
    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600)
        const minutes = Math.floor((totalSeconds % 3600) / 60)
        const seconds = totalSeconds % 60

        return [
            hours.toString().padStart(2, '0'),
            minutes.toString().padStart(2, '0'),
            seconds.toString().padStart(2, '0')
        ].join(':')
    }

    // Actualizar temporizador
    function updateTimer() {
        seconds++
        timerDisplay.textContent = formatTime(seconds)
    }        

    function iniciar() {
        isRunning = true
        timerDisplay.textContent = "00:00:00"
        timer = setInterval(updateTimer, 1000)
        btnempezar.textContent = 'FINALIZAR'
        bgcontbtn.classList.add('bg-finalizar')
        bgcontbtn.classList.remove('bg-inicio')
    }

    function parar() {
        isRunning = false
        clearInterval(timer)
        btnempezar.textContent = 'INICIO'
        timerDisplay.textContent = "00:00:00"
        bgcontbtn.classList.add('bg-inicio')
        bgcontbtn.classList.remove('bg-finalizar')
    }

    // Iniciar temporizador
    btnempezar.addEventListener('click', toggleTimerPrincipal)

    async function toggleTimerPrincipal() {
        if (!isRunning)
            iniciar()
        else
            parar()

        if (evento < 1 || ciclo < 1)
            return null      

        await saveEvento("save_cerrar_evento_ciclo_normal", { idingreso, costura, nombre, evento, ciclo, usuario, motivo_tipo })
        loadingData(true)

        window.top.location.href = `${urlapi}blank_evento_costura/`;
    }

    // Limpiar al cerrar
    window.addEventListener('beforeunload', function () {
        if (isRunning)
            clearInterval(timer)
    })

    //Mostra el tiempo improductivo
    actualizarTiempos()

    // Puedes repetirlo cada 5 minutos si deseas: setInterval(() => actualizarTiempos(usuarioid), 5 * 60 * 1000)
    async function actualizarTiempos() {
        if (!usuario || usuario.trim() === "") {
            console.warn("El usuario no está definido o está vacío, no se puede actualizar tiempos improductivos.")
            return
        }
        const data = await metodoGet('get_tiempo_improductivo',`usuario=${usuario}`)
        if (data) {
            // Actualizar los valores en el DOM
            document.getElementById("timp").textContent = data.timp
            document.getElementById("pimp").textContent = data.pimp
        }
    }

    // Función para guardar el evento
    async function saveEvento(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`        
        try {
            const data = await postJSON(url, payload)
            let evento = (data.code === 200) ? data.data.evento : 0
            document.getElementById("evento_id").value = evento
        } catch (error) {
            console.error("Error al guardar evento:", error)
        }
    }

    // Función para realizar la solicitud POST y manejar el JSON
    async function postJSON(url, data) {
        try {
            loadingData(true)
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })

            if (response.status === 401 || response.status === 403) {
                direccionar('app_Login_costura')
                return { code: 401, msn: "Sesión expirada", data: null }
            }

            return await response.json()
        } catch (error) {
            return { code: 500, msn: "Error en fetch", data: null }
        } finally {
            loadingData(false)
        }
    }

    // Función para obtener datos de un metodo
    async function metodoGet(metodo, param) {
        const url = `${urlapi}${metodo}/?${param}`
        try {
            loadingData(true)
            const response = await fetch(url)

	    if (response.status === 401 || response.status === 403) {
                direccionar('app_Login_costura')
                return { code: 401, msn: "Sesión expirada", data: null }
            }

            const data = await response.json()

            return data.code === 200 ? data.data : null

        } catch (error) {
            return null
        } finally {
            loadingData(false)
        }
    }
})
