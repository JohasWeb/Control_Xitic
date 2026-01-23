<?php
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conectado. Ejecutando parches SQL (Comentarios)...\n";

    // Agregar columna comentario a encuestas_respuestas_detalle
    try {
        $sql = "ALTER TABLE encuestas_respuestas_detalle ADD COLUMN comentario TEXT DEFAULT NULL";
        $pdo->exec($sql);
        echo "[OK] Columna 'comentario' agregada a 'encuestas_respuestas_detalle'.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'comentario' ya existe.\n";
        } else {
             echo "[INFO] " . $e->getMessage() . "\n";
        }
    }

    echo "\nMaldad completada (Migraciones listas).";

} catch (Exception $e) {
    echo "Error Crítico: " . $e->getMessage();
}
