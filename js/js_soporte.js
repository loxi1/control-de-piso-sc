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

    const Alerta = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer
          toast.onmouseleave = Swal.resumeTimer
        }
    })

    // Variables para el primer temporizador (principal)
    let timerPrincipal
    let isRunningPrincipal = false
    let secondsPrincipal = parseInt(document.getElementById('segundos').value) || 0

    // Variables para el segundo temporizador (mecánico)
    let timerMecanico
    let isRunningMecanico = false
    let secondsMecanico = parseInt(document.getElementById('segatencion').value, 10) || 0
    let elMecanico = parseInt(document.getElementById("mecanico").value, 10) || 0 //Valor entero o 0

    //Elementos
    const btnEmpezarPrincipal = document.getElementById('btnempezar')
    const timerDisplayMecanico = document.getElementById('timerDisplayMecanico')
    const btnInicioMecanico = document.getElementById('btniniomecanico')
    const timerDisplayPrincipal = document.getElementById('timerDisplay')
    const bgContBtnPrincipal = document.getElementById('btns')    
    const problemaid = document.getElementById('problemaid')
    const idingreso = parseInt(document.getElementById("idingreso").value) || 0

    //Variables necesarias
    parseInt(document.getElementById("ciclo_id").value, 10)
    parseInt(document.getElementById("usuario").value, 10)
    const soporteid = parseInt(document.getElementById("evento_soporte_id").value, 10)
    const cicloid = document.querySelector('input[name="ciclo_id"]').value
    const usuario = document.querySelector('input[name="usuario"]').value
    const costuraid = parseInt(document.getElementById("costura_id").value, 10) || 0
    const nombre = document.querySelector('input[name="nombre_usuario"]').value
    const problema = parseInt(document.getElementById('problema').value, 10)

    //Iniciar el temporalizador
    iniciarTemporizadorPrincipal()
    
    //En caso se haya llegado el mecánico
    if(secondsMecanico > 0) {
        iniciarTemporizadorMecanico()
        btnInicioMecanico.style.display = 'none'
    }

    //En caso exista el idmecanico
    if(elMecanico >0)
        actualizarMecanico(elMecanico)

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

    // Actualizar temporizador principal
    function updateTimerPrincipal() {
        secondsPrincipal++
        timerDisplayPrincipal.textContent = formatTime(secondsPrincipal)
    }

    // Iniciar/Parar temporizador principal
    function toggleTimerPrincipal() {        
        const problemaSeleccionado = obtenerProblema();

        if (problemaSeleccionado === null) {
            Swal.fire("¡Ups..!", "Seleccione el problema.", "error");
            return;
        }

        if (!isRunningPrincipal) {
            iniciarTemporizadorPrincipal()
        } else {
            pararTemporizadorPrincipal()
        }

        if (soporteid < 1 || cicloid < 1) {
            return null
        }

        let tfatencion = 1

        let payload = {
            cicloid,
            tfatencion,
            soporteid,
            costuraid,
            nombre,
            usuario,
            idingreso
        }

        saveSoporte('save_evento_soporte', payload)
        AlertaToast("El mecánico inicio ok!")
        loadingData(true)

        window.top.location.href = `${urlapi}blank_evento_costura/`;
    }

    //Iniciar crometro principal
    function iniciarTemporizadorPrincipal() {
        isRunningPrincipal = true
        timerDisplayPrincipal.textContent = "00:00:00"
        timerPrincipal = setInterval(updateTimerPrincipal, 1000)
        btnEmpezarPrincipal.textContent = 'FINALIZAR'
        bgContBtnPrincipal.classList.add('bg-finalizar')
        bgContBtnPrincipal.classList.remove('bg-inicio')
    }

    function obtenerProblema() {
        const problemaValor = parseInt(problemaid.value, 10);
        if (isNaN(problemaValor) || problemaValor <= 0) { // Considera 0 o NaN como inválido
            return null;
        }
        return problemaValor;
    }

    //Iniciar crometro del mecánico
    function pararTemporizadorPrincipal() {
        isRunningPrincipal = false
        clearInterval(timerPrincipal)
        btnEmpezarPrincipal.textContent = 'INICIO'
        bgContBtnPrincipal.classList.add('bg-inicio')
        bgContBtnPrincipal.classList.remove('bg-finalizar')
        btnEmpezarPrincipal.disabled = true
    }

    // Actualizar temporizador mecánico
    function updateTimerMecanico() {
        secondsMecanico++
        timerDisplayMecanico.textContent = formatTime(secondsMecanico)
    }

    // Iniciar/Parar temporizador mecánico
    function toggleTimerMecanico() {        
        const problemaSeleccionado = obtenerProblema();

        if (problemaSeleccionado === null) {
            Swal.fire("¡Ups..!", "Seleccione el problema.", "error");
            return;
        }

        if (!isRunningMecanico) {
            iniciarTemporizadorMecanico()         
            btnInicioMecanico.style.display = 'none' // Ocultar el boton del mecanico
            if (soporteid < 1 || cicloid < 1) {
                return null
            }
            
            let tiatencion = 1

            let payload = {
                tiatencion,
                soporteid,
                usuario
            }
    
            saveSoporte('save_evento_soporte', payload)
            AlertaToast("Mecánico Iniciodo ok!")            
        } else {
            pararTemporizadorMecanico()
        }
    }

    //Iniciar cronometro del mecánico
    function iniciarTemporizadorMecanico() {
        isRunningMecanico = true
        timerDisplayMecanico.textContent = "00:00:00"
        timerMecanico = setInterval(updateTimerMecanico, 1000)
        btnInicioMecanico.textContent = 'FINALIZAR' // Opcional: cambiar texto del botón
        btnInicioMecanico.classList.add('bg-finalizar')
        btnInicioMecanico.classList.remove('bg-inicio')
    }

    //Parar cronometro del mecánico
    function pararTemporizadorMecanico() {
        isRunningMecanico = false
        clearInterval(timerMecanico)
        btnInicioMecanico.textContent = 'INICIO' // Opcional: cambiar texto del botón
        btnInicioMecanico.classList.add('bg-inicio')
        btnInicioMecanico.classList.remove('bg-finalizar')
        btnInicioMecanico.disabled = true
    }

    // Event click
    btnEmpezarPrincipal.addEventListener('click', toggleTimerPrincipal)

    // Asegúrate de que el botón con id "btniniomecanico" exista en tu HTML
    if (btnInicioMecanico) {
        btnInicioMecanico.addEventListener('click', toggleTimerMecanico)
    }

    // Limpiar al cerrar (se limpian ambos intervalos si están activos)
    window.addEventListener('beforeunload', function () {
        if (isRunningPrincipal)
            clearInterval(timerPrincipal)
        
        if (isRunningMecanico)
            clearInterval(timerMecanico)        
    })

    //Evento change de problemaid
    problemaid.addEventListener('change', function () {
        //Mostra un # decimal
        let problema = Math.max(parseInt(problemaid.value, 10) || 0, 0)

        if (soporteid < 1 || cicloid < 1) {
            return null
        }

        let payload = {
            problema,
            soporteid,
            usuario
        }

        saveSoporte('save_evento_soporte', payload)
        AlertaToast("Problema guardado ok!")  
    })

    //Alerta de mensajes
    function AlertaToast(mensaje) {
        Alerta.fire({
            icon: "success",
            title: mensaje,
            showClass: {
                popup: "animate__animated animate__fadeInDown"
            },
            hideClass: {
                popup: "animate__animated animate__fadeOutUp"
            }
        })
    }
    
    //Mostra el tiempo improductivo
    actualizarTiempos()

    // Puedes repetirlo cada 5 minutos si deseas: setInterval(() => actualizarTiempos(usuarioid), 5 * 60 * 1000)
    async function actualizarTiempos() {
        const data = await metodoGet('get_tiempo_improductivo',`usuario=${usuario}`)
        if (data) {
            // Actualizar los valores en el DOM
            document.getElementById("timp").textContent = data.timp
            document.getElementById("pimp").textContent = data.pimp
        }
    }

    async function getMecanico() {
        if (soporteid < 1) return null
    
        const data = await metodoGet('get_mecanico', `id=${soporteid}`, false)
        const mecaid =  (!data || typeof data.mecanico === 'undefined' || data.mecanico === null) ? 0 : parseInt(data.mecanico)

        if(mecaid > 0)
            await actualizarMecanico(mecaid)
    }
    
    async function actualizarMecanico(mecaid) {
        const data = await metodoGet('get_colaborador', `id=${mecaid}`, false)
        const nombre = (!data || typeof data.mecanico === 'undefined' || data.mecanico === null) ? "(Sin Asignar)" : data.mecanico
        document.querySelector("#nombre_mecanico").textContent = nombre
    }
    
    setInterval(getMecanico, 5000)

    // Función para guardar el evento
    async function saveSoporte(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`
        try {
            const data = await postJSON(url, payload)
            if (data.code === 200) {
                evento = data.data.soporte
            } else {
                document.getElementById("ciclo_id").value = 0
                console.error("Error en la respuesta:", data)
            }
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
    
    // Función para cargar el combo de problemas
    getProblema()
    async function getProblema() {
        const data = await metodoGet('get_problema', ``)
        const select = document.getElementById("problemaid")
        const selectedProblema = parseInt(document.getElementById("problema").value, 10)

        if (data && Array.isArray(data)) {         
            // Limpiar el select por si ya tiene opciones
            select.innerHTML = ''

            // Agregar opción por defecto
            const defaultOption = document.createElement("option")
            defaultOption.value = "0"
            defaultOption.textContent = "--SELECCIONAR--"
            select.appendChild(defaultOption)

            // Agregar las opciones dinámicamente
            data.forEach(item => {
                const option = document.createElement("option")
                option.value = item.id
                option.textContent = item.motivo
                if (item.id === selectedProblema) {
                    option.selected = true
                }
                select.appendChild(option)
            })
        }
    }

    // Función para obtener datos de un metodo
    async function metodoGet(metodo, param, mostrarLoader = true) {
        const url = `${urlapi}${metodo}/?${param}`
        try {
            if (mostrarLoader) loadingData(true)
            const response = await fetch(url)
            const data = await response.json()          

            if (response.status === 401 || response.status === 403) {
                direccionar('app_Login_costura')
                return { code: 401, msn: "Sesión expirada", data: null }
            }

            return data.code === 200 ? data.data : null
        } catch (error) {
            return null
        } finally {
            if (mostrarLoader) loadingData(false)
        }
    }
})