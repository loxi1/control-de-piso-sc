document.addEventListener('DOMContentLoaded', () => {
    const urlapi = document.querySelector('input[name="api"]').value
    const btnEmpezar = document.getElementById('senddatos')
    const inputCodigo = document.getElementById('codigo')
    
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
    inputCodigo.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault()  // Prevenir comportamiento por defecto (especialmente Tab)
            btnEmpezar.click()      // Disparar el click del botón
        }
    })

    btnEmpezar.addEventListener('click', async function () {
        const codigo = inputCodigo.value.trim();
        if (!codigo) {
            Swal.fire("Aviso", "Ingrese el código del operario.", "warning");
            return;
        }

        let payload = { codigo }
        console.log("payload ->", payload);

        const metodo = "login_operario"
        const data = await endpoint(metodo, payload)
        console.log("data ->", data)

        if (parseInt(data.code) !== 200) {
            Swal.fire({
                title: "Error!",
                text: "No existe operario.",
                icon: "error"
            })
            return;
        }

        // Validar hora de ingreso
        const validaTurno = await endpoint("get_validar_hora_ingreso", payload);        
        let tienePermiso = (parseInt(validaTurno.code) === 200 && parseInt(validaTurno.data.code) === 2)
        
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

            if (result.isConfirmed) {
                payload.id = validaTurno.data.id
                payload.fecha_permiso = validaTurno.data.horario_ingreso
                console.log("payload save permiso ->", payload);
                const permiso = await endpoint('save_permiso', payload)
            }
        }
        direccionar(`blank_login_operario/?codigo=${codigo}&id=${validaTurno.data.id}`);
    })

    // Función para enviar al endpoint
    async function endpoint(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`
        try {
            const data = await postJSON(url, payload)
            console.log("<-- data ->", data)
            return data
        } catch (error) {
            console.error("Error en endpoint:", error)
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
})