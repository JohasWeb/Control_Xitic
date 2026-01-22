<?php
// migration_encuestas_dates_runner.php
include_once "Model/DataBase.php";

try {
    $pdo = DataBase::conectar();
    $sql = file_get_contents('migration_encuestas_dates.sql');
    
    // Split by semicolon 
    $statements = explode(';', $sql);
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            $pdo->exec($stmt);
            echo "Ejecutado: " . substr($stmt, 0, 50) . "...\n";
        }
    }
    
    echo "Migración Encuestas Fechas completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
