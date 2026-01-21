<?php
/**
 * Archivo: View/layouts/header_cliente.php
 * Header compartido (ClienteAdmin)
 *
 * Incluye:
 * - Sesión segura + token CSRF (SecurityController)
 * - Botón "Cerrar sesión" por POST con CSRF
 * - Menú con rutas reales + activo automático por ?System=
 */

include_once "Controller/SecurityController.php";
$Csrf_token_header = (string) SecurityController::obtenerCsrfToken();

include_once "View/layouts/session_vars.php";

$Usuario_mostrar = '';
if (isset($_SESSION["_Nombre_Sesion"])) {
    $Usuario_mostrar = (string) $_SESSION["_Nombre_Sesion"];
}

if ($Usuario_mostrar === '' && isset($_SESSION["_sesion_usuario"])) {
    $Usuario_mostrar = (string) $_SESSION["_sesion_usuario"];
}

$Usuario_label = 'Cuenta';
if ($Usuario_mostrar !== '') {
    $Usuario_label = $Usuario_mostrar;
}

$Rol_mostrar = '';
if (isset($_SESSION["_Rol"])) {
    $Rol_mostrar = (string) $_SESSION["_Rol"];
}

/**
 * Rutas del menú
 */
$Ruta_inicio = 'index.php?System=dashboard&a=index';
$Ruta_reportes = 'index.php?System=reportes&a=index';
$Ruta_encuestas = 'index.php?System=encuestas&a=index';
$Ruta_casos = 'index.php?System=casos&a=index';
$Ruta_sucursales = 'index.php?System=sucursales&a=index';
$Ruta_regiones = 'index.php?System=regiones&a=index';
$Ruta_usuarios = 'index.php?System=usuarios&a=index';

$Ruta_logout = 'index.php?System=login&a=salir';

/**
 * Activo automático
 */
$System_actual = '';
if (isset($_GET['System'])) {
    $System_actual = (string) $_GET['System'];
}
$System_actual = mb_strtolower(trim($System_actual));

$Clase_inicio = 'nav-link';
$Clase_reportes = 'nav-link';
$Clase_encuestas = 'nav-link';
$Clase_casos = 'nav-link';
$Clase_sucursales = 'nav-link';
$Clase_regiones = 'nav-link';
$Clase_usuarios = 'nav-link';

if ($System_actual === '' || $System_actual === 'dashboard' || $System_actual === 'inicio') {
    $Clase_inicio = 'nav-link active';
} elseif ($System_actual === 'reportes') {
    $Clase_reportes = 'nav-link active';
} elseif ($System_actual === 'encuestas') {
    $Clase_encuestas = 'nav-link active';
} elseif ($System_actual === 'casos') {
    $Clase_casos = 'nav-link active';
} elseif ($System_actual === 'sucursales') {
    $Clase_sucursales = 'nav-link active';
} elseif ($System_actual === 'regiones') {
    $Clase_regiones = 'nav-link active';
} elseif ($System_actual === 'usuarios') {
    $Clase_usuarios = 'nav-link active';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Xitic · Dashboard de Encuestas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link href="View/layouts/estilos.css" rel="stylesheet">
</head>

<body data-modo="ejecutivo">

<nav class="navbar navbar-expand-lg navbar-light fixed-top app-navbar">
  <div class="container-fluid px-3 px-md-4">

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo htmlspecialchars($Ruta_inicio); ?>">
      <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
            style="width:28px;height:28px;background:#eef2ff;color:#4f46e5;">
        <img src="View/layouts/Logo.png" alt="Logo Xitic"
             style="width:20px;height:20px;object-fit:contain;">
      </span>
      <span>Xitic · Encuestas</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarXitic">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarXitic">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_inicio); ?>" href="<?php echo htmlspecialchars($Ruta_inicio); ?>">
            <i class="bi bi-speedometer2"></i> Inicio
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_reportes); ?>" href="<?php echo htmlspecialchars($Ruta_reportes); ?>">
            <i class="bi bi-bar-chart"></i> Reportes
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_encuestas); ?>" href="<?php echo htmlspecialchars($Ruta_encuestas); ?>">
            <i class="bi bi-ui-checks-grid"></i> Encuestas
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_casos); ?>" href="<?php echo htmlspecialchars($Ruta_casos); ?>">
            <i class="bi bi-radioactive"></i> Casos
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_sucursales); ?>" href="<?php echo htmlspecialchars($Ruta_sucursales); ?>">
            <i class="bi bi-shop"></i> Sucursales
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_regiones); ?>" href="<?php echo htmlspecialchars($Ruta_regiones); ?>">
            <i class="bi bi-diagram-3"></i> Regiones
          </a>
        </li>

        <li class="nav-item">
          <a class="<?php echo htmlspecialchars($Clase_usuarios); ?>" href="<?php echo htmlspecialchars($Ruta_usuarios); ?>">
            <i class="bi bi-people"></i> Usuarios
          </a>
        </li>

      </ul>

      <div class="d-flex align-items-center gap-3">

        <div class="d-none d-lg-flex align-items-center text-muted small">
          <i class="bi bi-person-circle me-1"></i>
          <?php echo htmlspecialchars($Usuario_label); ?>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($Ruta_logout); ?>" class="mb-0">
          <input type="hidden" name="csrf_token"
                 value="<?php echo htmlspecialchars($Csrf_token_header); ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger"
                  style="border-radius:999px;">
            <i class="bi bi-box-arrow-right me-1"></i>
            Cerrar sesión
          </button>
        </form>

      </div>

    </div>
  </div>
</nav>
