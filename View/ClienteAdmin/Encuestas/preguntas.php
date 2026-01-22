<?php
// View/ClienteAdmin/Encuestas/preguntas.php
include 'View/layouts/header_cliente.php';
$Csrf = SecurityController::obtenerCsrfToken();
?>

<!-- Custom Styles for this view -->
<style>
    .question-card {
        cursor: move;
        transition: all 0.2s;
        border-left: 4px solid var(--accent, #0d6efd);
        background: #fff;
    }
    .question-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        background-color: #fcfcfc;
    }
    .ghost {
        opacity: 0.5;
        background: #e2e6ea;
        border: 2px dashed #ccc;
    }
    .logic-badge {
        font-size: 0.75em;
        background-color: #e9ecef;
        color: #495057;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    .type-badge {
        font-size: 0.75em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .smartphone-preview {
        width: 300px;
        height: 550px;
        border-radius: 30px;
        border: 8px solid #333;
        display: flex;
        flex-direction: column;
    }
    .preview-content {
        background: #f8f9fa;
        flex: 1;
    }
</style>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<div class="page-wrapper anime-fade-in">
    
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <div class="mb-1">
                <a href="index.php?System=encuestas" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Encuestas
                </a>
            </div>
            <h1 class="page-title mb-1">Gestor de Preguntas</h1>
            <p class="page-subtitle mb-0">
                Encuesta: <span class="fw-bold text-dark"><?= htmlspecialchars($Encuesta['titulo']) ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light border text-info bg-white shadow-sm" onclick="abrirAyuda()">
                <i class="bi bi-question-circle me-1"></i> Ayuda
            </button>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" 
                    style="background:var(--accent);border:none;" 
                    onclick="abrirModalPregunta()">
                <i class="bi bi-plus-lg me-2"></i>Nueva Pregunta
            </button>
        </div>
    </div>

    <!-- Alert / Tip -->
    <div class="alert alert-light border border-info-subtle bg-info-subtle text-info-emphasis d-flex align-items-center gap-3 shadow-sm mb-4">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong>Tip:</strong> Arrastra y suelta las preguntas para reordenarlas automáticamente.
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Questions List -->
    <div class="row">
        <div class="col-12">
            <div id="preguntas-lista" class="list-group list-group-flush gap-2">
                <?php if (empty($Preguntas)): ?>
                    <div class="text-center py-5 soft-card">
                        <div class="text-muted opacity-50 mb-3"><i class="bi bi-clipboard-x fa-3x"></i></div>
                        <h6 class="fw-bold text-muted">No hay preguntas agregadas aún</h6>
                        <p class="small text-muted mb-0">Comienza creando la primera pregunta para tu encuesta.</p>
                    </div>
                <?php else: 
                    // Create Map for Logic Names
                    $MapNombres = [];
                    foreach ($Preguntas as $Pre) $MapNombres[$Pre['id']] = $Pre['texto_pregunta'];
                ?>
                    <?php foreach ($Preguntas as $P): 
                        $Logica = json_decode($P['logica_condicional'] ?? '{}', true);
                        $TieneLogica = !empty($Logica) && isset($Logica['rules']) && count($Logica['rules']) > 0;
                        
                        $Opciones = [];
                        if($P['tipo_pregunta'] === 'unica' || $P['tipo_pregunta'] === 'multiple' || $P['tipo_pregunta'] === 'botonera') {
                             if(!empty($P['opciones_json'])) {
                                 $Opciones = json_decode($P['opciones_json'], true) ?? [];
                             }
                             // Fallback for Botonera if empty (visual only)
                             if($P['tipo_pregunta'] === 'botonera' && empty($Opciones)) {
                                 $Opciones = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
                             }
                        }

                        $Config = json_decode($P['configuracion_json'] ?? '{}', true);
                    ?>
                        <div class="list-group-item question-card p-3 rounded shadow-sm border-0 border-start border-4 mb-2" data-id="<?= $P['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-start gap-3 flex-grow-1">
                                    <div class="text-muted mt-1" style="cursor:move;"><i class="bi bi-grip-vertical fs-5"></i></div>
                                    <div class="w-100">
                                        <!-- Header: Type & Req -->
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold text-uppercase ls-1">
                                                <?= ucfirst($P['tipo_pregunta']) ?>
                                            </span>
                                            <?php if ($P['requerido']): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle extra-small">
                                                    <i class="bi bi-asterisk me-1"></i>Obligatoria
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border extra-small">Opcional</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Question Text -->
                                        <h6 class="mb-3 fw-bold text-dark fs-6 text-wrap">
                                            <?= htmlspecialchars($P['texto_pregunta']) ?>
                                        </h6>

                                        <div class="bg-light p-2 rounded border border-light-subtle small text-muted">
                                            <!-- Config Details -->
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <i class="bi bi-sliders me-1"></i> <strong>Configuración:</strong>
                                                    <?php if($P['tipo_pregunta'] === 'texto'): ?>
                                                        <?php if (isset($Config['max_chars']) && $Config['max_chars'] > 0): ?>
                                                            <span class="ms-1">Límite de <?= $Config['max_chars'] ?> caracteres.</span>
                                                        <?php else: ?>
                                                            <span class="ms-1 fst-italic">Sin límite de caracteres.</span>
                                                        <?php endif; ?>
                                                    <?php elseif(!empty($Opciones)): ?>
                                                        <span class="ms-1"><?= count($Opciones) ?> Opciones definidas.</span>
                                                    <?php else: ?>
                                                        <span class="ms-1">Estándar.</span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Options List -->
                                                <?php if (!empty($Opciones)): ?>
                                                    <div class="col-12 mt-1">
                                                        <div class="d-flex flex-wrap gap-1 ps-3 border-start border-3 border-secondary-subtle">
                                                            <?php foreach($Opciones as $Opt): ?>
                                                                <span class="badge bg-white text-dark border fw-normal shadow-sm"><?= htmlspecialchars($Opt) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Logic Details -->
                                                <div class="col-12 mt-2 pt-2 border-top">
                                                    <?php if ($TieneLogica): 
                                                        $Regla = $Logica['rules'][0];
                                                        $NombrePadre = $MapNombres[$Regla['question_id']] ?? 'ID #' . $Regla['question_id'];
                                                        $MapOps = [
                                                            '==' => 'sea igual a',
                                                            '!=' => 'sea diferente de',
                                                            '>'  => 'sea mayor que',
                                                            '<'  => 'sea menor que'
                                                        ];
                                                        $Operador = $MapOps[$Regla['operator']] ?? 'sea igual a';
                                                    ?>
                                                        <div class="text-primary">
                                                            <i class="bi bi-diagram-2-fill me-1"></i>
                                                            Mostrar solo si <strong><?= htmlspecialchars($NombrePadre) ?></strong> <?= $Operador ?> <span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($Regla['value']) ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-muted opacity-75">
                                                            <i class="bi bi-eye-fill me-1"></i> Visible siempre (Sin condiciones).
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="btn-group ms-3 shadow-sm rounded">
                                    <button class="btn btn-sm btn-white border text-primary" onclick='editarPregunta(<?= json_encode($P) ?>)' title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-white border text-danger" onclick="eliminarPregunta(<?= $P['id'] ?>)" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pregunta -->
<div class="modal fade" id="modalPregunta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"> <!-- XL Modal -->
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitulo">Nueva Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formPregunta" onsubmit="guardarPregunta(event)" class="h-100">
                <div class="modal-body pt-4">
                    <input type="hidden" name="encuesta_id" value="<?= $Encuesta['id'] ?>">
                    <input type="hidden" name="pregunta_id" id="pregunta_id">
                    
                    <div class="row h-100">
                        <!-- FORMULARIO (FULL WIDTH) -->
                        <div class="col-12">
                            <div class="pe-lg-3">
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Texto de la Pregunta <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-light border-0" name="texto_pregunta" id="texto_pregunta" required placeholder="Ej: ¿Cómo califica nuestro servicio?">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Tipo de Respuesta</label>
                                        <select class="form-select bg-light border-0" name="tipo_pregunta" id="tipo_pregunta" onchange="cambiarTipo()">
                                            <option value="texto">Texto Libre</option>
                                            <option value="unica">Selección Única (Radio)</option>
                                            <option value="multiple">Selección Múltiple (Check)</option>
                                            <option value="nps">NPS (0-10)</option>
                                            <option value="1to10">Escala 1 a 10</option>
                                            <option value="1to5">Escala 1 a 5 (Estrellas)</option>
                                            <option value="botonera">Botonera (Feedback rápido)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="requerido" id="requerido">
                                            <label class="form-check-label small text-muted" for="requerido">
                                                Respuesta Obligatoria
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración Específica -->
                                <div id="config-options" style="display:none;" class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title extra-small fw-bold text-uppercase text-muted ls-1">Opciones de Respuesta</h6>
                                        <small class="d-block mb-2 text-muted">Ingresa las opciones (una por renglón).</small>
                                        <textarea class="form-control border-0 bg-white" name="opciones" id="opciones" rows="4" placeholder="Opción A&#10;Opción B&#10;Opción C"></textarea>
                                    </div>
                                </div>

                                <div id="config-text" style="display:none;" class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title extra-small fw-bold text-uppercase text-muted ls-1">Configuración de Texto</h6>
                                        <label class="form-label small text-muted">Límite de Caracteres</label>
                                        <input type="number" class="form-control border-0 bg-white" id="max_chars" placeholder="5000">
                                    </div>
                                </div>

                                <div id="config-botonera" style="display:none;" class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title extra-small fw-bold text-uppercase text-muted ls-1 mb-0">Botones de Feedback</h6>
                                            <span class="badge bg-warning text-dark border">Estándar</span>
                                        </div>
                                        <p class="extra-small text-muted mb-3">Estos botones son fijos y no se pueden editar.</p>
                                        <div id="botonera-list" class="vstack gap-2">
                                            <!-- JS generará inputs aquí -->
                                        </div>
                                        <input type="hidden" name="opciones_json_raw" id="opciones_json_raw">
                                    </div>
                                </div>

                                <!-- Lógica Condicional -->
                                <div class="card border border-light-subtle shadow-sm mt-3">
                                    <div class="card-header bg-white border-0 py-2" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#logicCollapse">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 text-primary extra-small fw-bold text-uppercase ls-1">
                                                <i class="bi bi-diagram-2 me-2"></i>Lógica Condicional
                                            </h6>
                                            <i class="bi bi-chevron-down text-muted"></i>
                                        </div>
                                    </div>
                                    <div id="logicCollapse" class="collapse">
                                        <div class="card-body pt-0">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="tiene_logica" onchange="toggleLogic()">
                                                <label class="form-check-label small text-muted" for="tiene_logica">
                                                    Mostrar esta pregunta <b>SOLO SI...</b>
                                                </label>
                                            </div>
                                            
                                            <div id="logic-builder" style="display:none;" class="p-3 bg-light rounded">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-12 mb-2">
                                                        <label class="extra-small text-muted fw-bold">La pregunta anterior...</label>
                                                        <select class="form-select form-select-sm border-0" id="logic_parent">
                                                            <option value="">-- Seleccionar --</option>
                                                            <!-- JS Populará esto -->
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="extra-small text-muted fw-bold">Condición</label>
                                                        <select class="form-select form-select-sm border-0" id="logic_operator">
                                                            <option value="==">Es igual a</option>
                                                            <option value="!=">Es diferente de</option>
                                                            <option value=">">Es mayor que</option>
                                                            <option value="<">Es menor que</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label class="extra-small text-muted fw-bold">Valor</label>
                                                        <input type="text" class="form-control form-control-sm border-0" id="logic_value" placeholder="Valor esperado">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="logica_condicional" id="logica_condicional">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="configuracion_json" id="configuracion_json">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:var(--accent);border:none;">
                        Guardar Pregunta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ayuda -->
<div class="modal fade" id="modalAyuda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Ayuda del Gestor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-muted small">
                <p><strong>Tipos de Pregunta:</strong></p>
                <ul class="mb-4">
                    <li><strong>Botonera:</strong> Botones fijos: "Gran experiencia", "Sugerencia", "Problema".</li>
                    <li><strong>NPS:</strong> Escala 0-10 estándar (encuestas de satisfacción).</li>
                    <li><strong>Texto Libre:</strong> Campo de texto abierto.</li>
                </ul>
                <p><strong>Organización:</strong> Arrastra las tarjetas para reordenar.</p>
                <p><strong>Lógica:</strong> Configura condiciones para mostrar preguntas.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>

<script>
    // Variables Globales
    const EncuestaID = <?= $Encuesta['id'] ?>;
    const AllPreguntas = <?= json_encode($Preguntas) ?>;
    
    // Fixed options for Botonera
    const FIXED_BOTONERA = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
    let BotoneraItems = [...FIXED_BOTONERA];

    var modalPregunta = null;
    var modalAyuda = null;

    document.addEventListener('DOMContentLoaded', function() {
        if(typeof bootstrap !== 'undefined') {
            modalPregunta = new bootstrap.Modal(document.getElementById('modalPregunta'));
            modalAyuda = new bootstrap.Modal(document.getElementById('modalAyuda'));
        }

        var el = document.getElementById('preguntas-lista');
        if(el) {
            Sortable.create(el, {
                animation: 150,
                ghostClass: 'ghost',
                handle: '.question-card',
                onEnd: function (evt) { guardarOrden(); }
            });
        }
    });

    function abrirModalPregunta() {
        document.getElementById('formPregunta').reset();
        document.getElementById('pregunta_id').value = '';
        document.getElementById('modalTitulo').innerText = 'Nueva Pregunta';
        
        document.getElementById('tiene_logica').checked = false;
        toggleLogic();
        
        // Reset Botonera to fixed defaults
        BotoneraItems = [...FIXED_BOTONERA];
        renderBotoneraInputs();
        
        cambiarTipo();
        modalPregunta.show();
        popularPreguntasPadre();
    }

    function editarPregunta(pregunta) {
        document.getElementById('formPregunta').reset();
        document.getElementById('pregunta_id').value = pregunta.id;
        document.getElementById('modalTitulo').innerText = 'Editar Pregunta';
        document.getElementById('texto_pregunta').value = pregunta.texto_pregunta;
        
        // Normalize type to lowercase and trimmed
        const currentType = (pregunta.tipo_pregunta || '').trim().toLowerCase();
        document.getElementById('tipo_pregunta').value = currentType;
        
        document.getElementById('requerido').checked = (pregunta.requerido == 1);
        
        // Handle Options
        if (currentType === 'botonera') {
            // Always force fixed items for botonera, regardless of what's in DB
            BotoneraItems = [...FIXED_BOTONERA];
            renderBotoneraInputs();
        } else if (pregunta.opciones_json) {
            try {
                const opts = JSON.parse(pregunta.opciones_json);
                document.getElementById('opciones').value = opts.join('\n');
            } catch(e) { document.getElementById('opciones').value = ''; }
        }

        if (pregunta.configuracion_json) {
            try {
                const conf = JSON.parse(pregunta.configuracion_json);
                if (conf.max_chars) document.getElementById('max_chars').value = conf.max_chars;
            } catch(e){}
        }

        // Logic
        if (pregunta.logica_condicional) {
            try {
                const log = JSON.parse(pregunta.logica_condicional);
                if (log && log.rules && log.rules.length > 0) {
                    const rule = log.rules[0];
                    document.getElementById('tiene_logica').checked = true;
                    toggleLogic();
                    popularPreguntasPadre(); 
                    setTimeout(() => {
                        document.getElementById('logic_parent').value = rule.question_id;
                        document.getElementById('logic_operator').value = rule.operator;
                        document.getElementById('logic_value').value = rule.value;
                    }, 50);
                } else {
                    document.getElementById('tiene_logica').checked = false;
                    toggleLogic();
                }
            } catch(e) { document.getElementById('tiene_logica').checked = false; toggleLogic(); }
        } else {
            document.getElementById('tiene_logica').checked = false;
            toggleLogic();
        }

        cambiarTipo();
        modalPregunta.show();
    }

    function cambiarTipo() {
        const tipo = document.getElementById('tipo_pregunta').value;
        const divOpciones = document.getElementById('config-options');
        const divText = document.getElementById('config-text');
        const divBotonera = document.getElementById('config-botonera');

        divOpciones.style.display = 'none';
        divText.style.display = 'none';
        divBotonera.style.display = 'none';

        if (['unica', 'multiple'].includes(tipo)) {
            divOpciones.style.display = 'block';
        } else if (tipo === 'texto') {
            divText.style.display = 'block';
        } else if (tipo === 'botonera') {
            divBotonera.style.display = 'block';
            renderBotoneraInputs();
        }
    }

    // --- LOGIC FOR BOTONERA ---
    function renderBotoneraInputs() {
        const container = document.getElementById('botonera-list');
        container.innerHTML = '';
        BotoneraItems.forEach((btn, index) => {
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mb-1';
            row.innerHTML = `
                <span class="input-group-text bg-white border-0"><i class="bi bi-lock-fill text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-light shadow-sm text-muted" value="${btn}" readonly>
            `;
            container.appendChild(row);
        });
    }

    // --- LOGIC BUILDER ---
    function toggleLogic() {
        const show = document.getElementById('tiene_logica').checked;
        document.getElementById('logic-builder').style.display = show ? 'block' : 'none';
        if(show) popularPreguntasPadre();
    }

    function popularPreguntasPadre() {
        const select = document.getElementById('logic_parent');
        const currentId = document.getElementById('pregunta_id').value;
        const currentVal = select.value;
        
        select.innerHTML = '<option value="">-- Seleccionar --</option>';
        AllPreguntas.forEach(p => {
            if(currentId && p.id == currentId) return;
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.innerText = p.texto_pregunta;
            if(p.id == currentVal) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function guardarPregunta(e) {
        e.preventDefault();
        
        const form = document.getElementById('formPregunta');
        const fd = new FormData(form);

        if (fd.get('tipo_pregunta') === 'botonera') {
            // Force save fixed items
            document.getElementById('opciones_json_raw').value = JSON.stringify(FIXED_BOTONERA);
            fd.set('opciones_json_raw', JSON.stringify(FIXED_BOTONERA));
        }

        const config = {};
        if (fd.get('tipo_pregunta') === 'texto') {
            const max = document.getElementById('max_chars').value;
            if(max) config.max_chars = parseInt(max);
        }
        document.getElementById('configuracion_json').value = JSON.stringify(config);
        fd.set('configuracion_json', JSON.stringify(config));

        let logicJson = null;
        if (document.getElementById('tiene_logica').checked) {
            const pid = document.getElementById('logic_parent').value;
            const op = document.getElementById('logic_operator').value;
            const val = document.getElementById('logic_value').value;
            if (pid && val) {
                logicJson = JSON.stringify({
                    rules: [{
                        question_id: pid,
                        operator: op,
                        value: val
                    }],
                    action: 'show'
                });
            }
        }
        if(logicJson) fd.set('logica_condicional', logicJson);
        else fd.delete('logica_condicional'); 

        fetch('index.php?System=encuestas&a=guardar_pregunta', {
            method: 'POST',
            body: fd
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al guardar');
                }
            } catch(e) {
                console.error('Server Error:', text);
                alert('Error del servidor:\n' + text.substring(0, 500));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión o red.');
        });
    }

    function eliminarPregunta(id) {
        if(!confirm('¿Seguro que deseas eliminar esta pregunta?')) return;
        const fd = new FormData();
        fd.append('id', id);
        fd.append('encuesta_id', EncuestaID);
        fetch('index.php?System=encuestas&a=eliminar_pregunta', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if(data.success) location.reload(); else alert(data.message); });
    }

    function guardarOrden() {
        const lista = document.getElementById('preguntas-lista');
        const items = [];
        lista.querySelectorAll('.question-card').forEach((el, index) => {
            items.push({ id: el.getAttribute('data-id'), orden: index + 1 });
        });
        fetch('index.php?System=encuestas&a=reordenar_preguntas', {
            method: 'POST',
            body: JSON.stringify({ encuesta_id: EncuestaID, items: items })
        });
    }
</script>
