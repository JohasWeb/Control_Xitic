<?php
// verify_advanced_questions.php
// Run from C:\xampp\htdocs\Control

// Disable headers/cookies issues in CLI
ob_start();

// Mock Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['_AdminID_user'] = 1;
$_SESSION['_Es_ClienteAdmin'] = 1;
$_SESSION['usuario_id'] = 1;
$_SESSION['_csrf_token'] = 'testtoken'; // Pre-set token
$_SESSION['_Cliente_id'] = 1;

// Define post csrf for validation
$_POST['csrf_token'] = 'testtoken';

// Include files (paths relative to root)
include_once "Controller/Encuestas.Controller.php";

$Controller = new EncuestasController();
$Model = new EncuestasModel();

echo "\n--- INICIO PRUEBA ---\n";

// 1. Create Survey
$SurveyId = $Model->crearEncuesta(1, "Test CLI " . time(), "Desc", date('Y-m-d'), null, 1);
if (!$SurveyId) die("Error creando encuesta básica.\n");
echo "Encuesta creada ID: $SurveyId\n";

// 2. Add Question (Botonera)
echo "Agregando pregunta Botonera...\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['encuesta_id'] = $SurveyId;
$_POST['texto_pregunta'] = "CLI Test Q1";
$_POST['tipo_pregunta'] = "botonera";
$_POST['opciones_json_raw'] = json_encode(["A", "B"]);
$_POST['configuracion_json'] = json_encode(["max" => 10]);
$_POST['logica_condicional'] = null;

// Capture output of controller (it echoes json)
ob_clean(); // Clean previous output
$Controller->guardar_pregunta();
$Res = ob_get_clean();
echo "Resultado Guardar: " . substr($Res, 0, 100) . "...\n";

// Verify DB
$Preguntas = $Model->obtenerPreguntas($SurveyId);
if(count($Preguntas) > 0 && $Preguntas[0]['tipo_pregunta'] === 'botonera') {
    echo "VERIFICACIÓN 1 EXITOSA: Pregunta creada correctamente.\n";
} else {
    echo "FALLO: No se creó la pregunta.\n";
    print_r($Preguntas);
}

// 3. Reorder
$Model->agregarPregunta($SurveyId, "Q2", "text", 2, 0, null);
$Preguntas = $Model->obtenerPreguntas($SurveyId);
if(count($Preguntas) < 2) die("Error creando Q2\n");

$Id1 = $Preguntas[0]['id'];
$Id2 = $Preguntas[1]['id'];

echo "Reordenando ID=$Id2 a pos=1, ID=$Id1 a pos=2...\n";

// Call model directly for simplicity in CLI (controller reads php://input)
$Items = [
    ['id' => $Id2, 'orden' => 1],
    ['id' => $Id1, 'orden' => 2]
];
$Model->reordenarPreguntas($Items);

$Preguntas = $Model->obtenerPreguntas($SurveyId);
// Check first item
if ($Preguntas[0]['id'] == $Id2) {
    echo "VERIFICACIÓN 2 EXITOSA: Reordenamiento correcto.\n";
} else {
    echo "FALLO: Orden incorrecto.\n";
    foreach($Preguntas as $p) echo "Orden " . $p['orden'] . ": " . $p['texto_pregunta'] . "\n";
}

echo "--- FIN PRUEBA ---\n";
?>
