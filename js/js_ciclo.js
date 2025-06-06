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
    let seconds = parseInt(document.getElementById('segundos').value) || 0
    let isRunning = false

    //Elementos
    const btnempezar = document.getElementById('btnempezar')
    const btnsalir = document.getElementById('btnsalir')
    const btnatras = document.getElementById('btnatras')
    const timerDisplay = document.getElementById('timerDisplay')
    const bgcontbtn = document.getElementById('btns')
    const porcentameta = document.getElementById('porcentameta')
    //const porcentajeficiencia = document.getElementById('porcentajeficiencia')
    const reprocesos = document.getElementById("reprocesos")

    //Variables necesarias
    const usuario = document.querySelector('input[name="usuario"]').value
    const nombre = document.getElementById("nombre_usuario").value.trim()
    const costura = parseInt(document.getElementById("costura_id").value)
    const op = parseInt(document.getElementById("es_op")?.value) || 0
    const linea = document.getElementById("linea").value.trim() || ""
    const idingreso = parseInt(document.getElementById("idingreso").value) || 0
    
    //Formatear el tiempo
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

    // Iniciar temporizador
    btnempezar.addEventListener('click', function () {
        if (!isRunning) {
            iniciar()
        } else {
            document.getElementById('segundos').value = 0
            //mostrarPorcentajeDeMetas()
            parar()
            iniciar()
        }

        let ciclo = parseInt(document.getElementById("ciclo_id").value)
        saveCiclo("save_ciclo", { costura, ciclo, usuario, nombre, linea, op })
        metodoGet("save_costura_datos", `usuario=${usuario}&linea=${linea}&costura=${costura}&op=${op}`)
        actualizarEficiencia()
    })

    //Salir
    btnsalir.addEventListener('click', async function () {
        const ncicloid = await getSalirApp()
        if(ncicloid > 0) {
            document.getElementById("ciclo_id").value = ncicloid
            const result = await Swal.fire({ // Espera el resultado de la confirmación
                title: "¿Desea eliminar esta operación?",
                text: "Descartar operación Iniciada!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No cancelar!"
            })

            let estado = (result.isConfirmed) ? 3 :2

            termiarOperacion(estado)
        }
        direccionar('blank_login_operario')
    })
    
    // Atras
    btnatras.addEventListener('click', async function () {
        const ncicloid = await getSalirApp()

        if(ncicloid > 0) 
            await termiarOperacion(0)
        const param = costura > 0 ? `?id=${costura}` : ""
        direccionar(`form_costura_operacion/${param}`)
    })

    // Función principal para manejar la salida o redirección
    function direccionar(url) {
        loadingData(true)
        window.top.location.href = `${urlapi}${url}`
    }

    //Iniciar el conteo
    function iniciar() {
        isRunning = true
        timerDisplay.textContent = "00:00:00"
        seconds = parseInt(document.getElementById('segundos').value) || 0
        timer = setInterval(updateTimer, 1000)
        btnempezar.textContent = 'FINALIZAR'
        bgcontbtn.classList.add('bg-finalizar')
        bgcontbtn.classList.remove('bg-inicio')
    }
    
    //Parar el conteo
    function parar() {
        isRunning = false
        clearInterval(timer)
        btnempezar.textContent = 'INICIO'
        timerDisplay.textContent = "00:00:00"
        bgcontbtn.classList.add('bg-inicio')
        bgcontbtn.classList.remove('bg-finalizar')
    }

    // Limpiar al cerrar
    window.addEventListener('beforeunload', function () {
        if (isRunning) {
            clearInterval(timer)
        }
    })

    document.getElementById('btns-eventos').addEventListener('click', async function (e) {
        const button = e.target.closest('.event-btn')
        if (!button) return // No es un botón válido

        const tipo = parseInt(button.getAttribute('tipo'))
        const motivo = parseInt(button.getAttribute('motivoid'))
        const ciclo = parseInt(document.getElementById("ciclo_id").value)   

        if (isNaN(tipo)) return        

        if (ciclo > 0 && !isNaN(ciclo)) {
            await saveCiclo("save_cerrar_ciclo", { ciclo, estado: 1, tipo, usuario })
        }            

        const result = await Swal.fire({
            title: "¿Está seguro?",
            text: "Descartar operación Iniciada!",
            icon: "question",
            showCancelButton: true,
            allowOutsideClick: false,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, eliminar!",
            cancelButtonText: "No cancelar!"
        })

        if (result.isConfirmed) {
            if (tipo != 52) {
                const elevento = await saveCicloEvento('save_evento_ciclo_normal', { costura, ciclo, motivo, nombre, usuario, tipo, op, linea })

                if (elevento > 0) {
                    metodoGet("save_costura_datos", `usuario=${usuario}&linea=${linea}&costura=${costura}&op=${op}`)
                    loadingData(true)
                    const sendUrl = buildRedirectionUrl(tipo)
                    direccionar(sendUrl)
                }
            } else {
                const resultpermiso = await Swal.fire({
                    title: "¿Es un permiso con retorno?",
                    text: "Hoy volveras a trabajar?",
                    icon: "question",
                    showCancelButton: true,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Si, eliminar!",
                    cancelButtonText: "No cancelar!"
                })
                // 1: No, 2: Sí
                const con_permiso = 2
                // 3: Permiso con retorno, volveré a trabajar, 4: Permiso sin retorno
                const tipo_permiso =  (resultpermiso.isConfirmed) ? 3 : 4
                tipo = 2 // 2: Permiso

                payload = {con_permiso, tipo_permiso, tipo, idingreso}

                //console.log("payload save permiso ->", payload);
                const permiso = await endpoint('save_permiso', payload)
            }
        }
    })

    // Función para guardar el ciclo
    async function saveCiclo(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`
    
        try {
            const data = await postJSON(url, payload)
            if (data.code === 200) {
                if(metodo === "save_ciclo")
                    document.getElementById("ciclo_id").value = data.data.ciclo
            } else {
                document.getElementById("ciclo_id").value = 0
            }
        } catch (error) {
            console.error("Error al guardar ciclo:", error)
        }
    }
    
    // Función para guardar el evento ciclo
    async function saveCicloEvento(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`

        try {
            const data = await postJSON(url, payload)

            if (data.code === 200) {
                return parseInt(data.data.evento)
            }
        } catch (error) {
            console.error("Error al guardar evento ciclo:", error)
            return 0
        }
    }

    // Función para construir la URL de redirección
    function buildRedirectionUrl(tipo) {
        let pagina = ""
        if (tipo === 49) {
            pagina = "blank_soporte_ciclo/"
        } else {
            pagina = "blank_evento_ciclo_normal/"
        }
        return `${pagina}`
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
    
    // Ejecutar cada 3 minutos (180,000 ms)
    //setInterval(actualizarEficiencia, 3 * 60 * 1000)

    // También puedes llamar una vez al cargar la página si es necesario
    actualizarEficiencia()

    async function actualizarEficiencia() {
        porcentajeficiencia.value = 0
        const data = await metodoGet('get_eficiencia_x_hora',`usuario=${usuario}`, false)
        if (data) {
            let valorNumerico = data.eficiencia
            let eficiencia = `${valorNumerico}%`
            // Actualizar los valores en el eficiencia
            //document.getElementById("eficienciaxcolaborador").textContent = eficiencia
            //porcentajeficiencia.value = valorNumerico
        }
    }

    // Invocar a la los reprocesos
    getReproceso()
    // Función para obtener datos de los reprocesos
    async function getReproceso() {
        const data = await metodoGet('get_reprocesos',`usuario=${usuario}`)
        if (data) {
            // Actualizar los valores en el DOM
            reprocesos.textContent = data.reprocesos
        }
    }

    // Función para validar si existe una operación a eliminar
    async function getSalirApp() {
        const data = await metodoGet('get_salir_app',`usuario=${usuario}`)
        if (!data || typeof data.ciclo_id === 'undefined' || data.ciclo_id === null) {
            return 0 // o cualquier valor por defecto que prefieras
        }
        return data.ciclo_id
    }

    async function termiarOperacion(estado) {
        let ciclo = parseInt(document.getElementById("ciclo_id").value)
        let payload = { ciclo, usuario }

        estado = parseInt(estado)
        if(estado > 0)
            payload.estado = estado

        await saveCiclo("save_cerrar_ciclo", payload)
    }

    // Obtener Meta x OP x Línea x Día
    async function getMetaxDiaxLinea() {
        if (op <= 0 || !linea) return 0

        try {
            const data = await metodoGet("get_meta_x_op_linea_dia", `op=${op}&linea=${linea}`)
            return data?.meta ? parseInt(data.meta) : 0
        } catch {
            return 0
        }
    }

    // Obtener # de timbrados x OP x Línea x Día
    async function getTimbradasxDia() {
        if (op <= 0 || !linea) return 0

        try {
            const data = await metodoGet("get_cantidad_timbradas_x_dia", `op=${op}&linea=${linea}`)
            return data?.cant ? parseInt(data.cant) : 0
        } catch {
            return 0
        }
    }

    async function mostrarPorcentajeDeMetas() {
        let porcentaje = 0;
        porcentameta.value  = porcentaje.toFixed(2)

        try {
            const numTimbrados = await getTimbradasxDia();
            const numMeta = await getMetaxDiaxLinea();
            if (numMeta > 0) {
                porcentaje = (numTimbrados / numMeta) * 100;
            }
            const indicador = document.getElementById("indicator-value");
            if (indicador) {
                indicador.innerHTML  = `${numTimbrados} / ${numMeta}<br>${porcentaje.toFixed(2)} %`
                porcentameta.value  = porcentaje.toFixed(2)
            } else {
                console.warn("Elemento con ID 'indicator-value' no encontrado.")
            }
        } catch (error) {
            console.error("Error mostrando porcentaje de metas:", error)
        }
    }


    //Mostra el % de metas x dia
    mostrarPorcentajeDeMetas()

    // ✅ Declarar antes de geEvento o usar function
    function formatearTexto(texto) {
        const palabrasLargas = ['PAUSA ACTIVA', 'FALTA CARGA', 'SERVICIOS HIGIENICOS'];
        const upperTexto = texto.toUpperCase().trim();
        if (palabrasLargas.includes(upperTexto)) {
            return upperTexto.replace(/\s+/g, "<br>");
        }
        return upperTexto;
    }

    // Obtener el eventos
    //geEvento()
    // Obtener eventos desde el endpoint y construir botones
    async function geEvento() {
        const data = await metodoGet('get_evento_tipo', '')
        const contbtn = document.getElementById("btns-eventos")

        contbtn.innerHTML = ''; // Limpiar contenido actual

        if (data && Array.isArray(data)) {
            data.forEach(item => {
                const btn = document.createElement("button")
                btn.className = "event-btn"
                btn.setAttribute("motivoid", item.id)
                btn.setAttribute("tipo", item.tipo)
                btn.innerHTML = formatearTexto(item.motivo)
                
                contbtn.appendChild(btn)
            })
        } else {
            contbtn.innerHTML = '<h1 class="text-center">No hay eventos disponibles</h1>'
        }
    }


    // Función para obtener datos de un metodo
    async function metodoGet(metodo, param, mostrarLoader = true) {
        const url = `${urlapi}${metodo}/?${param}`
        try {
            if (mostrarLoader) loadingData(true);
            const response = await fetch(url)
            const data = await response.json()

            return data.code === 200 ? data.data : null
        } catch (error) {
            return null          
        } finally {
            if (mostrarLoader) loadingData(false);
        }
    }

    // Función para realizar la solicitud POST y manejar el JSON
    async function postJSON(url, data) {
        try {
            loadingData(true);
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            return await response.json()
        } catch (error) {
            return { code: 500, msn: "Error en fetch", data: null }
        } finally {
            loadingData(false);
        }
    }
    iniciar()
})