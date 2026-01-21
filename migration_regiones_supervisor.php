<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Ajustar path de inclusión ya que el script está en root de Control
include_once "DataBase.php"; // Si está en C:\xampp\htdocs\Control\
// O si DataBase.php está en Control/, el script en Control/ ... include "DataBase.php" debería funcionar o "Control/DataBase.php" si run from htdocs?
// El usuario trabaja en c:\xampp\htdocs\Control.
// DataBase.php usually is in c:\xampp\htdocs\Control\DataBase.php based on list_dir output earlier?
// list_dir of Model showed DataBase.php inside Model? No, list_dir of Model showed DataBase.php inside? 
// Wait, Step 570 list_dir of `C:\xampp\htdocs\Control\Model` showed:
// {"name":"DataBase.php","sizeBytes":"533"}
// So DataBase.php is in Model/DataBase.php probably, accessed via `include_once "DataBase.php"` inside Model files because they are included from index?
// Actually `UsuariosModel` says `include_once "DataBase.php";` but step 570 says DataBase.php IS inside Model folder.
// Let's assume standard inclusion from root index.php logic, but standalone script needs correct path.
// If I place script in C:\xampp\htdocs\Control\migration_sup.php:
include_once "Model/DataBase.php"; 

$pdo = DataBase::conectar();

echo "Iniciando migración de regiones (Agregando Supervisor)...<br>";

try {
    // 1. Agregar columna supervisor_id si no existe
    $Sql = "SHOW COLUMNS FROM regiones LIKE 'supervisor_id'";
    $Stmt = $pdo->prepare($Sql);
    $Stmt->execute();
    $Columna = $Stmt->fetch();

    if (!$Columna) {
        $SqlAdd = "ALTER TABLE regiones ADD COLUMN supervisor_id INT(11) DEFAULT NULL AFTER cliente_id";
        $pdo->exec($SqlAdd);
        echo "Columna 'supervisor_id' agregada.<br>";
        
        // Agregar FK
        $SqlFK = "ALTER TABLE regiones ADD CONSTRAINT fk_region_supervisor FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL";
        $pdo->exec($SqlFK);
        echo "Foreign Key agregada.<br>";
    } else {
        echo "La columna 'supervisor_id' ya existe.<br>";
    }

    echo "Migración completada exitosamente.";

} catch (PDOException $e) {
    echo "Error en migración: " . $e->getMessage();
}
