<?php
require_once 'Controller/SecurityController.php';
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conexión exitosa. Ejecutando migración de imagen header...\n";

    // Add imagen_header column
    try {
        $sql = "ALTER TABLE encuestas ADD COLUMN imagen_header VARCHAR(255) NULL COMMENT 'Ruta de la imagen de cabecera'";
        $pdo->exec($sql);
        echo "[OK] Columna 'imagen_header' agregada.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'imagen_header' ya existe.\n";
        } else {
             echo "[ERROR] " . $e->getMessage() . "\n";
        }
    }

    echo "\nMigración completada.";

} catch (Exception $e) {
    echo "Error General: " . $e->getMessage();
}
