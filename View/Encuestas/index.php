<?php
// View/Encuestas/index.php
include 'View/layouts/header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Encuestas</h2>
            <p class="text-muted">Administra las encuestas de satisfacción.</p>
        </div>
        <a href="index.php?System=encuestas&a=crear" class="btn btn-success">
            <i class="fa-solid fa-plus me-2"></i>Nueva Encuesta
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($Encuestas)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No hay encuestas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($Encuestas as $E): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $E['id']; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($E['titulo']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($E['descripcion'], 0, 50)) . '...'; ?></small>
                                    </td>
                                    <td class="text-info small"><?php echo htmlspecialchars($E['cliente_nombre']); ?></td>
                                    <td>
                                        <div class="small text-muted"> <i class="fa-solid fa-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($E['fecha_inicio'])); ?> </div>
                                        <div class="small text-muted"> <i class="fa-solid fa-flag-checkered me-1"></i> <?php echo date('d/m/Y', strtotime($E['fecha_fin'])); ?> </div>
                                    </td>
                                    <td>
                                        <?php if ($E['estado'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary" title="Ver QR (Pendiente)">
                                            <i class="fa-solid fa-qrcode"></i>
                                        </button>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-pen"></i>
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

<?php include 'View/layouts/footer.php'; ?>
