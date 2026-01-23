<?php
require_once 'Model/DataBase.php';

// Config
$ENCUESTA_ID = 1;
$TOTAL_RECORDS = 1000;

try {
    $pdo = DataBase::conectar();
    echo "Iniciando Seeding para Encuesta ID: $ENCUESTA_ID\n";

    // 1. Get Questions
    $stmtQ = $pdo->prepare("SELECT * FROM encuestas_preguntas WHERE encuesta_id = ?");
    $stmtQ->execute([$ENCUESTA_ID]);
    $preguntas = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($preguntas)) die("No hay preguntas para esta encuesta.\n");

    // 2. Get Branches
    // Schema said 'activo' TINYINT(1).
    $stmtS = $pdo->query("SELECT id FROM sucursales WHERE activo = 1");
    $sucursales = $stmtS->fetchAll(PDO::FETCH_COLUMN);

    if (empty($sucursales)) $sucursales = [1]; // Fallback

    // 3. Generators
    $comentariosPos = ["Excelente servicio", "Muy amables", "Todo bien", "Rápido y eficiente", "Me encantó", "Gran experiencia", "Volveré pronto", "De lo mejor", "Limpio y ordenado", "Gracias"];
    $comentariosNeg = ["Muy lento", "Mala atención", "Sucio", "No me gustó", "Muy caro", "Pésimo servicio", "Tardaron mucho", "El personal grosero", "No tenían cambio", "Malo"];
    $comentariosNeu = ["Regular", "Puede mejorar", "Normal", "X", "Pasable", "Sin comentarios", "Ok", "Bien a secas"];

    // Transaction
    $pdo->beginTransaction();

    $count = 0;
    for ($i = 0; $i < $TOTAL_RECORDS; $i++) {
        
        // Random Data
        $sucursalId = $sucursales[array_rand($sucursales)];
        $fecha = date('Y-m-d H:i:s', strtotime('-' . rand(0, 60) . ' days -' . rand(0, 23) . ' hours'));
        
        // Insert Header
        $sqlHead = "INSERT INTO encuestas_respuestas (encuesta_id, sucursal_id, ip_cliente, user_agent, fecha_respuesta, duracion_segundos) 
                    VALUES (:enc, :suc, :ip, :ua, :fecha, :dur)";
        $stmtHead = $pdo->prepare($sqlHead);
        $stmtHead->execute([
            ':enc' => $ENCUESTA_ID,
            ':suc' => $sucursalId,
            ':ip' => '127.0.0.' . rand(1, 255),
            ':ua' => 'SeederBot/1.0',
            ':fecha' => $fecha,
            ':dur' => rand(30, 480) // 30s to 8 mins
        ]);
        $respId = $pdo->lastInsertId();

        // Generate Answers
        foreach ($preguntas as $p) {
            $val = '';
            $comment = null;

            if ($p['tipo_pregunta'] === 'nps') {
                // Weighted Random for NPS
                $r = rand(1, 100);
                if ($r > 60) $nps = rand(9, 10); // 40% Promoters
                elseif ($r > 30) $nps = rand(7, 8); // 30% Passives
                else $nps = rand(0, 6); // 30% Detractors
                $val = $nps;
            } elseif ($p['tipo_pregunta'] === 'botonera') {
                $opts = json_decode($p['opciones_json'] ?? '[]', true);
                if (empty($opts)) $opts = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
                $val = $opts[array_rand($opts)];
                
                // Add comment dependent on value
                if (stripos($val, 'problema') !== false) {
                    $comment = $comentariosNeg[array_rand($comentariosNeg)];
                } elseif (stripos($val, 'experiencia') !== false) {
                    if (rand(0, 1)) $comment = $comentariosPos[array_rand($comentariosPos)];
                } else {
                    if (rand(0, 1)) $comment = $comentariosNeu[array_rand($comentariosNeu)];
                }

            } elseif ($p['tipo_pregunta'] === 'texto') {
                if (rand(0, 10) > 7) { // 30% chance of text
                    $val = $comentariosNeu[array_rand($comentariosNeu)];
                } else {
                    $val = "-";
                }
            } else {
                 $val = "Respuesta simulada";
            }

            // Insert Detail
            $sqlDet = "INSERT INTO encuestas_respuestas_detalle (respuesta_id, pregunta_id, valor_respuesta, comentario) 
                       VALUES (:rid, :pid, :val, :com)";
            $stmtDet = $pdo->prepare($sqlDet);
            $stmtDet->execute([
                ':rid' => $respId,
                ':pid' => $p['id'],
                ':val' => $val,
                ':com' => $comment
            ]);
        }
        
        $count++;
        if ($count % 100 == 0) echo "Generados: $count\r";
    }

    $pdo->commit();
    echo "\n¡Seeding Completo! $count respuestas generadas.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "\nError Fatídico: " . $e->getMessage();
}
