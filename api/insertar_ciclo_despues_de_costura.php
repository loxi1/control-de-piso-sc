<?php
[vg_costura_id] = {costura_id};

//Obtener parametros
session_start();
$idcost = intval([vg_costura_id]);
$idingreso = intval($_SESSION["ingreso_id"] ?? 0);
$codigouser = $_SESSION["usr_login"] ?? "";
$nombreusuario = $_SESSION["usr_name"] ?? "";

/*print_r("idingreso->$idingreso codigouser->$codigouser nombreusuario->$nombreusuario"); die();*/
//Registrar el ciclo des pues de registrar la constura. tiempo_inicio = fecha_creacion (costura)
$fechaingreso = null;
if($idcost>0 && $idingreso>0) {
	//Verificar si existe permiso
	$sqlexiste = "select id, fecha_permiso, tipo_permiso, tipo from permiso where codigo='$codigouser' and ingreso_id=$idingreso and estado=1";
    sc_lookup(rs_existe, $sqlexiste);
    if (!empty({rs_existe}[0][0])) {
		//1 Ingreso, 2 Permiso
		$tipo = {rs_existe}[0][3] ?? "Ingreso";
		switch ($tipo) {
			//La fecha y hora que debe contar es el primer registr del login
			case "Ingreso":
				$fechaingreso = {rs_existe}[0][1];
				//Cambiar estado al permiso estado: 2 (ejecutado)
				$sqlupdate = "UPDATE permiso SET fecha_modificacion=now(), estado=2 WHERE id=".{rs_existe}[0][0];
        		sc_exec_sql($sqlupdate);
				break;
			//Permiso con retorno: Estuve trabajando, pero tuve que ausentarme temporalmente.
			case "Permiso":
				$tipopermiso = {rs_existe}[0][2] ?? "";
				if($tipopermiso == "Permiso con retorno" || $tipopermiso == "Permiso sin retorno") {
					$sqlupdate = "UPDATE permiso SET fecha_modificacion=now(), fecha_permiso=now(), estado=2 WHERE id=".{rs_existe}[0][0];
					sc_exec_sql($sqlupdate);
				}
				break;
		}
	}
	
	$sql = "insert into ciclo (costua_id,usuario_nombre,usuario_registra, ingreso_id) select costura_id, '$nombreusuario', '$codigouser',$idingreso  from costura where costura_id=$idcost";
    sc_exec_sql($sql);
	
	$sql_id = "SELECT LAST_INSERT_ID()";
    sc_lookup(rs_id, $sql_id);

    if (!empty({rs_id}[0][0]) && !empty($fechaingreso)) {
		$sqlupciclo = "update ciclo set tiempo_inicio='$fechaingreso' where ciclo_id=".{rs_id}[0][0];
		sc_exec_sql($sqlupciclo);
	}
	
	$sql = "update costura set fecha_creacion=(select tiempo_inicio from ciclo where costua_id=$idcost limit 1) where costura_id=$idcost";
	sc_exec_sql($sql);
}
sc_commit_trans();
sc_redir(blank_evento_costura);