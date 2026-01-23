<?php
// View/AdminMaster/Clientes/index.php
// Acceso vía Controller: $Clientes disponible
include 'View/layouts/header_admin.php';
$Csrf = SecurityController::obtenerCsrfToken();
?>

<div class="page-wrapper anime-fade-in">
    <!-- Header de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="page-title mb-1">Gestión de Clientes</h1>
            <p class="page-subtitle mb-0">Administra las empresas y genera sus credenciales de acceso.</p>
        </div>
        <div>
            <!-- Botón Trigger Modal -->
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background:var(--accent);border:none;" data-bs-toggle="modal" data-bs-target="#modalCrearCliente">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Filtros / Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="soft-card p-2 d-flex align-items-center gap-2" style="max-width: 400px;">
                <i class="bi bi-search text-muted ms-2"></i>
                <input type="text" class="form-control border-0 shadow-none bg-transparent" placeholder="Buscar empresa, RFC...">
            </div>
        </div>
    </div>

    <!-- Tabla Principal -->
    <div class="soft-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.05em;font-weight:600;">Empresa</th>
                        <th class="py-3 text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.05em;font-weight:600;">RFC / Razón Social</th>
                        <th class="py-3 text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.05em;font-weight:600;">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.05em;font-weight:600;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Clientes)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-building fa-2x mb-2 d-block"></i>
                                    <span class="small fw-bold">No hay clientes registrados</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Clientes as $C): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($C['logo_url'])): ?>
                                            <div class="rounded-circle shadow-sm overflow-hidden d-flex align-items-center justify-content-center" 
                                                 style="width:48px;height:48px;background:#fff;border:1px solid #eef2ff;">
                                                <img src="<?php echo htmlspecialchars($C['logo_url']); ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold shadow-sm"
                                                 style="width:48px;height:48px;background:linear-gradient(135deg, var(--accent) 0%, #818cf8 100%);">
                                                <?php echo strtoupper(substr($C['nombre_comercial'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($C['nombre_comercial']); ?></div>
                                            <div class="extra-small text-muted">ID: #<?php echo str_pad((string)$C['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex flex-column" style="max-width: 250px;">
                                        <span class="text-muted extra-small fst-italic text-truncate">
                                            <?php echo htmlspecialchars(!empty($C['comentarios']) ? $C['comentarios'] : 'Sin comentarios'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <?php if ($C['activo'] == 1): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                <td class="text-end pe-4 py-3">
                                    <a href="index.php?System=clientes&a=ver&id=<?php echo $C['id']; ?>" class="btn btn-sm btn-light border text-muted" title="Ver Perfil">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CREACIÓN -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="index.php?System=clientes&a=guardar" method="POST" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                
                <div class="modal-body pt-4">
                    <!-- SECCIÓN 1: EMPRESA -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-indigo-subtle text-indigo rounded-circle d-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;background:#eef2ff;color:#4f46e5;">
                            <i class="bi bi-building-fill small"></i>
                        </div>
                        <h6 class="text-uppercase text-muted extra-small fw-bold mb-0 ls-1">Datos de la Empresa / Grupo</h6>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Nombre del Grupo</label>
                            <input type="text" class="form-control bg-light border-0" name="nombre_comercial" required placeholder="Ej. Grup Alsea">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Logotipo de la Marca</label>
                            <input type="file" class="form-control bg-light border-0" name="logo" accept="image/*">
                            <div class="form-text extra-small">Formatos: JPG, PNG, WEBP.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Comentarios / Notas</label>
                            <textarea class="form-control bg-light border-0" name="comentarios" rows="2" placeholder="Notas internas sobre este cliente..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-danger">Límite de Sucursales</label>
                            <input type="number" class="form-control bg-light border-0" name="limite_sucursales" value="1" min="0">
                            <div class="form-text extra-small">Introduce 0 para sucursales ilimitadas. (Default: 1)</div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold text-primary">Módulos Iniciales</label>
                            <div class="d-flex gap-4">
                                <div class="form-check form-switch cursor-pointer">
                                    <input class="form-check-input" type="checkbox" id="newModEnc" name="modulo_encuestas" value="1" checked>
                                    <label class="form-check-label small user-select-none" for="newModEnc">Encuestas</label>
                                </div>
                                <div class="form-check form-switch cursor-pointer">
                                    <input class="form-check-input" type="checkbox" id="newModCas" name="modulo_casos" value="1">
                                    <label class="form-check-label small user-select-none" for="newModCas">Casos IA</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- SECCIÓN 2: ENCARGADO -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;">
                            <i class="bi bi-person-badge-fill small"></i>
                        </div>
                        <h6 class="text-uppercase text-primary extra-small fw-bold mb-0 ls-1">Datos del Encargado (Admin)</h6>
                    </div>

                    <div class="alert alert-light border d-flex align-items-start gap-2 mb-3 py-2">
                        <i class="bi bi-info-circle-fill text-info mt-1"></i>
                        <div class="extra-small text-muted line-height-sm">
                            Este usuario será el <strong>Dueño de la Cuenta</strong>. Se le generará una contraseña automática.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Nombre</label>
                            <input type="text" class="form-control bg-light border-0" name="admin_nombre" required placeholder="Ej. Juan">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Apellidos</label>
                            <input type="text" class="form-control bg-light border-0" name="admin_apellido" required placeholder="Ej. Pérez">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico (Login)</label>
                            <input type="email" class="form-control bg-light border-0" name="email_admin" required placeholder="juan.perez@empresa.com">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        <i class="bi bi-save me-2"></i>Guardar y Descargar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#modalCrearCliente form');
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSubmit = form.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // 1. Cerrar Modal
                    const modalEl = document.getElementById('modalCrearCliente');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if(modalInstance) modalInstance.hide();

                    // 2. Descargar Archivo (Si existe)
                    if(data.file) {
                        const link = document.createElement('a');
                        link.href = 'data:application/octet-stream;base64,' + data.file.content;
                        link.download = data.file.name;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }

                    // 3. Notificar y Recargar
                   // alert(data.message);
                    location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error inesperado al procesar la solicitud.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            });
        });
    }
});
</script>

<?php include 'View/layouts/footer.php'; ?>
