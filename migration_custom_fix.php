<?php
require_once 'Controller/SecurityController.php';
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    
    // Change column to VARCHAR(50) to support new types like 'botonera', 'nps', etc.
    // In case it was ENUM('texto','opcion_multiple', etc.)
    $sql = "ALTER TABLE encuestas_preguntas MODIFY COLUMN tipo_pregunta VARCHAR(50) NOT NULL DEFAULT 'texto'";
    $pdo->exec($sql);
    
    echo "Migration Success: Column tipo_pregunta modified to VARCHAR(50).\n";
    
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
