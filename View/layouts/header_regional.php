<?php
include_once "View/layouts/session_vars.php";
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
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top app-navbar">
    <div class="container-fluid px-3 px-md-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
<span class="d-inline-flex align-items-center justify-content-center rounded-circle"
      style="width:28px;height:28px;background:#eef2ff;color:#4f46e5;">
  <img src="View/layouts/Logo.png" alt="Logo Xitic" style="width:20px;height:20px;object-fit:contain;">
</span>

        <span>Xitic · Encuestas</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarXitic">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarXitic">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
          <li class="nav-item">
            <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i>Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-ui-checks-grid"></i>Encuestas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-diagram-3"></i>Regiones</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-shop"></i>Sucursales</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-bar-chart"></i>Reportes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-people"></i>Usuarios</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-3">
          <div class="mode-toggle btn-group" role="group" aria-label="Modo">
            <button type="button" class="btn btn-outline-dark active" id="btn-modo-ejecutivo">Ejecutivo</button>
            <button type="button" class="btn btn-outline-dark" id="btn-modo-operaciones">Operaciones</button>
          </div>
          <span class="badge-realtime">
            <span class="badge-realtime-dot"></span>
           </span>
        </div>
      </div>
    </div>
  </nav>
