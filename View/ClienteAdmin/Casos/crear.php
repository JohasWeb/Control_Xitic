<?php include 'View/layouts/header_cliente.php'; ?>

<div class="page-wrapper anime-fade-in">
    <div class="mb-4">
        <a href="index.php?System=casos" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Volver a Casos
        </a>
        <h1 class="page-title mt-2">Nuevo Caso</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="soft-card p-4">
                <form action="index.php?System=casos&a=guardar" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Asunto / Título</label>
                        <input type="text" class="form-control bg-light border-0" name="titulo" required placeholder="Ej. Cliente solicitando devolución fuera de plazo">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">Descripción del Caso</label>
                        <div class="form-text mb-2">Describe la situación detalladamente. La IA analizará este texto.</div>
                        <textarea class="form-control bg-light border-0 p-3" name="descripcion" rows="8" required placeholder="Escribe aquí los detalles..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" style="background:var(--accent);border:none;">
                            <i class="bi bi-stars me-2"></i>Analizar con IA
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'View/layouts/footer.php'; ?>
