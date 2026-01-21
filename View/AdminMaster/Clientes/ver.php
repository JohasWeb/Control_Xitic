<?php
// View/AdminMaster/Clientes/ver.php
include 'View/layouts/header_admin.php';
?>

<div class="page-wrapper anime-fade-in">
    <!-- Header: Breadcrumb + Título -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb extra-small mb-2">
                <li class="breadcrumb-item"><a href="index.php?System=Dashboard" class="text-muted text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="index.php?System=clientes" class="text-muted text-decoration-none">Clientes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Perfil de Cliente</li>
            </ol>
        </nav>
        
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($Cliente['logo_url'])): ?>
                <div class="rounded-circle shadow-sm overflow-hidden d-flex align-items-center justify-content-center bg-white border" style="width:64px;height:64px;">
                    <img src="<?php echo htmlspecialchars($Cliente['logo_url']); ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                </div>
            <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                     style="width:64px;height:64px;background:linear-gradient(135deg, var(--accent) 0%, #818cf8 100%);font-size:1.5rem;">
                    <?php echo strtoupper(substr($Cliente['nombre_comercial'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <div>
                <h1 class="page-title mb-0"><?php echo htmlspecialchars($Cliente['nombre_comercial']); ?></h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge rounded-pill <?php echo ($Cliente['activo'] == 1) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?> px-2 border">
                        <?php echo ($Cliente['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                    </span>
                    <span class="text-muted extra-small">ID: #<?php echo str_pad((string)$Cliente['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido: Tabs y Paneles -->
    <div class="row g-4">
        <!-- Columna Izq: Datos Generales -->
        <div class="col-lg-4">
            <div class="soft-card p-4 h-100">
                <h6 class="text-uppercase text-muted extra-small fw-bold mb-3 ls-1">Información General</h6>
                
                <div class="mb-3">
                    <label class="d-block text-muted extra-small fw-bold mb-1">Razón Social</label>
                    <div class="text-dark"><?php echo htmlspecialchars($Cliente['razon_social'] ?? 'No registrada'); ?></div>
                </div>

                <div class="mb-3">
                    <label class="d-block text-muted extra-small fw-bold mb-1">Fecha de Registro</label>
                    <div class="text-dark">
                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                        <?php echo date('d/m/Y', strtotime($Cliente['fecha_registro'])); ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="d-block text-muted extra-small fw-bold mb-1">Comentarios</label>
                    <div class="p-3 bg-light rounded text-muted small fst-italic">
                        <?php echo nl2br(htmlspecialchars(!empty($Cliente['comentarios']) ? $Cliente['comentarios'] : 'Sin notas adicionales.')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Der: Estadísticas y Accesos Rápidos -->
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <!-- Stat: Usuarios -->
                <div class="col-6 col-md-3">
                    <div class="soft-card p-3 text-center">
                        <div class="mb-2 text-primary"><i class="bi bi-people fs-4"></i></div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo $Stats['usuarios']; ?></div>
                        <div class="extra-small text-muted fw-bold text-uppercase">Usuarios</div>
                    </div>
                </div>
                <!-- Stat: Marcas -->
                <div class="col-6 col-md-3">
                    <div class="soft-card p-3 text-center">
                        <div class="mb-2 text-indigo"><i class="bi bi-diagram-3 fs-4"></i></div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo $Stats['marcas']; ?></div>
                        <div class="extra-small text-muted fw-bold text-uppercase">Marcas</div>
                    </div>
                </div>
                <!-- Stat: Sucursales -->
                <div class="col-6 col-md-3">
                    <div class="soft-card p-3 text-center">
                        <div class="mb-2 text-success"><i class="bi bi-shop fs-4"></i></div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo $Stats['sucursales']; ?></div>
                        <div class="extra-small text-muted fw-bold text-uppercase">Sucursales</div>
                    </div>
                </div>
                <!-- Stat: Encuestas -->
                <div class="col-6 col-md-3">
                    <div class="soft-card p-3 text-center">
                        <div class="mb-2 text-warning"><i class="bi bi-ui-checks-grid fs-4"></i></div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo $Stats['encuestas']; ?></div>
                        <div class="extra-small text-muted fw-bold text-uppercase">Encuestas</div>
                    </div>
                </div>
            </div>

            <!-- Aquí se podría agregar una tabla rápida de usuarios si se quisiera expandir -->
            <div class="soft-card p-4 text-center text-muted">
                <i class="bi bi-cone-striped fs-1 d-block mb-3 opacity-50"></i>
                <p class="mb-0">Próximamente: Gestión detallada de usuarios y marcas desde este panel.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>
