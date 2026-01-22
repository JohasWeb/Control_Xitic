<?php
require_once 'Controller/SecurityController.php';
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conexión exitosa. Ejecutando migraciones...\n";

    // 1. Add JSON columns
    try {
        $sql = "ALTER TABLE encuestas_preguntas ADD COLUMN logica_condicional JSON DEFAULT NULL";
        $pdo->exec($sql);
        echo "[OK] Columna 'logica_condicional' agregada.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'logica_condicional' ya existe.\n";
        } else {
             echo "[INFO] " . $e->getMessage() . "\n";
        }
    }

    try {
        $sql = "ALTER TABLE encuestas_preguntas ADD COLUMN configuracion_json JSON DEFAULT NULL";
        $pdo->exec($sql);
        echo "[OK] Columna 'configuracion_json' agregada.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
             echo "[SKIP] La columna 'configuracion_json' ya existe.\n";
        } else {
             echo "[INFO] " . $e->getMessage() . "\n";
        }
    }

    // 2. Modify Type column
    try {
        $sql = "ALTER TABLE encuestas_preguntas MODIFY COLUMN tipo_pregunta VARCHAR(50) NOT NULL DEFAULT 'texto'";
        $pdo->exec($sql);
        echo "[OK] Columna 'tipo_pregunta' actualizada a VARCHAR(50).\n";
    } catch (Exception $e) {
        echo "[ERROR] Al modificar tipo_pregunta: " . $e->getMessage() . "\n";
    }

    echo "\nMigración completada exitosamente.";

} catch (Exception $e) {
    echo "Error General: " . $e->getMessage();
}
