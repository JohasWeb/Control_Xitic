<?php
// View/AdminMaster/Clientes/ver.php
include 'View/layouts/header_admin.php';
$CsrfToken = SecurityController::obtenerCsrfToken();
?>

<div class="page-wrapper anime-fade-in">
    <!-- ... (resto del código sin cambios hasta el modal) ... -->
    
<!-- MODAL EDICIÓN -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="index.php?System=clientes&a=guardar" method="POST" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $CsrfToken; ?>">
                <input type="hidden" name="id" value="<?php echo $Cliente['id']; ?>">
                
                <!-- Campos ocultos requeridos por validación de guardar() pero que no se editan aquí (Admin) -->
                <input type="hidden" name="admin_nombre" value="N/A">
                <input type="hidden" name="admin_apellido" value="N/A">
                <input type="hidden" name="email_admin" value="placeholder@xitic.com">

                <div class="modal-body pt-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nombre Comercial</label>
                        <input type="text" class="form-control bg-light border-0" name="nombre_comercial" required value="<?php echo htmlspecialchars($Cliente['nombre_comercial']); ?>">
                    </div>

                    <div class="mb-3">
                         <label class="form-label small fw-bold text-muted">Logo (Opcional - Reemplazar)</label>
                         <input type="file" class="form-control bg-light border-0" name="logo" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Comentarios</label>
                        <textarea class="form-control bg-light border-0" name="comentarios" rows="2"><?php echo htmlspecialchars($Cliente['comentarios'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger">Límite de Sucursales</label>
                        <input type="number" class="form-control bg-light border-0" name="limite_sucursales" min="0" value="<?php echo (int)$Cliente['limite_sucursales']; ?>">
                        <div class="form-text extra-small">0 = Ilimitadas.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-primary">Módulos Activos</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="modEnc" name="modulo_encuestas" value="1" <?php echo (!empty($Cliente['modulo_encuestas']) && $Cliente['modulo_encuestas'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="modEnc">Encuestas</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="modCas" name="modulo_casos" value="1" <?php echo (!empty($Cliente['modulo_casos']) && $Cliente['modulo_casos'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="modCas">Casos IA</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
            
            <div class="ms-auto d-flex gap-2">
                <!-- Toggle Activo -->
                <?php if ($Cliente['activo'] == 1): ?>
                    <a href="index.php?System=clientes&a=toggle_activo&id=<?php echo $Cliente['id']; ?>&estado=1" 
                       class="btn btn-outline-danger btn-sm rounded-pill px-3"
                       onclick="return confirm('¿Seguro que deseas desactivar este cliente? No podrán acceder al sistema.')">
                        <i class="bi bi-power me-1"></i>Desactivar
                    </a>
                <?php else: ?>
                    <a href="index.php?System=clientes&a=toggle_activo&id=<?php echo $Cliente['id']; ?>&estado=0" 
                       class="btn btn-outline-success btn-sm rounded-pill px-3">
                        <i class="bi bi-power me-1"></i>Activar
                    </a>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditarCliente">
                    <i class="bi bi-pencil me-1"></i>Editar
                </button>
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
                    <label class="d-block text-muted extra-small fw-bold mb-1">
                        Límite Sucursales
                        <a href="#" class="text-decoration-none ms-1" data-bs-toggle="modal" data-bs-target="#modalEditarCliente" title="Editar Límite">
                            <i class="bi bi-pencil-square small"></i>
                        </a>
                    </label>
                    <div class="fs-5 fw-bold <?php echo ($Cliente['limite_sucursales'] > 0) ? 'text-danger' : 'text-success'; ?>">
                        <?php echo ($Cliente['limite_sucursales'] > 0) ? $Cliente['limite_sucursales'] : 'Ilimitadas'; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="d-block text-muted extra-small fw-bold mb-2">Módulos Habilitados</label>
                    <div class="d-flex flex-column gap-2 bg-light p-3 rounded">
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                             <input class="form-check-input toggle-module cursor-pointer" type="checkbox" role="switch" id="switchEnc"
                                    data-id="<?php echo $Cliente['id']; ?>" data-mod="modulo_encuestas"
                                    <?php echo ($Cliente['modulo_encuestas'] == 1) ? 'checked' : ''; ?>>
                             <label class="form-check-label small fw-bold text-dark cursor-pointer" for="switchEnc">
                                <i class="bi bi-ui-checks-grid me-1 text-primary"></i>Encuestas
                             </label>
                        </div>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                             <input class="form-check-input toggle-module cursor-pointer" type="checkbox" role="switch" id="switchCas"
                                    data-id="<?php echo $Cliente['id']; ?>" data-mod="modulo_casos"
                                    <?php echo ($Cliente['modulo_casos'] == 1) ? 'checked' : ''; ?>>
                             <label class="form-check-label small fw-bold text-dark cursor-pointer" for="switchCas">
                                <i class="bi bi-robot me-1 text-indigo"></i>Casos con IA
                             </label>
                        </div>
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

            <!-- Card: Configuración IA -->
            <div class="soft-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-indigo-subtle text-indigo rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                            <i class="bi bi-robot small"></i>
                        </div>
                        <div>
                             <h6 class="text-uppercase text-muted extra-small fw-bold mb-0 ls-1">Prompt para Casos IA</h6>
                             <?php if (!empty($Cliente['config_ia_token'])): ?>
                                <span class="badge bg-success-subtle text-success extra-small border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Token Configurado</span>
                             <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning extra-small border border-warning-subtle"><i class="bi bi-exclamation-triangle me-1"></i>Sin Token</span>
                             <?php endif; ?>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-light border text-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditarIA">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </button>
                </div>
                
                <div class="bg-light p-3 rounded border" style="max-height: 200px; overflow-y: auto;">
                    <?php if (!empty($Cliente['config_ia_prompt'])): ?>
                        <pre class="mb-0 text-muted small" style="white-space: pre-wrap; font-family: inherit;"><?php echo htmlspecialchars($Cliente['config_ia_prompt']); ?></pre>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted opacity-50">
                            <i class="bi bi-chat-square-quote fs-1 mb-2 d-block"></i>
                            <span class="small">No se ha definido un Prompt de Sistema.</span>
                        </div>
                    <?php endif; ?>
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

<!-- MODAL EDITOR IA -->
<div class="modal fade" id="modalEditarIA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Configuración IA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?System=clientes&a=guardar" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $CsrfToken; ?>">
                <input type="hidden" name="id" value="<?php echo $Cliente['id']; ?>">
                
                <!-- Mantener datos requeridos que no cambian aquí -->
                <input type="hidden" name="nombre_comercial" value="<?php echo htmlspecialchars($Cliente['nombre_comercial']); ?>">
                <input type="hidden" name="admin_nombre" value="N/A">
                <input type="hidden" name="email_admin" value="placeholder@xitic.com">
                <!-- Mantener otros campos para no borrarlos al guardar (o el backend debe manejar nulls con cuidado, pero el backend actualiza todo) -->
                <!-- Mejor opción: Enviar solo ID y Prompt si el backend lo soportara parcialmente, pero 'actualizar' pide todo. 
                     Truco: Enviamos hidden con los valores actuales del cliente -->
                <input type="hidden" name="razon_social" value="<?php echo htmlspecialchars($Cliente['razon_social']); ?>">
                <input type="hidden" name="comentarios" value="<?php echo htmlspecialchars($Cliente['comentarios']); ?>">
                <input type="hidden" name="limite_sucursales" value="<?php echo htmlspecialchars($Cliente['limite_sucursales']); ?>">
                <input type="hidden" name="modulo_encuestas" value="<?php echo $Cliente['modulo_encuestas']; ?>">
                <input type="hidden" name="modulo_casos" value="<?php echo $Cliente['modulo_casos']; ?>">

                <div class="modal-body pt-4">
                    <div class="alert alert-light border d-flex gap-2 mb-3">
                         <i class="bi bi-shield-lock-fill text-muted mt-1"></i>
                         <div class="small text-muted line-height-sm">
                             La información proporcionada aquí se utilizará para configurar el comportamiento del asistente de IA exclusivo para este cliente.
                         </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">OpenAI API Token</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control bg-light border-0" name="config_ia_token" placeholder="sk-..." value="<?php echo htmlspecialchars($Cliente['config_ia_token'] ?? ''); ?>">
                        </div>
                        <div class="form-text extra-small">Introduce la clave API de OpenAI. Se guardará de forma segura.</div>
                    </div>



                    <div class="mb-3">
                        <label class="form-label fw-bold text-indigo small">Prompt para Casos IA</label>
                        <p class="extra-small text-muted mb-2">Instrucciones del sistema para el análisis de casos.</p>
                        <textarea class="form-control bg-light border-0 p-3 shadow-inner" name="config_ia_prompt" rows="12" placeholder="Eres un asistente experto en atención al cliente..." style="resize: vertical; font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($Cliente['config_ia_prompt'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                     <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                     <button type="submit" class="btn btn-primary rounded-pill px-4" style="background:var(--accent);border:none;">Guardar Prompt</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selector para ambos formularios
    const forms = document.querySelectorAll('#modalEditarCliente form, #modalEditarIA form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Guardando...';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
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
    });

    // Toggle Modulos AJAX
    document.querySelectorAll('.toggle-module').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const modulo = this.dataset.mod;
            const estado = this.checked ? 1 : 0;
            const self = this;

            // Deshabilitar temporalmente
            self.disabled = true;

            fetch('index.php?System=clientes&a=toggle_modulo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, modulo: modulo, estado: estado })
            })
            .then(res => res.json())
            .then(data => {
                self.disabled = false;
                if(!data.success) {
                    alert('Error al actualizar módulo: ' + (data.message || 'Desconocido'));
                    self.checked = !self.checked; // Revertir
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de red');
                self.disabled = false;
                self.checked = !self.checked;
            });
        });
    });
});
</script>

<?php include 'View/layouts/footer.php'; ?>
