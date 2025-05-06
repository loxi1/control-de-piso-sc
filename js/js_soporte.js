document.addEventListener('DOMContentLoaded', () => {
    const Alerta = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
    });

    // Variables para el primer temporizador (principal)
    let timerPrincipal;
    let isRunningPrincipal = false;
    const btnEmpezarPrincipal = document.getElementById('btnempezar');
    const timerDisplayPrincipal = document.getElementById('timerDisplay');
    const bgContBtnPrincipal = document.getElementById('btns');
    
    const problemaid = document.getElementById('problemaid');
    let secondsPrincipal = parseInt(document.getElementById('segundos').value) || 0;
    const urlapi = document.querySelector('input[name="api"]').value;
    const soporteid = document.querySelector('input[name="evento_soporte_id"]').value;
    const cicloid = document.querySelector('input[name="ciclo_id"]').value;
    const usuario = document.querySelector('input[name="usuario"]').value;

    // Variables para el segundo temporizador (mecánico)
    let timerMecanico;
    let isRunningMecanico = false;
    const btnInicioMecanico = document.getElementById('btniniomecanico');
    const timerDisplayMecanico = document.getElementById('timerDisplayMecanico');
    let secondsMecanico = parseInt(document.getElementById('segatencion').value) || 0;

    const elMecanico = document.getElementById("nombre_mecanico");

    const problema = parseInt(document.getElementById('problema').value, 10);        
    
    const mecanicoNombre = document.getElementById("nombre_mecanico").textContent;

    let mecanicoActual = "";

    iniciarTemporizadorPrincipal()

    if(secondsMecanico > 0) {
        iniciarTemporizadorMecanico()
        btnInicioMecanico.style.display = 'none';
    }

    if(problema >0) {
        problemaid.value = problema;
    }

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

    // Actualizar temporizador principal
    function updateTimerPrincipal() {
        secondsPrincipal++;
        timerDisplayPrincipal.textContent = formatTime(secondsPrincipal);
    }

    // Iniciar/Parar temporizador principal
    function toggleTimerPrincipal() {
        if (!isRunningPrincipal) {
            iniciarTemporizadorPrincipal();
        } else {
            pararTemporizadorPrincipal();
        }

        if (soporteid < 1 || cicloid < 1) {
            return null;
        }

        let tfatencion = 1

        let payload = {
            cicloid,
            tfatencion,
            soporteid,
            usuario
        }

        saveSoporte('save_evento_soporte', payload);
        AlertaToast("Mecánico Iniciodo ok!");

        window.top.location.href = `${urlapi}blank_evento_costura/`;        
    }

    function iniciarTemporizadorPrincipal() {
        isRunningPrincipal = true;
        timerDisplayPrincipal.textContent = "00:00:00";
        timerPrincipal = setInterval(updateTimerPrincipal, 1000);
        btnEmpezarPrincipal.textContent = 'FINALIZAR';
        bgContBtnPrincipal.classList.add('bg-finalizar');
        bgContBtnPrincipal.classList.remove('bg-inicio');
    }

    function pararTemporizadorPrincipal() {
        isRunningPrincipal = false;
        clearInterval(timerPrincipal);
        btnEmpezarPrincipal.textContent = 'INICIO';
        bgContBtnPrincipal.classList.add('bg-inicio');
        bgContBtnPrincipal.classList.remove('bg-finalizar');
        btnEmpezarPrincipal.disabled = true;
    }

    // Actualizar temporizador mecánico
    function updateTimerMecanico() {
        secondsMecanico++;
        timerDisplayMecanico.textContent = formatTime(secondsMecanico);
    }

    // Iniciar/Parar temporizador mecánico
    function toggleTimerMecanico() {
        if (!isRunningMecanico) {
            iniciarTemporizadorMecanico();            
            btnInicioMecanico.style.display = 'none'; // Ocultar el boton del mecanico
            if (soporteid < 1 || cicloid < 1) {
                return null;
            }
            let tiatencion = 1

            let payload = {
                tiatencion,
                soporteid,
                usuario
            }
    
            saveSoporte('save_evento_soporte', payload);
            AlertaToast("Mecánico Iniciodo ok!");            
        } else {
            pararTemporizadorMecanico();
        }
    }

    function iniciarTemporizadorMecanico() {
        isRunningMecanico = true;
        timerDisplayMecanico.textContent = "00:00:00";
        timerMecanico = setInterval(updateTimerMecanico, 1000);
        btnInicioMecanico.textContent = 'FINALIZAR'; // Opcional: cambiar texto del botón
        btnInicioMecanico.classList.add('bg-finalizar');
        btnInicioMecanico.classList.remove('bg-inicio');
    }

    function pararTemporizadorMecanico() {
        isRunningMecanico = false;
        clearInterval(timerMecanico);
        btnInicioMecanico.textContent = 'INICIO'; // Opcional: cambiar texto del botón
        btnInicioMecanico.classList.add('bg-inicio');
        btnInicioMecanico.classList.remove('bg-finalizar');
        btnInicioMecanico.disabled = true;
    }

    // Event listeners
    btnEmpezarPrincipal.addEventListener('click', toggleTimerPrincipal);

    // Asegúrate de que el botón con id "btniniomecanico" exista en tu HTML
    if (btnInicioMecanico) {
        btnInicioMecanico.addEventListener('click', toggleTimerMecanico);

    }

    // Limpiar al cerrar (se limpian ambos intervalos si están activos)
    window.addEventListener('beforeunload', function () {
        if (isRunningPrincipal) {
            clearInterval(timerPrincipal);
        }
        if (isRunningMecanico) {
            clearInterval(timerMecanico);
        }
    });

    //Evento change de problemaid
    problemaid.addEventListener('change', function () {
        //Mostra un # decimal
        let problema = Math.max(parseInt(problemaid.value, 10) || 0, 0);

        if (soporteid < 1 || cicloid < 1) {
            return null;
        }
        let payload = {
            problema,
            soporteid,
            usuario
        }

        saveSoporte('save_evento_soporte', payload);
        AlertaToast("Problema guardado ok!");     
    });

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
        });
    }    

    // Llamar cada 5 segundos
    async function actualizarMecanico() {
        const mecanico = await getMecanico(soporteid);
        console.log("Mecanico: ", mecanico);
        console.log("Mecanico Actual: ", mecanicoActual);
        if (mecanico && mecanico !== mecanicoActual) {
            mecanicoActual = mecanico;
            if (elMecanico) {
                elMecanico.textContent = mecanicoActual;
            } else {
                console.warn("Elemento #nombre_mecanico no encontrado en el DOM.");
            }
        }
    }
    
    // Llamar inmediatamente y luego cada 5 segundos
    actualizarMecanico();
    setInterval(actualizarMecanico, 5000);
    
    // Función para obtener el nombre del mecánico desde el endpoint GET
    async function getMecanico(id) {
        const url = `${urlapi}get_mecanico/?id=${id}&nmgp_outra_jan=true`;
        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.code === 200) {
                return data.data.mecanico;
            } else {
                console.error("Error en la respuesta:", data);
                return null;
            }
        } catch (error) {
            console.error("Error al obtener el mecánico:", error);
            return null;
        }
    }

    // Función para guardar el evento
    async function saveSoporte(metodo, payload = {}) {
        const url = `${urlapi}${metodo}/?nmgp_outra_jan=true`;        
        try {
            const data = await postJSON(url, payload);
            if (data.code === 200) {
                evento = data.data.soporte;
            } else {
                document.getElementById("ciclo_id").value = 0;
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