<?php require_once 'View/layouts/header_cliente.php'; ?>

<div class="page-wrapper anime-fade-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="index.php?System=encuestas" class="text-decoration-none text-muted small mb-1 d-block">
                <i class="bi bi-arrow-left me-1"></i> Volver a Encuestas
            </a>
            <h4 class="fw-bold text-dark mb-0">Configuración de Encuesta</h4>
            <small class="text-muted">Parametriza los detalles y el alcance de tu encuesta.</small>
        </div>
        <button type="submit" form="formConfig" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-check-lg me-2"></i>Guardar Cambios
        </button>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" id="configTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold border-0 bg-transparent border-bottom border-primary border-2 text-primary" 
                    id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button">
                <i class="bi bi-sliders me-2"></i>Configuración
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold border-0 bg-transparent text-muted" 
                    id="qr-tab" data-bs-toggle="tab" data-bs-target="#qr" type="button">
                <i class="bi bi-qr-code me-2"></i>Códigos QR
            </button>
        </li>
    </ul>

    <form id="formConfig" action="index.php?System=encuestas&a=actualizar" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $Encuesta['id'] ?>">
        <!-- Re-enviar configuracion visual existente para no perderla -->
        <input type="hidden" name="configuracion" value="<?= htmlspecialchars($Encuesta['configuracion_json'] ?? '') ?>">
        <input type="hidden" name="csrf_token" value="<?= $Security->obtenerCsrfToken() ?>">

        <div class="tab-content" id="configTabsContent">
            <!-- Tab Configuración -->
            <div class="tab-pane fade show active" id="config" role="tabpanel">
                <div class="row g-4">
                    <!-- Column Left: Detalles -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-dark">Detalles Generales</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Título de la Encuesta <span class="text-danger">*</span></label>
                                    <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($Encuesta['titulo']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Descripción / Introducción</label>
                                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($Encuesta['descripcion']) ?></textarea>
                                    <div class="form-text extra-small">Texto que verá el usuario antes de iniciar.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Fecha Inicio</label>
                                        <?php 
                                            // Format date to Y-m-d for input type="date"
                                            $f_inicio = '';
                                            if (!empty($Encuesta['fecha_inicio'])) {
                                                $f_inicio = date('Y-m-d', strtotime($Encuesta['fecha_inicio']));
                                            }
                                        ?>
                                        <input type="date" name="fecha_inicio" class="form-control" value="<?= $f_inicio ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Fecha Fin</label>
                                        <div class="input-group">
                                            <?php 
                                                // Format date to Y-m-d for input type="date"
                                                $f_fin = '';
                                                if (!empty($Encuesta['fecha_fin'])) {
                                                    $f_fin = date('Y-m-d', strtotime($Encuesta['fecha_fin']));
                                                }
                                            ?>
                                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?= $f_fin ?>">
                                            <div class="input-group-text bg-light">
                                                <input class="form-check-input mt-0" type="checkbox" id="sin_fin" 
                                                       <?= is_null($Encuesta['fecha_fin']) ? 'checked' : '' ?>>
                                                <label class="small ms-2 mb-0 cursor-pointer" for="sin_fin">Sin fecha</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="form-label small fw-bold text-muted">Imagen de Encabezado</label>
                                    <input type="file" name="imagen_header" class="form-control" accept="image/*">
                                    
                                    <?php if($Encuesta['imagen_header']): ?>
                                        <div class="mt-2 p-2 bg-light rounded text-center">
                                            <img src="<?= $Encuesta['imagen_header'] ?>" alt="Header" style="max-height: 100px; max-width: 100%;">
                                            <div class="extra-small text-muted mt-1">Imagen actual</div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row mt-4 align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted d-block">Privacidad</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="anonima" id="anonima0" value="0" <?= $Encuesta['anonima'] == 0 ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary" for="anonima0">Registro Req.</label>

                                            <input type="radio" class="btn-check" name="anonima" id="anonima1" value="1" <?= $Encuesta['anonima'] == 1 ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary" for="anonima1">Anónima</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Tiempo Est. (Min)</label>
                                        <input type="number" name="tiempo_estimado" class="form-control" value="<?= $Encuesta['tiempo_estimado'] ?>" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column Right: Alcance -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                             <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-dark">Alcance y Asignación</h6>
                                <p class="small text-muted mb-3">¿Dónde estará disponible esta encuesta?</p>

                                <div class="vstack gap-3">
                                    <!-- Option: Global -->
                                    <label class="assign-option card p-3 border <?= empty($Asignaciones) ? 'border-primary bg-primary-subtle' : '' ?> cursor-pointer transition-base" id="opt-global">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="alcance" value="global" 
                                                   <?= empty($Asignaciones) ? 'checked' : '' ?>>
                                            <div>
                                                <div class="fw-bold text-dark">Toda la Empresa</div>
                                                <div class="small text-muted">Disponible en todas las sucursales y regiones.</div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Option: Region -->
                                    <label class="assign-option card p-3 border <?= isset($Asignaciones['region']) ? 'border-primary bg-primary-subtle' : '' ?> cursor-pointer transition-base" id="opt-region">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input mt-1" type="radio" name="alcance" value="region" 
                                                   <?= isset($Asignaciones['region']) ? 'checked' : '' ?>>
                                            <div class="w-100">
                                                <div class="fw-bold text-dark">Por Regiones</div>
                                                <div class="small text-muted mb-2">Selecciona regiones específicas.</div>
                                                
                                                <div id="select-regiones" class="<?= isset($Asignaciones['region']) ? '' : 'd-none' ?> mt-2">
                                                    <select name="asignacion_region[]" class="form-select" multiple>
                                                        <?php foreach($Regiones as $Reg): ?>
                                                            <option value="<?= $Reg['id'] ?>" 
                                                                <?= in_array($Reg['id'], $Asignaciones['region'] ?? []) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($Reg['nombre']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Option: Sucursal -->
                                    <label class="assign-option card p-3 border <?= isset($Asignaciones['sucursal']) ? 'border-primary bg-primary-subtle' : '' ?> cursor-pointer transition-base" id="opt-sucursal">
                                        <div class="d-flex align-items-start gap-3">
                                             <input class="form-check-input mt-1" type="radio" name="alcance" value="sucursal" 
                                                   <?= isset($Asignaciones['sucursal']) ? 'checked' : '' ?>>
                                            <div class="w-100">
                                                <div class="fw-bold text-dark">Por Sucursales</div>
                                                <div class="small text-muted mb-2">Selecciona sucursales específicas.</div>

                                                <div id="select-sucursales" class="<?= isset($Asignaciones['sucursal']) ? '' : 'd-none' ?> mt-2">
                                                    <select name="asignacion_sucursal[]" class="form-select" multiple style="min-height: 120px;">
                                                         <?php foreach($Sucursales as $Suc): ?>
                                                            <option value="<?= $Suc['id'] ?>" 
                                                                <?= in_array($Suc['id'], $Asignaciones['sucursal'] ?? []) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($Suc['nombre']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab QR -->
            <div class="tab-pane fade" id="qr" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-4">Códigos QR de Acceso</h6>
                        <p class="text-muted">Descarga el QR para cada sucursal.</p>
                        
                        <div class="row g-4 justify-content-center">
                            <?php if(empty($SucursalesHabilitadas)): ?>
                                <div class="col-12 p-5">
                                    <div class="text-muted">La encuesta no está asignada a ninguna sucursal o es global y no hay sucursales registradas.</div>
                                </div>
                            <?php else: ?>
                                <?php foreach($SucursalesHabilitadas as $SucQR): ?>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="card h-100 border text-center p-3">
                                            <div class="mb-3">
                                                <!-- Generator Placeholder (Usando API publica por ahora) -->
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(BASE_URL . '/index.php?System=encuestas&a=responder&id=' . $Encuesta['id'] . '&sucursal_id=' . $SucQR['id']) ?>" 
                                                     class="img-fluid rounded border p-1" alt="QR">
                                            </div>
                                            <h6 class="small fw-bold mb-1 show-one-line"><?= htmlspecialchars($SucQR['nombre']) ?></h6>
                                            <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=<?= urlencode(BASE_URL . '/index.php?System=encuestas&a=responder&id=' . $Encuesta['id'] . '&sucursal_id=' . $SucQR['id']) ?>" 
                                               download="QR_<?= $SucQR['nombre'] ?>.png" 
                                               class="btn btn-sm btn-outline-primary rounded-pill mt-auto">
                                               <i class="bi bi-download me-1"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function appToggleFechaFin() {
        const check = document.getElementById('sin_fin');
        const input = document.getElementById('fecha_fin');
        if(check && input) {
            if(check.checked) {
                input.disabled = true;
                input.value = '';
            } else {
                input.disabled = false;
            }
        }
    }

    function appToggleAlcance() {
        // Find checked Radio
        const radios = document.getElementsByName('alcance');
        let selectedVal = 'global';
        for(let i=0; i<radios.length; i++) {
            if(radios[i].checked) {
                selectedVal = radios[i].value;
                break;
            }
        }

        // Logic
        const selReg = document.getElementById('select-regiones');
        const selSuc = document.getElementById('select-sucursales');
        const optGlobal = document.getElementById('opt-global');
        const optRegion = document.getElementById('opt-region');
        const optSucursal = document.getElementById('opt-sucursal');
        
        // Reset Visuals
        if(optGlobal) optGlobal.classList.remove('border-primary', 'bg-primary-subtle');
        if(optRegion) optRegion.classList.remove('border-primary', 'bg-primary-subtle');
        if(optSucursal) optSucursal.classList.remove('border-primary', 'bg-primary-subtle');
        
        // Reset Visibility
        if(selReg) selReg.classList.add('d-none');
        if(selSuc) selSuc.classList.add('d-none');

        // Apply Active
        if(selectedVal === 'global') {
            if(optGlobal) optGlobal.classList.add('border-primary', 'bg-primary-subtle');
        } 
        else if(selectedVal === 'region') {
            if(optRegion) optRegion.classList.add('border-primary', 'bg-primary-subtle');
            if(selReg) selReg.classList.remove('d-none');
        } 
        else if(selectedVal === 'sucursal') {
            if(optSucursal) optSucursal.classList.add('border-primary', 'bg-primary-subtle');
            if(selSuc) selSuc.classList.remove('d-none');
        }
    }
    
    // Init state properly waiting for DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Attach Event Listeners explicitly (More robust than inline onchange)
        const checkFin = document.getElementById('sin_fin');
        if(checkFin) {
            checkFin.addEventListener('change', appToggleFechaFin);
        }

        const radiosAlcance = document.getElementsByName('alcance');
        for(let i=0; i < radiosAlcance.length; i++) {
            radiosAlcance[i].addEventListener('change', appToggleAlcance);
        }

        // Initial State
        appToggleFechaFin();
        appToggleAlcance();
    });
</script>
