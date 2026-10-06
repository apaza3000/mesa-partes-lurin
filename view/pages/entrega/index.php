<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Entrega de Expediente - Mesa de Partes Virtual</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.entrega-page {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 40px 15px;
        }

        .entrega-box {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .3);
            border: none;
            overflow: hidden;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            text-align: center;
            padding: 25px;
        }

        .card-header i.fa-file-upload {
            font-size: 2.4rem;
            color: #1e3c72;
            margin-bottom: 8px;
        }

        .card-header .logo-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 5px;
        }

        .card-header .logo-subtitle {
            color: #777;
            font-size: .95rem;
        }

        .btn-primary {
            background: #1e3c72;
            border-color: #1e3c72;
        }

        .btn-primary:hover {
            background: #2a5298;
            border-color: #2a5298;
        }

        .invalid-feedback {
            display: block;
        }

        .top-actions {
            text-align: right;
            margin-bottom: 15px;
        }

        .top-actions a {
            color: #fff;
            font-weight: 600;
        }

        /* ===== Stepper ===== */
        .stepper {
            display: flex;
            justify-content: space-between;
            background: #f8f9fa;
            padding: 20px 30px;
            position: relative;
        }

        .stepper::before {
            content: "";
            position: absolute;
            top: 40px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #dee2e6;
            z-index: 0;
        }

        .step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }

        .step .circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #dee2e6;
            color: #777;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 700;
            font-size: 1rem;
            transition: all .3s;
        }

        .step.active .circle {
            background: #1e3c72;
            color: #fff;
            box-shadow: 0 4px 12px rgba(30, 60, 114, .4);
        }

        .step.done .circle {
            background: #28a745;
            color: #fff;
        }

        .step .label {
            font-size: .82rem;
            color: #777;
            font-weight: 600;
        }

        .step.active .label {
            color: #1e3c72;
        }

        .step.done .label {
            color: #28a745;
        }

        /* ===== Pasos ===== */
        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
            animation: fadeIn .35s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: .95rem;
            font-weight: 600;
            color: #1e3c72;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 0 0 15px;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f1f5fb;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 8px;
            font-size: .9rem;
        }

        .file-item i.fa-file {
            color: #1e3c72;
            margin-right: 10px;
        }

        .file-item .remove-file {
            color: #dc3545;
            cursor: pointer;
            border: none;
            background: none;
        }

        .resumen-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .resumen-box h6 {
            color: #1e3c72;
            font-weight: 700;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 6px;
        }

        .resumen-box p {
            margin-bottom: 5px;
            font-size: .92rem;
        }

        .resumen-box p strong {
            color: #333;
            min-width: 110px;
            display: inline-block;
        }
    </style>
</head>

