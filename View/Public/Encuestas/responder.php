<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($Encuesta['titulo']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
<?php
// Override Theme logic for Preview or DB Config
$Theme = 'light';
$CustomColor = '#4f46e5'; // Default Indigo

if (isset($_GET['theme'])) {
    $Theme = $_GET['theme'];
} elseif (!empty($Encuesta['configuracion_json'])) {
    $Conf = json_decode($Encuesta['configuracion_json'], true);
    if ($Conf && isset($Conf['tema'])) {
        $Theme = $Conf['tema'];
    }
}

// Custom Color Logic
if (isset($_GET['color'])) {
    $CustomColor = $_GET['color'];
} elseif (!empty($Encuesta['configuracion_json'])) {
    $Conf = json_decode($Encuesta['configuracion_json'], true);
    if ($Conf && isset($Conf['color'])) {
        $CustomColor = $Conf['color'];
    }
}

// Define Theme Colors
$ThemesMap = [
    'light' => [
        'bg' => '#f8fafc',
        'card' => '#ffffff',
        'text' => '#1e293b',
        'muted' => '#64748b',
        'border' => '#e2e8f0',
        'input_bg' => '#ffffff'
    ],
    'navy' => [
        'bg' => '#0f172a',
        'card' => '#1e293b',
        'text' => '#f8fafc',
        'muted' => '#94a3b8',
        'border' => '#334155',
        'input_bg' => '#0f172a'
    ],
    'dark' => [
        'bg' => '#000000',
        'card' => '#121212',
        'text' => '#ffffff',
        'muted' => '#a1a1aa',
        'border' => '#27272a',
        'input_bg' => '#18181b'
    ]
];

$CurrentTheme = $ThemesMap[$Theme] ?? $ThemesMap['light'];
?>
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($CustomColor) ?>;
            --primary-soft: <?= htmlspecialchars($CustomColor) ?>1a; /* Hex opacity approx */
            
            --bg-color: <?= $CurrentTheme['bg'] ?>;
            --card-bg: <?= $CurrentTheme['card'] ?>;
            --text-main: <?= $CurrentTheme['text'] ?>;
            --text-muted: <?= $CurrentTheme['muted'] ?>;
            --border-color: <?= $CurrentTheme['border'] ?>;
            --input-bg: <?= $CurrentTheme['input_bg'] ?>;

            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --font-display: 'Outfit', system-ui, -apple-system, sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: var(--font-sans);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .survey-wrapper {
            max-width: 680px;
            margin: 0 auto;
            width: 100%;
            padding: 2rem 1rem;
            flex: 1;
        }

        .survey-card {
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            padding: 2rem;
            position: relative;
            margin-top: 60px;
        }

        .brand-logo-container {
            width: auto;
            max-width: 240px;
            height: auto;
            min-height: 80px;
            background: #fff;
            border-radius: 24px;
            position: relative;
            margin: -80px auto 1.5rem auto;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            padding: 8px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .brand-logo {
            max-height: 100px;
            border-radius: 18px;
            object-fit: contain;
            display: block;
        }

        h1.survey-title {
            font-family: var(--font-display);
            font-weight: 700;
            color: var(--text-main);
            text-align: center;
            margin-top: 3rem;
            margin-bottom: 1rem;
            font-size: 1.75rem;
            letter-spacing: -0.02em;
        }
        
        .survey-desc {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .question-item {
            margin-bottom: 2rem;
            transition: opacity 0.3s ease;
        }
        
        .question-item.hidden { display: none !important; }

        .q-label {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            display: block;
            color: var(--text-main);
        }
        
        .form-control, .form-select {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--card-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-soft);
            color: var(--text-main); /* Ensure text remains visible */
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .custom-option {
            background: var(--card-bg); /* O bg-color? card-bg matches container */
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            color: var(--text-main);
        }
        
        .custom-option:hover {
            border-color: var(--text-muted); /* Slightly darken border on hover */
            background-color: var(--bg-color);
        }

        /* FIX: Dark Mode Contrast & Colors for Buttons */
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background-color: var(--input-bg);
        }
        .btn-outline-success {
            color: #198754;
            border-color: #198754;
            background-color: var(--input-bg);
        }
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: var(--input-bg);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #ffffff;
            opacity: 1;
        }
        .btn-outline-success:hover {
            background-color: #198754;
            border-color: #198754;
            color: #ffffff;
            opacity: 1;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
            opacity: 1;
        }

        .btn-check:checked + .btn-outline-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #ffffff !important;
        }
        .btn-check:checked + .btn-outline-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }
        .btn-check:checked + .btn-outline-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
        }
        
        .form-check-input {
            width: 1.25em;
            height: 1.25em;
            margin-right: 0.75rem;
            cursor: pointer;
            border: 2px solid var(--border-color);
            background-color: var(--bg-color);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Highlight selected option wrapper */
        .custom-option:has(.form-check-input:checked) {
            border-color: var(--primary-color);
            background-color: var(--primary-soft);
            color: var(--primary-color);
            font-weight: 500;
        }

        /* NPS Styles */
        .nps-container {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 4px;
            margin-top: 0.5rem;
        }
        
        .nps-opt label {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            background-color: var(--input-bg);
            color: var(--text-main);
        }
        
        .nps-opt input:checked + label {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .nps-opt input:hover + label {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        @media (max-width: 576px) {
            .nps-container {
                grid-template-columns: repeat(6, 1fr);
                gap: 6px;
            }
        }

        /* Botón Enviar */
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.2s;
            width: 100%;
        }
        
        .btn-submit:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }
        .btn-submit:disabled {
            opacity: 0.7;
            transform: none;
            cursor: not-allowed;
        }

        /* Footer */
        .survey-footer {
            text-align: center;
            padding: 2rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="survey-wrapper">
    <div class="survey-card anime-fade-in">
        
        <!-- Logo Centrado y Redondeado -->
        <?php if (!empty($Encuesta['imagen_header'])): ?>
            <div class="brand-logo-container">
                <img src="<?= htmlspecialchars($Encuesta['imagen_header']) ?>" alt="Logo" class="brand-logo">
            </div>
        <?php else: ?>
             <!-- Placeholder Logo si no hay imagen -->
            <div class="brand-logo-container d-flex align-items-center justify-content-center bg-light text-primary fs-3">
                <i class="bi bi-ui-checks"></i>
            </div>
        <?php endif; ?>

        <h1 class="survey-title"><?= htmlspecialchars($Encuesta['titulo']) ?></h1>
        
        <?php if (!empty($Encuesta['descripcion'])): ?>
            <div class="survey-desc">
                <?= nl2br(htmlspecialchars($Encuesta['descripcion'])) ?>
            </div>
        <?php endif; ?>

        <form id="publicSurveyForm" onsubmit="submitSurvey(event)">
            <input type="hidden" name="encuesta_id" value="<?= $Encuesta['id'] ?>">
            <?php 
                $SucursalId = 0;
                if (isset($_GET['sucursal_id'])) {
                    $SucursalId = (int)$_GET['sucursal_id'];
                }
            ?>
            <input type="hidden" name="sucursal_id" value="<?= $SucursalId ?>">
            
            <div id="questions-list">
                <?php foreach ($Preguntas as $Index => $P): 
                    $Logica = json_decode($P['logica_condicional'] ?? '{}', true);
                    $Config = json_decode($P['configuracion_json'] ?? '{}', true);
                    $Opciones = [];
                    if(!empty($P['opciones_json'])) $Opciones = json_decode($P['opciones_json'], true);
                    
                    if($P['tipo_pregunta'] === 'botonera' && empty($Opciones)) {
                        $Opciones = ['Gran experiencia', 'Sugerencia', 'Tuve un problema'];
                    }
                ?>
                    <div class="question-item" 
                         id="q_<?= $P['id'] ?>" 
                         data-id="<?= $P['id'] ?>"
                         data-type="<?= $P['tipo_pregunta'] ?>"
                         data-logic='<?= json_encode($Logica) ?>'>
                        
                        <label class="q-label">
                            <span class="text-muted opacity-50 me-1"><?= $Index + 1 ?>.</span>
                            <?= htmlspecialchars($P['texto_pregunta']) ?>
                            <?php if($P['requerido']): ?>
                                <span class="text-danger small" title="Requerido">*</span>
                            <?php endif; ?>
                        </label>

                        <div class="input-area">
                            <?php if ($P['tipo_pregunta'] === 'texto'): ?>
                                <textarea class="form-control" 
                                          name="resp[<?= $P['id'] ?>]" 
                                          rows="3" 
                                          placeholder="Escribe aquí tu respuesta..."
                                          <?= $P['requerido'] ? 'required' : '' ?>
                                          <?= isset($Config['max_chars']) ? 'maxlength="'.$Config['max_chars'].'"' : '' ?>
                                ></textarea>

                            <?php elseif ($P['tipo_pregunta'] === 'unica'): ?>
                                <div class="vstack">
                                <?php foreach($Opciones as $Opt): ?>
                                    <label class="custom-option">
                                        <input class="form-check-input" type="radio" 
                                               name="resp[<?= $P['id'] ?>]" 
                                               value="<?= htmlspecialchars($Opt) ?>"
                                               <?= $P['requerido'] ? 'required' : '' ?>>
                                        <span><?= htmlspecialchars($Opt) ?></span>
                                    </label>
                                <?php endforeach; ?>
                                </div>

                            <?php elseif ($P['tipo_pregunta'] === 'multiple'): ?>
                                <div class="vstack">
                                <?php foreach($Opciones as $Opt): ?>
                                    <label class="custom-option">
                                        <input class="form-check-input" type="checkbox" 
                                               name="resp[<?= $P['id'] ?>][]" 
                                               value="<?= htmlspecialchars($Opt) ?>">
                                        <span><?= htmlspecialchars($Opt) ?></span>
                                    </label>
                                <?php endforeach; ?>
                                </div>

                            <?php elseif ($P['tipo_pregunta'] === 'nps'): ?>
                                <div class="nps-container">
                                    <?php for($i=0; $i<=10; $i++): ?>
                                        <div class="nps-opt">
                                            <input type="radio" class="d-none" // Hide default
                                                   name="resp[<?= $P['id'] ?>]" 
                                                   id="nps_<?= $P['id'] ?>_<?= $i ?>" 
                                                   value="<?= $i ?>"
                                                   <?= $P['requerido'] ? 'required' : '' ?>>
                                            <label for="nps_<?= $P['id'] ?>_<?= $i ?>"><?= $i ?></label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="d-flex justify-content-between px-1 mt-2 extra-small text-muted fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">
                                    <span>Nada probable</span>
                                    <span>Muy probable</span>
                                </div>

                            <?php elseif ($P['tipo_pregunta'] === 'botonera'): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach($Opciones as $Opt): 
                                        $BtnClass = 'btn-outline-primary'; // Default Blue (Sugerencia)
                                        $OptLower = mb_strtolower($Opt, 'UTF-8');
                                        
                                        if (str_contains($OptLower, 'gran experiencia')) {
                                            $BtnClass = 'btn-outline-success';
                                        } elseif (str_contains($OptLower, 'problema')) {
                                            $BtnClass = 'btn-outline-danger';
                                        }
                                    ?>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check" 
                                                   name="resp[<?= $P['id'] ?>]" 
                                                   id="btn_<?= $P['id'] ?>_<?= md5($Opt) ?>" 
                                                   value="<?= htmlspecialchars($Opt) ?>"
                                                   <?= $P['requerido'] ? 'required' : '' ?>>
                                            <label class="btn <?= $BtnClass ?> rounded-pill w-100 py-2" for="btn_<?= $P['id'] ?>_<?= md5($Opt) ?>">
                                                <?= htmlspecialchars($Opt) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Sección de Comentarios Predeterminada para Botonera -->
                                <div class="mt-3">
                                    <textarea class="form-control" 
                                              name="comentarios[<?= $P['id'] ?>]" 
                                              rows="2" 
                                              placeholder="Cuéntanos más sobre tu elección (opcional)..."></textarea>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-5 mb-4">
                <button type="submit" class="btn-submit">
                    Enviar Respuestas
                </button>
            </div>
        </form>
    </div>
    
    <div class="survey-footer">
        Powered by <strong>Xitic Control</strong> &copy; <?= date('Y') ?>
    </div>
</div>

<!-- SCRIPTS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initLogic();
        
        // Animacion entrada
        document.querySelector('.survey-card').style.opacity = 0;
        document.querySelector('.survey-card').animate([
            { opacity: 0, transform: 'translateY(20px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], {
            duration: 600,
            fill: 'forwards',
            easing: 'ease-out'
        });
    });

    // Check Logic on every change
    document.getElementById('publicSurveyForm').addEventListener('change', function() {
        runLogic();
    });
    
    document.getElementById('publicSurveyForm').addEventListener('input', function(e) {
        if(e.target.tagName === 'TEXTAREA' || e.target.type === 'text') {
            runLogic();
        }
    });

    function initLogic() {
        runLogic(); 
    }

    function runLogic() {
        const cards = document.querySelectorAll('.question-item');
        const answers = getAnswers(); 

        cards.forEach(card => {
            const rawLogic = card.getAttribute('data-logic');
            if(!rawLogic) return;

            const logic = JSON.parse(rawLogic);
            if (!logic || !logic.rules || logic.rules.length === 0) {
                card.classList.remove('hidden');
                return;
            }

            const rule = logic.rules[0]; 
            const parentId = rule.question_id;
            const operator = rule.operator; 
            const expectedValue = rule.value;

            const actualValue = answers[parentId]; 

            let match = false;

            if (actualValue === undefined || actualValue === null || actualValue === '') {
                 match = false; 
            } else {
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
                enableInputs(card, true);
            } else {
                card.classList.add('hidden');
                enableInputs(card, false);
            }
        });
    }

    function enableInputs(card, enable) {
        const inputs = card.querySelectorAll('input, textarea, select');
        inputs.forEach(i => {
            i.disabled = !enable;
        });
    }

    function getAnswers() {
        const form = document.getElementById('publicSurveyForm');
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
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
        
        const btn = document.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Procesando...';

        const form = document.getElementById('publicSurveyForm');
        const formData = new FormData(form);

        fetch('index.php?System=encuestas&a=guardar_respuesta_publica', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Redirigir a vista de agradecimiento
                const encId = formData.get('encuesta_id');
                window.location.href = `index.php?System=encuestas&a=agradecimiento&id=${encId}`;
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al enviar la encuesta. Verifica tu conexión.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
</script>

</body>
</html>
