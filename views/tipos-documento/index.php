<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesa de Partes - Tipos de Documento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Mantenimiento de Tipos de Documento</h3>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-primary" id="btnNuevo"><i class="fas fa-plus"></i> Nuevo Tipo</button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tblTiposDoc" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Req. Archivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalTipoDoc" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form id="frmTipoDoc" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Nuevo Tipo de Documento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tipo_id" name="id">
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label for="codigo">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="codigo" name="codigo" maxlength="20" required style="text-transform:uppercase;">
                        <small class="invalid-feedback" id="err-codigo"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                        <small class="invalid-feedback" id="err-nombre"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                        <small class="invalid-feedback" id="err-descripcion"></small>
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="requiere_archivo" name="requiere_archivo" value="1" checked>
                        <label class="custom-control-label" for="requiere_archivo">Requiere adjuntar archivo PDF</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Ruta absoluta limpia directa desde la raíz de Laragon -->
<script src="/js/tipos-documento/tipos-documento.js"></script>
</body>
</html>
