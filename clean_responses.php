<?php
require_once 'Model/DataBase.php';

try {
    $pdo = DataBase::conectar();
    echo "Conectando al sistema...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM encuestas_respuestas");
    $count = $stmt->fetchColumn();
    echo "Encontradas $count respuestas. Procediendo a eliminar...\n";
    
    // DELETE activará el ON DELETE CASCADE para los detalles
    $pdo->exec("DELETE FROM encuestas_respuestas");
    
    echo "¡Éxito! Todas las respuestas han sido eliminadas.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
