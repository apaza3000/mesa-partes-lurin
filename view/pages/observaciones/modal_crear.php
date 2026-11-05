<?php
/**
 * Vista (parcial): Modal de Observación de Expediente — TAREA E05-1
 * Se incluye dentro de area/bandeja.php. Los campos se rellenan vía JS
 * desde los data-* del botón ".btn-abrir-observar".
 */
?>
<!-- ======================================================= -->
<!-- MODAL: OBSERVAR EXPEDIENTE (TAREA E05-1)                -->
<!-- ======================================================= -->
<div class="modal fade" id="modalObservar" tabindex="-1" aria-labelledby="modalObservarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/area/observar" class="modal-content">
            <input type="hidden" name="id_derivacion" id="observar_id_derivacion">
            <input type="hidden" name="tab" id="observar_tab" value="en_proceso">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalObservarLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Observar Expediente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <span class="text-muted small d-block">Expediente a observar:</span>
                    <strong id="observar_codigo" class="text-warning-emphasis fs-6"></strong>
                </div>

                <p class="text-muted small">
                    El expediente pasará a estado <span class="badge bg-warning text-dark">Observado</span> y se
                    generará una notificación al administrado para que subsane dentro del plazo de ley.
                    El trámite <strong>no se rechaza de plano</strong>.
                </p>

                <div class="mb-3">
                    <label for="observar_descripcion" class="form-label fw-bold">
                        Motivos / Requisitos faltantes <span class="text-danger">*</span>
                    </label>
                    <textarea name="descripcion" id="observar_descripcion" rows="5" class="form-control" required
                              minlength="10" maxlength="1000"
                              placeholder="Ej: Falta plano visado por el CIP; comprobante de pago de tasa ilegible; no adjunta copia del DNI del solicitante..."></textarea>
                    <div class="form-text">
                                Detalle con claridad cada documento o requisito a subsanar (mínimo 10 caracteres).
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning text-white">
                    <i class="bi bi-exclamation-triangle me-1"></i>Registrar Observación
                </button>
            </div>
        </form>
    </div>
</div>
