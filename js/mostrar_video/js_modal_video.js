function openModal(videoUrl) {    
    // Crear el fondo del modal
    let urlvideo = urlApp()
    urlvideo = `${urlvideo}/videos/${videoUrl}`
    const modalOverlay = document.createElement("div");
    modalOverlay.classList.add("modal-overlay");

    // Crear el contenido del modal
    const modalContent = document.createElement("div");
    modalContent.classList.add("modal-content");

    // Crear el botón de cierre
    const closeBtn = document.createElement("span");
    closeBtn.classList.add("close-btn");
    closeBtn.innerHTML = "&times;";
    closeBtn.onclick = () => closeModal(modalOverlay);

    // Crear el elemento de video
    const video = document.createElement("video");
    video.src = urlvideo;
    video.controls = true;
    video.autoplay = true;

    // Agregar elementos al modal
    modalContent.appendChild(closeBtn);
    modalContent.appendChild(video);
    modalOverlay.appendChild(modalContent);

    // Agregar evento para cerrar el modal al hacer clic fuera del contenido
    modalOverlay.onclick = (event) => {
        if (event.target === modalOverlay) {
            closeModal(modalOverlay);
        }
    };

    // Agregar el modal al cuerpo del documento
    document.body.appendChild(modalOverlay);

    // Mostrar el modal
    setTimeout(() => modalOverlay.classList.add("active"), 10);
}

// Obtener la url
function urlApp() {
    // ✅ Obtener el esquema (http o https)
    const scheme = window.location.protocol; // Incluye ":" al final

    // ✅ Obtener el host (ejemplo: example.com o localhost)
    const host = window.location.host;

    // ✅ Construir la URL base
    const baseUrl = `${scheme}//${host}`;

    // ✅ Obtener la ruta del script (equivalente a $_SERVER['REQUEST_URI'])
    const requestUri = window.location.pathname;

    // ✅ Subir 2 niveles en la ruta
    const scriptDir = requestUri.split('/').slice(0, -2).join('/');

    // ✅ Construir la URL final eliminando la última barra si existe
    return `${baseUrl}${scriptDir}`.replace(/\/+$/, '');
}

// ✅ Función para cerrar el modal
function closeModal(modalOverlay) {
    modalOverlay.classList.remove("active");
    setTimeout(() => document.body.removeChild(modalOverlay), 300);
}