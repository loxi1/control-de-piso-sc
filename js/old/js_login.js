document.addEventListener('DOMContentLoaded', () => {
    const urlapi = document.querySelector('input[name="api"]').value
    const btnEmpezar = document.getElementById('senddatos')
    const inputCodigo = document.getElementById('codigo')
    const container = document.querySelector(".container")
    let listaTurnos = []  // ⬅️ Aquí guardamos los turnos cargados

    if (inputCodigo && container) {
        inputCodigo.addEventListener("focus", () => {
            container.classList.add("form-focus")
        })

        inputCodigo.addEventListener("blur", () => {
            container.classList.remove("form-focus")
        })
    }

    //Funcion para activar y desactivar el preload
    const loadingData = (estado) => {
        const loading = document.querySelector("#preloader")
        document.body.style.overflow = estado ? "hidden" : "auto"

        if (estado)
            loading.classList.remove("d-none")
        else
            loading.classList.add("d-none")
    }

    // Detectar tecla Enter (key = 'Enter') o Tab (key = 'Tab')
    inputCodigo.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault()  // Prevenir comportamiento por defecto (especialmente Tab)
            btnEmpezar.click()      // Disparar el click del botón
        }
    })

    btnEmpezar.addEventListener('click', async function () {
        const codigo = inputCodigo.value.trim();

        if (!codigo) {
            Swal.fire("Aviso", "Ingrese el código del operario.", "error");
            return;
        }

        let payload = { codigo }
        console.log("login_operario ->", payload);

        let metodo = "login_colaborador"
        const data = await endpoint(metodo, payload)
        console.log("data login ->", data)

        if (parseInt(data.code) !== 200) {
            Swal.fire({
                title: "Error!",
                text: "No existe operario.",
                icon: "error"
            })
            return;
        }
        // 🔄 Cargar turnos
        metodo = "get_turno"
        const datos = await metodoGet(metodo, `nmgp_outra_jan=true`)
        console.log("datos turnos ->", datos)
        listaTurnos = Array.isArray(datos) ? datos : []

        // 📌 Turno: si hay más de 1, mostrar SweetAlert select
        let turno = null;

        if (listaTurnos.length === 0) {
            Swal.fire("Aviso", "No hay turnos cargados.", "error")
            return
        }

        if (listaTurnos.length === 1) {
            turno = listaTurnos[0].id
        } else {
            const inputOptions = {};
            listaTurnos.forEach(t => inputOptions[t.id] = t.turno)

            const { value: selected } = await Swal.fire({
                title: "A que turno perteneces?",
                input: "select",
                inputOptions,
                allowOutsideClick: false,
                allowEscapeKey: false,
                inputPlaceholder: "Selecciona un turno",
                showCancelButton: false,
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (value) resolve()
                        else resolve("Debes seleccionar un turno.")
                    })
                }
            })

            if (!selected) return;

            turno = parseInt(selected)
        }

        payload.turno = turno

        console.log("payload con turno ->", payload)

        // Validar hora de ingreso
        const validaTurno = await endpoint("get_validar_hora_ingreso", payload);
        let tienePermiso = (parseInt(validaTurno.code) === 200 && parseInt(validaTurno.data.code) === 2)
        console.log("data validarTurno", validaTurno)
        payload.id = validaTurno.data.id
        // 1 = ingreso, 2 = salida
        payload.tipo = 1
        // 1: No, 2: Sí
        payload.con_permiso = 1
        payload.fecha_permiso = validaTurno.data.horario_ingreso
        // 1: Ingreso puntual, 2 Ingreso tarde, 
        // 3: Permiso con retorno, volveré a trabajar, 4: Permiso sin retorno,, 5: Permiso refriegerio, 6: Salida
        payload.tipo_permiso = 1
        if (tienePermiso) {
            const result = await Swal.fire({
                title: validaTurno.data.titulo,
                text: validaTurno.data.descripcion,
                icon: "warning",
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, lo tengo!",
                cancelButtonText: "No!"
            })

            payload.tipo_permiso = 2

            if (result.isConfirmed) {
                payload.fecha_permiso = "",
                    payload.con_permiso = 2
            }
        }

        //console.log("payload save permiso ->", payload);
        const permiso = await endpoint('save_permiso', payload)
        direccionar(`blank_login_operario/?codigo=${codigo}&id=${validaTurno.data.id}`);
    })

    // Función para enviar al endpoint
    async function endpoint(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`
        try {
            const data = await postJSON(url, payload)
            //console.log("<-- data ->", data)
            return data
        } catch (error) {
            //console.error("Error en endpoint:", error)
            return { code: 405, msn: "Error al enviar datos", data: null }
        }
    }

    // Función para realizar POST y manejar JSON
    async function postJSON(url, data) {
        try {
            loadingData(true)
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error("Fetch error:", error)
            return { code: 500, msn: "Error en fetch", data: null }
        } finally {
            loadingData(false)
        }
    }

    // Función principal para manejar la salida o redirección
    function direccionar(url) {
        loadingData(true)
        window.top.location.href = `${urlapi}${url}`
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
})