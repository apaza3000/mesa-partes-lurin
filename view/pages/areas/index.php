<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Gestión de Áreas</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="plantilla.php?p=inicio">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Áreas</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="card-title">Listado de Áreas Institucionales</h3>
                            </div>
                            <div class="col-6 text-end">
                                <!-- Botón que lleva a la página independiente de creación -->
                                <a href="plantilla.php?p=areas/crear" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle-fill me-1"></i> Nueva Área
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                               <thead>
                                   <tr>
                                       <th>#</th>
                                       <th>Nombre del Área</th>
                                       <th>Siglas</th>
                                       <th>Estado</th>
                                       <th class="text-center">Acciones</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php if (!$areas): ?>
                                       <tr><td colspan="5" class="text-center py-4">No hay áreas registradas.</td></tr>
                                   <?php else: ?>
                                       <?php foreach ($areas as $indice => $item): ?>
                                           <tr>
                                               <td><?= $indice + 1 ?></td>
                                               <td><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                               <td><?= htmlspecialchars($item['siglas'], ENT_QUOTES, 'UTF-8') ?></td>
                                               <td><span class="badge bg-<?= $item['estado'] === 'Activo' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($item['estado'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                               <td class="text-center">
                                                   <a href="plantilla.php?p=areas/editar&id=<?= (int) $item['id_area'] ?>" class="btn btn-sm btn-warning text-white" title="Editar">
                                                       <i class="bi bi-pencil-square"></i>
                                                   </a>
                                                   <?php if ($item['estado'] === 'Activo'): ?>
                                                       <form action="plantilla.php?p=areas/eliminar" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta área?');">
                                                           <input type="hidden" name="id_area" value="<?= (int) $item['id_area'] ?>">
                                                           <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                                       </form>
                                                   <?php endif; ?>
                                               </td>
                                           </tr>
                                       <?php endforeach; ?>
                                   <?php endif; ?>
                               </tbody>
                            </table>
                        <?php if (!empty($_SESSION['area_mensaje']) || !empty($_SESSION['area_error'])): ?>
                            <div class="container-fluid mt-3">
                                <div class="alert alert-<?= !empty($_SESSION['area_error']) ? 'danger' : 'success' ?>">
                                    <?= htmlspecialchars($_SESSION['area_error'] ?? $_SESSION['area_mensaje'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <?php unset($_SESSION['area_error'], $_SESSION['area_mensaje']); ?>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>