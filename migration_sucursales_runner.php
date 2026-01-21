<?php
// migration_sucursales_runner.php
include_once "Model/DataBase.php";

try {
    $pdo = DataBase::conectar();
    $sql = file_get_contents('migration_sucursales_supervisor.sql');
    
    // Split by semicolon in case there are multiple statements (simple split)
    $statements = explode(';', $sql);
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            $pdo->exec($stmt);
            echo "Ejecutado: " . substr($stmt, 0, 50) . "...\n";
        }
    }
    
    echo "Migración completada con éxito.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
