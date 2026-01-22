<!-- FOOTER (Minimalista & Sutil) -->
<footer class="app-footer mt-auto py-3">
  <div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      
      <!-- Marca / Copyright -->
      <div class="text-muted extra-small">
        <span class="opacity-75">© <span id="Footer_anio"></span> Xitic Control.</span>
        <span class="mx-2 opacity-25">|</span>
        <span class="opacity-75">v1.0.0</span>
      </div>

      <!-- Links Simples -->
      <div>
        <ul class="nav justify-content-center justify-content-md-end gap-1">
          <li class="nav-item">
            <a class="nav-link p-0 text-muted extra-small opacity-75 hover-opacity-100 px-2" href="#">Privacidad</a>
          </li>
          <li class="nav-item">
            <a class="nav-link p-0 text-muted extra-small opacity-75 hover-opacity-100 px-2" href="#">Términos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link p-0 text-muted extra-small opacity-75 hover-opacity-100 px-2" href="#">Ayuda</a>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <style>
    .app-footer {
        /* Borde superior muy sutil */
        border-top: 1px solid rgba(0,0,0,0.03); 
        background-color: #fafafa; /* Fondo ligeramente diferente al body */
    }
    
    .extra-small {
        font-size: 0.75rem; 
    }
    
    .hover-opacity-100 {
        transition: opacity 0.2s ease;
    }
    
    .hover-opacity-100:hover {
        opacity: 1 !important;
        text-decoration: underline;
    }

    /* Sticky fix */
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
  </style>

  <script>
    document.getElementById('Footer_anio').textContent = new Date().getFullYear();
  </script>
  
  <!-- Bootstrap Bundle (Si no está ya incluido en header) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</footer>
