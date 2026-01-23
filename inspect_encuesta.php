<?php
require_once 'Model/DataBase.php';
$pdo = DataBase::conectar();

// 1. Find Survey
$stmt = $pdo->query("SELECT * FROM encuestas WHERE titulo LIKE '%Experiencia%' ORDER BY id DESC LIMIT 1");
$encuesta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$encuesta) {
    die("No se encontró ninguna encuesta con 'Experiencia' en el título.\n");
}

echo "Encuesta Encontrada: [ID: {$encuesta['id']}] {$encuesta['titulo']}\n";
echo "------------------------------------------------\n";

// 2. Find Questions
$stmtQ = $pdo->prepare("SELECT * FROM encuestas_preguntas WHERE encuesta_id = ? ORDER BY orden ASC");
$stmtQ->execute([$encuesta['id']]);
$preguntas = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

foreach ($preguntas as $p) {
    echo "ID: {$p['id']} | Tipo: {$p['tipo_pregunta']} | Texto: " . substr($p['texto_pregunta'], 0, 50) . "...\n";
    if ($p['opciones_json']) {
        echo "   Opciones: {$p['opciones_json']}\n";
    }
}

// 3. Find Branches (for context)
$stmtS = $pdo->query("SELECT id, nombre, region FROM sucursales LIMIT 5");
echo "------------------------------------------------\n";
echo "Ejemplo Sucursales:\n";
while ($row = $stmtS->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} - {$row['nombre']} ({$row['region']})\n";
}
