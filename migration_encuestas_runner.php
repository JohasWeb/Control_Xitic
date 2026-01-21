<?php
// migration_encuestas_runner.php
include_once "Model/DataBase.php";

try {
    $pdo = DataBase::conectar();
    $sql = file_get_contents('migration_encuestas_update.sql');
    
    // Split by semicolon 
    $statements = explode(';', $sql);
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            // Check if column exists before trying to add (simple check)
            if (stripos($stmt, 'ADD COLUMN anonima') !== false) {
                 try {
                     $chk = $pdo->query("SELECT anonima FROM encuestas LIMIT 1");
                     if ($chk) { echo "Columna anonima ya existe, saltando.\n"; continue; }
                 } catch (Exception $e) {}
            }
            if (stripos($stmt, 'ADD COLUMN tiempo_estimado') !== false) {
                 try {
                     $chk = $pdo->query("SELECT tiempo_estimado FROM encuestas LIMIT 1");
                     if ($chk) { echo "Columna tiempo_estimado ya existe, saltando.\n"; continue; }
                 } catch (Exception $e) {}
            }

            try {
                $pdo->exec($stmt);
                echo "Ejecutado: " . substr($stmt, 0, 50) . "...\n";
            } catch (Exception $e) {
                // Ignore "duplicate column" errors if our check failed
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "Columna ya existe (Excepcion controlada).\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "Migración Encuestas completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
