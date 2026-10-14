<?php
$areas = $areas ?? [];
$roles = $roles ?? [];
?>
<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Registrar Nuevo Usuario</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($errors['db'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errors['db'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card card-primary card-outline mb-4">

                    <form id="form-crear-usuario" action="/usuarios/store" method="POST" class="needs-validation"
                        novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <!-- SECCIÓN 1: DATOS PERSONALES -->
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus me-1"></i> Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Tipo Documento -->
                                <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                                    <label for="tipo_documento" class="form-label">Tipo Documento</label>
                                    <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                                        <option value="DNI">DNI</option>
                                        <option value="CE">CE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>

                                <!-- N° Documento con Botón de Búsqueda -->
                                <div class="col-md-4 col-lg-4 col-sm-6 mb-3">
                                    <label for="numero_documento" class="form-label">N° Documento</label>
                                    <div class="input-group">
                                        <input type="text" name="numero_documento" id="numero_documento"
                                            class="form-control" required maxlength="20"
                                            placeholder="Ingrese número...">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-buscar-persona"
                                            title="Buscar persona">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" id="info-busqueda">Ingrese DNI para verificar si
                                        existe.</small>
                                </div>

                                <!-- Correo Electrónico -->
                                <div class="col-md-4 col-lg-5 col-sm-12 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control persona-field"
                                        required disabled placeholder="ejemplo@correo.com">
                                </div>
                            </div>

                            <div class="row">
                                <!-- Nombres -->
                                <div class="col-md-4 mb-3">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" name="nombres" id="nombres" class="form-control persona-field"
                                        required disabled>
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="col-md-4 mb-3">
                                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno"
                                        class="form-control persona-field" required disabled>
                                </div>

                                <!-- Apellido Materno -->
                                <div class="col-md-4 mb-3">
                                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" id="apellido_materno"
                                        class="form-control persona-field" disabled>
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-4 mb-3">
                                    <label for="telefono" class="form-label">Teléfono celular</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control persona-field"
                                        disabled>
                                </div>

                                <!-- Direccion -->
                                <div class="col-md-8 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" name="direccion" id="direccion"
                                        class="form-control persona-field" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="my-0">

                        <!-- SECCIÓN 2: CREDENCIALES Y ASIGNACIÓN -->
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key me-1"></i> Credenciales y Asignación</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Usuario (Username)</label>
                                    <input type="text" name="username" id="username" class="form-control" required
                                        placeholder="jdoe">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" name="password" id="password" class="form-control" required
                                        minlength="8">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="id_area" class="form-label">Área Asignada</label>
                                    <select name="id_area" id="id_area" class="form-select" required>
                                        <option value="" disabled selected>Seleccione Un Área</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?= $area['id_area'] ?>">
                                                <?= htmlspecialchars($area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="id_rol" class="form-label">Rol del Usuario</label>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="" disabled selected>Seleccione Un Rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id_rol'] ?>">
                                                <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i> Guardar Usuario
                            </button>
                            <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script AJAX para consultar la persona -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnBuscar = document.getElementById('btn-buscar-persona');
        const inputDoc = document.getElementById('numero_documento');
        const selectTipoDoc = document.getElementById('tipo_documento');
        const infoBusqueda = document.getElementById('info-busqueda');

        const camposPersona = {
            nombres: document.getElementById('nombres'),
            apellido_paterno: document.getElementById('apellido_paterno'),
            apellido_materno: document.getElementById('apellido_materno'),
            email: document.getElementById('email'),
            telefono: document.getElementById('telefono'),
            direccion: document.getElementById('direccion')
        };

        function bloquearTodo(bloquear) {
            Object.values(camposPersona).forEach(input => {
                input.disabled = bloquear;
            });
        }

        btnBuscar.addEventListener('click', function () {
            let tipo = selectTipoDoc.value;
            let num = inputDoc.value.trim();

            if (!num) {
                infoBusqueda.innerHTML = '<span class="text-danger">Por favor ingrese un número de documento para buscar.</span>';
                return;
            }

            // Petición AJAX al backend
            fetch(`/api/personas/buscar?tipo=${tipo}&numero=${num}`)
                .then(response => response.json())
                .then(data => {
                    if (data.encontrado) {
                        if (data.tiene_usuario) {
                            // Cambiamos el alert por un mensaje claro en el texto de estado
                            bloquearTodo(true);
                            limpiarCampos();
                            infoBusqueda.innerHTML = '<span class="text-danger">Esta persona ya cuenta con un usuario registrado en el sistema. Imposible duplicar.</span>';
                        } else {
                            // Encontrado pero sin usuario: Llenar y evaluar campo por campo
                            let persona = data.persona;

                            for (let campo in camposPersona) {
                                let valor = persona[campo] || '';
                                camposPersona[campo].value = valor;

                                if (valor.trim() !== '') {
                                    camposPersona[campo].disabled = true;
                                } else {
                                    camposPersona[campo].disabled = false;
                                }
                            }

                            infoBusqueda.innerHTML = '<span class="text-success">¡Persona encontrada! Los datos existentes están bloqueados y los vacíos habilitados para completar.</span>';
                        }
                    } else {
                        // No encontrado: Liberar todos los campos en blanco para llenado completo
                        limpiarCampos();
                        bloquearTodo(false);
                        infoBusqueda.innerHTML = '<span class="text-warning">Persona no registrada. Complete los datos libremente.</span>';
                    }
                })
                .catch(error => console.error('Error en la búsqueda:', error));
        });

        function limpiarCampos() {
            Object.values(camposPersona).forEach(input => {
                input.value = '';
            });
        }
    });
</script>