<?php
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conectado. Iniciando actualización de Duración...\n";

    // 1. Add Column
    try {
        $sql = "ALTER TABLE encuestas_respuestas ADD COLUMN duracion_segundos INT DEFAULT NULL COMMENT 'Tiempo en segundos'";
        $pdo->exec($sql);
        echo "[OK] Columna 'duracion_segundos' agregada.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'duracion_segundos' ya existe.\n";
        } else {
             echo "[INFO] " . $e->getMessage() . "\n";
        }
    }

    // 2. Backfill Data (Random 30s to 300s)
    echo "Asignando tiempos aleatorios a registros existentes...\n";
    $stmt = $pdo->query("SELECT id FROM encuestas_respuestas WHERE duracion_segundos IS NULL");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $upd = $pdo->prepare("UPDATE encuestas_respuestas SET duracion_segundos = :sec WHERE id = :id");
    
    $count = 0;
    $pdo->beginTransaction();
    foreach ($ids as $id) {
        $sec = rand(30, 300); // 30 sec to 5 min
        $upd->execute([':sec' => $sec, ':id' => $id]);
        $count++;
    }
    $pdo->commit();

    echo "[OK] Se actualizaron $count registros con tiempos simulados.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "Error Crítico: " . $e->getMessage();
}
