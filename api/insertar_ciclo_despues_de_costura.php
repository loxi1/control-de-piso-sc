<?php
//onAfterInsert
[vg_costura_id] = {costura_id};

//Obtener parametros
$idcost = intval([vg_costura_id]);

//Registrar el ciclo des pues de registrar la constura. tiempo_inicio = fecha_creacion (costura)
if($idcost>0) {
	$sql = "insert into ciclo (costua_id,tiempo_inicio,usuario_nombre,usuario_registra) select costura_id, fecha_creacion, usuario_nombre, usuario_registra from costura where costura_id=$idcost";
	
    sc_exec_sql($sql);
}

sc_commit_trans();
sc_redir(blank_evento_costura);