const modalOverlay = document.createElement("div");
modalOverlay.classList.add("modal-overlay");

// Crear el contenido del modal
const modalContent = document.createElement("div");
modalContent.classList.add("modal-content");

// Crear el botón de cierre
const closeBtn = document.createElement("span");
closeBtn.classList.add("close-btn");
closeBtn.innerHTML = "×";
closeBtn.onclick = () => closeModal(modalOverlay);

// Crear el elemento de video
const video = document.createElement("video");
video.src = 'http://192.168.150.42:8092/scriptcase/app/eCorporativoM/capacitacion.mp4';
video.controls = true;
video.autoplay = true;

// Agregar elementos al modal
modalContent.appendChild(closeBtn);
modalContent.appendChild(video);
modalOverlay.appendChild(modalContent);

// Agregar evento para cerrar el modal al hacer clic fuera del contenido
modalOverlay.onclick = (event) => {
	if (event.target === modalOverlay) {
		modalOverlay.classList.remove("active");
		setTimeout(() => document.body.removeChild(modalOverlay), 300);
	}
};

// Agregar el modal al cuerpo del documento
document.body.appendChild(modalOverlay);

// Mostrar el modal
setTimeout(() => modalOverlay.classList.add("active"), 10);