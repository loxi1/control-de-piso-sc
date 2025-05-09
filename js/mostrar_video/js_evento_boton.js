let operacionValue = $('#id_sc_field_operacion').val().trim() || ""

if(operacionValue.length > 0)
	openModal('20250509_101016.mp4')
else 
	Swal.fire({
	  icon: "error",
	  title: "Oops...",
	  text: "¡Seleccionar Operación!"
	})