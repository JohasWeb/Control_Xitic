<?php
// View/Clientes/index.php
include 'View/layouts/header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gestión de Clientes</h2>
            <p class="text-muted">Administra las empresas que contratan el servicio.</p>
        </div>
        <a href="index.php?System=clientes&a=crear" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Nuevo Cliente
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre Comercial</th>
                            <th>Razón Social</th>
                            <th>RFC</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($Clientes)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No hay clientes registrados aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($Clientes as $C): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $C['id']; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($C['nombre_comercial']); ?></div>
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($C['razon_social']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($C['rfc_tax_id']); ?></span></td>
                                    <td>
                                        <?php if ($C['activo'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="index.php?System=clientes&a=editar&id=<?php echo $C['id']; ?>" class="btn btn-sm btn-outline-secondary">
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
