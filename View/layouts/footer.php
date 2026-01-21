<!-- FOOTER (todo incluido) -->
<footer class="app-footer mt-auto">
  <div class="container-fluid px-3 px-md-4 py-3">
    <div class="row g-3 align-items-center">
      <!-- Marca -->
      <div class="col-12 col-md-4">
        <div class="d-flex align-items-center gap-2">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle app-footer__logo">
            <img src="View/layouts/Logo.png" alt="Logo Xitic" class="app-footer__logo-img">
          </span>

          <div class="small">
            <strong>App Xitic</strong>
            <div class="text-muted">Dashboard · v1.0</div>
          </div>
        </div>
      </div>

      <!-- Links -->
      <div class="col-12 col-md-5">
        <ul class="nav justify-content-start justify-content-md-center gap-1">
          <li class="nav-item">
            <a class="nav-link px-2 py-1 app-footer__link" href="#">
              <i class="bi bi-shield-check me-1"></i>Privacidad
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-2 py-1 app-footer__link" href="#">
              <i class="bi bi-file-text me-1"></i>Términos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-2 py-1 app-footer__link" href="#">
              <i class="bi bi-question-circle me-1"></i>Soporte
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-2 py-1 app-footer__link" href="#">
              <i class="bi bi-bug me-1"></i>Reportar
            </a>
          </li>
        </ul>
      </div>

      <!-- Estado / derechos -->
      <div class="col-12 col-md-3 text-start text-md-end">
        <div class="small text-muted">
          <span class="me-2">
            <i class="bi bi-circle-fill me-1 app-footer__status"></i>En línea
          </span>
          <span>© <span id="Footer_anio"></span> Xitic</span>
        </div>
      </div>
    </div>
  </div>

  <style>
    .app-footer{
      border-top: 1px solid rgba(0,0,0,.08);
      background: #ffffff;
    }

    .app-footer__logo{
      width: 26px;
      height: 26px;
      background: #eef2ff;
      color: #4f46e5;
    }

    .app-footer__logo-img{
      width: 18px;
      height: 18px;
      object-fit: contain;
    }

    .app-footer__link{
      color: rgba(0,0,0,.65);
      border-radius: 10px;
      transition: background .15s ease, color .15s ease;
    }

    .app-footer__link:hover{
      background: rgba(79,70,229,.08);
      color: #4f46e5;
    }

    .app-footer__status{
      color: #22c55e;
    }

    /* Sticky footer sin tapar contenido */
    body{
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
  </style>

  <script>
    document.getElementById('Footer_anio').textContent = new Date().getFullYear();
  </script>
  
  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</footer>
