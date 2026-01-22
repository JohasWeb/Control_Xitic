<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($Encuesta['titulo']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --bg-color: #f8f9fa;
        }
        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .survey-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        @media (min-width: 768px) {
            .survey-container {
                margin: 40px auto;
                min-height: auto;
                border-radius: 12px;
                overflow: hidden;
            }
        }
        .header-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #ddd;
        }
        .question-card {
            border: 1px solid #eee;
            border-left: 4px solid var(--primary-color);
            background: #fff;
            transition: all 0.2s;
        }
        .question-card.hidden {
            display: none !important;
        }
        .btn-option {
            transition: all 0.2s;
        }
        .btn-option:hover {
            transform: translateY(-2px);
        }
        .nps-btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="survey-container">
    <!-- HEADER IMAGE -->
    <?php if (!empty($Encuesta['imagen_header'])): ?>
        <img src="<?= htmlspecialchars($Encuesta['imagen_header']) ?>" alt="Header" class="header-image">
    <?php endif; ?>

    <div class="p-4 p-md-5">
        <!-- TITLE & DESC -->
        <h1 class="fw-bold text-dark mb-3"><?= htmlspecialchars($Encuesta['titulo']) ?></h1>
        <?php if (!empty($Encuesta['descripcion'])): ?>
            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($Encuesta['descripcion'])) ?></p>
        <?php endif; ?>

        <hr class="mb-5">

        <form id="publicSurveyForm" onsubmit="submitSurvey(event)">
            <input type="hidden" name="encuesta_id" value="<?= $Encuesta['id'] ?>">
            
            <div id="questions-list">
                <?php foreach ($Preguntas as $Index => $P): 
                    $Logica = json_decode($P['logica_condicional'] ?? '{}', true);
                    $Config = json_decode($P['configuracion_json'] ?? '{}', true);
                    $Opciones = [];
                    if(!empty($P['opciones_json'])) $Opciones = json_decode($P['opciones_json'], true);
                    
                    // Fallback Botonera
                    if($P['tipo_pregunta'] === 'botonera' && empty($Opciones)) {
                        $Opciones = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
                    }
                ?>
                    <div class="question-card p-4 rounded mb-4" 
                         id="q_<?= $P['id'] ?>" 
                         data-id="<?= $P['id'] ?>"
                         data-type="<?= $P['tipo_pregunta'] ?>"
                         data-logic='<?= json_encode($Logica) ?>'>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark mb-1 d-block">
                                <?= $Index + 1 ?>. <?= htmlspecialchars($P['texto_pregunta']) ?>
                                <?php if($P['requerido']): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                        </div>

                        <!-- INPUTS RENDERING -->
                        <div class="input-area">
                            <?php if ($P['tipo_pregunta'] === 'texto'): ?>
                                <textarea class="form-control bg-light border-0" 
                                          name="resp[<?= $P['id'] ?>]" 
                                          rows="3" 
                                          placeholder="Escribe tu respuesta aquí..."
                                          <?= $P['requerido'] ? 'required' : '' ?>
                                          <?= isset($Config['max_chars']) ? 'maxlength="'.$Config['max_chars'].'"' : '' ?>
                                ></textarea>

                            <?php elseif ($P['tipo_pregunta'] === 'unica'): ?>
                                <?php foreach($Opciones as $Opt): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="resp[<?= $P['id'] ?>]" 
                                               id="opt_<?= $P['id'] ?>_<?= md5($Opt) ?>" 
                                               value="<?= htmlspecialchars($Opt) ?>"
                                               <?= $P['requerido'] ? 'required' : '' ?>>
                                        <label class="form-check-label" for="opt_<?= $P['id'] ?>_<?= md5($Opt) ?>">
                                            <?= htmlspecialchars($Opt) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($P['tipo_pregunta'] === 'multiple'): ?>
                                <?php foreach($Opciones as $Opt): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="resp[<?= $P['id'] ?>][]" 
                                               id="chk_<?= $P['id'] ?>_<?= md5($Opt) ?>" 
                                               value="<?= htmlspecialchars($Opt) ?>">
                                        <label class="form-check-label" for="chk_<?= $P['id'] ?>_<?= md5($Opt) ?>">
                                            <?= htmlspecialchars($Opt) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($P['tipo_pregunta'] === 'nps'): ?>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <?php for($i=0; $i<=10; $i++): ?>
                                        <div class="text-center">
                                            <input type="radio" class="btn-check" 
                                                   name="resp[<?= $P['id'] ?>]" 
                                                   id="nps_<?= $P['id'] ?>_<?= $i ?>" 
                                                   value="<?= $i ?>" 
                                                   <?= $P['requerido'] ? 'required' : '' ?>>
                                            <label class="btn btn-outline-secondary nps-btn mb-1" for="nps_<?= $P['id'] ?>_<?= $i ?>"><?= $i ?></label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="d-flex justify-content-between px-2 mt-1 small text-muted">
                                    <span>Nada probable</span>
                                    <span>Muy probable</span>
                                </div>

                            <?php elseif ($P['tipo_pregunta'] === 'botonera'): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach($Opciones as $Opt): ?>
                                        <input type="radio" class="btn-check" 
                                               name="resp[<?= $P['id'] ?>]" 
                                               id="btn_<?= $P['id'] ?>_<?= md5($Opt) ?>" 
                                               value="<?= htmlspecialchars($Opt) ?>"
                                               <?= $P['requerido'] ? 'required' : '' ?>>
                                        <label class="btn btn-outline-primary rounded-pill px-4" for="btn_<?= $P['id'] ?>_<?= md5($Opt) ?>">
                                            <?= htmlspecialchars($Opt) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                    Enviar Encuesta
                </button>
            </div>
        </form>

        <div class="text-center mt-5 mb-3 text-muted small opacity-50">
            Powered by ControlAdmin
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initLogic();
    });

    // Check Logic on every change
    document.getElementById('publicSurveyForm').addEventListener('change', function() {
        runLogic();
    });
    
    // Also check on text input (debounced or blur)
    document.getElementById('publicSurveyForm').addEventListener('input', function(e) {
        if(e.target.tagName === 'TEXTAREA' || e.target.type === 'text') {
            runLogic();
        }
    });

    function initLogic() {
        runLogic(); // Run once on load
    }

    function runLogic() {
        const cards = document.querySelectorAll('.question-card');
        const answers = getAnswers(); // Helper to get current state of all answers

        cards.forEach(card => {
            const rawLogic = card.getAttribute('data-logic');
            if(!rawLogic) return;

            const logic = JSON.parse(rawLogic);
            if (!logic || !logic.rules || logic.rules.length === 0) {
                card.classList.remove('hidden');
                return;
            }

            // Verify Rules
            // Currently simplified to SINGLE rule support as per builder
            const rule = logic.rules[0]; 
            const parentId = rule.question_id;
            const operator = rule.operator; // ==, !=, >, <
            const expectedValue = rule.value;

            const actualValue = answers[parentId]; // Could be string, array, or undefined

            let match = false;

            // Handle undefined/null (not answered yet)
            if (actualValue === undefined || actualValue === null || actualValue === '') {
                 match = false; // By default logic hides if parent not answered? Or distinct logic? 
                 // Usually: "Show ONLY IF parent == X". If parent is empty, it's not X. So hide.
            } else {
                // Normalize for comparison
                // Treat arrays (checkboxes) as "contains" if operator is ==? 
                // For now, builder only supports simple types mostly.
                
                // If actualValue is array (checkbox), simple == check might fail unless we check includes.
                // But typically logic parent is single choice.
                
                const val = (Array.isArray(actualValue)) ? actualValue.join(',') : actualValue.toString();
                const exp = expectedValue.toString();

                if (operator === '==') {
                    match = (val.toLowerCase() === exp.toLowerCase());
                } else if (operator === '!=') {
                    match = (val.toLowerCase() !== exp.toLowerCase());
                } else if (operator === '>') {
                    match = (parseFloat(val) > parseFloat(exp));
                } else if (operator === '<') {
                    match = (parseFloat(val) < parseFloat(exp));
                }
            }

            if (match) {
                card.classList.remove('hidden');
                // Re-enable inputs inside?
                enableInputs(card, true);
            } else {
                card.classList.add('hidden');
                // Disable inputs inside so they don't validate/submit?
                enableInputs(card, false);
            }
        });
    }

    function enableInputs(card, enable) {
        const inputs = card.querySelectorAll('input, textarea, select');
        inputs.forEach(i => {
            i.disabled = !enable;
            // If hiding, also maybe clear value? 
            // Better not to clear immediately in case user toggles back. 
            // But validation (required) will block submit if hidden field is required.
            // Disabling inputs fixes the "required" block issue naturally.
        });
    }

    function getAnswers() {
        const form = document.getElementById('publicSurveyForm');
        const formData = new FormData(form);
        const data = {};
        
        // Convert FormData to easier object
        for (let [key, value] of formData.entries()) {
            // key is resp[123] or resp[123][]
            // Regex to extract ID
            const match = key.match(/resp\[(\d+)\]/);
            if(match) {
                const id = match[1];
                if(data[id]) {
                    if(!Array.isArray(data[id])) data[id] = [data[id]];
                    data[id].push(value);
                } else {
                    data[id] = value;
                }
            }
        }
        return data; 
    }

    function submitSurvey(e) {
        e.preventDefault();
        alert("Enviado (Simulación)");
        // TODO: Implement actual AJAX submit
    }
</script>

</body>
</html>
