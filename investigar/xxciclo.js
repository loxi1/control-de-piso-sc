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
    const reprocesos = document.getElementById("reprocesos")

    //Variables necesarias
    const usuario = document.querySelector('input[name="usuario"]').value
    const nombre = document.getElementById("nombre_usuario").value.trim()
    const costura = parseInt(document.getElementById("costura_id").value)
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
            parar()
            iniciar()

        }
        let ciclo = parseInt(document.getElementById("ciclo_id").value)
        
        //saveCiclo("save_costura_datos", { costura, meta, eficiencia, usuario })
        saveCiclo("save_ciclo", { costura, ciclo, usuario, nombre, idingreso })
    })

    //Salir
    btnsalir.addEventListener('click', async function () {
        const vaSalir = await getSalirApp()
        const ncicloid = parseInt(vaSalir.ciclo_id) || 0
        const cerrar = vaSalir.cerrarsession

        if(ncicloid > 0) {
            document.getElementById("ciclo_id").value = ncicloid
            const result = await Swal.fire({ // Espera el resultado de la confirmación
                title: "¿Desea eliminar esta operación?",
                text: "Descartar operación Iniciada!",
                icon: "warning",
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No cancelar!"
            })

            let estado = (result.isConfirmed) ? 3 :2

            termiarOperacion(estado)
        }
        
       if (cerrar) {
            const rtasalir = await Swal.fire({ // Espera el resultado de la confirmación
                title: "¿Finalizó su jornada laboral?",
                text: "Está a punto de cerrar sesión. ¿Desea confirmar que ha terminado por hoy?",
                icon: "warning",
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, he terminado",
                cancelButtonText: "No, continuaré trabajando"
            })

            if (rtasalir.isConfirmed) {
                const con_permiso = 1            // 1: No, 2: Sí        
                const tipo_permiso = 6          // 6: Salida
                const tipo = 3                 // 3: Salida

                payload = { con_permiso, tipo_permiso, tipo }
                payload.codigo = usuario
                payload.id = idingreso
                console.log("payload save permiso ->", payload);
                const permiso = await saveCiclo('save_registro_permiso', payload)
            } else {
                await saveCiclo('logout', {})
            }
        }
        direccionar('app_Login_costura')
    })
    
    // Atras
    btnatras.addEventListener('click', async function () {
        const vaSalir = await getSalirApp()
        const ncicloid = parseInt(vaSalir.ciclo_id) || 0

        if(ncicloid > 0) 
            await termiarOperacion(3)

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

        let tipoEvento = Math.max(parseInt(button.getAttribute('tipo')) || 0, 0)
        const motivo = parseInt(button.getAttribute('motivoid'))
        const ciclo = parseInt(document.getElementById("ciclo_id").value)  

        if (!tipoEvento) return

        await mostrarPorcentajeDeMetas()
      
        let esevento = (tipoEvento == 52 || tipoEvento == 51) ? false : true

        const result = await Swal.fire({
            title: "¿Está seguro?",
            text: "Descartar operación Iniciada!",
            icon: "warning",
            showCancelButton: true,
            showCancelButton: true,
            allowOutsideClick: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, eliminar!",
            cancelButtonText: "No cancelar!"
        })

        if (result.isConfirmed) {
            if (ciclo > 0 && !isNaN(ciclo)) {
                await saveCiclo("save_cerrar_ciclo", { ciclo, estado: 1, tipo: tipoEvento , usuario })
            }

            if (esevento) {
                await saveCicloEvento('save_evento_ciclo_normal', { idingreso, costura, ciclo, motivo, nombre, usuario, tipo: tipoEvento })
                Swal.fire({
                    title: "Eliminado!",
                    text: "Esta direccionando al soporte.",
                    icon: "success"
                })
                return
            }
            // Permiso con refrierio
            let tipo_permiso = 5
            //Tipo Ingreso
            tipo = 2
            
            // 1: No, 2: Sí
            const con_permiso = 2

            if (tipoEvento == 52) {
                const resultpermiso = await Swal.fire({
                    title: "¿El permiso incluye retorno?",
                    text: "",
                    icon: "question",
                    showCancelButton: true,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Con retorno",
                    cancelButtonText: "Sin retorno"
                })
                // 3: Permiso con retorno, volveré a trabajar, 4: Permiso sin retorno
                tipo_permiso = (resultpermiso.isConfirmed) ? 3 : 4
                tipo = tipo_permiso == 3 ? 2 : 3 // 2: Permiso y 3: Salida
            }

            payload = { con_permiso, tipo_permiso, tipo }
            payload.codigo = usuario
            payload.id = idingreso
            const permiso = await saveCiclo('save_registro_permiso', payload)
            direccionar('app_Login_costura')
        }
    })

    // Función para guardar el ciclo
    async function saveCiclo(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`
    
        try {
            const data = await postJSON(url, payload)
            if (data.code === 200) {
                if(metodo === "save_ciclo") {
                    document.getElementById("ciclo_id").value = data.data.ciclo
                    let eficiencia = `${data.data.cantidad}  pds <br> ${data.data.eficiencia}%`
                    document.getElementById("eficienciaxcolaborador").innerHTML = eficiencia
                }                    
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
                const evento = parseInt(data.data.evento)

                if (evento > 0) {
                    const sendUrl = buildRedirectionUrl(payload.tipo)
                    direccionar(sendUrl)
                }
            }
        } catch (error) {
            console.error("Error al guardar evento ciclo:", error)
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
    document.getElementById("eficienciaxcolaborador").textContent = "0.00%"
    async function actualizarEficiencia() {
        const data = await metodoGet('get_eficiencia_x_hora',`idingreso=${idingreso}`, false)
        if (data) {            
            let eficiencia = `${data.cantidad} pds <br> ${data.eficiencia}%`
            // Actualizar los valores en el eficiencia
            document.getElementById("eficienciaxcolaborador").innerHTML = eficiencia
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
            return { "ciclo_id": 0, "cerrarsession": false} // o cualquier valor por defecto que prefieras
        }
        return data
    }

    async function termiarOperacion(estado) {
        let ciclo = parseInt(document.getElementById("ciclo_id").value)
        let payload = { ciclo, usuario, idingreso }

        estado = parseInt(estado)
        if(estado > 0)
            payload.estado = estado

        await saveCiclo("save_cerrar_ciclo", payload)
    }

    // Obtener Meta x OP x Línea x Día
    async function getMetaxDiaxLinea() {
        const op = parseInt(document.getElementById("es_op")?.value) || 0
        const linea = document.getElementById("linea").value.trim() || ""

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
        const op = parseInt(document.getElementById("es_op")?.value) || 0
        const linea = document.getElementById("linea").value.trim() || ""

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

        try {
            const numTimbrados = await getTimbradasxDia();
            const numMeta = await getMetaxDiaxLinea();
            if (numMeta > 0) {
                porcentaje = (numTimbrados / numMeta) * 100;
            }
            const indicador = document.getElementById("indicator-value");
            if (indicador) {
                indicador.innerHTML  = `${numTimbrados} / ${numMeta}<br>${porcentaje.toFixed(2)} %`
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

            if (response.status === 401 || response.status === 403) {
                direccionar('app_Login_costura')
                return { code: 401, msn: "Sesión expirada", data: null }
            }

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

            if (response.status === 401 || response.status === 403) {
                direccionar('app_Login_costura')
                return { code: 401, msn: "Sesión expirada", data: null }
            }

            return await response.json()
        } catch (error) {
            return { code: 500, msn: "Error en fetch", data: null }
        } finally {
            loadingData(false);
        }
    }
    iniciar()
})