<body class="entrega-page">

    <div class="entrega-box">

        <div class="top-actions">
            <a href="/"><i class="fas fa-home"></i> Volver al inicio</a>
        </div>

        <div class="card">

            <div class="card-header">
                <i class="fas fa-file-upload"></i>
                <div class="logo-title">Entrega de Expediente</div>
                <div class="logo-subtitle">Presenta tu solicitud o documento sin necesidad de registrarte</div>
            </div>

            <!-- ===== Stepper ===== -->
            <div class="stepper">
                <div class="step active" data-step="1">
                    <div class="circle">1</div>
                    <div class="label">Remitente</div>
                </div>
                <div class="step" data-step="2">
                    <div class="circle">2</div>
                    <div class="label">Expediente</div>
                </div>
                <div class="step" data-step="3">
                    <div class="circle">3</div>
                    <div class="label">Adjuntos</div>
                </div>
                <div class="step" data-step="4">
                    <div class="circle">4</div>
                    <div class="label">Confirmar</div>
                </div>
            </div>

            <form method="POST" action="/entregas/store" enctype="multipart/form-data" id="formEntrega" novalidate>
                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Corrige los siguientes errores:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $campo => $mensajes): ?>
                                    <?php foreach ((array) $mensajes as $msg): ?>
                                        <li><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- ============================================================
                     PASO 1: DATOS DEL REMITENTE
                     ============================================================ -->
                    <div class="step-panel active" data-step="1">
                        <div class="section-title"><i class="fas fa-user"></i> Datos del remitente</div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipo de Documento *</label>
                                    <select name="tipo_documento"
                                        class="form-control <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>">
                                        <?php $td = $datos_viejos['tipo_documento'] ?? 'DNI'; ?>
                                        <?php foreach (['DNI', 'CE', 'Pasaporte', 'Otro'] as $t): ?>
                                            <option value="<?= $t ?>" <?= $td === $t ? 'selected' : '' ?>><?= $t ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['tipo_documento'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['tipo_documento']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Número de Documento *</label>
                                    <input type="text" name="numero_documento"
                                        class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['numero_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['numero_documento'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['numero_documento']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombres *</label>
                                    <input type="text" name="nombres"
                                        class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['nombres'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['nombres']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido Paterno *</label>
                                    <input type="text" name="apellido_paterno"
                                        class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['apellido_paterno'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['apellido_paterno']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido Materno</label>
                                    <input type="text" name="apellido_materno" class="form-control"
                                        value="<?= htmlspecialchars($datos_viejos['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Correo Electrónico *</label>
                                    <input type="email" name="email"
                                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" name="telefono" class="form-control"
                                        value="<?= htmlspecialchars($datos_viejos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="direccion" class="form-control"
                                value="<?= htmlspecialchars($datos_viejos['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <!-- ============================================================
                     PASO 2: DATOS DEL EXPEDIENTE + DOCUMENTO
                     ============================================================ -->
                    <div class="step-panel" data-step="2">
                        <div class="section-title"><i class="fas fa-folder-open"></i> Datos del expediente</div>

                        <div class="form-group">
                            <label>Asunto del Expediente *</label>
                            <input type="text" name="asunto"
                                class="form-control <?= isset($errors['asunto']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($datos_viejos['asunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="500">
                            <?php if (isset($errors['asunto'])): ?>
                                <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['asunto']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prioridad *</label>
                                    <select name="prioridad"
                                        class="form-control <?= isset($errors['prioridad']) ? 'is-invalid' : '' ?>">
                                        <?php $pr = $datos_viejos['prioridad'] ?? 'Normal'; ?>
                                        <option value="Normal" <?= $pr === 'Normal' ? 'selected' : '' ?>>Normal</option>
                                        <option value="Urgente" <?= $pr === 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                                    </select>
                                    <?php if (isset($errors['prioridad'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['prioridad']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mt-3"><i class="fas fa-file-alt"></i> Documento principal</div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipo de Documento *</label>
                                    <select name="doc_tipo"
                                        class="form-control <?= isset($errors['doc_tipo']) ? 'is-invalid' : '' ?>">
                                        <option value="">— Seleccione —</option>
                                        <?php $dt = $datos_viejos['doc_tipo'] ?? ''; ?>
                                        <?php foreach ($tiposDocumento as $t): ?>
                                            <option value="<?= $t['id_tipo_doc'] ?>" <?= (string)$dt === (string)$t['id_tipo_doc'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['doc_tipo'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['doc_tipo']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Número (opcional)</label>
                                    <input type="text" name="doc_numero" class="form-control" maxlength="100"
                                        value="<?= htmlspecialchars($datos_viejos['doc_numero'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha del documento</label>
                                    <input type="date" name="doc_fecha" class="form-control"
                                        value="<?= htmlspecialchars($datos_viejos['doc_fecha'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Asunto del Documento *</label>
                                    <input type="text" name="doc_asunto"
                                        class="form-control <?= isset($errors['doc_asunto']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['doc_asunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        maxlength="500">
                                    <?php if (isset($errors['doc_asunto'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['doc_asunto']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Folios *</label>
                                    <input type="number" name="doc_folios" class="form-control"
                                        value="<?= htmlspecialchars($datos_viejos['doc_folios'] ?? '1', ENT_QUOTES, 'UTF-8') ?>"
                                        min="1">
                                    <?php if (isset($errors['doc_folios'])): ?>
                                        <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['doc_folios']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                     PASO 3: ADJUNTOS (N archivos para el documento único)
                     ============================================================ -->
                    <div class="step-panel" data-step="3">
                        <div class="section-title"><i class="fas fa-paperclip"></i> Archivos adjuntos del documento</div>

                        <p class="text-muted small">
                            Formatos permitidos: <strong>PDF, DOC, DOCX, JPG, PNG</strong>. Tamaño máximo: <strong>10 MB</strong> por archivo.
                        </p>

                        <?php if (isset($errors['adjuntos'])): ?>
                            <div class="alert alert-danger py-2">
                                *<?= implode('<br>', (array)$errors['adjuntos']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="font-weight-bold">Seleccionar archivos:</label>
                            <input type="file" name="adjuntos[]" id="inputAdjuntos"
                                class="form-control-file" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>

                        <div id="listaAdjuntos" class="mt-3"></div>
                    </div>

                    <!-- ============================================================
                     PASO 4: CONFIRMACIÓN
                     ============================================================ -->
                    <div class="step-panel" data-step="4">
                        <div class="section-title"><i class="fas fa-check-circle"></i> Confirma tu información</div>

                        <div class="resumen-box">
                            <h6><i class="fas fa-user"></i> Remitente</h6>
                            <p><strong>Documento:</strong> <span id="res_tipo_doc"></span> <span id="res_num_doc"></span></p>
                            <p><strong>Nombres:</strong> <span id="res_nombres"></span></p>
                            <p><strong>Correo:</strong> <span id="res_email"></span></p>
                            <p><strong>Teléfono:</strong> <span id="res_telefono"></span></p>
                        </div>

                        <div class="resumen-box">
                            <h6><i class="fas fa-folder-open"></i> Expediente</h6>
                            <p><strong>Asunto:</strong> <span id="res_asunto"></span></p>
                            <p><strong>Prioridad:</strong> <span id="res_prioridad"></span></p>
                        </div>

                        <div class="resumen-box">
                            <h6><i class="fas fa-file-alt"></i> Documento</h6>
                            <p><strong>Tipo:</strong> <span id="res_doc_tipo"></span></p>
                            <p><strong>Asunto:</strong> <span id="res_doc_asunto"></span></p>
                            <p><strong>Folios:</strong> <span id="res_doc_folios"></span></p>
                        </div>

                        <div class="resumen-box">
                            <h6><i class="fas fa-paperclip"></i> Adjuntos</h6>
                            <ul id="res_adjuntos" class="mb-0 pl-3"></ul>
                        </div>

                        <div class="form-check mt-4">
                            <input type="checkbox" name="terminos" id="terminos"
                                class="form-check-input <?= isset($errors['terminos']) ? 'is-invalid' : '' ?>">
                            <label class="form-check-label" for="terminos">
                                Declaro bajo juramento que la información y documentos presentados son verídicos.
                            </label>
                            <?php if (isset($errors['terminos'])): ?>
                                <div class="invalid-feedback d-block">*<?= implode('<br>', (array)$errors['terminos']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between">
                    <button type="button" id="btnPrev" class="btn btn-secondary" disabled>
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <div>
                        <a href="/" class="btn btn-outline-secondary mr-2">Cancelar</a>
                        <button type="button" id="btnNext" class="btn btn-primary">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" id="btnSubmit" class="btn btn-success d-none">
                            <i class="fas fa-paper-plane"></i> Enviar Expediente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* ============================================================
         * CONTROL DEL WIZARD
         * ============================================================ */
        let pasoActual = 1;
        const totalPasos = 4;

        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const btnSubmit = document.getElementById('btnSubmit');

        function mostrarPaso(paso) {
            document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
            document.querySelector(`.step-panel[data-step="${paso}"]`).classList.add('active');

            document.querySelectorAll('.stepper .step').forEach(s => {
                const n = parseInt(s.dataset.step);
                s.classList.remove('active', 'done');
                if (n < paso) s.classList.add('done');
                if (n === paso) s.classList.add('active');
            });

            btnPrev.disabled = paso === 1;

            if (paso === totalPasos) {
                btnNext.classList.add('d-none');
                btnSubmit.classList.remove('d-none');
                actualizarResumen();
            } else {
                btnNext.classList.remove('d-none');
                btnSubmit.classList.add('d-none');
            }

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        /* Validación HTML5 por paso */
        function validarPaso(paso) {
            const panel = document.querySelector(`.step-panel[data-step="${paso}"]`);
            const campos = panel.querySelectorAll('input, select, textarea');
            let valido = true;

            campos.forEach(c => {
                if (!c.checkValidity()) {
                    c.classList.add('is-invalid');
                    valido = false;
                } else {
                    c.classList.remove('is-invalid');
                }
            });

            // Reglas específicas por paso
            if (paso === 3) {
                const input = document.getElementById('inputAdjuntos');
                if (!input.files || input.files.length === 0) {
                    alert('Debe adjuntar al menos un archivo.');
                    valido = false;
                }
            }

            return valido;
        }

        btnNext.addEventListener('click', () => {
            if (validarPaso(pasoActual)) {
                pasoActual++;
                mostrarPaso(pasoActual);
            }
        });

        btnPrev.addEventListener('click', () => {
            if (pasoActual > 1) {
                pasoActual--;
                mostrarPaso(pasoActual);
            }
        });

        /* ============================================================
         * LISTA DE ADJUNTOS
         * ============================================================ */
        document.getElementById('inputAdjuntos').addEventListener('change', function() {
            const cont = document.getElementById('listaAdjuntos');
            cont.innerHTML = '';

            if (this.files.length > 0) {
                const titulo = document.createElement('label');
                titulo.className = 'font-weight-bold';
                titulo.textContent = `Archivos seleccionados (${this.files.length}):`;
                cont.appendChild(titulo);

                Array.from(this.files).forEach(f => {
                    const div = document.createElement('div');
                    div.className = 'file-item';
                    div.innerHTML = `
                <span><i class="fas fa-file"></i> ${f.name} <small class="text-muted">(${(f.size/1024/1024).toFixed(2)} MB)</small></span>
            `;
                    cont.appendChild(div);
                });
            }
        });

        /* ============================================================
         * RESUMEN ANTES DE ENVIAR
         * ============================================================ */
        function actualizarResumen() {
            const get = n => document.querySelector(`[name="${n}"]`)?.value || '—';

            document.getElementById('res_tipo_doc').textContent = get('tipo_documento');
            document.getElementById('res_num_doc').textContent = get('numero_documento');
            document.getElementById('res_nombres').textContent = `${get('nombres')} ${get('apellido_paterno')} ${get('apellido_materno')}`.trim();
            document.getElementById('res_email').textContent = get('email');
            document.getElementById('res_telefono').textContent = get('telefono') || '—';

            document.getElementById('res_asunto').textContent = get('asunto');
            document.getElementById('res_prioridad').textContent = get('prioridad');

            const selDoc = document.querySelector('[name="doc_tipo"]');
            document.getElementById('res_doc_tipo').textContent = selDoc.options[selDoc.selectedIndex]?.text || '—';
            document.getElementById('res_doc_asunto').textContent = get('doc_asunto');
            document.getElementById('res_doc_folios').textContent = get('doc_folios');

            const ul = document.getElementById('res_adjuntos');
            ul.innerHTML = '';
            const input = document.getElementById('inputAdjuntos');
            if (input.files.length > 0) {
                Array.from(input.files).forEach(f => {
                    const li = document.createElement('li');
                    li.textContent = f.name;
                    ul.appendChild(li);
                });
            } else {
                ul.innerHTML = '<li class="text-muted">Sin archivos</li>';
            }
        }

        /* Validación final antes de enviar */
        document.getElementById('formEntrega').addEventListener('submit', function(e) {
            const terminos = document.getElementById('terminos');
            if (!terminos.checked) {
                e.preventDefault();
                alert('Debe aceptar la declaración jurada para continuar.');
                return;
            }
            if (!validarPaso(1) || !validarPaso(2) || !validarPaso(3)) {
                e.preventDefault();
                alert('Complete correctamente todos los pasos.');
            }
        });

        /* Si hay errores del backend, abrir el paso correspondiente */
        <?php if (!empty($errors)): ?>
            <?php
            $pasoConError = 1;
            if (isset($errors['asunto']) || isset($errors['prioridad']) || isset($errors['doc_tipo']) || isset($errors['doc_asunto']) || isset($errors['doc_folios'])) {
                $pasoConError = 2;
            }
            if (isset($errors['adjuntos'])) {
                $pasoConError = 3;
            }
            if (isset($errors['terminos'])) {
                $pasoConError = 4;
            }
            ?>
            pasoActual = <?= $pasoConError ?>;
            mostrarPaso(pasoActual);
        <?php else: ?>
            mostrarPaso(1);
        <?php endif; ?>
    </script>

</body>

</html>