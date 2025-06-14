<?php
require_once('../_lib/util/session_check.php');
require_once('../_lib/util/funciones.php');
function get_data_meta(array $param):float {
    $compania = "02";
    $empresa = "COFACO";
    $op = $param['op'] ?? null;
    $linea = $param['linea'] ?? null;

    if(empty($op) || empty($linea)) return 0;   

    //Cantidad de timbradas
    $select_sql_sybase = "
        SELECT SUM(cant) AS total
        FROM (
            select COUNT(*) as cant from ordencortetallasmov
            WHERE 
            ccmpn='$compania'
            AND nnope='$op'
            AND cod_equipo='$linea'
            AND fechamodifica >= CAST(GETDATE() AS DATE)
            AND fechamodifica < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa='$empresa'
            UNION ALL
            select COUNT(*) as cant from ordencortetallasmov 
            where 
            ccmpn='$compania' 
            AND nnope='$op' 
            AND cod_equipo='$linea' 
            AND fechSalCostExt2 >= CAST(GETDATE() AS DATE)
            AND fechSalCostExt2 < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
            AND empresa='$empresa'
        ) AS total_prendas
    ";

    sc_lookup(rs_data_sybase, $select_sql_sybase);

    if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
        return 0;
    }
    // ✅ Armar respuesta
    $canttimbradas = {rs_data_sybase}[0][0] ?? 0;

    if($canttimbradas == 0) {
        return 0;
    }

    //Meta del día
    $area = "SALIDA DE COSTURA";
    $select_sql_sybase = "SELECT TOP 1 cantmeta
    FROM meta_linea_areas
    WHERE ccmpn = '02'
    AND nnope = '$op'
    AND fecha >= CAST(GETDATE() AS DATE)
    AND fecha < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))
    AND linea = '$linea'
    AND empresa='$empesa'
    AND area='$area'
    ";

    sc_lookup(rs_data_sybase, $select_sql_sybase);

    if (!isset({rs_data_sybase}) || !is_array({rs_data_sybase})) {
        return 0;
    }

    if (count({rs_data_sybase}) === 0) {
        return 0;
    }

    // ✅ Armar respuesta
    $meta = {rs_data_sybase}[0][0] ?? 0;

    if($meta == 0) {
        return 0;
    }

    return ($meta > 0) ? ($canttimbradas * 100) / $meta : 0;
}

