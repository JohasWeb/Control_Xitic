<?php
/**
 * Archivo: View/layouts/header_cliente.php
 * Header compartido (ClienteAdmin) - Rediseño Moderno
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

// Iniciales del usuario
$Iniciales = 'U';
if (!empty($Usuario_mostrar)) {
    $Parts = explode(' ', trim($Usuario_mostrar));
    $Iniciales = strtoupper(substr($Parts[0], 0, 1));
    if (count($Parts) > 1) {
        $Iniciales .= strtoupper(substr($end($Parts), 0, 1));
    }
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

function isActive($current, $target) {
    if ($target === 'inicio' && ($current === '' || $current === 'dashboard' || $current === 'inicio')) return 'active';
    return ($current === $target) ? 'active' : '';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Xitic · Dashboard de Encuestas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#ffffff">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link href="View/layouts/estilos.css" rel="stylesheet">
  
  <style>
      :root {
          --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
          --header-height: 64px;
          --primary-color: #4f46e5;
          --primary-soft: #eef2ff;
      }
      
      body {
          font-family: var(--font-sans);
          background-color: #f8fafc; /* Fondo gris muy claro */
          color: #1e293b;
          padding-top: calc(var(--header-height) + 20px);
      }

      /* Navbar Moderno "Glass" */
      .app-navbar {
          height: var(--header-height);
          background-color: rgba(255, 255, 255, 0.9);
          backdrop-filter: blur(12px);
          -webkit-backdrop-filter: blur(12px);
          border-bottom: 1px solid rgba(0,0,0,0.05);
          box-shadow: 0 4px 20px rgba(0,0,0,0.02);
      }

      .navbar-brand {
          font-weight: 700;
          font-size: 1rem;
          color: #0f172a;
          letter-spacing: -0.02em;
      }

      /* Enlaces de Navegación */
      .nav-link {
          font-weight: 500;
          font-size: 0.9rem;
          color: #64748b !important;
          padding: 0.5rem 1rem !important;
          border-radius: 8px;
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          gap: 0.5rem;
      }

      .nav-link i {
          font-size: 1.1rem;
          opacity: 0.75;
      }

      .nav-link:hover {
          color: var(--primary-color) !important;
          background-color: rgba(79, 70, 229, 0.04);
      }
      
      .nav-link.active {
          color: var(--primary-color) !important;
          background-color: var(--primary-soft);
          font-weight: 600;
      }
      
      .nav-link.active i {
          opacity: 1;
      }

      /* Perfil Usuario */
      .user-avatar {
          width: 36px;
          height: 36px;
          background: linear-gradient(135deg, #6366f1, #8b5cf6);
          color: white;
          font-weight: 600;
          font-size: 0.9rem;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          border: 2px solid white;
          box-shadow: 0 0 0 2px #e2e8f0;
      }

      /* Utilidades Extra */
      .page-wrapper {
          max-width: 1280px;
          margin: 0 auto;
          padding: 0 1.5rem;
      }
      
      .page-title {
          font-weight: 700;
          color: #0f172a;
          letter-spacing: -0.03em;
      }
      
      .page-subtitle {
          color: #64748b;
          font-size: 0.95rem;
      }

      .anime-fade-in {
          animation: fadeIn 0.4s ease-out forwards;
      }
      
      @keyframes fadeIn {
          from { opacity: 0; transform: translateY(10px); }
          to { opacity: 1; transform: translateY(0); }
      }
      
      /* Botones redesined */
      .btn {
          font-weight: 500;
          letter-spacing: -0.01em;
      }
      
      /* Card Styling Global */
      .soft-card {
          background: #fff;
          border-radius: 16px;
          border: 1px solid rgba(0,0,0,0.04);
          box-shadow: 0 2px 8px rgba(0,0,0,0.03);
      }

  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg fixed-top app-navbar">
  <div class="container-fluid px-3 px-xl-5">

    <a class="navbar-brand me-4" href="<?= $Ruta_inicio ?>">
      <div class="d-flex align-items-center gap-2">
          <img src="View/layouts/Logo.png" alt="Xitic Control" style="height:32px; width:auto; object-fit:contain;">
          <span style="font-weight:700; font-size:1.1rem; color:#0f172a;">Xitic Control</span>
      </div>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
        <li class="nav-item">
          <a class="nav-link <?= isActive($System_actual, 'inicio') ?>" href="<?= $Ruta_inicio ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= isActive($System_actual, 'encuestas') ?>" href="<?= $Ruta_encuestas ?>">
            <i class="bi bi-clipboard-data-fill"></i> Encuestas
          </a>
        </li>
        <li class="nav-item">
            <span class="nav-link disabled text-muted opacity-25 px-1 py-0 d-none d-lg-block">|</span>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= isActive($System_actual, 'reportes') ?>" href="<?= $Ruta_reportes ?>">
            <i class="bi bi-bar-chart-line-fill"></i> Reportes
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($System_actual, ['sucursales', 'regiones', 'usuarios']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-gear-fill"></i> Configuración
          </a>
          <ul class="dropdown-menu border-0 shadow-lg rounded-3 p-2 mt-2">
            <li><a class="dropdown-item rounded-2 small mb-1 <?= isActive($System_actual, 'sucursales') ?>" href="<?= $Ruta_sucursales ?>"><i class="bi bi-shop me-2"></i>Sucursales</a></li>
            <li><a class="dropdown-item rounded-2 small mb-1 <?= isActive($System_actual, 'regiones') ?>" href="<?= $Ruta_regiones ?>"><i class="bi bi-diagram-3 me-2"></i>Regiones</a></li>
            <li><hr class="dropdown-divider my-1"></li>
            <li><a class="dropdown-item rounded-2 small <?= isActive($System_actual, 'usuarios') ?>" href="<?= $Ruta_usuarios ?>"><i class="bi bi-people me-2"></i>Usuarios</a></li>
          </ul>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3 ps-lg-3 border-start-lg border-light-subtle">
        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                <div class="text-end me-2 d-none d-md-block line-height-sm">
                    <div class="fw-bold extra-small text-dark mb-0" style="font-size:0.85rem;"><?= htmlspecialchars($Usuario_mostrar) ?></div>
                    <div class="text-muted extra-small" style="font-size:0.7rem;">Administrador</div>
                </div>
                <div class="user-avatar shadow-sm">
                    <?= $Iniciales ?>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2" style="width: 200px;">
                <li><span class="dropdown-header text-uppercase extra-small fw-bold ls-1">Mi Cuenta</span></li>
                <li><a class="dropdown-item rounded-2 small mb-1" href="#"><i class="bi bi-person me-2"></i>Perfil</a></li>
                <li><a class="dropdown-item rounded-2 small mb-1" href="#"><i class="bi bi-shield-lock me-2"></i>Seguridad</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="<?= $Ruta_logout ?>" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?= $Csrf_token_header ?>">
                        <button type="submit" class="dropdown-item rounded-2 small text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
      </div>

    </div>
  </div>
</nav>
