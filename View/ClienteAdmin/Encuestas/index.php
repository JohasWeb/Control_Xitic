<?php
// View/ClienteAdmin/Encuestas/index.php
include 'View/layouts/header_cliente.php';
$Csrf = SecurityController::obtenerCsrfToken();
?>
<div class="page-wrapper anime-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="page-title mb-1">Mis Encuestas</h1>
            <p class="page-subtitle mb-0">Gestiona y analiza las encuestas de satisfacción.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" 
                    style="background:var(--accent);border:none;" 
                    onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg me-2"></i>Nueva Encuesta
            </button>
        </div>
    </div>

    <div class="soft-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted extra-small fw-bold">Título</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Fechas</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Configuración</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Encuestas)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <span class="text-muted">No tienes encuestas registradas.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Encuestas as $E): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($E['titulo']); ?></div>
                                    <div class="extra-small text-muted"><?php echo htmlspecialchars(mb_strimwidth($E['descripcion'], 0, 50, "...")); ?></div>
                                </td>
                                <td class="py-3">
                                    <div class="extra-small"><i class="bi bi-calendar-event me-1 text-muted"></i> <?php echo $E['fecha_inicio']; ?></div>
                                    <div class="extra-small"><i class="bi bi-calendar-check me-1 text-muted"></i> <?php echo $E['fecha_fin']; ?></div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex gap-2">
                                        <?php if(isset($E['anonima']) && $E['anonima'] == 1): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Anónima</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Registro Req.</span>
                                        <?php endif; ?>
                                        
                                        <?php if(isset($E['tiempo_estimado']) && $E['tiempo_estimado'] > 0): ?>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-stopwatch me-1"></i> <?php echo $E['tiempo_estimado']; ?> min
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <button class="btn btn-sm btn-light border text-muted"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Encuesta -->
<div class="modal fade" id="modalEncuesta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Nueva Encuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEncuesta" action="index.php?System=encuestas&a=guardar" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                
                <div class="modal-body pt-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Título de la Encuesta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light border-0" name="titulo" required placeholder="Ej. Satisfacción Cliente">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Tiempo Estimado (Minutos) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control bg-light border-0" name="tiempo_estimado" value="5" min="1" required>
                        <div class="form-text extra-small">Tiempo promedio para responder.</div>
                    </div>
                    
                    <div class="alert alert-light border border-light-subtle d-flex align-items-start gap-2 mb-0">
                        <i class="bi bi-info-circle text-primary mt-1"></i>
                        <div class="extra-small text-muted">
                            Se creará con la configuración predeterminada (1 mes de duración, registro requerido). Podrás editar estos detalles después.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        Crear Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('modalEncuesta');
    var modal = null;
    
    function initModal() {
        if (typeof bootstrap !== 'undefined' && modalEl) {
            modal = new bootstrap.Modal(modalEl);
        } else {
            setTimeout(initModal, 100);
        }
    }
    initModal();

    window.abrirModalCrear = function() {
        if (!modal) return;
        document.getElementById('formEncuesta').reset();
        modal.show();
    };

    // Auto submit simple (el controller redirige)
    // Opcional: Agregar loading state al boton
    const form = document.getElementById('formEncuesta');
    if(form) {
        form.addEventListener('submit', function() {
             const btn = this.querySelector('button[type="submit"]');
             if(btn) {
                 btn.disabled = true;
                 btn.innerHTML = 'Creando...';
             }
        });
    }
});
</script>
