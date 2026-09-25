<?php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="mb-0 fs-3">Personas remitentes</h1><a href="/personas/crear" class="btn btn-primary"><i
                class="bi bi-person-plus me-1"></i>Nueva persona</a>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Registro de remitentes</h3>
            </div>
            <div class="card-body">
                <div class="input-group mb-3" style="max-width: 520px"><span class="input-group-text"><i
                            class="bi bi-search"></i></span><input id="buscar-documento" type="search"
                        class="form-control" placeholder="Buscar por DNI o RUC" inputmode="numeric"><button
                        id="btn-buscar-persona" class="btn btn-outline-primary" type="button">Buscar</button></div>
                <div id="resultado-busqueda" class="mb-3"></div>
                <div class="table-responsive">
                    <table id="tabla-personas" class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Nombre / razón social</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($personas as $persona): ?><tr>
                                <td><?= $e($persona['tipo_documento']) ?>: <?= $e($persona['numero_documento']) ?></td>
                                <td><?= $e($persona['tipo_persona']) ?></td>
                                <td><?= $e($persona['tipo_persona'] === 'Natural' ? trim($persona['nombres'].' '.$persona['apellido_paterno'].' '.$persona['apellido_materno']) : $persona['razon_social']) ?>
                                </td>
                                <td><?= $e($persona['email']) ?></td>
                                <td><span
                                        class="badge text-bg-<?= $persona['estado'] === 'Activo' ? 'success' : 'secondary' ?>"><?= $e($persona['estado']) ?></span>
                                </td>
                                <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" title="Ver"
                                        href="/personas/<?= (int) $persona['id_persona'] ?>"><i
                                            class="bi bi-eye"></i></a> <a class="btn btn-sm btn-outline-primary"
                                        title="Editar" href="/personas/<?= (int) $persona['id_persona'] ?>/editar"><i
                                            class="bi bi-pencil"></i></a></td>
                            </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<script>
document.addEventListener('DOMContentLoaded', () => {
    new DataTable('#tabla-personas', {
        language: {
            search: 'Filtrar:',
            emptyTable: 'No hay personas registradas',
            zeroRecords: 'No se encontraron coincidencias'
        }
    });
    const input = document.querySelector('#buscar-documento');
    const resultado = document.querySelector('#resultado-busqueda');
    const buscar = async () => {
        const numero = input.value.trim();
        if (!numero) {
            resultado.innerHTML = '<div class="alert alert-warning">Ingrese un DNI o RUC.</div>';
            return;
        }
        try {
            const response = await fetch('/personas/buscar?numero_documento=' + encodeURIComponent(
                numero), {
                headers: {
                    Accept: 'application/json'
                }
            });
            const data = await response.json();
            resultado.innerHTML = data.encontrado ?
                '<div class="alert alert-success">Encontrado: <a href="/personas/' + data.persona
                .id_persona + '">' + (data.persona.razon_social || data.persona.nombres + ' ' + data
                    .persona.apellido_paterno) + '</a></div>' :
                '<div class="alert alert-info">No existe una persona activa con ese documento.</div>';
        } catch {
            resultado.innerHTML =
                '<div class="alert alert-danger">No se pudo realizar la búsqueda.</div>';
        }
    };
    document.querySelector('#btn-buscar-persona').addEventListener('click', buscar);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            buscar();
        }
    });
    <?php if (!empty($_SESSION['flash'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Listo',
        text: <?= json_encode($_SESSION['flash']) ?>,
        timer: 2200,
        showConfirmButton: false
    });
    <?php unset($_SESSION['flash']); endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'No disponible',
        text: <?= json_encode($_SESSION['flash_error']) ?>
    });
    <?php unset($_SESSION['flash_error']); endif; ?>
});
</script>