<?php include 'View/layouts/header_cliente.php'; ?>

<div class="page-wrapper anime-fade-in">
    <div class="mb-4">
        <a href="index.php?System=casos" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Volver a Casos
        </a>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Información Principal -->
        <div class="col-lg-7">
            <div class="soft-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border">#<?php echo str_pad($Caso['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        <span class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($Caso['fecha_creacion'])); ?></span>
                    </div>
                    <div>
                        <?php if ($Caso['estado'] == 'abierto'): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Abierto</span>
                        <?php else: ?>
                            <span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill px-3">Cerrado</span>
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="fw-bold mb-3 text-dark"><?php echo htmlspecialchars($Caso['titulo']); ?></h3>

                <?php if (!empty($Caso['resumen'])): ?>
                    <div class="alert alert-light border mb-4">
                        <i class="bi bi-stars text-indigo me-1"></i>
                        <span class="fw-bold text-indigo small text-uppercase ls-1">Resumen AI:</span>
                        <p class="mb-0 mt-1 small text-muted"><?php echo htmlspecialchars($Caso['resumen']); ?></p>
                    </div>
                <?php endif; ?>
                
                <h6 class="text-uppercase text-muted extra-small fw-bold mb-2 ls-1">Detalle / Encuesta</h6>
                <div class="p-3 bg-light rounded text-dark border" style="white-space: pre-wrap; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($Caso['descripcion']); ?></div>
            </div>
        </div>

        <!-- Columna Derecha: Metadatos y Gestión -->
        <div class="col-lg-5">
            <div class="soft-card p-4 mb-4 border-start-lg border-primary" style="border-left: 4px solid var(--accent) !important;">
                <h6 class="fw-bold mb-3 text-indigo">Análisis e Impacto</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="d-block text-muted extra-small fw-bold mb-1">Categoría</label>
                        <?php if (!empty($Caso['categoria'])): ?>
                            <span class="badge bg-white text-dark border shadow-sm py-2 px-3 d-block text-start text-truncate">
                                <i class="bi bi-tag text-muted me-1"></i> <?php echo htmlspecialchars($Caso['categoria']); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">No definida</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <label class="d-block text-muted extra-small fw-bold mb-1">Severidad</label>
                        <?php 
                            $SevClass = 'bg-secondary-subtle text-secondary';
                            $Icon = 'bi-circle';
                            if ($Caso['severidad'] == 'Alta') { $SevClass = 'bg-warning-subtle text-warning'; $Icon = 'bi-exclamation-triangle'; }
                            if ($Caso['severidad'] == 'Critica') { $SevClass = 'bg-danger-subtle text-danger'; $Icon = 'bi-exclamation-octagon-fill'; }
                        ?>
                        <span class="badge <?php echo $SevClass; ?> border py-2 px-3 d-block text-start">
                            <i class="bi <?php echo $Icon; ?> me-1"></i> <?php echo htmlspecialchars($Caso['severidad']); ?>
                        </span>
                    </div>
                </div>

                <hr class="border-secondary-subtle opacity-25 my-4">

                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-stopwatch text-muted"></i>
                        <span class="fw-bold text-dark small">SLA (Acuerdo de Nivel de Servicio)</span>
                    </div>
                    <?php if (!empty($Caso['sla_vencimiento'])): ?>
                        <?php 
                            $Vence = strtotime($Caso['sla_vencimiento']);
                            $Ahora = time();
                            $Restante = $Vence - $Ahora;
                            // Formato amigable de tiempo restante?
                        ?>
                        <div class="bg-light p-3 rounded border text-center">
                            <?php if ($Caso['estado'] == 'cerrado'): ?>
                                <div class="text-success fw-bold"><i class="bi bi-check-circle-fill fs-5 d-block mb-1"></i>Caso Resuelto</div>
                            <?php elseif ($Ahora > $Vence): ?>
                                <div class="text-danger fw-bold"><i class="bi bi-fire fs-5 d-block mb-1"></i>¡SLA Vencido!</div>
                                <div class="extra-small text-muted mt-1">Debió resolverse antes de: <?php echo date('d/m/Y H:i', $Vence); ?></div>
                            <?php else: ?>
                                <div class="text-primary fw-bold fs-4 mb-0">
                                    <?php 
                                        $Horas = floor($Restante / 3600); 
                                        echo $Horas . 'h'; 
                                    ?>
                                </div>
                                <div class="extra-small text-muted text-uppercase fw-bold">Restantes</div>
                                <div class="extra-small text-muted mt-2 border-top pt-2">Vence: <?php echo date('d/m/Y H:i', $Vence); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small">Sin SLA configurado.</div>
                    <?php endif; ?>
                </div>

                <div class="mb-2">
                    <label class="d-block text-muted extra-small fw-bold mb-1">Ubicación</label>
                    <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                        <div class="bg-white p-2 rounded border text-secondary"><i class="bi bi-shop"></i></div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-dark small text-truncate"><?php echo htmlspecialchars($Caso['sucursal_nombre'] ?? 'Sin Sucursal'); ?></div>
                            <div class="extra-small text-muted"><?php echo htmlspecialchars($Caso['region_nombre'] ?? 'Sin Región'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>
