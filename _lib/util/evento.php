<?php
require_once(__DIR__ . '/conexion.php');

function getEventos():string {
    $btns = "<h1 class='text-center'>Crear eventos</h1>";
    $conf = EnvConfig::getMySQL();
    $conn = PDOp::mysql($conf['mes']);

    if (!$conn) return $btns;

    try {
        //🔎 Buscar informacion
        $sql = "SELECT codigo_motivo, motivo, tipo_actividad_id 
                FROM motivo 
                WHERE caracteristica_id=5 AND visible=2 AND codigo_motivo<>0 
                ORDER BY orden 
                ASC";

        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result || count($result) === 0) {
            return $btns;
        }
        // ✅ Generar botones HTML
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
    } finally {
        if ($conn) {
            $conn = null; // Cerrar conexión
        }
    }
}