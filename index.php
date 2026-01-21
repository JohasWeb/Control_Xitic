<?php
/**
 * Archivo: index.php
 * Router principal del sistema.
 */

error_reporting(0);

$System_input = 'login';
if (isset($_GET['System'])) {
    $System_input = trim((string) $_GET['System']);
}

if ($System_input === '') {
    $System_input = 'login';
}

/**
 * Seguridad: solo letras/números/underscore (permitimos mayúsculas).
 */
if (preg_match('/^[a-zA-Z0-9_]+$/', $System_input) !== 1) {
    $System_input = 'login';
}

/**
 * Normalizamos para validar.
 */
$System_key = strtolower((string) $System_input);

/**
 * Alias existentes del proyecto.
 */
if ($System_key === 'usuario') {
    $System_key = 'login';
}

/**
 * Whitelist (TODO en minúsculas).
 */
$Sistemas_permitidos = array(
    'login',
    'dashboard',
    'dashboardcliente',
    'clientes',
    'regiones',
    'encuestas',
    'sucursales',
    'casos'

);

if (in_array($System_key, $Sistemas_permitidos, true) === false) {
    $System_key = 'login';
}

/**
 * Acción
 */
$Action = 'index';
if (isset($_GET['a'])) {
    $Action_input = trim((string) $_GET['a']);
    if ($Action_input !== '' && preg_match('/^[a-zA-Z0-9_]+$/', $Action_input) === 1) {
        $Action = (string) $Action_input;
    }
}

/**
 * Mapeo de System => Nombre real de clase/archivo
 * (porque "dashboardcliente" debe ser "DashboardCliente").
 */
$Mapa_sistema_clase = array(
    'dashboardcliente' => 'DashboardCliente',
    'dashboard' => 'Dashboard',
    'login' => 'Login',
    'clientes' => 'Clientes',
    'regiones' => 'Regiones',
    'encuestas' => 'Encuestas',
    'sucursales' => 'Sucursales',
    'casos' => 'Casos'
);


$System_clase = 'Login';
if (isset($Mapa_sistema_clase[$System_key])) {
    $System_clase = (string) $Mapa_sistema_clase[$System_key];
}

$Ruta_controller = "Controller/" . $System_clase . ".Controller.php";

if (file_exists($Ruta_controller) === false) {
    $System_clase = 'Login';
    $Ruta_controller = "Controller/Login.Controller.php";
}

require_once $Ruta_controller;

$Controller = $System_clase . "Controller";

if (class_exists($Controller) === false) {
    require_once "Controller/Login.Controller.php";
    $Controller = "LoginController";
    $Action = "index";
}

$Obj = new $Controller();

if (method_exists($Obj, $Action) === false) {
    $Action = "index";
}

$Obj->$Action();
