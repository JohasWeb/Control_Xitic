<?php
// View/ClienteAdmin/Regiones/index.php
include 'View/layouts/header_cliente.php';

// Variables disponibles: $Regiones (array)
$Csrf = SecurityController::obtenerCsrfToken();
?>

<div class="page-wrapper anime-fade-in">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="page-title mb-1">Regiones y Zonas</h1>
            <p class="page-subtitle mb-0">Organiza tus sucursales por zonas geográficas o áreas de negocio.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" 
                    style="background:var(--accent);border:none;" 
                    onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg me-2"></i>Nueva Región
            </button>
        </div>
    </div>

    <!-- Lista de Regiones -->
    <div class="row g-4">
        <?php if (empty($Regiones)): ?>
            <div class="col-12">
                <div class="soft-card p-5 text-center text-muted">
                    <i class="bi bi-geo-alt fs-1 d-block mb-3 opacity-50"></i>
                    <h5>Sin regiones registradas</h5>
                    <p class="mb-0 small">Crea tu primera región para organizar tus sucursales.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($Regiones as $R): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="soft-card p-3 d-flex align-items-center justify-content-between hover-scale">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" 
                                 style="width:48px;height:48px;">
                                <?php echo strtoupper(substr($R['nombre'], 0, 1)); ?>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($R['nombre']); ?></h6>
                                <div class="extra-small text-muted mb-1">ID: #<?php echo $R['id']; ?></div>
                                <div class="badge bg-light text-dark border fw-normal extra-small">
                                    <i class="bi bi-person-fill me-1 text-muted"></i>
                                    <?php echo htmlspecialchars($R['supervisor_nombre'] ?? 'Sin Supervisor'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <a class="dropdown-item small" href="#" onclick='abrirModalEditar(<?php echo json_encode($R); ?>)'>
                                        <i class="bi bi-pencil me-2 text-primary"></i>Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item small text-danger" href="#" onclick="eliminarRegion(<?php echo $R['id']; ?>)">
                                        <i class="bi bi-trash me-2"></i>Eliminar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Gestión -->
<div class="modal fade" id="modalRegion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitulo">Nueva Región</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formRegion" action="index.php?System=regiones&a=guardar" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                <input type="hidden" name="id" id="regionId" value="0">
                <!-- Input hidden para mantener ID de supervisor si no se toca (opcional, pero ayuda lógica controller) -->
                <input type="hidden" name="supervisor_id_hidden" id="regionSupervisorIdHidden" value="0">
                
                <div class="modal-body pt-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="text-muted text-uppercase extra-small fw-bold ls-1 mb-3">Datos de la Región</h6>
                            <label class="form-label small fw-bold text-muted">Nombre de la Región <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="nombre" id="regionNombre" required placeholder="Ej. Zona Norte">
                        </div>
                        
                        <div class="col-12">
                            <hr class="border-light">
                            <h6 class="text-muted text-uppercase extra-small fw-bold ls-1 mb-3">Supervisor Asignado</h6>
                            <div class="alert alert-light border border-light-subtle d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-info-circle text-primary mt-1"></i>
                                <div class="small text-muted">Asegúrese de ingresar un correo válido. Si el usuario no existe, se creará automáticamente y <b>se descargará un archivo con las credenciales</b>.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Nombre(s) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="supervisor_nombre" id="supNombre" placeholder="Ej. Juan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Apellido(s)</label>
                            <input type="text" class="form-control bg-light border-0" name="supervisor_apellido" id="supApellido" placeholder="Ej. Pérez">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico (Usuario) <span class="text-danger">*</span></label>
                            <input type="email" class="form-control bg-light border-0" name="supervisor_email" id="supEmail" placeholder="usuario@empresa.com">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('modalRegion');
    var modal = null;
    
    if (modalEl) {
        modal = new bootstrap.Modal(modalEl);
    }

    window.abrirModalCrear = function() {
        if (!modal) return;
        document.getElementById('formRegion').reset();
        document.getElementById('regionId').value = 0;
        document.getElementById('regionSupervisorIdHidden').value = 0;
        document.getElementById('modalTitulo').textContent = 'Nueva Región';
        modal.show();
    };

    window.abrirModalEditar = function(region) {
        if (!modal) return;
        document.getElementById('formRegion').reset();
        document.getElementById('regionId').value = region.id;
        document.getElementById('regionNombre').value = region.nombre;
        document.getElementById('regionSupervisorIdHidden').value = region.supervisor_id || 0;
        
        // Cargar datos supervisor plano
        document.getElementById('supNombre').value = region.supervisor_n || '';
        document.getElementById('supApellido').value = region.supervisor_a || '';
        document.getElementById('supEmail').value = region.supervisor_email || '';

        document.getElementById('modalTitulo').textContent = 'Editar Región';
        modal.show();
    };

    window.eliminarRegion = function(id) {
        if(!confirm('¿Estás seguro de eliminar esta región?')) return;
        
        const fd = new FormData();
        fd.append('id', id);
        // fd.append('csrf_token', ''); // Si se requiere CSRF en action eliminar
        
        // Simular token si es post simple
        // Mejor agregar input hidden al form si fuera submit, pero es fetch
        
        fetch('index.php?System=regiones&a=eliminar', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    };

    const form = document.getElementById('formRegion');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Guardando...';

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if (data.credentials) {
                        const content = `CREDENCIALES DE ACCESO - REGIONAL\n\n` + 
                                      `Hola ${data.credentials.email},\n\n` +
                                      `Se ha creado tu cuenta de supervisor regional exitosamente.\n\n` +
                                      `Usuario: ${data.credentials.email}\n` +
                                      `Contraseña: ${data.credentials.password}\n\n` +
                                      `Por favor ingresa al sistema y cambia tu contraseña lo antes posible.\n` +
                                      `Fecha de generación: ${new Date().toLocaleString()}`;
                        
                        const blob = new Blob([content], { type: 'text/plain' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `credenciales_regional_${Date.now()}.txt`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                        
                        alert(data.message); // Mostrar mensaje antes de recargar
                    }
                    location.reload();
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }
});
</script>

<?php include 'View/layouts/footer.php'; ?>
