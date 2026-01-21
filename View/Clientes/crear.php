<?php
// View/Clientes/crear.php (Sirve también para editar si se pasa $Cliente)
include 'View/layouts/header_admin.php';

$EsEdicion = isset($Cliente);
$Titulo = $EsEdicion ? 'Editar Cliente' : 'Nuevo Cliente';
$Action = 'index.php?System=clientes&a=guardar';
$Csrf = SecurityController::obtenerCsrfToken();

// Valores por defecto
$Id = $EsEdicion ? $Cliente['id'] : 0;
$Nombre = $EsEdicion ? $Cliente['nombre_comercial'] : '';
$Razon = $EsEdicion ? $Cliente['razon_social'] : '';
$Rfc = $EsEdicion ? $Cliente['rfc_tax_id'] : '';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="d-flex align-items-center mb-4">
                <a href="index.php?System=clientes" class="btn btn-outline-secondary me-3">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="mb-0"><?php echo $Titulo; ?></h2>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?php echo $Action; ?>" method="POST" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                        <input type="hidden" name="id" value="<?php echo $Id; ?>">

                        <div class="mb-3">
                            <label for="nombre_comercial" class="form-label fw-bold small text-uppercase">Nombre Comercial</label>
                            <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" 
                                   value="<?php echo htmlspecialchars($Nombre); ?>" required placeholder="Ej: Restaurante El Mariachi">
                        </div>

                        <div class="mb-3">
                            <label for="razon_social" class="form-label fw-bold small text-uppercase">Razón Social</label>
                            <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                   value="<?php echo htmlspecialchars($Razon); ?>" required placeholder="Ej: Operadora de Alimentos SA de CV">
                        </div>

                        <div class="mb-4">
                            <label for="rfc" class="form-label fw-bold small text-uppercase">RFC</label>
                            <input type="text" class="form-control" id="rfc" name="rfc" 
                                   value="<?php echo htmlspecialchars($Rfc); ?>" required maxlength="13" placeholder="XAXX010101000">
                            <div class="form-text">Clave de Registro Federal de Contribuyentes (México).</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php?System=clientes" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-save me-2"></i>Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>
