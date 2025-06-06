<?php
[vg_costura_id] = {costura_id};

//Obtener parametros
$idcost = intval([vg_costura_id]);

//Registrar el ciclo des pues de registrar la constura. tiempo_inicio = fecha_creacion (costura)
if($idcost>0) {
	$sql = "insert into ciclo (costua_id,usuario_nombre,usuario_registra) select costura_id, usuario_nombre, usuario_registra from costura where costura_id=$idcost";	
    sc_exec_sql($sql);
	
	$sql = "update costura set fecha_creacion=(select tiempo_inicio from ciclo where costua_id=$idcost limit 1) where costura_id=$idcost";
	sc_exec_sql($sql);
}

// Obtener URL base de forma segura
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST'];

$script_dir = dirname($_SERVER['PHP_SELF']); // Más preciso que REQUEST_URI en Scriptcase
$api = rtrim($base_url . $script_dir, '/');

// Armar URL destino
$url_destino = $api . '/blank_evento_costura/';
sc_alert($url_destino);
//sc_ajax_javascript('redirConDelay', array($url_destino));
sc_commit_trans();