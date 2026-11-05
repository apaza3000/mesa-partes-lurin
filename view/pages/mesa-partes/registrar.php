<?php
/**
 * Vista: Registro de Documento en Ventanilla (Mesa de Partes) — TAREA E02-1
 * Variables recibidas:
 * @var string $titulo
 * @var array $areas
 * @var array $tiposDocumento
 * @var array $errors
 * @var array $datos_viejos
 * @var string|null $flash_success
 * @var string|null $flash_error
 */

$areas = $areas ?? [];
$tiposDocumento = $tiposDocumento ?? [];
$errors = $errors ?? [];
$datos = $datos_viejos ?? [];

$e = fn(?string $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn(string $k, string $def = ''): string => (string) ($datos[$k] ?? $def);
$err = fn(string $k): ?string => isset($errors[$k]) && is_array($errors[$k]) ? (string) ($errors[$k][0] ?? '') : (isset($errors[$k]) && is_string($errors[$k]) ? $errors[$k] : null);
$invalid = fn(string $k): string => $err($k) ? ' is-invalid' : '';

$tipoPersona = $val('tipo_persona', 'Natural');
$canal = $val('canal_ingreso', 'Presencial');
$tieneAdjuntos = !empty($errors['adjuntos']);
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-0 fs-3">
                    <i class="bi bi-window-desktop text-primary me-2"></i>Registro de Documento en Ventanilla
                </h1>
                <p class="text-muted mb-0">Recepción presencial o interna de documentos dirigidos a la Municipalidad de Lurín.</p>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <a href="/mesa-partes/bandeja" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-inbox-fill me-1"></i>Bandeja de Mesa de Partes
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $e($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i><?= $e($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 ps-3">
                    <?php foreach ((array) $errors['general'] as $errorGeneral): ?>
                        <li><?= $e(is_array($errorGeneral) ? implode(', ', $errorGeneral) : (string) $errorGeneral) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="/mesa-partes/registrar" enctype="multipart/form-data" id="form-ventanilla" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="id_persona_existente" id="id_persona_existente" value="">

            <div class="row g-3">

                <!-- ==================================================== -->
                <!-- COLUMNA IZQUIERDA: REMITENTE                          -->
                <!-- ==================================================== -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-person-vcard-fill text-primary me-2"></i>1. Remitente del documento</h6>
                        </div>
                        <div class="card-body">

                            <!-- Búsqueda en vivo -->
                            <div class="bg-light border rounded p-3 mb-3">
                                <label for="busqueda-remitente" class="form-label fw-bold small text-uppercase mb-1">
                                    <i class="bi bi-search me-1"></i>Búsqueda en vivo (DNI / RUC / nombre)
                                </label>
                                <input type="text" class="form-control" id="busqueda-remitente"
                                       placeholder="Escriba el documento o el nombre del remitente..." aria-describedby="ayuda-busqueda">
                                <div class="form-text" id="ayuda-busqueda">Mínimo 3 caracteres. Seleccione un resultado para precargar nombres y apellidos desde la base local.</div>
                                <div id="listado-remitentes" class="list-group mt-2 d-none"></div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="canal_ingreso" class="form-label">Canal de ingreso <span class="text-danger">*</span></label>
                                    <select class="form-select<?= $invalid('canal_ingreso') ?>" id="canal_ingreso" name="canal_ingreso" required>
                                        <option value="Presencial" <?= $canal === 'Presencial' ? 'selected' : '' ?>>Presencial (ventanilla)</option>
                                        <option value="Interno" <?= $canal === 'Interno' ? 'selected' : '' ?>>Interno (oficio entre áreas/instituciones)</option>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('canal_ingreso')) ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="tipo_persona" class="form-label">Tipo de persona <span class="text-danger">*</span></label>
                                    <select class="form-select<?= $invalid('tipo_persona') ?>" id="tipo_persona" name="tipo_persona" required>
                                        <option value="Natural" <?= $tipoPersona === 'Natural' ? 'selected' : '' ?>>Natural</option>
                                        <option value="Juridica" <?= $tipoPersona === 'Juridica' ? 'selected' : '' ?>>Jurídica</option>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('tipo_persona')) ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                                    <select class="form-select<?= $invalid('prioridad') ?>" id="prioridad" name="prioridad" required>
                                        <option value="Normal" <?= $val('prioridad', 'Normal') === 'Normal' ? 'selected' : '' ?>>Normal</option>
                                        <option value="Urgente" <?= $val('prioridad') === 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('prioridad')) ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="tipo_documento" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                                    <select class="form-select<?= $invalid('tipo_documento') ?>" id="tipo_documento" name="tipo_documento" required>
                                        <?php foreach (['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'] as $tipoDoc): ?>
                                            <option value="<?= $tipoDoc ?>" <?= $val('tipo_documento', 'DNI') === $tipoDoc ? 'selected' : '' ?>><?= $tipoDoc ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('tipo_documento')) ?></div>
                                </div>

                                <div class="col-md-5">
                                    <label for="numero_documento" class="form-label">Número de documento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control<?= $invalid('numero_documento') ?>" id="numero_documento"
                                           name="numero_documento" value="<?= $e($val('numero_documento')) ?>" maxlength="20" required>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('numero_documento')) ?></div>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary w-100" id="btn-buscar-reniec"
                                            title="Carga la persona si ya existe en la base; solo si no existe consulta RENIEC/SUNAT">
                                        <i class="bi bi-broadcast me-1"></i>Verificar&nbsp;BD/RENIEC
                                    </button>
                                </div>

                                <div class="col-12" id="mensaje-remitente" aria-live="polite"></div>

                                <!-- Persona Natural -->
                                <div class="col-md-5 campo-natural">
                                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control<?= $invalid('nombres') ?>" id="nombres" name="nombres"
                                           value="<?= $e($val('nombres')) ?>" maxlength="100">
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('nombres')) ?></div>
                                </div>
                                <div class="col-md-4 campo-natural">
                                    <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control<?= $invalid('apellido_paterno') ?>" id="apellido_paterno"
                                           name="apellido_paterno" value="<?= $e($val('apellido_paterno')) ?>" maxlength="100">
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('apellido_paterno')) ?></div>
                                </div>
                                <div class="col-md-3 campo-natural">
                                    <label for="apellido_materno" class="form-label">Apellido materno</label>
                                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno"
                                           value="<?= $e($val('apellido_materno')) ?>" maxlength="100">
                                </div>

                                <!-- Persona Jurídica -->
                                <div class="col-12 campo-juridica">
                                    <label for="razon_social" class="form-label">Razón social <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control<?= $invalid('razon_social') ?>" id="razon_social"
                                           name="razon_social" value="<?= $e($val('razon_social')) ?>" maxlength="200">
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('razon_social')) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control<?= $invalid('email') ?>" id="email" name="email"
                                           value="<?= $e($val('email')) ?>" maxlength="150">
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('email')) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono"
                                           value="<?= $e($val('telefono')) ?>" maxlength="30">
                                </div>

                                <div class="col-12">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="2" maxlength="255"><?= $e($val('direccion')) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================================================== -->
                <!-- COLUMNA DERECHA: DOCUMENTO Y DERIVACIÓN               -->
                <!-- ==================================================== -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>2. Documento recibido</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="doc_tipo" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                                    <select class="form-select<?= $invalid('doc_tipo') ?>" id="doc_tipo" name="doc_tipo" required>
                                        <option value="">-- Seleccione --</option>
                                        <?php foreach ($tiposDocumento as $tipo): ?>
                                            <option value="<?= (int) $tipo['id_tipo_doc'] ?>" <?= (string) $val('doc_tipo') === (string) $tipo['id_tipo_doc'] ? 'selected' : '' ?>>
                                                <?= $e($tipo['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('doc_tipo')) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="doc_numero" class="form-label">Número del documento físico</label>
                                    <input type="text" class="form-control" id="doc_numero" name="doc_numero"
                                           value="<?= $e($val('doc_numero')) ?>" maxlength="100" placeholder="Ej: Oficio N° 124-2026-MDL/GDU">
                                </div>

                                <div class="col-12">
                                    <label for="asunto" class="form-label">Asunto del expediente <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control<?= $invalid('asunto') ?>" id="asunto" name="asunto"
                                           value="<?= $e($val('asunto')) ?>" maxlength="500" required>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('asunto')) ?></div>
                                </div>

                                <div class="col-12">
                                    <label for="doc_asunto" class="form-label">Asunto específico del documento</label>
                                    <input type="text" class="form-control" id="doc_asunto" name="doc_asunto"
                                           value="<?= $e($val('doc_asunto')) ?>" maxlength="500" placeholder="Si se deja vacío se usa el asunto del expediente">
                                </div>

                                <div class="col-md-4">
                                    <label for="doc_folios" class="form-label">Folios exactos <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control<?= $invalid('doc_folios') ?>" id="doc_folios" name="doc_folios"
                                           value="<?= $e($val('doc_folios', '1')) ?>" min="1" max="9999" required>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('doc_folios')) ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="doc_fecha" class="form-label">Fecha del documento</label>
                                    <input type="date" class="form-control<?= $invalid('doc_fecha') ?>" id="doc_fecha" name="doc_fecha"
                                           value="<?= $e($val('doc_fecha')) ?>">
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('doc_fecha')) ?></div>
                                </div>

                                <div class="col-md-4">
                                    <label for="adjuntos" class="form-label">Escaneo / PDF <span class="text-danger<?= $tieneAdjuntos ? '' : ' *' ?>"></span></label>
                                    <input type="file" class="form-control<?= $invalid('adjuntos') ?>" id="adjuntos" name="adjuntos[]"
                                           accept="application/pdf,.pdf" multiple>
                                    <div class="invalid-feedback d-block">
                                        <?= $e((string) ($err('adjuntos') ?? 'Adjunte el escaneo del documento (PDF, máx. 10 MB por archivo).')) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-send-fill text-primary me-2"></i>3. Derivación inicial</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="id_area_destino" class="form-label">Área destino inicial</label>
                                    <select class="form-select<?= $invalid('id_area_destino') ?>" id="id_area_destino" name="id_area_destino">
                                        <option value="0">-- No derivar aún --</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?= (int) $area['id_area'] ?>" <?= (string) $val('id_area_destino') === (string) $area['id_area'] ? 'selected' : '' ?>>
                                                <?= $e(($area['siglas'] ?? '') . ' — ' . ($area['nombre'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback d-block"><?= $e((string) $err('id_area_destino')) ?></div>
                                    <div class="form-text">Se creará una derivación en estado <em>Pendiente</em> para la bandeja del área.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="instrucciones" class="form-label">Instrucciones de derivación</label>
                                    <textarea class="form-control" id="instrucciones" name="instrucciones" rows="3"
                                              maxlength="1000" placeholder="Ej: evaluar y emitir informe técnico en el plazo de 5 días."><?= $e($val('instrucciones')) ?></textarea>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-2 justify-content-end border-top pt-3">
                                    <a href="/dashboard" class="btn btn-light border">Cancelar</a>
                                    <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-eraser me-1"></i>Limpiar</button>
                                    <button type="submit" class="btn btn-primary px-4" id="btn-registrar">
                                        <i class="bi bi-check2-circle me-1"></i>Registrar y derivar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-ventanilla');
    const tipoPersona = document.getElementById('tipo_persona');
    const tipoDocumento = document.getElementById('tipo_documento');
    const numeroDocumento = document.getElementById('numero_documento');
    const camposNatural = document.querySelectorAll('.campo-natural');
    const camposJuridica = document.querySelectorAll('.campo-juridica');
    const mensaje = document.getElementById('mensaje-remitente');
    const buscador = document.getElementById('busqueda-remitente');
    const listado = document.getElementById('listado-remitentes');
    const idPersonaExistente = document.getElementById('id_persona_existente');
    const btnReniec = document.getElementById('btn-buscar-reniec');

    const escapar = (texto) => String(texto ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const mostrarMensaje = (tipo, texto) => {
        mensaje.innerHTML = '<div class="alert alert-' + tipo + ' py-2 mb-0">' + escapar(texto) + '</div>';
    };

    const alternarTipoPersona = () => {
        const esNatural = tipoPersona.value === 'Natural';
        camposNatural.forEach((el) => {
            el.style.display = esNatural ? '' : 'none';
            const input = el.querySelector('input');
            if (input && ['nombres', 'apellido_paterno'].includes(input.id)) {
                input.required = esNatural;
            }
        });
        camposJuridica.forEach((el) => {
            el.style.display = esNatural ? 'none' : '';
            const input = el.querySelector('input');
            if (input && input.id === 'razon_social') {
                input.required = !esNatural;
            }
        });
    };

    tipoPersona.addEventListener('change', alternarTipoPersona);
    alternarTipoPersona();

    const asignarRemitente = (persona) => {
        tipoPersona.value = persona.tipo_persona || 'Natural';
        alternarTipoPersona();
        tipoDocumento.value = persona.tipo_documento || 'DNI';
        numeroDocumento.value = persona.numero_documento || '';
        idPersonaExistente.value = persona.id_persona || '';

        const nombres = document.getElementById('nombres');
        const apPaterno = document.getElementById('apellido_paterno');
        const apMaterno = document.getElementById('apellido_materno');
        const razonSocial = document.getElementById('razon_social');

        if (tipoPersona.value === 'Natural') {
            nombres.value = persona.nombres || '';
            apPaterno.value = persona.apellido_paterno || '';
            apMaterno.value = persona.apellido_materno || '';
            razonSocial.value = '';
        } else {
            razonSocial.value = persona.razon_social || persona.nombre_completo || '';
            nombres.value = '';
            apPaterno.value = '';
            apMaterno.value = '';
        }

        document.getElementById('direccion').value = persona.direccion || '';
        document.getElementById('email').value = persona.email || '';
        document.getElementById('telefono').value = persona.telefono || '';
    };

    /* ---------- BÚSQUEDA EN VIVO (registro interno) ---------- */
    let temporizador = null;
    buscador.addEventListener('input', function () {
        const consulta = this.value.trim();
        clearTimeout(temporizador);

        if (consulta.length < 3) {
            listado.classList.add('d-none');
            listado.innerHTML = '';
            return;
        }

        temporizador = setTimeout(async () => {
            try {
                const respuesta = await fetch('/mesa-partes/personas/buscar?q=' + encodeURIComponent(consulta), {
                    headers: { Accept: 'application/json' }
                });
                const data = await respuesta.json();
                const resultados = data.resultados || [];

                if (!resultados.length) {
                    listado.innerHTML = '<button type="button" class="list-group-item list-group-item-action text-muted small" disabled>Sin registros en el sistema. Complete los datos y use «Verificar BD/RENIEC».</button>';
                    listado.classList.remove('d-none');
                    return;
                }

                listado.innerHTML = resultados.map((p) => `
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-start"
                            data-persona='${escapar(JSON.stringify(p))}'>
                        <span>
                            <i class="bi bi-person-badge text-primary me-1"></i>
                            <strong class="small">${escapar(p.nombre_completo)}</strong>
                            <span class="d-block text-muted" style="font-size:.75rem">${escapar(p.tipo_documento)}: ${escapar(p.numero_documento)}</span>
                        </span>
                        <span class="badge bg-light text-dark border">${escapar(p.tipo_persona)}</span>
                    </button>
                `).join('');
                listado.classList.remove('d-none');
            } catch (error) {
                listado.classList.add('d-none');
            }
        }, 350);
    });

    listado.addEventListener('click', function (evento) {
        const boton = evento.target.closest('[data-persona]');
        if (!boton) return;
        asignarRemitente(JSON.parse(boton.dataset.persona));
        listado.classList.add('d-none');
        listado.innerHTML = '';
        buscador.value = '';
        mostrarMensaje('success', 'Datos del remitente cargados desde el registro interno.');
    });

    /* ---------- VERIFICACIÓN: BASE LOCAL → RENIEC / SUNAT ----------
     * El endpoint empieza buscando en `personas`; el servicio externo
     * solo se invierte cuando la persona no existe aún en la base. */
    btnReniec.addEventListener('click', async function () {
        const tipo = tipoDocumento.value;
        const numero = numeroDocumento.value.trim();

        if (!['DNI', 'RUC'].includes(tipo)) {
            mostrarMensaje('warning', 'La verificación funciona con DNI o RUC.');
            return;
        }
        const longitud = tipo === 'DNI' ? 8 : 11;
        if (!new RegExp('^[0-9]{' + longitud + '}$').test(numero)) {
            mostrarMensaje('warning', 'Ingrese un ' + tipo + ' de ' + longitud + ' dígitos.');
            return;
        }

        btnReniec.disabled = true;
        btnReniec.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Buscando';
        try {
            const parametros = new URLSearchParams({ tipo_documento: tipo, numero_documento: numero });
            const respuesta = await fetch('/mesa-partes/personas/consultar?' + parametros.toString(), {
                headers: { Accept: 'application/json' }
            });
            const data = await respuesta.json();

            if (!respuesta.ok || !data.encontrado) {
                mostrarMensaje('danger', data.mensaje || 'No se encontraron datos para ese documento.');
                return;
            }

            // `asignarRemitente` usa data.id_persona: si viene de la BD local queda
            // como persona existente; si es externa no trae id y se deja vacío.
            asignarRemitente(Object.assign({ tipo_persona: data.data.tipo_persona }, data.data));
            if (data.origen !== 'interna') {
                idPersonaExistente.value = '';
            }
            mostrarMensaje(
                'success',
                data.origen === 'interna'
                    ? 'Remitente encontrado en la base local. No se consultó a RENIEC/SUNAT.'
                    : 'Datos encontrados en RENIEC/SUNAT. Verifique antes de registrar.'
            );
        } catch (error) {
            mostrarMensaje('danger', 'El servicio de consulta no está disponible. Intente más tarde.');
        } finally {
            btnReniec.disabled = false;
            btnReniec.innerHTML = '<i class="bi bi-broadcast me-1"></i>Verificar&nbsp;BD/RENIEC';
        }
    });

    form.addEventListener('submit', function () {
        const boton = document.getElementById('btn-registrar');
        // Se deshabilita tras despachar el envío para evitar dobles registros
        setTimeout(() => {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Registrando';
        }, 0);
    });
});
</script>
