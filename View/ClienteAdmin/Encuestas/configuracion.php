 <?php
/**
 * Archivo: View/ClienteAdmin/Encuestas/configuracion.php
 * Propósito: Vista dedicada para la configuración general y asignación de encuestas.
 * Autor: Asistente Experto PHP
 */

include 'View/layouts/header_cliente.php';
$Csrf = SecurityController::obtenerCsrfToken();

// Determinar selección actual de asignaciones
$TipoAsignacion = 'CLIENTE'; // Default
$IdsSeleccionados = array();

if (!empty($Asignaciones)) {
    // Tomamos la primera para ver el nivel (asumiendo consistencia)
    $First = $Asignaciones[0];
    $TipoAsignacion = $First['nivel'];
    
    foreach ($Asignaciones as $A) {
        if ($A['valor_id'] > 0) {
            $IdsSeleccionados[] = $A['valor_id'];
        }
    }
}
?>

<div class="page-wrapper anime-fade-in">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <div class="mb-1">
                <a href="index.php?System=encuestas" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Encuestas
                </a>
            </div>
            <h1 class="page-title mb-1">Configuración de Encuesta</h1>
            <p class="page-subtitle mb-0">Parametriza los detalles y el alcance de tu encuesta.</p>
        </div>
        <div>
            <button type="submit" form="formConfig" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" style="background:var(--accent);border:none;">
                <i class="bi bi-save me-2"></i>Guardar Cambios
            </button>
        </div>
    </div>

    <form id="formConfig" action="index.php?System=encuestas&a=actualizar" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $Csrf ?>">
        <input type="hidden" name="id" value="<?= $Encuesta['id'] ?>">

        <ul class="nav nav-tabs mb-4 px-3 border-bottom-0" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small rounded-top-3" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" style="border-width:0 0 2px 0;">
                    <i class="bi bi-sliders me-2"></i>Configuración
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small rounded-top-3" id="qrs-tab" data-bs-toggle="tab" data-bs-target="#qrs" type="button" role="tab" style="border-width:0 0 2px 0;">
                    <i class="bi bi-qr-code me-2"></i>Códigos QR
                </button>
            </li>
        </ul>

        <div class="tab-content" id="configTabsContent">
            
            <!-- Tab General y Asignación -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="row g-4">
                    <!-- Columna Izquierda: Datos Generales -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                                <h5 class="fw-bold mb-0 text-dark">Detalles Generales</h5>
                            </div>
                            <div class="card-body p-4">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Título de la Encuesta <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-light border-0 py-2" name="titulo" value="<?= htmlspecialchars($Encuesta['titulo']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Descripción / Introducción</label>
                                    <textarea class="form-control bg-light border-0" name="descripcion" rows="3"><?= htmlspecialchars($Encuesta['descripcion']) ?></textarea>
                                    <div class="form-text extra-small">Texto que verá el usuario antes de iniciar.</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Fecha Inicio</label>
                                        <input type="date" class="form-control bg-light border-0 py-2" name="fecha_inicio" value="<?= $Encuesta['fecha_inicio'] ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Fecha Fin</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control bg-light border-0 py-2" name="fecha_fin" id="fechaFin" 
                                                   value="<?= $Encuesta['fecha_fin'] ?>" 
                                                   <?= ($Encuesta['fecha_fin'] === null) ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="sinLimite" name="sin_limite" value="1" <?= ($Encuesta['fecha_fin'] === null) ? 'checked' : '' ?>>
                                            <label class="form-check-label extra-small text-muted" for="sinLimite">
                                                Sin fecha límite
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Imagen de Encabezado</label>
                                    <input type="file" class="form-control bg-light border-0" name="imagen_header" accept="image/*">
                                    <?php if (!empty($Encuesta['imagen_header'])): ?>
                                        <div class="mt-2 text-center p-2 bg-light rounded border border-dashed">
                                            <img src="<?= $Encuesta['imagen_header'] ?>" alt="Header Actual" class="img-fluid rounded" style="max-height: 120px;">
                                            <div class="extra-small text-muted mt-1">Imagen actual</div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-3">
                                     <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted d-block">Privacidad</label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="anonima" id="tipoReg" value="0" <?= ($Encuesta['anonima'] == 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label small text-muted" for="tipoReg">Registro Req.</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="anonima" id="tipoAnon" value="1" <?= ($Encuesta['anonima'] == 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label small text-muted" for="tipoAnon">Anónima</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Tiempo Est. (Min)</label>
                                        <input type="number" class="form-control bg-light border-0 py-2" name="tiempo_estimado" value="<?= $Encuesta['tiempo_estimado'] ?>" min="1">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Asignación -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                                <h5 class="fw-bold mb-0 text-dark">Alcance y Asignación</h5>
                            </div>
                            <div class="card-body p-4">
                                
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted mb-3">¿Dónde estará disponible esta encuesta?</label>
                                    
                                    <div class="vstack gap-3">
                                        <!-- Opción Global -->
                                        <div class="form-check custom-option-card p-3 border rounded-3 <?= ($TipoAsignacion == 'CLIENTE') ? 'selected-option' : '' ?>">
                                            <input class="form-check-input mt-1" type="radio" name="tipo_asignacion" id="asignGlobal" value="CLIENTE" <?= ($TipoAsignacion == 'CLIENTE') ? 'checked' : '' ?> onchange="cambiarAsignacion()">
                                            <label class="form-check-label d-block ms-2" for="asignGlobal" style="cursor:pointer">
                                                <span class="fw-bold d-block text-dark">Toda la Empresa</span>
                                                <span class="extra-small text-muted">Disponible en todas las sucursales y regiones.</span>
                                            </label>
                                        </div>

                                        <!-- Opción Regiones -->
                                        <div class="form-check custom-option-card p-3 border rounded-3 <?= ($TipoAsignacion == 'REGION') ? 'selected-option' : '' ?>">
                                            <input class="form-check-input mt-1" type="radio" name="tipo_asignacion" id="asignRegion" value="REGION" <?= ($TipoAsignacion == 'REGION') ? 'checked' : '' ?> onchange="cambiarAsignacion()">
                                            <label class="form-check-label d-block ms-2" for="asignRegion" style="cursor:pointer">
                                                <span class="fw-bold d-block text-dark">Por Regiones</span>
                                                <span class="extra-small text-muted">Selecciona regiones específicas.</span>
                                            </label>
                                            
                                            <div id="panelRegiones" class="mt-3 ps-2" style="display:none;">
                                                <input type="text" class="form-control form-control-sm bg-light border-0 mb-2" 
                                                       placeholder="Buscar regiones..." onkeyup="filtrarLista(this, 'listaRegiones')">
                                                
                                                <div id="listaRegiones" class="border rounded bg-white p-2" style="max-height: 200px; overflow-y: auto;">
                                                    <div class="d-flex justify-content-end mb-1">
                                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 extra-small" onclick="seleccionarTodo('listaRegiones', true)">Todos</button>
                                                        <span class="text-muted mx-1">|</span>
                                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 extra-small" onclick="seleccionarTodo('listaRegiones', false)">Ninguno</button>
                                                    </div>
                                                    <?php foreach ($Regiones as $R): ?>
                                                        <div class="form-check filter-item">
                                                            <input class="form-check-input" type="checkbox" name="regiones_ids[]" 
                                                                   value="<?= $R['id'] ?>" id="reg_<?= $R['id'] ?>"
                                                                   <?= (in_array($R['id'], $IdsSeleccionados) && $TipoAsignacion == 'REGION') ? 'checked' : '' ?>>
                                                            <label class="form-check-label small text-muted" for="reg_<?= $R['id'] ?>">
                                                                <?= htmlspecialchars($R['nombre']) ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opción Sucursales -->
                                        <div class="form-check custom-option-card p-3 border rounded-3 <?= ($TipoAsignacion == 'SUCURSAL') ? 'selected-option' : '' ?>">
                                            <input class="form-check-input mt-1" type="radio" name="tipo_asignacion" id="asignSucursal" value="SUCURSAL" <?= ($TipoAsignacion == 'SUCURSAL') ? 'checked' : '' ?> onchange="cambiarAsignacion()">
                                            <label class="form-check-label d-block ms-2" for="asignSucursal" style="cursor:pointer">
                                                <span class="fw-bold d-block text-dark">Por Sucursales</span>
                                                <span class="extra-small text-muted">Selecciona sucursales específicas.</span>
                                            </label>

                                            <div id="panelSucursales" class="mt-3 ps-2" style="display:none;">
                                                <input type="text" class="form-control form-control-sm bg-light border-0 mb-2" 
                                                       placeholder="Buscar sucursales..." onkeyup="filtrarLista(this, 'listaSucursales')">
                                                
                                                <div id="listaSucursales" class="border rounded bg-white p-2" style="max-height: 250px; overflow-y: auto;">
                                                    <div class="d-flex justify-content-end mb-1">
                                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 extra-small" onclick="seleccionarTodo('listaSucursales', true)">Todos</button>
                                                        <span class="text-muted mx-1">|</span>
                                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 extra-small" onclick="seleccionarTodo('listaSucursales', false)">Ninguno</button>
                                                    </div>
                                                    <?php foreach ($Sucursales as $S): ?>
                                                        <div class="form-check filter-item border-bottom py-1">
                                                            <input class="form-check-input" type="checkbox" name="sucursales_ids[]" 
                                                                   value="<?= $S['id'] ?>" id="suc_<?= $S['id'] ?>"
                                                                   <?= (in_array($S['id'], $IdsSeleccionados) && $TipoAsignacion == 'SUCURSAL') ? 'checked' : '' ?>>
                                                            <label class="form-check-label small text-muted w-100" for="suc_<?= $S['id'] ?>">
                                                                <span class="d-block text-dark fw-bold"><?= htmlspecialchars($S['nombre']) ?></span>
                                                                <span class="extra-small d-block text-secondary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($S['region']) ?></span>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab QRs -->
            <div class="tab-pane fade" id="qrs" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Gestor de Códigos QR</h5>
                                <p class="text-muted extra-small mb-0">Descarga y distribuye los QRs para cada punto de venta.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary rounded-pill btn-sm fw-bold" onclick="descargarSeleccionados()">
                                    <i class="bi bi-download me-2"></i>Descargar ZIP
                                </button>
                            </div>
                        </div>
                        
                        <!-- Toolbar Filter -->
                        <div class="row mt-4 mb-2 g-2">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" id="qrSearch" class="form-control bg-light border-0" placeholder="Buscar sucursal o región..." onkeyup="filtrarQRs()">
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="checkAllQrs" onclick="toggleAllQrs(this)">
                                    <label class="form-check-label small text-muted user-select-none" for="checkAllQrs">Seleccionar Todo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 bg-light-subtle">
                        
                        <?php if (empty($SucursalesHabilitadas)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-shop text-muted opacity-25" style="font-size:3rem;"></i>
                                <p class="text-muted mt-3">No hay sucursales asignadas a esta encuesta.</p>
                            </div>
                        <?php else: ?>
                            

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="qrTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40px;" class="ps-3"><input type="checkbox" class="form-check-input" id="checkAllQrs" onclick="toggleAllQrs(this)"></th>
                                            <th class="fw-bold text-muted small text-uppercase">Sucursal</th>
                                            <th class="fw-bold text-muted small text-uppercase">Región</th>
                                            <th class="fw-bold text-muted small text-uppercase text-end pe-3">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qrGrid">
                                        <?php 
                                            // Sort by name for table
                                            $SucsList = $SucursalesHabilitadas;
                                            usort($SucsList, function($a, $b) {
                                                return strcmp($a['nombre'], $b['nombre']);
                                            });
                                        ?>
                                        <?php foreach ($SucsList as $Suc): 
                                            // URL
                                            $BaseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
                                            $Url = $BaseUrl . "/index.php?System=encuestas&a=responder&id=" . $Encuesta['id'] . "&sucursal_id=" . $Suc['id'];
                                            $RegionName = $Suc['region'] ? $Suc['region'] : 'Sin Región';
                                        ?>
                                            <tr class="qr-item" data-name="<?= strtolower($Suc['nombre']) ?>" data-region="<?= strtolower($RegionName) ?>">
                                                <td class="ps-3">
                                                    <input type="checkbox" class="form-check-input qr-checkbox" 
                                                           value="<?= $Suc['id'] ?>" 
                                                           data-name="<?= htmlspecialchars($Suc['nombre']) ?>"
                                                           data-region="<?= htmlspecialchars($RegionName) ?>">
                                                    <!-- Hidden QR Gen Container -->
                                                    <div class="qr-gen d-none" data-url="<?= $Url ?>"></div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($Suc['nombre']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($RegionName) ?></span>
                                                </td>
                                                <td class="text-end pe-3">
                                                <td class="text-end pe-3">
                                                    <a href="<?= $Url ?>" target="_blank" class="btn btn-sm btn-light border text-primary" title="Abrir Encuesta">
                                                        <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-sm-inline ms-1">Link</span>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-light border text-dark ms-1" onclick="descargarIndividual(this)" title="Descargar Imagen QR">
                                                        <i class="bi bi-download"></i> <span class="d-none d-sm-inline ms-1">QR</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- No results Msg -->
                            <div id="noResults" class="text-center py-5 d-none">
                                <span class="text-muted">No se encontraron sucursales con ese filtro.</span>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div> <!-- End Tab Content -->

    </form>
</div>

<!-- Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<?php include 'View/layouts/footer.php'; ?>


<style>
    .selected-option {
        border-color: var(--accent) !important;
        background-color: rgba(var(--accent-rgb), 0.03);
    }
    .custom-option-card {
        transition: all 0.2s;
    }
    .custom-option-card:hover {
        background-color: #f8f9fa;
    }
    /* Estilo para Scrollbar */
    #listaRegiones::-webkit-scrollbar, #listaSucursales::-webkit-scrollbar {
        width: 6px;
    }
    #listaRegiones::-webkit-scrollbar-thumb, #listaSucursales::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 4px;
    }

    /* Tabs Style */
    .nav-tabs .nav-link {
        color: #64748b;
        background: transparent;
        border-color: transparent;
        padding-bottom: 0.8rem;
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom-color: var(--primary-color) !important;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: var(--primary-color);
    }
    
    .qr-card-hover {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .qr-card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
    }
    .qr-checkbox {
        width: 1.2em;
        height: 1.2em;
        cursor: pointer;
    }
    
    /* Responsive QR */
    .qr-gen img {
        max-width: 100% !important;
        height: auto !important;
    }
</style>

<script>
    // --- QR Generation ---
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('.qr-gen').forEach(el => {
                const url = el.getAttribute('data-url');
                if(url) {
                    el.innerHTML = '';
                    new QRCode(el, {
                        text: url,
                        width: 1024,
                        height: 1024,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                }
            });
        }, 300);
    });

    // --- Search Logic ---
    function filtrartQRs() { // Typo fix intention
        filtrarQRs();
    }
    
    function filtrarQRs() {
        const term = document.getElementById('qrSearch').value.toLowerCase();
        let visibleCount = 0;
        
        // Filter items
        document.querySelectorAll('.qr-item').forEach(item => {
            const name = item.getAttribute('data-name');
            const region = item.getAttribute('data-region');
            
            if(name.includes(term) || region.includes(term)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Toggle Region headers if empty
        document.querySelectorAll('.region-block').forEach(block => {
            const visibleChild = block.querySelectorAll('.qr-item[style="display: block;"]').length;
            if(visibleChild === 0 && term !== '') {
                block.style.display = 'none';
            } else {
                block.style.display = 'block';
            }
        });

        const noRes = document.getElementById('noResults');
        if(visibleCount === 0) {
            noRes.classList.remove('d-none');
        } else {
            noRes.classList.add('d-none');
        }
    }

    // --- Checkbox Logic ---
    function toggleAllQrs(source) {
        const checkboxes = document.querySelectorAll('.qr-checkbox');
        checkboxes.forEach(cb => {
            // Only toggle visible ones if searching? Standard UX is all.
            // Let's do visible only if searching to allow "Search -> Select All results" pattern
            const item = cb.closest('.qr-item');
            if(item.style.display !== 'none') {
                cb.checked = source.checked;
            }
        });
    }

    // --- ZIP Download Logic ---

    // Download Single Logic
    function descargarIndividual(btn) {
        const tr = btn.closest('tr');
        const img = tr.querySelector('.qr-gen img');
        
        if(!img) {
            alert("El QR aún se está generando, intenta en un momento.");
            return;
        }

        const name = tr.getAttribute('data-name').trim().replace(/[^a-z0-9]/gi, '_');
        
        // Canvas Processing
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const qrSize = img.naturalWidth || 512;
        const padding = 100;
        const totalSize = qrSize + (padding * 2);

        canvas.width = totalSize;
        canvas.height = totalSize;

        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, padding, padding, qrSize, qrSize);

        canvas.toBlob(function(blob) {
             saveAs(blob, "QR_" + name + ".png");
        });
    }

    function descargarSeleccionados() {
        const checkboxes = document.querySelectorAll('.qr-checkbox:checked');
        if(checkboxes.length === 0) {
            alert("Por favor selecciona al menos un QR para descargar.");
            return;
        }

        const btn = document.querySelector('button[onclick="descargarSeleccionados()"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando ZIP...';

        const zip = new JSZip();
        // Promesas para procesar cada imagen
        const promises = [];

        checkboxes.forEach(cb => {
            const p = new Promise((resolve) => {
                const tr = cb.closest('tr');
                const name = cb.getAttribute('data-name').trim().replace(/[^a-z0-9]/gi, '_');
                const region = cb.getAttribute('data-region').trim().replace(/[^a-z0-9]/gi, '_');
                
                // Get QR Data URL
                const img = tr.querySelector('.qr-gen img');
                
                if(img) {
                     // Create a canvas to add padding
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Config sizes
                    const qrSize = img.naturalWidth || 512; // Should be 1024 as requested or base
                    const padding = 100; // White border size
                    const totalSize = qrSize + (padding * 2);

                    canvas.width = totalSize;
                    canvas.height = totalSize;

                    // Draw White Background
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // Draw Image Centered
                    // We need to ensure img is loaded, but it's in DOM, so likely loaded.
                    // safely draw
                    ctx.drawImage(img, padding, padding, qrSize, qrSize);

                    // Add text label? (Opcional, user didnt ask but helpful. Staying strictly to request: just white space)
                    
                    canvas.toBlob(function(blob) {
                         zip.folder(region).file(name + ".png", blob);
                         resolve();
                    });
                } else {
                    resolve();
                }
            });
            promises.push(p);
        });

        Promise.all(promises).then(() => {
            zip.generateAsync({type:"blob"})
            .then(function(content) {
                saveAs(content, "QRs_Sucursales.zip");
                btn.disabled = false;
                btn.innerHTML = originalText;
            })
            .catch(err => {
                console.error(err);
                alert("Error al generar el ZIP");
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }


    /* -- Existing JS Config -- */
    function filtrarLista(input, listId) {
        const filter = input.value.toLowerCase();
        const container = document.getElementById(listId);
        const items = container.querySelectorAll('.filter-item');
        
        items.forEach(item => {
            const label = item.querySelector('label').innerText.toLowerCase();
            if (label.includes(filter)) {
                item.style.display = 'block'; 
            } else {
                item.style.display = 'none';
            }
        });
    }

    function seleccionarTodo(listId, estado) {
        const container = document.getElementById(listId);
        const items = container.querySelectorAll('.filter-item');
        items.forEach(item => {
            if(item.style.display !== 'none') {
                const chk = item.querySelector('input[type="checkbox"]');
                if(chk) chk.checked = estado;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Lógica Sin Límite
        const checkSinLimite = document.getElementById('sinLimite');
        const inputFechaFin = document.getElementById('fechaFin');
        
        if(checkSinLimite && inputFechaFin) {
            checkSinLimite.addEventListener('change', function() {
                if(this.checked) {
                    inputFechaFin.disabled = true;
                    inputFechaFin.value = '';
                } else {
                    inputFechaFin.disabled = false;
                }
            });
        }
    });

    // Lógica Asignación (Global var)
    window.cambiarAsignacion = function() {
        const val = document.querySelector('input[name="tipo_asignacion"]:checked').value;
        const panelR = document.getElementById('panelRegiones');
        const panelS = document.getElementById('panelSucursales');
        
        document.querySelectorAll('.custom-option-card').forEach(el => el.classList.remove('selected-option'));
        document.querySelector(`input[value="${val}"]`).closest('.custom-option-card').classList.add('selected-option');

        if (val === 'REGION') {
            panelR.classList.remove('anime-fade-in'); 
            void panelR.offsetWidth; 
            panelR.style.display = 'block';
            panelR.classList.add('anime-fade-in');
            panelS.style.display = 'none';
        } else if (val === 'SUCURSAL') {
            panelS.classList.remove('anime-fade-in');
            void panelS.offsetWidth;
            panelS.style.display = 'block';
            panelS.classList.add('anime-fade-in');
            panelR.style.display = 'none';
        } else {
            panelR.style.display = 'none';
            panelS.style.display = 'none';
        }
    };
    
    // Init state called inline or here
    window.onload = function() { // Ensure DOM fully loaded for radio
       if(document.querySelector('input[name="tipo_asignacion"]:checked')) {
           window.cambiarAsignacion();
       }
    };

    // AJAX Submit
    const form = document.getElementById('formConfig');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                const msg = document.createElement('div');
                msg.className = 'alert alert-success position-fixed top-0 end-0 m-4 shadow-lg anime-fade-in';
                msg.style.zIndex = 9999;
                msg.innerHTML = '<i class="bi bi-check-circle me-2"></i> Cambios guardados correctamente.';
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            alert('Error de conexión.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

</script>
