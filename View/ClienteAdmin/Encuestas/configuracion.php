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
                                        <!-- Buscador -->
                                        <input type="text" class="form-control form-control-sm bg-light border-0 mb-2" 
                                               placeholder="Buscar regiones..." onkeyup="filtrarLista(this, 'listaRegiones')">
                                        
                                        <!-- Lista Checkboxes Scrollable -->
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
                                        <!-- Buscador -->
                                        <input type="text" class="form-control form-control-sm bg-light border-0 mb-2" 
                                               placeholder="Buscar sucursales..." onkeyup="filtrarLista(this, 'listaSucursales')">
                                        
                                        <!-- Lista Checkboxes Scrollable -->
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
    </form>
</div>

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
</style>

<script>
    // JS Global funciones
    function filtrarLista(input, listId) {
        const filter = input.value.toLowerCase();
        const container = document.getElementById(listId);
        const items = container.querySelectorAll('.filter-item');
        
        items.forEach(item => {
            const label = item.querySelector('label').innerText.toLowerCase();
            if (label.includes(filter)) {
                item.style.display = 'block'; // o 'flex' o lo que fuera
            } else {
                item.style.display = 'none';
            }
        });
    }

    function seleccionarTodo(listId, estado) {
        const container = document.getElementById(listId);
        // Solo visibles si hay filtro? O todos? Usualmente todos los visibles.
        // Si no hay filtro, todos.
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

    // Lógica Asignación
    window.cambiarAsignacion = function() {
        const val = document.querySelector('input[name="tipo_asignacion"]:checked').value;
        const panelR = document.getElementById('panelRegiones');
        const panelS = document.getElementById('panelSucursales');
        
        // Reset styles
        document.querySelectorAll('.custom-option-card').forEach(el => el.classList.remove('selected-option'));
        document.querySelector(`input[value="${val}"]`).closest('.custom-option-card').classList.add('selected-option');

        if (val === 'REGION') {
            panelR.classList.remove('anime-fade-in'); // Reset animation hook
            void panelR.offsetWidth; // Trigger reflow
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
    
    // Init state
    cambiarAsignacion();


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

});
</script>
