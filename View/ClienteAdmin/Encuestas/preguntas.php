<?php
/**
 * Archivo: View/ClienteAdmin/Encuestas/preguntas.php
 * Propósito: Vista para la gestión (CRUD) de preguntas y Configuración Visual (Live Preview).
 * Autor: Refactorización Expert PHP
 * Fecha: 2026-01-22
 */

include 'View/layouts/header_cliente.php';
$Csrf = SecurityController::obtenerCsrfToken();

// Cargar Configuración Visual Actual
$ConfigVisual = ['tema' => 'light', 'color' => '#4f46e5'];
if (!empty($Encuesta['configuracion_json'])) {
    $Decoded = json_decode($Encuesta['configuracion_json'], true);
    if ($Decoded) {
        $ConfigVisual = array_merge($ConfigVisual, $Decoded);
    }
}
?>

<!-- Custom Styles -->
<style>
    .page-wrapper {
        height: calc(100vh - 80px); /* Ajuste segun header */
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .split-container {
        display: flex;
        height: 100%;
        overflow: hidden;
    }

    .editor-pane {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
    }

    .preview-pane {
        width: 450px;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        border-left: 1px solid #e2e8f0;
        background-color: #f1f5f9;
        /* background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 20px 20px; */
    }

    /* Smartphone Simulator */
    .smartphone-mockup {
        width: 375px;
        height: 750px;
        background: #1e293b;
        border-radius: 40px;
        border: 12px solid #1e293b;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .smartphone-notch {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 150px;
        height: 30px;
        background: #1e293b;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        z-index: 20;
    }

    .smartphone-screen {
        width: 100%;
        height: 100%;
        background: white;
        overflow: hidden;
        border-radius: 28px;
    }

    iframe#previewFrame {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .question-card {
        cursor: move;
        transition: all 0.2s;
        border-left: 4px solid var(--accent, #0d6efd);
        background: #fff;
    }
    .question-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .ghost { opacity: 0.5; background: #e2e6ea; border: 2px dashed #ccc; }

    /* Theme Option Radio */
    .theme-option {
        cursor: pointer;
        opacity: 0.7;
        transition: 0.2s;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 4px;
    }
    .theme-option.active {
        opacity: 1;
        border-color: var(--primary-color); 
        transform: scale(1.05);
    }
    .color-swatch {
        width: 40px; height: 40px; border-radius: 50%; display: inline-block;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Floating Help Button Animation */
    @keyframes pulse-attention {
        0% { transform: scale(1); background-color: #0d6efd; color: white; border: 1px solid #0d6efd; }
        50% { transform: scale(1.2); background-color: white; color: #0d6efd; box-shadow: 0 0 20px rgba(13, 110, 253, 0.5); border: 1px solid #0d6efd; }
        100% { transform: scale(1); background-color: #0d6efd; color: white; border: 1px solid #0d6efd; }
    }
    .btn-help-floating {
        position: fixed; 
        bottom: 30px; 
        right: 30px; 
        width: 60px; 
        height: 60px; 
        z-index: 1050;
        transition: transform 0.2s;
    }
    .btn-help-floating:hover {
        transform: scale(1.1);
    }
    .anime-pulse-init {
        animation: pulse-attention 2s 3 ease-out;
    }
</style>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<div class="page-wrapper anime-fade-in">
    
    <!-- Top Bar: Configuración Visual & Acciones -->
    <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm z-index-10">
        <div class="d-flex align-items-center gap-3">
             <a href="index.php?System=encuestas" class="btn btn-light rounded-circle border shadow-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($Encuesta['titulo']) ?></h5>
                <small class="text-muted">Diseña y organiza las preguntas</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <!-- Theme Selector -->
            <div class="d-flex align-items-center gap-3 bg-light px-3 py-2 rounded-pill border">
                <!-- Color Picker -->
                <div class="d-flex align-items-center border-end pe-3 me-2">
                    <label for="brandColor" class="small fw-bold text-muted me-2 cursor-pointer">Color:</label>
                    <input type="color" id="brandColor" class="form-control form-control-color border-0 p-0 shadow-none" 
                           value="<?= $ConfigVisual['color'] ?? '#4f46e5' ?>" 
                           title="Color de Marca"
                           style="width: 32px; height: 32px; cursor: pointer;"
                           onchange="updateBrandColor(this.value)">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold text-uppercase text-muted me-2">Tema:</span>
                    
                    <div class="theme-option <?= $ConfigVisual['tema'] == 'light' ? 'active' : '' ?>" onclick="setTheme('light')" title="Claro">
                        <div class="color-swatch" style="background: #f8fafc; border: 1px solid #cbd5e1;"></div>
                    </div>
                    <div class="theme-option <?= $ConfigVisual['tema'] == 'navy' ? 'active' : '' ?>" onclick="setTheme('navy')" title="Navy">
                        <div class="color-swatch" style="background: #0f172a;"></div>
                    </div>
                    <div class="theme-option <?= $ConfigVisual['tema'] == 'dark' ? 'active' : '' ?>" onclick="setTheme('dark')" title="Oscuro">
                        <div class="color-swatch" style="background: #18181b;"></div>
                    </div>
                </div>
            </div>
            
            <button class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="guardarDiseno()">
                <i class="bi bi-save me-2"></i>Guardar Diseño
            </button>
        </div>
    </div>

    <!-- Main Split Layout -->
    <div class="split-container">
        
        <!-- LEFT: Editor Pane -->
        <div class="editor-pane">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold text-muted text-uppercase ls-1 mb-0">Estructura de la Encuesta</h6>
                <button class="btn btn-primary rounded-pill px-3 fw-bold small shadow-sm" onclick="abrirModalPregunta()">
                    <i class="bi bi-plus-lg me-1"></i> Añadir Pregunta
                </button>
            </div>

            <div id="preguntas-lista" class="list-group list-group-flush gap-3 pb-5">
                <?php if (empty($Preguntas)): ?>
                    <div class="text-center py-5">
                        <div class="text-muted opacity-25 mb-3"><i class="bi bi-grid-1x2 fa-3x" style="font-size:3rem"></i></div>
                        <h6 class="fw-bold text-muted">Tu encuesta está vacía</h6>
                        <p class="small text-muted">Añade preguntas para ver la vista previa.</p>
                    </div>
                <?php else: 
                    // Init Maps
                    $MapNombres = []; foreach ($Preguntas as $P) $MapNombres[$P['id']] = $P['texto_pregunta'];
                    
                    foreach ($Preguntas as $P): 
                        // Logic Flags
                        $Logica = json_decode($P['logica_condicional'] ?? '{}', true);
                        $TieneLogica = !empty($Logica['rules']);
                        
                        // Options
                        $Opciones = [];
                        if(in_array($P['tipo_pregunta'], ['unica','multiple','botonera'])) {
                             $Opciones = json_decode($P['opciones_json'] ?? '[]', true);
                             if($P['tipo_pregunta'] === 'botonera' && empty($Opciones)) $Opciones = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
                        }
                        $Config = json_decode($P['configuracion_json'] ?? '{}', true);
                ?>
                    <div class="question-card p-3 rounded-4 shadow-sm border border-light-subtle position-relative" data-id="<?= $P['id'] ?>">
                        <!-- Drag Handle -->
                        <div class="position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center rounded-start-4" style="width:24px; background:rgba(0,0,0,0.02); cursor:grab;">
                             <i class="bi bi-three-dots-vertical text-muted opacity-50"></i>
                        </div>
                        
                        <div class="ps-3 d-flex gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-white text-dark border shadow-sm text-uppercase extra-small fw-bold">
                                        <?= ucfirst($P['tipo_pregunta']) ?>
                                    </span>
                                    <?php if ($P['requerido']): ?>
                                        <span class="text-danger extra-small fw-bold"><i class="bi bi-asterisk"></i></span>
                                    <?php endif; ?>
                                </div>
                                
                                <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($P['texto_pregunta']) ?></h6>
                                
                                <div class="small text-muted">
                                    <?php if(!empty($Opciones)): ?>
                                        <i class="bi bi-list-ul me-1"></i> <?= count($Opciones) ?> Opciones
                                    <?php endif; ?>
                                    <?php if($TieneLogica): ?>
                                        <span class="ms-2 text-primary bg-primary-subtle px-2 py-1 rounded fw-bold">
                                            <i class="bi bi-diagram-2"></i> Condicional
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-1">
                                <button class="btn btn-sm btn-light border text-primary" onclick='editarPregunta(<?= json_encode($P) ?>)'><i class="bi bi-pencil-fill"></i></button>
                                <button class="btn btn-sm btn-light border text-danger" onclick="eliminarPregunta(<?= $P['id'] ?>)"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- RIGHT: Live Simulator -->
        <div class="preview-pane">
            <h6 class="fw-bold text-muted text-uppercase ls-1 mb-3">Vista Previa (En vivo)</h6>
            <div class="smartphone-mockup">
                <div class="smartphone-notch"></div>
                <div class="smartphone-screen">
                    <iframe id="previewFrame" src="index.php?System=encuestas&a=responder&id=<?= $Encuesta['id'] ?>&preview=1&theme=<?= $ConfigVisual['tema'] ?>"></iframe>
                </div>
            </div>
            <div class="mt-3 text-muted extra-small">
                 <i class="bi bi-phone"></i> Perspectiva Móvil
            </div>
        </div>

    </div>
</div>

<!-- Modal Pregunta (Mismo contenido, solo ID wrappers) -->

<!-- El modal original es muy largo, para mantener limpio este archivo, podríamos extraerlo. 
     Para este paso, vamos a incluir el HTML del modal directamente aquí abajo si no extraemos. -->

<!-- Modal Pregunta Inline (Copia del original) -->
<div class="modal fade" id="modalPregunta" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitulo">Nueva Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPregunta" onsubmit="guardarPregunta(event)" class="h-100">
                <div class="modal-body pt-4">
                    <input type="hidden" name="encuesta_id" value="<?= $Encuesta['id'] ?>">
                    <input type="hidden" name="pregunta_id" id="pregunta_id">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Texto</label>
                            <input type="text" class="form-control bg-light border-0 py-2" name="texto_pregunta" id="texto_pregunta" required placeholder="Ej: ¿Cómo califica nuestro servicio?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Tipo</label>
                            <select class="form-select bg-light border-0" name="tipo_pregunta" id="tipo_pregunta" onchange="cambiarTipo()">
                                <option value="texto">Texto Libre</option>
                                <option value="unica">Selección Única</option>
                                <option value="multiple">Selección Múltiple</option>
                                <option value="nps">NPS (0-10)</option>
                                <option value="botonera">Botonera</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="requerido" id="requerido">
                                <label class="form-check-label small text-muted">Obligatoria</label>
                            </div>
                        </div>
                    </div>

                    <!-- Config Blocks -->
                    <div id="config-options" style="display:none;" class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <label class="small fw-bold text-muted mb-2">Opciones (Una por línea)</label>
                            <textarea class="form-control border-0 bg-white" name="opciones" id="opciones" rows="4"></textarea>
                        </div>
                    </div>

                    <div id="config-botonera" style="display:none;" class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="small fw-bold text-muted mb-0">Botones Fijos</h6>
                                <span class="badge bg-warning text-dark border">Estándar</span>
                            </div>
                            <div id="botonera-list" class="vstack gap-2"></div>
                             <input type="hidden" name="opciones_json_raw" id="opciones_json_raw">
                        </div>
                    </div>

                    <!-- Logic -->
                    <div class="card border border-light-subtle shadow-sm mt-3">
                         <div class="card-header bg-white border-0 py-2" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#logicCollapse">
                            <h6 class="mb-0 text-primary extra-small fw-bold text-uppercase"><i class="bi bi-diagram-2 me-2"></i>Lógica Condicional</h6>
                         </div>
                         <div id="logicCollapse" class="collapse">
                            <div class="card-body pt-0">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="tiene_logica" onchange="toggleLogic()">
                                    <label class="form-check-label small text-muted">Habilitar Condición</label>
                                </div>
                                <div id="logic-builder" style="display:none;" class="p-3 bg-light rounded">
                                     <select class="form-select form-select-sm mb-2" id="logic_parent"><option>-- Pregunta Padre --</option></select>
                                     <div class="d-flex gap-2">
                                         <select class="form-select form-select-sm" id="logic_operator">
                                             <option value="==">Igual a</option>
                                             <option value="!=">Diferente de</option>
                                         </select>
                                         <input type="text" class="form-control form-control-sm" id="logic_value" placeholder="Valor">
                                     </div>
                                </div>
                                <input type="hidden" name="logica_condicional" id="logica_condicional">
                            </div>
                         </div>
                    </div>
                    <input type="hidden" name="configuracion_json" id="configuracion_json">

                </div>
                <div class="modal-footer border-0">
                     <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                     <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include 'View/layouts/footer.php'; ?>

<script>
    // Global Config
    const EncuestaID = <?= $Encuesta['id'] ?>;
    const AllPreguntas = <?= json_encode($Preguntas) ?>;
    const FIXED_BOTONERA = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
    let BotoneraItems = [...FIXED_BOTONERA];
    
    // Initial State
    let currentTheme = '<?= $ConfigVisual['tema'] ?>';
    let currentColor = '<?= $ConfigVisual['color'] ?? '#4f46e5' ?>';

    var modalPregunta = null;

    document.addEventListener('DOMContentLoaded', function() {
        if(typeof bootstrap !== 'undefined') modalPregunta = new bootstrap.Modal(document.getElementById('modalPregunta'));
        
        var el = document.getElementById('preguntas-lista');
        if(el) {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'ghost',
                handle: '.question-card',
                onEnd: function (evt) { guardarOrden(); }
            });
        }
        
        // Init Preview
        updateIframeSrc();
    });

    // --- VISUAL CONFIG ---
    function updateIframeSrc() {
        const iframe = document.getElementById('previewFrame');
        if(iframe) {
            const url = new URL(iframe.src); // Or construct new if src is initial
            // Better construct clean to avoid params accumulation or just set params
            // index.php?System=encuestas&a=responder&id=...
            const base = 'index.php';
            const params = new URLSearchParams({
                System: 'encuestas',
                a: 'responder',
                id: EncuestaID,
                preview: 1,
                theme: currentTheme,
                color: currentColor,
                t: Date.now()
            });
            iframe.src = base + '?' + params.toString();
        }
    }

    function setTheme(theme) {
        currentTheme = theme;
        document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('active'));
        const activeEl = document.querySelector(`.theme-option[onclick="setTheme('${theme}')"]`);
        if(activeEl) activeEl.classList.add('active');
        
        updateIframeSrc();
    }
    
    function updateBrandColor(color) {
        currentColor = color;
        updateIframeSrc();
    }

    function guardarDiseno() {
        const btn = document.querySelector('button[onclick="guardarDiseno()"]');
        const originalContent = btn.innerHTML;
        
        // Estado Loading
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        const config = {
            tema: currentTheme,
            color: currentColor
        };

        const fd = new FormData();
        fd.append('id', EncuestaID);
        // Enviamos datos minimos para actualizarJson o usamos actualizarEncuesta
        // El controller actual usa actualizarEncuesta que requiere todos los campos.
        // HACK: Enviamos campos dummy o los actuales si los tenemos. 
        // Como no tenemos los valores de titulo/fechas aqui, el controller podria fallar o borrar datos si no manejamos esto.
        // PERO: En la iteracion 856 vi que creaste 'guardar_diseno_ajax' que RELLENA los datos faltantes con los de la DB.
        // ASI QUE: Solo necesitamos enviar ID y config.
        
        fd.append('configuracion', JSON.stringify(config));
        fd.append('csrf_token', '<?= $Csrf ?>'); 

        fetch('index.php?System=encuestas&a=guardar_diseno_ajax', {
            method: 'POST', body: fd
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                // Estado Éxito
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success', 'bg-success', 'text-white'); 
                btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>¡Guardado!';
                
                // Revertir
                setTimeout(() => {
                    btn.disabled = false;
                    btn.classList.remove('btn-outline-success', 'bg-success', 'text-white');
                    btn.classList.add('btn-success');
                    btn.innerHTML = originalContent;
                }, 2000);
            } else {
                alert('Error al guardar: ' + d.message);
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        })
        .catch(e => {
            alert('Error de red');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }
    // --- QUESTION MODAL & LOGIC (Condensed) ---
    function abrirModalPregunta() {
        document.getElementById('formPregunta').reset();
        document.getElementById('pregunta_id').value = '';
        renderBotoneraInputs();
        toggleLogic();
        modalPregunta.show();
        popularPreguntasPadre();
    }

    function editarPregunta(p) {
        document.getElementById('formPregunta').reset();
        document.getElementById('pregunta_id').value = p.id;
        document.getElementById('texto_pregunta').value = p.texto_pregunta;
        document.getElementById('tipo_pregunta').value = p.tipo_pregunta;
        document.getElementById('requerido').checked = (p.requerido == 1);
        
        cambiarTipo();

        // Opciones
        if (p.tipo_pregunta === 'botonera') {
             renderBotoneraInputs();
        } else if (p.opciones_json) {
             const opts = JSON.parse(p.opciones_json);
             document.getElementById('opciones').value = opts.join('\\n');
        }

        // Logic
        if (p.logica_condicional) {
            const l = JSON.parse(p.logica_condicional);
            if(l.rules && l.rules[0]) {
                document.getElementById('tiene_logica').checked = true;
                toggleLogic();
                // Delay fill
                setTimeout(() => {
                    popularPreguntasPadre();
                    document.getElementById('logic_parent').value = l.rules[0].question_id;
                    document.getElementById('logic_operator').value = l.rules[0].operator;
                    document.getElementById('logic_value').value = l.rules[0].value;
                }, 100);
            }
        }

        modalPregunta.show();
    }

    function cambiarTipo() {
        const t = document.getElementById('tipo_pregunta').value;
        document.getElementById('config-options').style.display = ['unica','multiple'].includes(t) ? 'block' : 'none';
        document.getElementById('config-botonera').style.display = (t === 'botonera') ? 'block' : 'none';
        if(t === 'botonera') renderBotoneraInputs();
    }

    function renderBotoneraInputs() {
        const c = document.getElementById('botonera-list');
        c.innerHTML = '';
        FIXED_BOTONERA.forEach(b => {
             c.innerHTML += `<input type="text" class="form-control form-control-sm mb-1 bg-white" value="${b}" readonly>`;
        });
    }

    function toggleLogic() {
        const s = document.getElementById('tiene_logica').checked;
        document.getElementById('logic-builder').style.display = s ? 'block' : 'none';
        if(s) popularPreguntasPadre();
    }

    function popularPreguntasPadre() {
        const s = document.getElementById('logic_parent');
        const curr = document.getElementById('pregunta_id').value;
        s.innerHTML = '<option value="">-- Seleccionar --</option>';
        AllPreguntas.forEach(p => {
             if(p.id != curr) {
                 const op = document.createElement('option');
                 op.value = p.id; 
                 op.text = p.texto_pregunta;
                 s.add(op);
             }
        });
    }

    function guardarPregunta(e) {
        e.preventDefault();
        
        // Validación de Opciones
        const tipo = document.getElementById('tipo_pregunta').value;
        if (['unica', 'multiple'].includes(tipo)) {
            const opcionesTxt = document.getElementById('opciones').value.trim();
            if (!opcionesTxt) {
                alert('Para este tipo de pregunta (' + (tipo === 'unica' ? 'Selección Única' : 'Selección Múltiple') + '), debes registrar al menos una opción.');
                return;
            }
            // Validar que no sean solo lineas vacias
            const lineas = opcionesTxt.split('\n').filter(l => l.trim().length > 0);
            if (lineas.length === 0) {
                 alert('Debes ingresar opciones válidas (una por línea).');
                 return;
            }
        }

        const fd = new FormData(document.getElementById('formPregunta'));
        
        if(fd.get('tipo_pregunta') === 'botonera') fd.set('opciones_json_raw', JSON.stringify(FIXED_BOTONERA));
        
        // Logic Builder
        if(document.getElementById('tiene_logica').checked) {
             const rule = {
                 question_id: document.getElementById('logic_parent').value,
                 operator: document.getElementById('logic_operator').value,
                 value: document.getElementById('logic_value').value
             };
             if(rule.question_id && rule.value) fd.set('logica_condicional', JSON.stringify({rules:[rule]}));
        }

        fetch('index.php?System=encuestas&a=guardar_pregunta', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                // Refresh iframe
                document.getElementById('previewFrame').contentWindow.location.reload();
                location.reload(); 
            } else alert(d.message);
        });
    }

    function eliminarPregunta(id) {
        if(!confirm('¿Eliminar?')) return;
        const fd = new FormData(); fd.append('id', id); fd.append('encuesta_id', EncuestaID);
        fetch('index.php?System=encuestas&a=eliminar_pregunta', {method:'POST', body:fd})
        .then(r => r.json()).then(d => { if(d.success) location.reload(); });
    }

    function guardarOrden() {
        const items = [];
        document.querySelectorAll('.question-card').forEach((el,i) => items.push({id:el.getAttribute('data-id'), orden:i+1}));
        fetch('index.php?System=encuestas&a=reordenar_preguntas', {
            method:'POST', body:JSON.stringify({encuesta_id:EncuestaID, items:items})
        }).then(() => {
             document.getElementById('previewFrame').contentWindow.location.reload();
        });
    }
</script>


<!-- FLOATING HELP BUTTON -->
<!-- FLOATING HELP BUTTON -->
<button class="btn btn-primary rounded-circle shadow-lg btn-help-floating anime-pulse-init d-flex align-items-center justify-content-center" 
        onclick="abrirAyuda()"
        title="Ver Guía de Ayuda">
    <i class="bi bi-question-lg fs-2"></i>
</button>

<!-- MODAL AYUDA / GUIA MEJORADO -->
<div class="modal fade" id="modalAyuda" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2-strong rounded-4 overflow-hidden">
            <!-- Header con gradiente -->
            <div class="modal-header bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div>
                    <h4 class="modal-title fw-bold mb-1">
                        <i class="bi bi-compass-fill me-2 opacity-75"></i> Gestión Profesional de Encuestas
                    </h4>
                    <p class="mb-0 text-white-50 small">Guía rápida para diseñar encuestas efectivas y atractivas</p>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body bg-light p-4">
                <div class="container-fluid">
                    <!-- Intro -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center" role="alert">
                                <i class="bi bi-lightbulb-fill fs-3 me-3"></i>
                                <div>
                                    <strong>Nueva Interfaz "Split View":</strong>
                                    Ahora puedes editar tus preguntas a la izquierda y ver <em>en tiempo real</em> cómo quedan en un móvil a la derecha. ¡Lo que ves es lo que obtienes!
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 d-flex align-items-stretch">
                        <!-- COL 1: FLUJO DE TRABAJO -->
                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="bi bi-kanban fs-4"></i>
                                        </span>
                                        <h5 class="fw-bold mb-0 text-dark">Gestión de Contenido</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">Controla la estructura lógica de tu encuesta desde el panel izquierdo.</p>
                                    
                                    <div class="d-flex mb-3">
                                        <i class="bi bi-plus-circle-fill text-success fs-5 me-3"></i>
                                        <div>
                                            <strong class="d-block text-dark">1. Añadir Preguntas</strong>
                                            <span class="text-muted small">Usa el botón superior "Añadir Pregunta". Elige entre múltiples tipos según necesites.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex mb-3">
                                        <i class="bi bi-pencil-square text-warning fs-5 me-3"></i>
                                        <div>
                                            <strong class="d-block text-dark">2. Editar y Detallar</strong>
                                            <span class="text-muted small">Haz clic en el lápiz para modificar textos. Define opciones claras para preguntas de selección.</span>
                                        </div>
                                    </div>

                                    <div class="d-flex">
                                        <i class="bi bi-arrow-down-up text-primary fs-5 me-3"></i>
                                        <div>
                                            <strong class="d-block text-dark">3. Ordenar (Drag & Drop)</strong>
                                            <span class="text-muted small">Arrastra las tarjetas de preguntas hacia arriba o abajo para cambiar su orden instantáneamente.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- COL 2: DISEÑO VISUAL -->
                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="bg-indigo bg-opacity-10 text-indigo rounded p-2 me-3" style="color: #6610f2; background-color: #f3e8ff;">
                                            <i class="bi bi-palette-fill fs-4"></i>
                                        </span>
                                        <h5 class="fw-bold mb-0 text-dark">Personalización Visual</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">Define la identidad visual de tu encuesta para mejorar la experiencia del usuario.</p>
                                    
                                    <div class="list-group list-group-flush small">
                                        <div class="list-group-item px-0 border-0">
                                            <h6 class="fw-bold mb-1">
                                                <i class="bi bi-moon-stars me-2"></i>Selector de Temas
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                En la barra superior encontrarás opciones de tema: 
                                                <span class="badge bg-light text-dark border">Claro</span>, 
                                                <span class="badge bg-dark border">Oscuro</span> y 
                                                <span class="badge border text-white" style="background-color:#0f172a">Navy</span>.
                                            </p>
                                        </div>
                                        <div class="list-group-item px-0 border-0">
                                            <h6 class="fw-bold mb-1">
                                                <i class="bi bi-eye me-2"></i>Vista Previa en Vivo
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                El simulador de teléfono se actualiza automáticamente al cambiar de tema o editar preguntas.
                                            </p>
                                        </div>
                                        <div class="list-group-item px-0 border-0">
                                            <div class="alert alert-warning py-2 mb-0 d-flex align-items-center">
                                                <i class="bi bi-save me-2"></i>
                                                <span><strong>¡Importante!</strong> Los cambios de diseño no se aplican hasta pulsar "Guardar Diseño".</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- COL 3: TIPOS AVANZADOS -->
                         <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                            <i class="bi bi-diagram-3-fill fs-4"></i>
                                        </span>
                                        <h5 class="fw-bold mb-0 text-dark">Lógica y Tipos</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">Utiliza herramientas avanzadas para encuestas dinámicas.</p>
                                    
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">TIPOS RECOMENDADOS</h6>
                                    <div class="row g-2 mb-4">
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-light">
                                                <strong class="d-block small text-dark">Botonera</strong>
                                                <span class="text-muted" style="font-size:10px">Ideal para satisfacción (1-10)</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded bg-light">
                                                <strong class="d-block small text-dark">Selección</strong>
                                                <span class="text-muted" style="font-size:10px">Simple o Múltiple para categorizar</span>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">LÓGICA CONDICIONAL</h6>
                                    <div class="d-flex align-items-start bg-light p-2 rounded">
                                        <i class="bi bi-share text-secondary me-2 mt-1"></i>
                                        <p class="mb-0 small text-muted">
                                            Puedes hacer que una pregunta aparezca <strong>solo si</strong> se elige una respuesta específica en la pregunta anterior. Activa el switch "Lógica Condicional" al editar.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm" data-bs-dismiss="modal">
                    ¡Comenzar a Trabajar!
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openingHelp() {
        var myModal = new bootstrap.Modal(document.getElementById('modalAyuda'));
        myModal.show();
    }
    // Alias para el botón
    function abrirAyuda() { openingHelp(); }
</script>
