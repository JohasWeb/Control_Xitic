<?php
// View/Dashboard/index.php
include 'View/layouts/header_admin.php'; // Asumimos que el header maneja la estructura HTML inicial
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Bienvenido, <?php echo htmlspecialchars($_SESSION['_Nombre_Sesion']); ?></h2>
            <p class="text-muted">Panel de Control General</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tarjeta de Clientes (Solo visible para AdminMaster) -->
        <?php if (isset($_SESSION['_Es_AdminMaster']) && $_SESSION['_Es_AdminMaster'] === 1): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="fa-solid fa-building fa-xl"></i>
                        </div>
                        <h5 class="card-title mb-0">Clientes</h5>
                    </div>
                    <p class="card-text text-muted">Gestión de empresas, marcas y configuración general.</p>
                    <a href="index.php?System=clientes" class="btn btn-outline-primary stretched-link">Administrar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tarjeta de Encuestas -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                            <i class="fa-solid fa-clipboard-question fa-xl"></i>
                        </div>
                        <h5 class="card-title mb-0">Encuestas</h5>
                    </div>
                    <p class="card-text text-muted">Creación de encuestas y generación de códigos QR.</p>
                    <a href="index.php?System=encuestas" class="btn btn-outline-success stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Reportes -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                            <i class="fa-solid fa-chart-pie fa-xl"></i>
                        </div>
                        <h5 class="card-title mb-0">Reportes</h5>
                    </div>
                    <p class="card-text text-muted">Analítica de resultados y satisfacción de clientes.</p>
                    <a href="#" class="btn btn-outline-info stretched-link">Ver Reportes</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>
