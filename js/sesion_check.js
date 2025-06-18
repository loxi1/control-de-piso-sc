let remainingTime = 0;
let intervalId = null;

// Llama al servidor para obtener tiempo restante
function checkSession() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "session_status.php", true);
    xhr.onload = function () {
        if (xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.remaining > 0) {
                remainingTime = response.remaining;
                startCountdown();
            } else {
                stopCountdown();
                let total = response.elapsed;
                let minutes = Math.floor(total / 60);
                let seconds = total % 60;
                document.getElementById("session_status").innerText =
                    `Sesión expirada. Duración total: ${minutes}m ${seconds}s`;
            }
        }
    };
    xhr.send();
}

// Empieza el cronómetro regresivo
function startCountdown() {
    if (intervalId !== null) return;

    intervalId = setInterval(() => {
        if (remainingTime <= 0) {
            stopCountdown();
            checkSession(); // verificar estado exacto en servidor
            return;
        }

        let minutes = Math.floor(remainingTime / 60);
        let seconds = remainingTime % 60;
        document.getElementById("session_status").innerText =
            `Tiempo restante: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        remainingTime--;
    }, 1000);
}

// Detiene el cronómetro
function stopCountdown() {
    clearInterval(intervalId);
    intervalId = null;
}

// Verifica sesión cada 30 segundos
setInterval(checkSession, 30000);
window.onload = checkSession;

/**
 * en caso no haya interaccion con la DB se coloca
 * setInterval(() => {
  fetch("keep_alive.php");
}, 60000); // cada 1 minuto
 */