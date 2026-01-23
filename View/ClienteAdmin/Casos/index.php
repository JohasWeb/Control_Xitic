<?php include 'View/layouts/header_cliente.php'; ?>

<div class="page-wrapper anime-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title mb-1">Casos con IA</h1>
            <p class="page-subtitle mb-0">Gestión y análisis de casos asistido por inteligencia artificial.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light border text-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalConfigCasos">
                <i class="bi bi-gear me-2"></i>Configurar
            </button>
            <a href="index.php?System=casos&a=crear" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background:var(--accent);border:none;">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Caso
            </a>
        </div>
    </div>

    <div class="soft-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted extra-small fw-bold">ID</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Fecha</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Ubicación</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Categoría / IA</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">SLA</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase text-muted extra-small fw-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Casos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted opacity-50">
                                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                                No hay casos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Casos as $C): ?>
                            <tr>
                                <td class="ps-4 py-3 text-muted fw-bold">#<?php echo str_pad($C['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="py-3 text-muted small">
                                    <?php echo date('d/m/Y H:i', strtotime($C['fecha_creacion'])); ?>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($C['sucursal_nombre'])): ?>
                                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars($C['sucursal_nombre']); ?></div>
                                        <div class="extra-small text-muted"><?php echo htmlspecialchars($C['region_nombre'] ?? 'Sin Región'); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($C['categoria'])): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1"><?php echo htmlspecialchars($C['categoria']); ?></span>
                                        <?php 
                                            $SevClass = 'bg-secondary-subtle text-secondary';
                                            if ($C['severidad'] == 'Alta') $SevClass = 'bg-warning-subtle text-warning';
                                            if ($C['severidad'] == 'Critica') $SevClass = 'bg-danger-subtle text-danger';
                                        ?>
                                        <span class="badge <?php echo $SevClass; ?> border extra-small"><?php echo htmlspecialchars($C['severidad']); ?></span>
                                        <div class="small text-muted mt-1 text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($C['titulo']); ?>">
                                            <?php echo htmlspecialchars($C['titulo']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small"><?php echo htmlspecialchars($C['titulo']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if (!empty($C['sla_vencimiento'])): ?>
                                        <?php 
                                            $Vence = strtotime($C['sla_vencimiento']);
                                            $Ahora = time();
                                            if ($C['estado'] == 'cerrado') {
                                                echo '<span class="text-success small"><i class="bi bi-check-all"></i> Cumplido</span>';
                                            } elseif ($Ahora > $Vence) {
                                                echo '<span class="text-danger small fw-bold"><i class="bi bi-exclamation-circle"></i> Vencido</span>';
                                            } else {
                                                echo '<span class="text-primary small">' . date('d/m H:i', $Vence) . '</span>';
                                            }
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3">
                                    <?php if ($C['estado'] == 'abierto'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Abierto</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill">Cerrado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <a href="index.php?System=casos&a=ver&id=<?php echo $C['id']; ?>" class="btn btn-sm btn-light border text-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<!-- MODAL CONFIGURACION CLIENTE -->
<div class="modal fade" id="modalConfigCasos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Configuración de Casos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formConfigCasos" action="index.php?System=casos&a=guardar_config" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SecurityController::obtenerCsrfToken(); ?>">

                <div class="modal-body pt-4">
                    <div class="alert alert-light border d-flex gap-2 mb-4">
                         <i class="bi bi-info-circle text-primary mt-1"></i>
                         <div class="small text-muted line-height-sm">
                             Esta configuración afecta cómo se calculan los tiempos de respuesta esperados para los casos generados automáticamente por la IA.
                         </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">SLA Estándar (Horas)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-clock"></i></span>
                            <!-- Se debería inyectar el valor actual del SLA, pero $Cliente no está disponible directamente aquí como variable simple.
                                 Mejor opción: Recuperarlo al inicio del view o pasarlo desde controller.
                                 Dado que index() ya carga config del usuario en header_cliente logic parcialmente, pero no el full object con SLA.
                                 
                                 FIX: El controller index() hace: $Cliente = $this->clientesModel->obtenerPorId($ClienteId); 
                                 Así que tenemos $Cliente disponible. -->
                            <input type="number" class="form-control bg-light border-0" name="sla_horas" value="<?php echo isset($Cliente['config_sla_horas']) ? (int)$Cliente['config_sla_horas'] : 24; ?>" min="1" max="720" required>
                            <span class="input-group-text bg-light border-0 small text-muted">horas</span>
                        </div>
                        <div class="form-text extra-small">Tiempo límite para resolver un caso (1-720 horas).</div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background:var(--accent);border:none;">Guardar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formConfig = document.getElementById('formConfigCasos');
    if(formConfig) {
        formConfig.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = formConfig.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Guardando...';

            fetch(formConfig.action, {
                method: 'POST',
                body: new FormData(formConfig)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                alert('Error de conexión');
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }
});
</script>

<?php include 'View/layouts/footer.php'; ?>
