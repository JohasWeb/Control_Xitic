<?php
// View/Encuestas/crear.php
include 'View/layouts/header_admin.php';
$Csrf = SecurityController::obtenerCsrfToken();
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php?System=encuestas" class="btn btn-outline-secondary me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-0">Nueva Encuesta</h2>
            <p class="text-muted mb-0">Configura la encuesta y agrega las preguntas.</p>
        </div>
    </div>

    <form action="index.php?System=encuestas&a=guardar" method="POST" autocomplete="off" id="FormEncuesta">
        <input type="hidden" name="csrf_token" value="<?php echo $Csrf; ?>">

        <div class="row g-4">
            <!-- Columna Izq: Configuración -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Configuración General</h6>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label fw-bold small text-uppercase">Cliente asignado</label>
                            <select class="form-select" name="cliente_id" required>
                                <option value="" selected disabled>Selecciona un cliente...</option>
                                <?php foreach ($Clientes as $C): ?>
                                    <option value="<?php echo $C['id']; ?>"><?php echo htmlspecialchars($C['nombre_comercial']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label fw-bold small text-uppercase">Título de Encuesta</label>
                            <input type="text" class="form-control" name="titulo" required placeholder="Ej: Satisfacción Servicio">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-bold small text-uppercase">Descripción (Opcional)</label>
                            <textarea class="form-control" name="descripcion" rows="2" placeholder="Breve introducción para el cliente"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="fecha_inicio" class="form-label fw-bold small text-uppercase">Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="fecha_fin" class="form-label fw-bold small text-uppercase">Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" required value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>">
                            </div>
                        </div>

                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fa-solid fa-save me-2"></i>Guardar Encuesta
                </button>
            </div>

            <!-- Columna Der: Preguntas -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Cuestionario</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarPregunta()">
                            <i class="fa-solid fa-plus me-1"></i>Agregar Pregunta
                        </button>
                    </div>
                    <div class="card-body bg-light" id="ContenedorPreguntas">
                        <!-- Aquí se inyectan las preguntas con JS -->
                        <div class="text-center text-muted py-5" id="EmptyState">
                            <i class="fa-regular fa-clipboard fa-3x mb-3 opacity-50"></i>
                            <p>No has agregado ninguna pregunta.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template de Pregunta (Oculto) -->
<template id="TemplatePregunta">
    <div class="card border mb-3 pregunta-item">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge bg-secondary index-badge">P#</span>
                <button type="button" class="btn-close" aria-label="Eliminar" onclick="eliminarPregunta(this)"></button>
            </div>
            
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Texto de la pregunta</label>
                    <input type="text" class="form-control form-control-sm" name="preguntas[INDEX][texto]" required placeholder="¿Qué te pareció el servicio?">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tipo de Respuesta</label>
                    <select class="form-select form-select-sm" name="preguntas[INDEX][tipo]" onchange="toggleOpciones(this)">
                        <option value="texto">Texto Libre</option>
                        <option value="calificacion_5">Calificación (1 a 5 estrellas)</option>
                        <option value="si_no">Sí / No</option>
                        <option value="opcion_unica">Opción Única (Radio)</option>
                        <option value="opcion_multiple">Opción Múltiple (Checkbox)</option>
                    </select>
                </div>
                
                <div class="col-12 opciones-box d-none">
                    <label class="form-label small fw-bold text-primary">Opciones (Separadas por coma)</label>
                    <input type="text" class="form-control form-control-sm" name="preguntas[INDEX][opciones]" placeholder="Ej: Excelente, Bueno, Regular, Malo">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="preguntas[INDEX][requerido]" value="1" id="req_INDEX" checked>
                        <label class="form-check-label small" for="req_INDEX">
                            Respuesta obligatoria
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    let PreguntaIndex = 0;

    function agregarPregunta() {
        // Ocultar EmptyState
        document.getElementById('EmptyState').style.display = 'none';

        const Template = document.getElementById('TemplatePregunta');
        const Clone = Template.content.cloneNode(true);
        const Container = document.getElementById('ContenedorPreguntas');

        // Reemplazar INDEX por el contador actual
        Clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
            el.name = el.name.replace('INDEX', PreguntaIndex);
            if(el.id) el.id = el.id.replace('INDEX', PreguntaIndex);
            if(el.htmlFor) el.htmlFor = el.htmlFor.replace('INDEX', PreguntaIndex);
        });
        
        // Actualizar número visual
        Clone.querySelector('.index-badge').textContent = 'P' + (PreguntaIndex + 1);

        Container.appendChild(Clone);
        PreguntaIndex++;
    }

    function eliminarPregunta(btn) {
        btn.closest('.pregunta-item').remove();
        if (document.querySelectorAll('.pregunta-item').length === 0) {
            document.getElementById('EmptyState').style.display = 'block';
        }
    }

    function toggleOpciones(select) {
        const val = select.value;
        const box = select.closest('.row').querySelector('.opciones-box');
        if (val === 'opcion_unica' || val === 'opcion_multiple') {
            box.classList.remove('d-none');
            box.querySelector('input').setAttribute('required', 'required');
        } else {
            box.classList.add('d-none');
            box.querySelector('input').removeAttribute('required');
        }
    }
</script>

<?php include 'View/layouts/footer.php'; ?>
