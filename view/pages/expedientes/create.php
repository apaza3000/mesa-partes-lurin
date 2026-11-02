<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="fs-3">Registrar expediente</h1>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <form id="form-expediente" enctype="multipart/form-data">
            <input type="hidden" name="id_remitente" id="id-remitente">
            <div class="card wizard-step">
                <div class="card-header"><b>1. Remitente</b></div>
                <div class="card-body row g-3">
                    <div class="col-md-3"><label class="form-label">Tipo de documento</label><select
                            name="tipo_documento" id="tipo-documento" class="form-select" required>
                            <option>DNI</option>
                            <option>CE</option>
                            <option>RUC</option>
                            <option>Pasaporte</option>
                            <option>Otro</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Número</label>
                        <div class="input-group"><input name="numero_documento" id="numero-documento"
                                class="form-control" required><button class="btn btn-outline-primary" type="button"
                                id="buscar-persona">Buscar</button></div>
                    </div>
                    <div class="col-md-5"><label class="form-label">Nombres / razón social</label><input name="nombres"
                            class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Apellido paterno</label><input
                            name="apellido_paterno" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Apellido materno</label><input
                            name="apellido_materno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Correo</label><input type="email" name="email"
                            class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono"
                            class="form-control"></div>
                    <div class="col-md-8"><label class="form-label">Dirección</label><input name="direccion"
                            class="form-control"></div>
                </div>
            </div>
            <div class="card wizard-step d-none">
                <div class="card-header"><b>2. Asunto, canal y prioridad</b></div>
                <div class="card-body row g-3">
                    <div class="col-md-12"><label class="form-label">Asunto del expediente</label><textarea
                            name="asunto" class="form-control" maxlength="500" required></textarea></div>
                    <div class="col-md-6"><label class="form-label">Canal</label><select name="canal_ingreso"
                            class="form-select">
                            <option>Virtual</option>
                            <option>Presencial</option>
                            <option>Interno</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Prioridad</label><select name="prioridad"
                            class="form-select">
                            <option>Normal</option>
                            <option>Alta</option>
                            <option>Urgente</option>
                        </select></div>
                </div>
            </div>
            <div class="card wizard-step d-none">
                <div class="card-header"><b>3. Documento</b></div>
                <div class="card-body row g-3">
                    <div class="col-md-4"><label class="form-label">Tipo</label><select name="doc_tipo"
                            class="form-select" required><?php foreach ($tipos_documento as $tipo): ?><option
                                value="<?= (int) $tipo['id_tipo_doc'] ?>"><?= $e($tipo['nombre']) ?></option>
                            <?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Número</label><input name="doc_numero"
                            class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Fecha</label><input type="date" name="doc_fecha"
                            class="form-control"></div>
                    <div class="col-md-9"><label class="form-label">Asunto del documento</label><input name="doc_asunto"
                            class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Folios</label><input type="number" min="1"
                            name="doc_folios" class="form-control" value="1" required></div>
                </div>
            </div>
            <div class="card wizard-step d-none">
                <div class="card-header"><b>4. Archivos</b></div>
                <div class="card-body">
                    <input id="archivos" type="file" name="archivos[]" class="form-control" multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <small class="text-muted">PDF, DOC, JPG o PNG. Máximo 10 MB por archivo.</small>
                    <div id="preview-archivos" class="mt-3"></div>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <button type="button" id="wizard-prev" class="btn btn-outline-secondary d-none">Anterior</button>
                <button type="button" id="wizard-next" class="btn btn-primary ms-auto">Siguiente</button>
                <button type="submit" id="wizard-submit" class="btn btn-success ms-auto d-none">Registrar
                    expediente</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/public/assets/js/expediente/create.js"></script>