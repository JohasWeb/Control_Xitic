<?php
/**
 * Archivo: View/ClienteAdmin/Encuestas/resultados.php
 * Propósito: Vista para listar respuestas de una encuesta y exportar a CSV.
 * Fecha: 2026-01-22
 * 
 */





include 'View/layouts/header_cliente.php';
?>
<div class="page-wrapper anime-fade-in">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <div class="mb-2">
                <a href="index.php?System=encuestas" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Mis Encuestas
                </a>
            </div>
            <h1 class="page-title mb-1">Resultados: <?php echo htmlspecialchars($Encuesta['titulo']); ?></h1>
            <p class="page-subtitle mb-0">Visualiza y exporta las respuestas recibidas.</p>
        </div>
        <div>
            <a href="index.php?System=encuestas&a=exportar_csv&id=<?php echo $EncuestaId; ?>" target="_blank" class="btn btn-outline-success rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-file-earmark-excel me-2"></i>Exportar CSV
            </a>
        </div>
    </div>
    </div>

    <!-- Filtros -->
    <!-- Filtros -->
    <div class="mb-4">
        <div class="soft-card p-4">
             <h5 class="fw-bold mb-3 small text-muted text-uppercase">
                 <i class="bi bi-funnel-fill me-2 text-primary"></i> Filtros de Búsqueda
             </h5>
             <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="System" value="encuestas">
                <input type="hidden" name="a" value="resultados">
                <input type="hidden" name="id" value="<?php echo $EncuestaId; ?>">
                
                <div class="col-6 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted">Fecha Inicio</label>
                    <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="fecha_inicio" value="<?php echo htmlspecialchars($F_FechaInicio); ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label extra-small fw-bold text-muted">Fecha Fin</label>
                    <input type="date" class="form-control form-control-sm border-0 shadow-sm" name="fecha_fin" value="<?php echo htmlspecialchars($F_FechaFin); ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label extra-small fw-bold text-muted">Sucursal</label>
                    <select class="form-select form-select-sm border-0 shadow-sm" name="sucursal_id">
                        <option value="">-- Todas --</option>
                        <?php foreach ($Sucursales as $S): ?>
                            <option value="<?php echo $S['id']; ?>" <?php echo ($F_SucursalId == $S['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($S['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold rounded-pill shadow-sm" style="background:var(--accent);border:none;">
                        Aplicar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="soft-card">
        <div class="table-responsive" style="overflow-x: auto; max-width: 100%; border-radius: 1rem;">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted extra-small fw-bold">ID</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Fecha</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Sucursal</th>
                        <!-- Dynamic Question Headers -->
                        <?php foreach ($Preguntas as $P): ?>
                            <th class="py-3 text-uppercase text-muted extra-small fw-bold" style="min-width: 150px;">
                                <?php echo htmlspecialchars(mb_strimwidth($P['texto_pregunta'], 0, 30, '...')); ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold text-center">Duración</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Respuestas)): ?>
                        <tr>
                            <td colspan="<?php echo 5 + count($Preguntas); ?>" class="text-center py-5">
                                <span class="text-muted">No hay respuestas registradas aún.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Respuestas as $R): ?>
                            <tr>
                                <td class="ps-4 py-3 align-middle text-muted">
                                    #<?php echo $R['id']; ?>
                                </td>
                                <td class="py-3 align-middle">
                                    <div class="fw-bold text-dark"><?php echo date('d/m/Y', strtotime($R['fecha_respuesta'])); ?></div>
                                    <div class="extra-small text-muted"><?php echo date('H:i', strtotime($R['fecha_respuesta'])); ?></div>
                                </td>
                                <td class="py-3 align-middle">
                                    <?php echo htmlspecialchars($R['sucursal_nombre'] ?? 'General / N/A'); ?>
                                </td>

                                <!-- Dynamic Answers -->
                                <?php foreach ($Preguntas as $P): ?>
                                    <td class="py-3 align-middle">
                                        <?php 
                                            // Get answer from matrix
                                            $Val = $MatrixRespuestas[$R['id']][$P['id']] ?? '-';
                                            
                                            // Styling for Botonera
                                            if (strtoupper($P['tipo_pregunta']) === 'BOTONERA') {
                                                $BadgeClass = 'bg-light text-dark';
                                                
                                                // Check for specific text values
                                                if ($Val === 'Tuve un problema') {
                                                    $BadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                } elseif ($Val === 'Sugerencia') {
                                                    $BadgeClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                                } elseif ($Val === 'Gran experiencia') {
                                                    $BadgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                                } elseif (is_numeric($Val)) {
                                                    // Fallback for numeric NPS just in case
                                                    if ($Val >= 0 && $Val <= 6) $BadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                    elseif ($Val >= 7 && $Val <= 8) $BadgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                                    elseif ($Val >= 9 && $Val <= 10) $BadgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                                }

                                                echo "<span class='badge rounded-pill $BadgeClass px-3 py-2 fw-bold'>$Val</span>";
                                            } else {
                                                // Default Truncate
                                                echo htmlspecialchars(mb_strimwidth($Val, 0, 40, '...'));
                                            }
                                        ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="py-3 align-middle text-center">
                                    <?php 
                                    $dur = $R['duracion_segundos'];
                                    $min = floor($dur / 60);
                                    $sec = $dur % 60;
                                    echo sprintf("%dm %02ds", $min, $sec);
                                    ?>
                                </td>
                                <td class="py-3 align-middle text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle shadow-sm"
                                            onclick="verDetalle(<?php echo $R['id']; ?>)"
                                            title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detalle de Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4" id="modalDetalleBody">
                <div class="text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    Cargando...
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" style="background:var(--accent);border:none;" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>

<script>
function verDetalle(id) {
    var modalEl = document.getElementById('modalDetalle');
    var modal = new bootstrap.Modal(modalEl);
    var body = document.getElementById('modalDetalleBody');
    
    // Reset Body
    body.innerHTML = '<div class="text-center text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando...</div>';
    
    modal.show();
    
    fetch('index.php?System=encuestas&a=ver_detalle&id=' + id)
        .then(response => response.text())
        .then(html => {
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="text-danger text-center">Error al cargar detalle.</div>';
        });
}
</script>
