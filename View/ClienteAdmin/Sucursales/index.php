<?php
// View/ClienteAdmin/Sucursales/index.php
include 'View/layouts/header_cliente.php';

// Variables disponibles del controlador:
// $Sucursales (array)
// $LimiteSucursales (int)
// $ConteoActual (int)

$Porcentaje = ($LimiteSucursales > 0) ? min(100, ($ConteoActual / $LimiteSucursales) * 100) : 0;
$ClaseBarra = 'bg-success';
if ($Porcentaje > 70) $ClaseBarra = 'bg-warning';
if ($Porcentaje >= 100) $ClaseBarra = 'bg-danger';

$LímiteAlcanzado = ($LimiteSucursales > 0 && $ConteoActual >= $LimiteSucursales);
$SinRegiones = empty($Regiones);
$Csrf = SecurityController::obtenerCsrfToken();
?>

<div class="page-wrapper anime-fade-in">
    <!-- Header y Stats -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="page-title mb-1">Mis Sucursales</h1>
            <p class="page-subtitle mb-0">Gestiona tus puntos de venta y monitorea su actividad.</p>
        </div>
        
        <?php if ($LimiteSucursales > 0): ?>
            <div class="soft-card px-3 py-2 d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="extra-small fw-bold text-uppercase text-muted ls-1">Plan Actual</div>
                    <div class="fw-bold <?php echo $LímiteAlcanzado ? 'text-danger' : 'text-dark'; ?>">
                        <?php echo $ConteoActual; ?> / <?php echo $LimiteSucursales; ?> Sucursales
                    </div>
                </div>
                <div style="width:100px;">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar rounded-pill <?php echo $ClaseBarra; ?>" role="progressbar" 
                             style="width: <?php echo $Porcentaje; ?>%"></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle rounded-pill">
                <i class="bi bi-infinity me-1"></i> Plan Ilimitado
            </div>
        <?php endif; ?>
    </div>

    <!-- Toolbar -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="soft-card p-2 d-flex align-items-center gap-2" style="max-width: 400px;">
                <i class="bi bi-search text-muted ms-2"></i>
                <input type="text" id="filtroTabla" class="form-control border-0 shadow-none bg-transparent" placeholder="Buscar sucursal...">
            </div>

            <?php if (!$LímiteAlcanzado): ?>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" 
                        style="background:var(--accent);border:none;" 
                        onclick="abrirModalCrear()">
                    <i class="bi bi-plus-lg me-2"></i>Nueva Sucursal
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm fw-bold opacity-75" 
                        data-bs-toggle="tooltip" data-bs-placement="left" 
                        title="Has alcanzado el límite de tu plan.">
                    <i class="bi bi-lock-fill me-2"></i>Límite Alcanzado
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla -->
    <div class="soft-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-uppercase text-muted extra-small fw-bold">Nombre</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Región</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Dirección</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Supervisor</th>
                        <th class="py-3 text-uppercase text-muted extra-small fw-bold">Estado</th>
                        <th class="text-end pe-4 py-3 text-uppercase text-muted extra-small fw-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaSucursales">
                    <?php if (empty($Sucursales)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-shop fa-2x mb-2 d-block"></i>
                                    <span class="small fw-bold">No tienes sucursales registradas</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Sucursales as $S): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($S['nombre']); ?></div>
                                    <div class="extra-small text-muted">ID: #<?php echo str_pad((string)$S['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($S['region'] ?: 'General'); ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($S['direccion']); ?>">
                                        <?php echo htmlspecialchars($S['direccion'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="badge bg-light text-dark border fw-normal extra-small">
                                        <i class="bi bi-person-fill me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($S['supervisor_nombre'] ?? 'Sin Supervisor'); ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <?php if ($S['activo'] == 1): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">Activa</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-light border text-primary" 
                                                onclick='abrirModalEditar(<?php echo json_encode($S); ?>)'
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light border text-muted" 
                                                onclick="toggleEstado(<?php echo $S['id']; ?>)"
                                                title="<?php echo ($S['activo'] == 1) ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="bi bi-power"></i>
                                        </button>
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

<!-- Modal Gestión -->
<div class="modal fade" id="modalSucursal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitulo">Nueva Sucursal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formSucursal" action="index.php?System=sucursales&a=guardar" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">
                <input type="hidden" name="id" id="sucursalId" value="0">
                <input type="hidden" name="supervisor_id_hidden" id="sucursalSupervisorIdHidden" value="0">
                
                <div class="modal-body pt-4">
                    <?php if ($SinRegiones): ?>
                         <div class="text-center py-4">
                            <div class="text-warning mb-3"><i class="bi bi-exclamation-circle fa-2x"></i></div>
                            <h6 class="fw-bold">No hay regiones disponibles</h6>
                            <p class="small text-muted mb-4">Para crear una sucursal, primero debes registrar al menos una región o zona.</p>
                            <a href="index.php?System=regiones" class="btn btn-primary rounded-pill px-4 fw-bold w-100" style="background:var(--accent);border:none;">
                                Registrar Región Ahora
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre de la Sucursal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="nombre" id="sucursalNombre" required placeholder="Ej. Sucursal Centro">
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Región / Zona <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0" name="region" id="sucursalRegion" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($Regiones as $R): ?>
                                        <option value="<?php echo htmlspecialchars($R['nombre']); ?>">
                                            <?php echo htmlspecialchars($R['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                 <label class="form-label small fw-bold text-muted">Dirección (Opcional)</label>
                                 <input type="text" class="form-control bg-light border-0" name="direccion" id="sucursalDireccion" placeholder="Calle y numero...">
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="border-light">
                            <h6 class="text-muted text-uppercase extra-small fw-bold ls-1 mb-3">Supervisor (Gerente)</h6>
                            <div class="alert alert-light border border-light-subtle d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-info-circle text-primary mt-1"></i>
                                <div class="small text-muted">Si ingresas un correo nuevo, se creará un usuario automáticamente y <b>se descargará un archivo con las credenciales</b>.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" name="supervisor_nombre" id="supNombre" placeholder="Ej. Ana">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Apellido(s)</label>
                                <input type="text" class="form-control bg-light border-0" name="supervisor_apellido" id="supApellido" placeholder="Ej. Lopez">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Correo Electrónico (Usuario) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control bg-light border-0" name="supervisor_email" id="supEmail" placeholder="gerente@sucursal.com">
                            </div>
                        </div>

                        <?php if ($LímiteAlcanzado): ?>
                            <div id="avisoLimite" class="alert alert-warning d-flex align-items-center gap-2 mt-3 mb-0 py-2 extra-small">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div>Atención: Estás cerca o en el límite de tu plan.</div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!$SinRegiones): ?>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        <i class="bi bi-save me-2"></i>Guardar Sucursal
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('modalSucursal');
    var modal = null;
    
    // Función segura para iniciar modal cuando bootstrap esté listo
    function initModal() {
        if (typeof bootstrap !== 'undefined' && modalEl) {
            modal = new bootstrap.Modal(modalEl);
        } else {
            // Reintentar brevemente si bootstrap aun no carga (caso extremo)
            setTimeout(initModal, 100);
        }
    }
    initModal();

    window.abrirModalCrear = function() {
        if (!modal) return;
        document.getElementById('formSucursal').reset();
        document.getElementById('sucursalId').value = 0;
        document.getElementById('sucursalSupervisorIdHidden').value = 0;
        document.getElementById('modalTitulo').textContent = 'Nueva Sucursal';
        
        // Reset aviso
        const aviso = document.getElementById('avisoLimite');
        if(aviso) aviso.style.display = 'flex';
        
        modal.show();
    };

    window.abrirModalEditar = function(sucursal) {
        if (!modal) return;
        document.getElementById('formSucursal').reset();
        document.getElementById('sucursalId').value = sucursal.id;
        document.getElementById('sucursalNombre').value = sucursal.nombre;
        
        // Setear select de región
        const selectRegion = document.getElementById('sucursalRegion');
        if(selectRegion) {
            selectRegion.value = sucursal.region; 
        }
        
        document.getElementById('sucursalDireccion').value = sucursal.direccion;
        document.getElementById('sucursalSupervisorIdHidden').value = sucursal.usuario_id || 0;
        
        // Cargar supervisor
        document.getElementById('supNombre').value = sucursal.supervisor_nombre || '';
        document.getElementById('supApellido').value = sucursal.supervisor_apellido || '';
        document.getElementById('supEmail').value = sucursal.supervisor_email || '';

        document.getElementById('modalTitulo').textContent = 'Editar Sucursal';
        
        // Ocultar aviso de límite al editar
        const aviso = document.getElementById('avisoLimite');
        if(aviso) aviso.style.display = 'none';

        modal.show();
    };

    const form = document.getElementById('formSucursal');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Guardando...';

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    if (data.credentials) {
                        const content = `CREDENCIALES DE ACCESO - GERENTE DE SUCURSAL\n\n` + 
                                      `Hola ${data.credentials.email},\n\n` +
                                      `Se ha creado tu cuenta de Gerente de Sucursal exitosamente.\n\n` +
                                      `Usuario: ${data.credentials.email}\n` +
                                      `Contraseña: ${data.credentials.password}\n\n` +
                                      `Por favor ingresa al sistema y cambia tu contraseña lo antes posible.\n` +
                                      `Fecha de generación: ${new Date().toLocaleString()}`;
                        
                        const blob = new Blob([content], { type: 'text/plain' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `credenciales_gerente_${Date.now()}.txt`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                        
                        alert(data.message); 
                    }
                    location.reload();
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }

    // Toggle Estado
    window.toggleEstado = function(id) {
        if(!confirm('¿Cambiar estado de la sucursal?')) return;
        
        const fd = new FormData();
        fd.append('id', id);

        fetch('index.php?System=sucursales&a=toggle', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    };
    
    // Filtro simple
    const filtro = document.getElementById('filtroTabla');
    if (filtro) {
        filtro.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tablaSucursales tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>
