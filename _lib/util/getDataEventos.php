<?php
require_once(__DIR__ . '/funciones.php');

function getEventos():string {
    $btns = "<h1 class='text-center'>Crear eventos</h1>";
    try {
        
        $connMysql = conectar_mysql();
        // ✅ Insertar en MySQL
        $sql = "
            select codigo_motivo, motivo, tipo_actividad_id 
            from motivo where caracteristica_id=5 and codigo_motivo<>0 and visible=2 order by orden asc;
        ";
        
        $stmt = $connMysql->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result || count($result) === 0) {
            return $btns;
        }
        $html = "";
        foreach ($result as $row) {
            $motivoId = (int)$row['codigo_motivo'];
            $tipoId   = (int)$row['tipo_actividad_id'];
            $motivo   = $row['motivo'];
            $html .= '<button class="event-btn" motivoid="' . $motivoId . '" tipo="' . $tipoId . '">' . $motivo . '</button>'; 
        }
        return $html;
    } catch (Exception $e) {
        return $btns;
    }
}