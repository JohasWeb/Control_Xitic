<?php
/**
 * Archivo: View/ClienteAdmin/Encuestas/index.php
 * Propósito: Vista principal para el listado y gestión de encuestas.
 * Autor: Refactorización Expert PHP
 * Fecha: 2026-01-22
 */

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
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold" style="width: 100px;">Imagen</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Fechas</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Configuración</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Encuestas)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <span class="text-muted">No tienes encuestas registradas.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Encuestas as $E): ?>
                            <tr>
                                <td class="ps-4 py-3 align-middle">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($E['titulo']); ?></div>
                                    <div class="extra-small text-muted"><?php echo htmlspecialchars(mb_strimwidth($E['descripcion'], 0, 50, "...")); ?></div>
                                </td>
                                <td class="py-3 align-middle">
                                    <?php if (!empty($E['imagen_header'])): ?>
                                        <div class="rounded border overflow-hidden shadow-sm bg-light position-relative" style="width: 80px; height: 45px;">
                                            <img src="<?php echo htmlspecialchars($E['imagen_header']); ?>" alt="Header" class="w-100 h-100 object-fit-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="rounded border border-dashed bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 45px;">
                                            <a href="index.php?System=encuestas&a=configuracion&id=<?= $E['id'] ?>" class="btn btn-link text-decoration-none text-muted p-0" title="Subir Imagen">
                                                <i class="bi bi-image"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 align-middle">
                                    <div class="extra-small"><i class="bi bi-calendar-event me-1 text-muted"></i> <?php echo $E['fecha_inicio']; ?></div>
                                    <div class="extra-small">
                                        <i class="bi bi-calendar-check me-1 text-muted"></i> 
                                        <?php 
                                        if ($E['fecha_fin']) {
                                            echo $E['fecha_fin'];
                                        } else {
                                            echo 'Sin límite';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="py-3 align-middle">
                                    <div class="d-flex gap-2">
                                        <?php 
                                        $EsAnonima = false;
                                        if (isset($E['anonima'])) {
                                            if ($E['anonima'] == 1) {
                                                $EsAnonima = true;
                                            }
                                        }
                                        
                                        if ($EsAnonima): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Anónima</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Registro Req.</span>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $Tiempo = 0;
                                        if (isset($E['tiempo_estimado'])) {
                                            $Tiempo = $E['tiempo_estimado'];
                                        }
                                        
                                        if ($Tiempo > 0): ?>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-stopwatch me-1"></i> <?php echo $E['tiempo_estimado']; ?> min
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <!-- Botón Editar ahora apunta a Configuración -->
                                        <a href="index.php?System=encuestas&a=configuracion&id=<?= $E['id'] ?>" 
                                           class="btn btn-sm btn-light border text-primary" 
                                           title="Editar Configuración">
                                            <i class="bi bi-gear-fill"></i>
                                        </a>
                                        
                                        <a href="index.php?System=encuestas&a=preguntas&id=<?php echo $E['id']; ?>" 
                                           class="btn btn-sm btn-light border text-dark" 
                                           title="Gestionar Preguntas">
                                            <i class="bi bi-list-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Encuesta (Solo para crear) -->
<div class="modal fade" id="modalEncuesta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Nueva Encuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEncuesta" method="POST" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                <input type="hidden" name="id" id="encuestaId" value="0">
                <input type="hidden" name="action_url" id="actionUrl" value="index.php?System=encuestas&a=guardar">
                
                <div class="modal-body pt-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Título de la Encuesta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-light border-0" name="titulo" required placeholder="Ej. Satisfacción Cliente">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Imagen de Encabezado (Opcional)</label>
                        <input type="file" class="form-control bg-light border-0" name="imagen_header" accept="image/*">
                        <div class="form-text extra-small text-muted">Recomendado: 1200x300px (JPG, PNG).</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-light border-0" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Fecha Fin</label>
                            <div class="input-group">
                                <input type="date" class="form-control bg-light border-0" name="fecha_fin" id="fechaFin" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="sinLimite" name="sin_limite" value="1">
                                <label class="form-check-label extra-small text-muted" for="sinLimite">
                                    Sin fecha límite
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted d-block">Tipo de Encuesta</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="anonima" id="tipoReg" value="0" checked>
                                <label class="form-check-label small text-muted" for="tipoReg">
                                    Requiere Registro
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="anonima" id="tipoAnon" value="1">
                                <label class="form-check-label small text-muted" for="tipoAnon">
                                    Anónima (Pública)
                                </label>
                            </div>
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

    // Lógica Sin Límite
    const checkSinLimite = document.getElementById('sinLimite');
    const inputFechaFin = document.getElementById('fechaFin');
    
    if(checkSinLimite && inputFechaFin) {
        checkSinLimite.addEventListener('change', function() {
            if(this.checked) {
                inputFechaFin.disabled = true;
                inputFechaFin.required = false;
                inputFechaFin.value = '';
            } else {
                inputFechaFin.disabled = false;
                inputFechaFin.required = true;
                if(!inputFechaFin.value) {
                     // Lógica opcional
                }
            }
        });
    }

    window.abrirModalCrear = function() {
        if (!modal) return;
        document.getElementById('formEncuesta').reset();
        document.getElementById('encuestaId').value = 0;
        document.getElementById('formEncuesta').action = 'index.php?System=encuestas&a=guardar';
        document.querySelector('.modal-title').textContent = 'Nueva Encuesta';
        
        // Reset manual
        if(checkSinLimite) {
            checkSinLimite.checked = false;
            checkSinLimite.dispatchEvent(new Event('change'));
        }
        
        modal.show();
    };
    
    // Función abrirModalEditar eliminada intencionalmente, ahora se redirige.

    const form = document.getElementById('formEncuesta');
    if(form) {
        form.addEventListener('submit', function(e) {
             const btn = this.querySelector('button[type="submit"]');
             if(btn) {
                 btn.disabled = true;
                 btn.innerHTML = 'Procesando...';
             }
        });
    }
});
</script>
