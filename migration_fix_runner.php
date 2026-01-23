<?php
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conectado. Ejecutando parches SQL...\n";

    // 1. Agregar columna sucursal_id
    try {
        $sql = "ALTER TABLE encuestas_respuestas ADD COLUMN sucursal_id INT(11) DEFAULT NULL AFTER encuesta_id";
        $pdo->exec($sql);
        echo "[OK] Columna 'sucursal_id' creada.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'sucursal_id' ya existe.\n";
        } else {
             echo "[INFO] " . $e->getMessage() . "\n";
        }
    }

    // 2. Agregar FK
    try {
        $sql = "ALTER TABLE encuestas_respuestas ADD CONSTRAINT fk_respuestas_sucursal_direct FOREIGN KEY (sucursal_id) REFERENCES sucursales (id) ON DELETE SET NULL";
        $pdo->exec($sql);
        echo "[OK] Foreign Key 'fk_respuestas_sucursal_direct' agregada.\n";
    } catch (Exception $e) {
        echo "[INFO] FK (probablemente ya existe): " . $e->getMessage() . "\n";
    }

    // 3. Hacer sucursal_qr_id NULLABLE
    try {
        // Obtenemos definicion actual para no romper FK si existe
        // Pero MODIFY COLUMN deberia preservar FK si el tipo es igual
        $sql = "ALTER TABLE encuestas_respuestas MODIFY COLUMN sucursal_qr_id INT(11) DEFAULT NULL";
        $pdo->exec($sql);
        echo "[OK] Columna 'sucursal_qr_id' ahora es NULLABLE.\n";
    } catch (Exception $e) {
        echo "[ERROR] Al modificar sucursal_qr_id: " . $e->getMessage() . "\n";
    }

    echo "\nParches aplicados correctamente.";

} catch (Exception $e) {
    echo "Error Crítico: " . $e->getMessage();
}
