$(document).ready(function() {
    const csrfToken = $('#csrf_token').val();

    const table = $('#tblTiposDoc').DataTable({
        ajax: {
            url: '/tipos-documento',
            type: 'GET'
        },
        columns: [
            { data: 'id' },
            { data: 'codigo', render: data => `<strong>${data}</strong>` },
            { data: 'nombre' },
            { data: 'descripcion', render: data => data || '<em class="text-muted">Sin descripción</em>' },
            { 
                data: 'requiere_archivo', 
                render: data => data == 1 
                    ? '<span class="badge badge-info">Sí</span>' 
                    : '<span class="badge badge-secondary">No</span>' 
            },
            { 
                data: 'estado', 
                render: (data, type, row) => {
                    const checked = data == 1 ? 'checked' : '';
                    return `
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input btn-estado" id="sw_${row.id}" data-id="${row.id}" ${checked}>
                            <label class="custom-control-label" for="sw_${row.id}">${data == 1 ? 'Activo' : 'Inactivo'}</label>
                        </div>`;
                }
            },
            {
                data: null,
                render: (data, type, row) => `
                    <button class="btn btn-sm btn-warning btn-editar" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                `
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });

    function resetForm() {
        $('#frmTipoDoc')[0].reset();
        $('#tipo_id').val('');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');$('#requiere_archivo').prop('checked', true);
    }

    $('#btnNuevo').click(function() {
        resetForm();
        $('#modalTitle').text('Nuevo Tipo de Documento');
        $('#modalTipoDoc').modal('show');
    });

    $('#frmTipoDoc').submit(function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');$('.invalid-feedback').text('');

        const id = $('#tipo_id').val();
        const url = id ? `/tipos-documento/${id}` : '/tipos-documento';

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#modalTipoDoc').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('¡Éxito!', res.message, 'success');
                } else if (res.errors) {
                    $.each(res.errors, function(field, msg) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#err-${field}`).text(msg);
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    });

    $('#tblTiposDoc').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        resetForm();

        $.get(`/tipos-documento/${id}`, function(res) {
            if (res.success) {
                const data = res.data;
                $('#tipo_id').val(data.id);
                $('#codigo').val(data.codigo);
                $('#nombre').val(data.nombre);
                $('#descripcion').val(data.descripcion);
                $('#requiere_archivo').prop('checked', data.requiere_archivo == 1);
                $('#modalTitle').text('Editar Tipo de Documento');
                $('#modalTipoDoc').modal('show');
            }
        });
    });

    $('#tblTiposDoc').on('change', '.btn-estado', function() {
        const id = $(this).data('id');
        const estado = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: `/tipos-documento/${id}/estado`,
            type: 'POST',
            data: { estado: estado, csrf_token: csrfToken },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    table.ajax.reload(null, false);
                }
            }
        });
    });

    $('#tblTiposDoc').on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Está seguro?',
            text: 'Si el registro está vinculado a expedientes, pasará a estar inactivo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/tipos-documento/${id}/eliminar`,
                    type: 'POST',
                    data: { csrf_token: csrfToken },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            table.ajax.reload(null, false);
                            Swal.fire('Procesado', res.message, 'success');
                        }
                    }
                });
            }
        });
    });
});
